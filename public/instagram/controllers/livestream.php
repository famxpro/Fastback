<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class livestream extends MX_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->tb_accounts = "instagram_accounts";
		$this->tb_livestreams = "instagram_livestreams";
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->library("instagramapi");
	}

	public function index($ids = "")
	{
		$post = array();
		if($ids != ""){
			$post = $this->model->get("*", $this->tb_livestreams, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				redirect(PATH."facebook/livestream");
			}
		}

		$data = array(
			'accounts' => $this->model->fetch("id, username, avatar, ids, pid", $this->tb_accounts, "uid = '".session("uid")."' AND status = 1"),
			'post' => $post
		);

		$this->template->build('livestream/index', $data);		
	}

	public function block_report(){
		$data = array();
		$this->load->view('livestream/block_report', $data);
	}

	public function save(){
		$ids = post("ids");
		$account = post("account");
		$medias = $this->input->post("media[]");
		$repeat = post("repeat");
		$watermark = post("watermark");
		$watermark_position = post("watermark_position");
		$add_text = post("add_text");
		$add_watermark = post("add_watermark");
		$time_post = post("time_post");
		$is_schedule = post("is_schedule");
		$show_livestream = post("show_livestream");
		$post = array();

		if($ids){
			$post = $this->model->get("*", $this->tb_livestreams, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				ms(array(
		        	"status"  => "error",
		        	"message" => lang('This post does not exist')
		        ));
			}
		}

		if($account == ""){
			ms(array(
				"status" => "error",
				"message" => lang("Please select an account")
			));
		}

		if(empty($medias)){
			ms(array(
				"status" => "error",
				"message" => lang("Please select an video")
			));
		}

		$instagram_account = $this->model->get("*", $this->tb_accounts, "ids = '".$account."' AND uid = '".session("uid")."' AND status = 1");
		if(!empty($instagram_account)){
			if(!post("is_schedule")){
				$data = array(
					"ids" => ids(),
					"uid" => session("uid"),
					"account" => $instagram_account->id,
					"group" => $instagram_account->pid,
					"data" => json_encode(array(
								"media"      => $medias,
								"caption"    => "",
								"add_watermark"  => $add_watermark,
								"watermark"  => $watermark,
								"watermark_position"  => $watermark_position,
								"add_text"   => $add_text,
								"repeat"     => $repeat
							)),
					"time_post" => NOW,
					"changed" => NOW,
					"created" => NOW 
				);

				$proxy_data = get_proxy($this->tb_accounts, $instagram_account->proxy, $instagram_account);	
				try {
					$ig = new InstagramAPI($instagram_account->username, $instagram_account->password, $proxy_data->use);
					$result = $ig->livestream->process($data);
				} catch (Exception $e) {
					ms(array(
						"status" => "error",
						"message" => $e->getMessage()
					));
				}

				if(is_string($result)){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result));

					//
					update_setting("ig_live_error_count", get_setting("ig_live_error_count", 0) + 1);
					update_setting("ig_live_count", get_setting("ig_live_count", 0) + 1);
					
					//Save
					$this->db->insert($this->tb_livestreams, $data);

					ms(array(
			        	"status"  => "error",
			        	"message"    => $result
			        ));
				}else{

					//Save report
					update_setting("ig_live_success_count", get_setting("ig_live_success_count", 0) + 1);
					update_setting("ig_live_count", get_setting("ig_live_count", 0) + 1);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result->broadcast_id));
					$this->db->insert($this->tb_livestreams, $data);

					ms(array(
			        	"status"  => "success",
			        	"message"  => lang("Your video is live now")
			        ));
				}
			}else{
				$time_post = get_timezone_system($time_post);

				$data = array(
					"uid" => session("uid"),
					"account" => $instagram_account->id,
					"group" => $instagram_account->pid,
					"data" => json_encode(array(
								"media"      => $medias,
								"caption"    => "",
								"add_watermark"  => $add_watermark,
								"watermark_position"  => $watermark_position,
								"watermark"  => $watermark,
								"add_text"   => $add_text,
								"repeat"     => $repeat
							)),
					"time_post" => $time_post,
					"status" => 1,
					"changed" => NOW
				);

				//Save
				//Save
				if(empty($post)){
					$data["ids"] = ids();
					$data["created"] = NOW;
					$this->db->insert($this->tb_livestreams, $data);
				}else{
					$this->db->update($this->tb_livestreams, $data, array("id" => $post->id));

					ms(array(
			        	"status"  => "success",
			        	"message" => lang('Edit post successfully')
			        ));
				}

				ms(array(
		        	"status"  => "success",
		        	"message"  => lang("Added your livestream schedule")
		        ));

			}
		}else{

			ms(array(
	        	"status"  => "error",
	        	"message"  => lang("This accounts not exists")
	        ));
		}
	}

	/****************************************/
	/* CRON                                 */
	/* Time cron: once_per_minute           */
	/****************************************/
	public function cron(){
		$schedule_list = $this->db->select('post.*, account.username, account.password, account.proxy, account.default_proxy')
		->from($this->tb_livestreams." as post")
		->join($this->tb_accounts." as account", "post.account = account.id")
		->where("(post.status = 1 OR post.status = 4) AND post.time_post <= '".NOW."' AND account.status = 1")->limit(5,0)->get()->result();
		
		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				if(!permission("instagram/post", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}

				$proxy_data = get_proxy($this->tb_accounts, $schedule->proxy, $schedule);
				try {
					$ig = new InstagramAPI($schedule->username, $schedule->password, $proxy_data->use);
					$result = $ig->livestream->process($schedule);
				} catch (Exception $e) {
					$result = array(
						"status" => "error",
						"message" => $e->getMessage()
					);
				}

				$data = array();
				if(is_array($result) && $result["status"] == "error"){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result["message"]));

					//
					update_setting("ig_live_error_count", get_setting("ig_live_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("ig_live_count", get_setting("ig_live_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					//Save report
					$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");

					echo $result["message"]."<br/>";
				}else{
					//Save report
					update_setting("ig_live_success_count", get_setting("ig_live_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("ig_live_count", get_setting("ig_live_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result->broadcast_id, "url" => ""));
					$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'javascript:void(0);\' >'.lang('post_successfully').'</a><br/>';
				}
			}
		}else{
			
		}
	}


	/****************************************/
	/*           SCHEDULES POST             */
	/****************************************/
	public function block_schedules_xml($type = ""){
		$template = array(
			"controller" => "instagram",
			"color" => "#d62976",
			"name"  => lang("Livestream"),
			"icon"  => "fa fa-instagram",
			"module" => "livestream"
		);
		echo Modules::run("schedules/block_schedules_xml", $template, $this->tb_livestreams, $type);
	}

	public function schedules(){
		echo Modules::run("schedules/schedules", "username", $this->tb_livestreams, $this->tb_accounts);
	}

	public function ajax_schedules(){
		echo Modules::run("schedules/ajax_schedules", "username", $this->tb_livestreams, $this->tb_accounts);
	}

	public function ajax_delete_schedules($delete_all = false, $type = ""){
		echo Modules::run("schedules/ajax_delete_schedules", $this->tb_livestreams, $delete_all, $type);
	}
	//****************************************/
	//         END SCHEDULES POST            */
	//****************************************/

}

