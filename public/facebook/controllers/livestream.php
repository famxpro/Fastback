<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class livestream extends MX_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->tb_accounts = "facebook_accounts";
		$this->tb_livestreams = "facebook_livestreams";
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->library("facebook_livestream");
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
			'accounts' => $this->model->fetch("id, fullname, avatar, ids, type, pid", $this->tb_accounts, "uid = '".session("uid")."' AND status = 1"),
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
		$title = post("title");
		$caption = post("caption");
		$repeat = post("repeat");
		$watermark = post("watermark");
		$watermark_position = post("watermark_position");
		$add_text = post("add_text");
		$add_watermark = post("add_watermark");
		$time_post = post("time_post");
		$is_schedule = post("is_schedule");
		$show_livestream = post("show_livestream");
		$video_id = post("video_id");
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

		if($title == ""){
			ms(array(
				"status" => "error",
				"message" => lang("Title is required")
			));
		}

		$fb_account = $this->model->get("*", $this->tb_accounts, "ids = '".$account."' AND uid = '".session("uid")."' AND status = 1");
		if(!empty($fb_account)){
			if(!post("is_schedule")){
				$type = $fb_account->type;
				$data = array(
					"ids" => ids(),
					"uid" => session("uid"),
					"account" => $fb_account->id,
					"group" => $fb_account->pid,
					"type" => $fb_account->type,
					"data" => json_encode(array(
								"media"      => $medias,
								"title"      => $title,
								"caption"    => $caption,
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

				$fb   = new FacebookAPI();
				$fb->set_access_token($fb_account->access_token);
				$livestream   = new Facebook_livestream($fb);
				$result = $livestream->livestream($data);

				if(is_string($result)){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result));

					//
					update_setting("fb_live_error_count", get_setting("fb_live_error_count", 0) + 1);
					update_setting("fb_live_count", get_setting("fb_live_count", 0) + 1);
					
					//Save
					$this->db->insert($this->tb_livestreams, $data);

					ms(array(
			        	"status"  => "error",
			        	"message"    => $result
			        ));
				}else{

					//Save report
					update_setting("fb_live_success_count", get_setting("fb_live_success_count", 0) + 1);
					update_setting("fb_live_count", get_setting("fb_live_count", 0) + 1);
					update_setting("fb_live_{$type}_count", get_setting("fb_live_{$type}_count", 0) + 1);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result->id, "url" => "http://fb.com/".$result->id));
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
					"account" => $fb_account->id,
					"group" => $fb_account->pid,
					"type" => $fb_account->type,
					"data" => json_encode(array(
								"media"      => $medias,
								"title"      => $title,
								"caption"    => $caption,
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

				if($show_livestream){
				
					$fb   = new FacebookAPI();
					$fb->set_access_token($fb_account->access_token);
					$livestream   = new Facebook_livestream($fb);
					$result = $livestream->livestream($data, get_timezone_user($time_post), $video_id);

					if(is_string($result)){
						ms(array(
				        	"status"  => "error",
				        	"message"    => $result
				        ));
					}

					$data['result'] = json_encode($result);
				}

				if(!$show_livestream && !empty($post) && $video_id != ""){
					$fb   = new FacebookAPI();
					$fb->set_access_token($fb_account->access_token);
					$livestream   = new Facebook_livestream($fb);
					$result = $livestream->livestream_delete($video_id);
					$data['result'] = "";
				}

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
	        	"status"  => "success",
	        	"message"  => lang("This accounts not exists")
	        ));
		}
	}

	/****************************************/
	/* CRON                                 */
	/* Time cron: once_per_minute           */
	/****************************************/
	public function cron(){
		$schedule_list = $this->db->select('post.*, account.access_token')
		->from($this->tb_livestreams." as post")
		->join($this->tb_accounts." as account", "post.account = account.id")
		->where("(post.status = 1 OR post.status = 4) AND post.time_post <= '".NOW."' AND account.status = 1")->limit(1,0)->order_by('post.time_post', 'ASC')->get()->result();

		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				if(!permission("facebook/livestream", $schedule->uid)){
					$this->db->delete($this->tb_livestreams, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}
				
				$fb   = new FacebookAPI();
				$fb->set_access_token($schedule->access_token);
				$livestream   = new Facebook_livestream($fb);
				$result = $livestream->livestream($schedule);

				if(is_string($result)){
					//Save report
					update_setting("fb_live_error_count", get_setting("fb_live_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("fb_live_count", get_setting("fb_live_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result));
					$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");

					echo $result."<br/>";
				}else{
					$schedule_data = $schedule->data;
					if( isset($schedule_data->repeat) 
						&& isset($schedule_data->repeat_end) 
						&& $schedule_data->repeat > 0 
						&& strtotime(NOW) < strtotime($schedule_data->repeat_end)
					){
						$time_post_next = date("Y-m-d H:i:s", get_to_time($schedule->time_post) + $schedule_data->repeat);
						$data['status'] = 1;
						$data['time_post'] = $time_post_next;
					}else{
						$data['status'] = 2;
					}

					//Save report
					update_setting("fb_live_success_count", get_setting("fb_live_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("fb_live_count", get_setting("fb_live_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("fb_live_{$schedule->type}_count", get_setting("fb_post_{$schedule->type}_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['result'] = json_encode(array("message" => "successfully", "id" => $result->id, "url" => "http://fb.com/".$result->id));
					$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'http://fb.com/'.$result->id.'\'>'.lang('post_successfully').'</a><br/>';
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
			"controller" => "facebook",
			"color" => "#4267b2",
			"name"  => lang("Livestream"),
			"icon"  => "fa fa-facebook-square",
			"module" => "livestream"
		);
		echo Modules::run("schedules/block_schedules_xml", $template, $this->tb_livestreams, $type);
	}

	public function schedules(){
		echo Modules::run("schedules/schedules", "fullname", $this->tb_livestreams, $this->tb_accounts);
	}

	public function ajax_schedules(){
		echo Modules::run("schedules/ajax_schedules", "fullname", $this->tb_livestreams, $this->tb_accounts);
	}

	public function ajax_delete_schedules($delete_all = false, $type = ""){
		echo Modules::run("schedules/ajax_delete_schedules", $this->tb_livestreams, $delete_all, $type);
	}
	//****************************************/
	//         END SCHEDULES POST            */
	//****************************************/

}

