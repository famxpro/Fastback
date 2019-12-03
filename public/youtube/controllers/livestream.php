<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class livestream extends MX_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->tb_accounts = "youtube_accounts";
		$this->tb_livestreams = "youtube_livestreams";
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->library("youtube_livestream");
	}

	public function index($ids = "")
	{
		$post = array();
		if($ids != ""){
			$post = $this->model->get("*", $this->tb_livestreams, "ids = '{$ids}' AND uid = '".session("uid")."'");
			if(empty($post)){
				redirect(PATH."youtube/post");
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

	public function ajax_post(){
		$ids = post("ids");
		$account = $this->input->post("account[]");
		$medias = $this->input->post("media[]");
		$title = post("title");
		$caption = post("caption");
		$category = post("category");
		$tags = post("tags");
		$watermark = post("watermark");
		$watermark_position = post("watermark_position");
		$add_text = post("add_text");
		$add_watermark = post("add_watermark");
		$thumbnail = post("thumbnail");
		$privacy_status = post("privacy_status");
		$show_livestream = post("show_livestream");
		$time_post = post("time_post");
		$is_schedule = post("is_schedule");
		$video_id = post("video_id");
		$time_post_default = get_to_time($time_post);
		$time_post_default = date("Y-m-d H:i:s", $time_post_default + 60);
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

		if(empty($account)){
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
			set_session("yt_account", $account[0]);
			$yt_account = $this->model->get("*", $this->tb_accounts, "ids = '".$account[0]."' AND uid = '".session("uid")."' AND status = 1");
			if(!empty($yt_account)){
				$data = array(
					"ids" => ids(),
					"uid" => session("uid"),
					"account" => $yt_account->id,
					"group" => $yt_account->pid,
					"data" => json_encode(array(
								"media"      => $medias,
								"title"      => $title,
								"caption"    => $caption,
								"category"   => $category,
								"add_watermark"  => $add_watermark,
								"watermark"  => $watermark,
								"watermark_position"  => $watermark_position,
								"add_text"   => $add_text,
								"thumbnail"  => $thumbnail,
								"privacy_status"  => $privacy_status,
								"show_livestream"  => $show_livestream,
								"tags" => $tags,
								"time_post_default" => date("Y-m-d H:i:s", time())
							)),
					"time_post" => NOW,
					"changed" => NOW,
					"created" => NOW 
				);

				$yt_post   = new Youtube_livestream($yt_account->access_token);
				$result = $yt_post->broadcast($data);

				if(is_string($result)){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result));

					//
					update_setting("yt_live_error_count", get_setting("yt_live_error_count", 0) + 1);
					update_setting("yt_live_count", get_setting("yt_live_count", 0) + 1);
					
					//Save
					$this->db->insert($this->tb_livestreams, $data);

					ms(array(
			        	"status"  => "error",
			        	"message"    => $result
			        ));
				}else{

					//Save report
					update_setting("yt_live_success_count", get_setting("yt_live_success_count", 0) + 1);
					update_setting("yt_live_count", get_setting("yt_live_count", 0) + 1);

					$time_end = time() + ceil($result['video_info']->duration);
					$broadcast_id = $result['broadcast_response']->id;

					$data['status'] = 2;
					$data['time_end'] = $time_end;
					$data['result'] = json_encode(
						array(
							"message" => "successfully", 
							"id" => $broadcast_id, 
							"url" => "https://www.youtube.com/watch?v=".$broadcast_id
						)
					);

					$this->db->insert($this->tb_livestreams, $data);

					ms(array(
			        	"status"  => "success",
			        	"message"  => lang("post_successfully")
			        ));
				}

			}else{

				ms(array(
		        	"status"  => "error",
		        	"message"  => lang("This accounts not exists")
		        ));

			}
		}else{
			foreach ($account as $ac) {
				$yt_account = $this->model->get("*", $this->tb_accounts, "ids = '".$ac."' AND uid = '".session("uid")."' AND status = 1");
				if(!empty($yt_account)){

					$time_post = get_timezone_system($time_post);
					$data = array(
						"uid" => session("uid"),
						"account" => $yt_account->id,
						"group" => $yt_account->pid,
						"data" => json_encode(array(
									"media"      => $medias,
									"title"      => $title,
									"caption"    => $caption,
									"category"   => $category,
									"add_watermark"  => $add_watermark,
									"watermark"  => $watermark,
									"watermark_position"  => $watermark_position,
									"add_text"   => $add_text,
									"thumbnail"  => $thumbnail,
									"privacy_status"  => $privacy_status,
									"show_livestream"  => $show_livestream,
									"tags" => $tags,
									"time_post_default" => $time_post_default
								)),
						"time_post" => $time_post,
						"status" => 1
					);

					if($show_livestream){
						if($video_id == ""){

							$yt_post   = new Youtube_livestream($yt_account->access_token);
							$result = $yt_post->broadcast($data, true);

							if(is_string($result)){
								ms(array(
						        	"status"  => "error",
						        	"message"    => $result
						        ));
							}

							$time_end = time() + ceil($result['video_info']->duration);
							$broadcast_response = $result['broadcast_response'];
							$stream_response = $result['stream_response'];

							$broadcast_id = $broadcast_response->id;
							$stream_name = $stream_response->getCdn()->getIngestionInfo()->getStreamName();
	            			$stream_url = $stream_response->getCdn()->getIngestionInfo()->getIngestionAddress();

							$data['time_end'] = $time_end;
							$data['result'] = json_encode(array(
								"message" => "processing",
								"id" => $broadcast_id, 
								"rtmp" => $stream_url."/".$stream_name
							));

						}else{

							$yt_post   = new Youtube_livestream($yt_account->access_token);
							$result = $yt_post->updateBroadcast($data, $video_id);

							$time_end = time() + ceil($result['video_info']->duration);
							$data['time_end'] = $time_end;

						}
					}

					if(!$show_livestream && !empty($post) && $video_id != ""){
						$yt_post   = new Youtube_livestream($yt_account->access_token);
						$result = $yt_post->deleteEvent($video_id);
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
		->from($this->tb_livestreams." as post")
		->join($this->tb_accounts." as account", "post.account = account.id")
		->where("(post.status = 1 AND post.time_post <= '".NOW."' AND account.status = 1) OR (post.status = 2 AND post.time_end <= '".strtotime(NOW)."' AND post.time_end != 0 AND account.status = 1)")->limit(5,0)->order_by('post.time_post', 'ASC')->get()->result();

		if(!empty($schedule_list)){
			foreach ($schedule_list as $key => $schedule) {
				$data = array();
				if(!permission("youtube/livestream", $schedule->uid)){
					$this->db->delete($this->tb_livestreams, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}
				
				$yt_post   = new Youtube_livestream($schedule->access_token);

				if($schedule->status == 1){

					$result = $yt_post->broadcast($schedule);

					if(is_string($result)){
						//Save report
						update_setting("yt_live_error_count", get_setting("yt_live_error_count", 0, $schedule->uid) + 1, $schedule->uid);
						update_setting("yt_live_count", get_setting("yt_live_count", 0, $schedule->uid) + 1, $schedule->uid);
						
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
						update_setting("yt_live_success_count", get_setting("yt_live_success_count", 0, $schedule->uid) + 1, $schedule->uid);
						update_setting("yt_live_count", get_setting("yt_live_count", 0, $schedule->uid) + 1, $schedule->uid);

						$broadcast_id = $result['broadcast_response']->id;

						$time_end = time() + ceil($result['video_info']->duration);
						$data['time_end'] = $time_end;

						$data['result'] = json_encode(
							array(
								"message" => "successfully", 
								"id" => $broadcast_id, 
								"url" => "https://www.youtube.com/watch?v=".$broadcast_id
							)
						);

						$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");

						echo '<a target=\'_blank\' href=\'https://www.youtube.com/watch?v='.$broadcast_id.'\'>'.lang('post_successfully').'</a><br/>';
					}

				}else if($schedule->status == 2){

					$res = json_decode($schedule->result);

					if(!empty($res) && isset($res->id)){
						$yt_post->transitionEvent($res->id, "complete");
					}

					$data['time_end'] = "";
					$this->db->update($this->tb_livestreams, $data, "id = '{$schedule->id}'");
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
			"name"  => lang("Live stream"),
			"icon"  => "fa fa-youtube-play",
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

