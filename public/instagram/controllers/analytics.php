<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class analytics extends MX_Controller {
	public function __construct(){
		parent::__construct();
		
		$this->tb_accounts = INSTAGRAM_ACCOUNTS;
		$this->tb_analytics = "instagram_analytics";
		$this->tb_analytics_stats = "instagram_analytics_stats";
		$this->module = get_class($this);
		$this->module_name = lang("instagram_accounts");
		$this->module_icon = "fa fa-instagram";
		$this->load->model($this->module.'_model', 'model');
		$this->accounts = $this->model->fetch("*", INSTAGRAM_ACCOUNTS, "uid = '".session("uid")."'");
	}

	public function index($ids = ""){
		$account = $this->model->get("*", $this->tb_accounts, "ids = '".$ids."' AND uid = '".session("uid")."'");

		if(empty($account)){
			$view = $this->load->view("analytics/ajax/empty", array(), true);
			return $this->template->build('analytics/index', array("view" => $view, "accounts" => $this->accounts));
		}
		
		//Check Start First Time
		$action_exist = $this->model->get("*", $this->tb_analytics, "account = '".$account->id."' AND '".session("uid")."'");
		if(empty($action_exist)){
			$proxy_data = get_proxy($this->tb_accounts, $account->proxy, $account);
			try {
				$ig = new InstagramAPI($account->username, $account->password, $proxy_data->use);
				$result = $ig->analytics->process();
				
				$user_timezone = get_timezone_user(NOW);
				$user_day = date("Y-m-d", strtotime($user_timezone));

				$check_stats_exist = $this->model->get("id", $this->tb_analytics_stats, " account = '".$account->id."' AND uid = '".session("uid")."' AND date = '".$user_day."'");
				if(empty($check_stats_exist)){

					//Save data
					$user_data = array(
						"media_count" => $result->userinfo->media_count,
						"follower_count" => $result->userinfo->follower_count,
						"following_count" => $result->userinfo->following_count,
						"engagement" => $result->engagement
					);

					
					$data = array(
						"ids" => ids(),
						"uid" => session("uid"),
						"account" => $account->id,
						"data" => json_encode($user_data),
						"date" => date("Y-m-d", strtotime($user_timezone))
					);

					$this->db->insert($this->tb_analytics_stats, $data);

					$save_info = array(
						"engagement" => $result->engagement,
						"average_likes" => $result->average_likes,
						"average_comments" => $result->average_comments,
						"top_hashtags" => $result->top_hashtags,
						"top_mentions" => $result->top_mentions,
						"feeds" => $result->feeds,
						"userinfo" => $result->userinfo,
					);
					
					//Next Action
					$now = date('Y-m-d 00:00:00', strtotime($user_timezone));
					$next_day = date('Y-m-d 00:00:00', strtotime($now) + 86400);
					$data_next_action = array(
						"ids" => ids(),
						"uid" => session("uid"),
						"account" => $account->id,
						"data" => json_encode($save_info),
						"next_action" => get_timezone_system($next_day)
					);

					$this->db->insert($this->tb_analytics, $data_next_action);
				}
			} catch (Exception $e) {
				
			}

		}

		$data = array(
			"result" => $this->model->get_stats($ids),
			"account" => $account
		);	


		if (!$this->input->is_ajax_request()) {
			$view = $this->load->view("analytics/ajax/analytics", $data, true);
			$this->template->build('analytics/index', array("view" => $view, "accounts" => $this->accounts));
		}else{
			$this->load->view("analytics/ajax/analytics", $data);
		}
		
	}

	/*
	* Ajax Functions
	*/
	public function ajax_add(){

		$address = post("address");
		$location = post("location");
		$limit = (int)post("limit");
		$package = (int)post("package");

		$proxy = $this->model->get("*", $this->tb_proxies, "address = '{$address}'");

		if($address == ""){
			ms(array(
				"status"  => "error",
				"message" => "Address is required"
			));
		}

		if(!empty($proxy)){
			ms(array(
				"status"  => "error",
				"message" => "This proxy already exists"
			));
		}
		
		if(!check_proxy($address)){
			ms(array(
				"status"  => "error",
				"message" => "Proxy is not valid or active"
			));
		}

		$data = array(
			'ids'   => ids(),
			'address'   => $address,
			'location'  => $location,
			'limit'  => $limit,
			'active' => 1,
			'status'  => 1,
			'changed'   => NOW,
			'created'   => NOW
		);

		$this->db->insert($this->tb_proxies, $data);

		ms(array(
			"status" => "success",
			"message" => lang("successfully")
		));

	}

	/****************************************/
	/* CRON                                 */
	/* Time cron: once_per_minute           */
	/****************************************/
	public function cron(){
		$schedule_list = $this->db->select('analytics.*, account.username, account.password, account.proxy, account.default_proxy, account.id as account_id')
		->from($this->tb_analytics." as analytics")
		->join($this->tb_accounts." as account", "analytics.account = account.id")
		->where("account.status = 1 AND analytics.next_action <= '".NOW."'")->limit(10,0)->get()->result();
		
		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				if(!permission("instagram/post", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}

				$proxy_data = get_proxy($this->tb_accounts, $schedule->proxy, $schedule);
				try {
					$ig = new InstagramAPI($schedule->username, $schedule->password, $proxy_data->use);
					$result = $ig->analytics->process();
					
					$user_timezone = get_timezone_user(NOW, false, $schedule->uid);
					$user_day = date("Y-m-d", strtotime($user_timezone));

					$check_stats_exist = $this->model->get("id", $this->tb_analytics_stats, " account = '".$schedule->account_id."' AND uid = '".$schedule->uid."' AND date = '".$user_day."'");
					if(empty($check_stats_exist)){

						//Save data
						$user_data = array(
							"media_count" => $result->userinfo->media_count,
							"follower_count" => $result->userinfo->follower_count,
							"following_count" => $result->userinfo->following_count,
							"engagement" => $result->engagement
						);

						
						$data = array(
							"ids" => ids(),
							"uid" => $schedule->uid,
							"account" => $schedule->account_id,
							"data" => json_encode($user_data),
							"date" => date("Y-m-d", strtotime($user_timezone))
						);

						$this->db->insert($this->tb_analytics_stats, $data);

						$save_info = array(
							"engagement" => $result->engagement,
							"average_likes" => $result->average_likes,
							"average_comments" => $result->average_comments,
							"top_hashtags" => $result->top_hashtags,
							"top_mentions" => $result->top_mentions,
							"feeds" => $result->feeds,
							"userinfo" => $result->userinfo,
						);
						
						//Next Action
						$now = date('Y-m-d 00:00:00', strtotime($user_timezone));
						$next_day = date('Y-m-d 00:00:00', strtotime($now) + 86400);
						$data_next_action = array(
							"data" => json_encode($save_info),
							"next_action" => get_timezone_system($next_day, false, $schedule->uid)
						);
						$this->db->update($this->tb_analytics, $data_next_action, "account = '".$schedule->account_id."'");
					}

					echo lang("successfully");
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}else{
			echo lang("no_activity");
		}
	}
	//****************************************/
	//               END CRON                */
	//****************************************/
}