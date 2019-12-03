<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class post extends MX_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->tb_accounts = "youtube_accounts";
		$this->tb_posts = "youtube_posts";
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->library("youtube_post");
	}

	public function index($ids = "")
	{
		$post = array();
		if($ids != ""){
			$post = $this->model->get("*", $this->tb_posts, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				redirect(PATH."youtube/post");
			}
		}

		$data = array(
			'accounts' => $this->model->fetch("id, username, avatar, ids, pid", $this->tb_accounts, "uid = '".session("uid")."' AND status = 1"),
			'post' => $post
		);

		$this->template->build('post/index', $data);		
	}

	public function block_report(){
		$data = array();
		$this->load->view('post/block_report', $data);
	}

	public function ajax_post(){
		$ids = post("ids");
		$accounts = $this->input->post("account[]");
		$medias = $this->input->post("media[]");
		$title = post("title");
		$caption = post("caption");
		$repeat = post("repeat");
		$category = (int)post("category");
		$tags = post("tags");
		$time_post = post("time_post");
		$is_schedule = post("is_schedule");
		$post = array();

		if($ids){
			$post = $this->model->get("*", $this->tb_posts, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				ms(array(
		        	"status"  => "error",
		        	"message" => lang('This post does not exist')
		        ));
			}
		}

		if(empty($accounts)){
			ms(array(
				"status" => "error",
				"stop"    => true,
				"message" => lang("Please select an account")
			));
		}

		if(empty($medias)){
			ms(array(
				"status" => "error",
				"stop"    => true,
				"message" => lang("Please select an video")
			));
		}

		if($category == 0){
			ms(array(
				"status" => "error",
				"stop"    => true,
				"message" => lang("Please select a category")
			));
		}

		if($title == ""){
			ms(array(
				"status" => "error",
				"stop"    => true,
				"message" => lang("Title is required")
			));
		}

		if(!post("is_schedule")){

			$count_error = 0;
			$count_success = 0;

			foreach ($accounts as $key => $account) {
				$account = $this->model->get("*", $this->tb_accounts, "ids = '".$account."' AND uid = '".session("uid")."' AND status = 1");
				if(!empty($account)){
					$data = array(
						"ids" => ids(),
						"uid" => session("uid"),
						"account" => $account->id,
						"group" => $account->pid,
						"data" => json_encode(array(
									"media"      => $medias,
									"title"      => $title,
									"caption"    => $caption,
									"category"   => $category,
									"tags"       => $tags
								)),
						"time_post" => NOW,
						"changed" => NOW,
						"created" => NOW 
					);

					$yt_post   = new Youtube_post($account->access_token);
					$result = $yt_post->post($data);

					if(is_string($result)){
						$data['status'] = 3;
						$data['result'] = json_encode(array("message" => $result));

						//
						update_setting("yt_post_error_count", get_setting("yt_post_error_count", 0) + 1);
						update_setting("yt_post_count", get_setting("yt_post_count", 0) + 1);
						
						//Save
						$this->db->insert($this->tb_posts, $data);

						if(count($accounts) == 1){
							ms(array(
					        	"status"  => "error",
					        	"message"    => $result
					        ));
						}

						$count_error += 1;
					}else{

						//Save report
						update_setting("yt_post_success_count", get_setting("yt_post_success_count", 0) + 1);
						update_setting("yt_post_count", get_setting("yt_post_count", 0) + 1);

						$data['status'] = 2;
						$data['result'] = json_encode(array("message" => "successfully", "id" => $result->id, "url" => "https://www.youtube.com/watch?v=".$result->id));
						$this->db->insert($this->tb_posts, $data);

						$count_success += 1;
					}

				}else{
					$count_error += 1;
				}
			}

			ms(array(
				"status"  => "success",
				"message" => sprintf(lang("Content is being published on %d profiles and %d profiles unpublished"), $count_success, $count_error)
			));
		}else{
			foreach ($accounts as $account) {
				$account = $this->model->get("*", $this->tb_accounts, "ids = '".$account."' AND uid = '".session("uid")."' AND status = 1");
				if(!empty($account)){

					$time_post = get_timezone_system($time_post);
					$data = array(
						"uid" => session("uid"),
						"account" => $account->id,
						"group" => $account->pid,
						"data" => json_encode(array(
									"media"      => $medias,
									"title"      => $title,
									"caption"    => $caption,
									"category"   => $category,
									"tags"       => $tags
								)),
						"time_post" => $time_post,
						"status" => 1,
						"changed" => NOW
					);

					//Save
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

			ms(array(
	        	"status"  => "success",
	        	"message"  => lang('add_schedule_successfully')
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
		->where("(post.status = 1 OR post.status = 4) AND post.time_post <= '".NOW."' AND account.status = 1")->limit(1,0)->order_by('post.time_post', 'ASC')->get()->result();

		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				if(!permission("facebook/livestream", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}
				
				$yt_post   = new Youtube_post($schedule->access_token);
				$result = $yt_post->post($schedule);
				if(is_string($result)){
					//Save report
					update_setting("yt_post_error_count", get_setting("yt_post_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("yt_post_count", get_setting("yt_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result));
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");

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
					update_setting("yt_post_success_count", get_setting("yt_post_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("yt_post_count", get_setting("yt_post_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['result'] = json_encode(array("message" => "successfully", "id" => $result->id, "url" => "https://www.youtube.com/watch?v=".$result->id));
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'https://www.youtube.com/watch?v='.$result->id.'\'>'.lang('post_successfully').'</a><br/>';
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
			"controller" => "youtube",
			"color" => "#c4302b",
			"name"  => lang("post"),
			"icon"  => "fa fa-youtube-play",
			"module" => "post"
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

