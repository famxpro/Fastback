<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class post extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();

		$this->tb_accounts = TWITTER_ACCOUNTS;
		$this->tb_posts = TWITTER_POSTS;
		$this->module = get_class($this);
		$this->module_name = lang("twitter_accounts");
		$this->module_icon = "fa fa-twitter";
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function index($ids = ""){ 
		$post = array();
		if($ids != ""){
			$post = $this->model->get("*", $this->tb_posts, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				redirect(PATH."facebook/post");
			}
		}

		$data = array(
			'post'         => $post,
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon,
			'accounts'     => $this->model->fetch("id, pid, username, avatar, ids", $this->tb_accounts, "uid = '".session("uid")."' AND status = 1")
		);
		$this->template->build('post/index', $data);
	}

	public function preview(){
		$data = array();
		$this->load->view('post/preview', $data);
	}

	public function block_report(){
		$data = array();
		$this->load->view('post/block_report', $data);
	}

	public function ajax_post(){
		$ids = post("ids");
		$accounts  = $this->input->post("account");
		$media     = $this->input->post("media");
		$type      = post("type");
		$time_post = post("time_post");
		$caption   = post("caption");
		$repeat = (int)post("repeat_every");
		$repeat_end = post("repeat_end"); 
		$post = array();

		if($ids){
			$post = $this->model->get("*", $this->tb_posts, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				ms(array(
		        	"status"  => "error",
		        	"stop"    => true,
		        	"message" => lang('This post does not exist')
		        ));
			}
		}
		
		if(!$accounts){
			ms(array(
	        	"status"  => "error",
	        	"stop"    => true,
	        	"message" => lang('please_select_an_account')
	        ));
		}

		if(!Twitter_Post_Type($type)){
			ms(array(
	        	"status"  => "error",
	        	"stop"    => true,
	        	"message" => lang('please_select_a_post_type')
	        ));
		}

		switch ($type) {
			case 'photo':
				if(empty($media)){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('please_select_image')
			        ));
				}

				if(count($media) > 4){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('twitter_allow_maximum_4_images')
			        ));
				}
				break;

			case 'video':
				if(!$media && empty($media)){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('please_select_a_video')
			        ));
				}

				break;

			default:
				if($caption == ""){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('please_enter_caption')
			        ));
				}
				break;
		}

		if(!post("is_schedule")){

			$count_error = 0;
			$count_success = 0;

			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$twitter_account = $this->model->get("id, access_token", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."' AND status = 1");
					if(!empty($twitter_account)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $twitter_account->id,
							"type" => post("type"),
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"repeat"     => $repeat*86400, 
										"repeat_end" => get_timezone_system($repeat_end)
									)),
							"time_post" => NOW,
							"delay" => 0,
							"time_delete" => 0,
							"changed" => NOW,
							"created" => NOW
						);

						$tw = new TwitterAPI(CONSUMER_KEY, CONSUMER_SECRET);
						$tw->set_access_token($twitter_account->access_token);
						$result = $tw->post($data);
						if(is_array($result)){
							$data['status'] = 3;
							$data['result'] = $result['message'];

							//Save report
							update_setting("tw_post_error_count", get_setting("tw_post_error_count", 0) + 1);
							update_setting("tw_post_count", get_setting("tw_post_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							if(count($accounts) == 1){
								ms($result);
							}

							$count_error += 1;
						}else{
							$data['status'] = 2;
							$data['result'] = json_encode(array("message" => "successfully", "id" => $result, "url" => "https://twitter.com/statuses/".$result));

							//Save report
							update_setting("tw_post_success_count", get_setting("tw_post_success_count", 0) + 1);
							update_setting("tw_post_count", get_setting("tw_post_count", 0) + 1);
							update_setting("tw_post_{$type}_count", get_setting("tw_post_{$type}_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							$count_success += 1;
						}

					}else{
						$count_error += 1;
					}
				}
			}

			ms(array(
				"status"  => "success",
				"message" => sprintf(lang("Content is being published on %d profiles and %d profiles unpublished"), $count_success, $count_error)
			));		
		}else{
			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$twitter_account = $this->model->get("id, access_token", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($twitter_account)){
						$data = array(
							"uid" => session("uid"),
							"account" => $twitter_account->id,
							"type" => post("type"),
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"repeat"     => $repeat*86400, 
										"repeat_end" => get_timezone_system($repeat_end)
									)),
							"time_post" => get_timezone_system($time_post),
							"delay" => 0,
							"time_delete" => 0,
							"status" => 1,
							"changed" => NOW
						);

						if(empty($post)){
							$data["ids"] = ids();
							$data["created"] = NOW;
							$this->db->insert($this->tb_posts, $data);
						}else{
							$this->db->update($this->tb_posts, $data, array("id" => $post->id));

							ms(array(
					        	"status"  => "success",
					        	"message" => lang('Edit post successfully')
					        ));
						}
					}
				}
			}

			ms(array(
	        	"status"  => "success",
	        	"message" => lang('add_schedule_successfully')
	        ));
		}
	}

	/****************************************/
	/* CRON                                 */
	/* Time cron: once_per_minute           */
	/****************************************/
	public function cron(){
		$schedule_list = $this->db->select('post.*, account.access_token')
		->from($this->tb_posts." as post")
		->join($this->tb_accounts." as account", "post.account = account.id")
		->where("(post.status = 1 OR post.status = 4) AND post.time_post <= '".NOW."' AND account.status = 1")->get()->result();

		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				if(!permission("twitter/post", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}
				
				$tw = new TwitterAPI(CONSUMER_KEY, CONSUMER_SECRET);
				$tw->set_access_token($schedule->access_token);
				$result = $tw->post($schedule);

				$data = array();
				if(is_array($result) && $result["status"] == "error"){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result["message"]));

					//
					update_setting("tw_post_error_count", get_setting("tw_post_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("tw_post_count", get_setting("tw_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					//Save report
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'", $schedule->uid);

					echo $result["message"]."<br/>";
				}else{
					$schedule_data = $schedule->data;
					if( isset($schedule_data->repeat) 
						&& isset($schedule_data->repeat_end) 
						&& $schedule_data->repeat > 0 
						&& strtotime(NOW) < strtotime($schedule_data->repeat_end)
					){
						$time_post_next = date("Y-m-d H:i:s", get_to_time($schedule->time_post) + $schedule_data->repeat);
						$this->db->insert($this->tb_posts, array(
							"ids" => ids(),
							"uid" => $schedule->uid,
							"account" => $schedule->account,
							"type" => $schedule->type,
							"data" => json_encode($schedule->data),
							"time_post" => $time_post_next,
							"delay" => $schedule->delay,
							"time_delete" => $schedule->time_delete,
							"status" => $schedule->status,
							"changed" => NOW,
							"created" => NOW
						));
					}

					$data['status'] = 2;

					//Save report
					update_setting("tw_post_success_count", get_setting("tw_post_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("tw_post_count", get_setting("tw_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("tw_post_{$schedule->type}_count", get_setting("tw_post_{$schedule->type}_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result, "url" => "https://twitter.com/statuses/".$result));
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'https://twitter.com/statuses/'.$result.'\'>'.lang('post_successfully').'</a><br/>';
				}
			}
		}else{
			
		}
	}
	//****************************************/
	//               END CRON                */
	//****************************************/

	/****************************************/
	/*           SCHEDULES POST             */
	/****************************************/
	public function block_schedules_xml($type = ""){
		$template = array(
			"controller" => "twitter",
			"color" => "#00aced",
			"name"  => lang("auto_post"),
			"icon"  => $this->module_icon
		);
		echo Modules::run("schedules/block_schedules_xml", $template, $this->tb_posts, $type);
	}

	public function schedules(){
		echo Modules::run("schedules/schedules", "username", $this->tb_posts, $this->tb_accounts);
	}

	public function ajax_schedules(){
		echo Modules::run("schedules/ajax_schedules", "username", $this->tb_posts, $this->tb_accounts);
	}

	public function ajax_delete_schedules($delete_all = false, $type = ""){
		echo Modules::run("schedules/ajax_delete_schedules", $this->tb_posts, $delete_all, $type);
	}
	//****************************************/
	//         END SCHEDULES POST            */
	//****************************************/

}