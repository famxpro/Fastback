<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class post extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();

		$this->tb_accounts  = PINTEREST_ACCOUNTS;
		$this->tb_posts  = PINTEREST_POSTS;
		$this->module = get_class($this);
		$this->module_name = lang("pinterest_accounts");
		$this->module_icon = "fa fa-pinterest";
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
			'pinterest_account' => $this->model->fetch("id, username, avatar, pid, ids, status", $this->tb_accounts, "uid = '".session("uid")."'"),
			'post' => $post
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
		$ids       = post("ids");
		$accounts  = $this->input->post("account");
		$media     = $this->input->post("media");
		$time_post = post("time_post");
		$caption   = post("caption");
		$url       = post("url");
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
	        	"message" => lang('please_select_a_board')
	        ));
		}

		if(!$media && empty($media)){
			ms(array(
	        	"status"  => "error",
	        	"stop"    => true,
	        	"message" => lang('please_select_a_media')
	        ));
		}
		
		if(!post("is_schedule")){
			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$pinterest_account = $this->model->get("pid,access_token,id", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($pinterest_account)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $pinterest_account->id,
							"board"   => $pinterest_account->pid,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"url"     => $url
									)),
							"time_post" => NOW,
							"delay" => 0,
							"time_delete" => 0,
							"changed" => NOW,
							"created" => NOW
						);

						$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
						$pin->set_access_token($pinterest_account->access_token);
						$result = $pin->post($data);
						if(is_array($result)){
							$data['status'] = 3;
							$data['result'] = $result['message'];

							//Save report
							update_setting("pin_post_error_count", get_setting("pin_post_error_count", 0) + 1);
							update_setting("pin_post_count", get_setting("pin_post_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							ms($result);
						}else{
							$data['status'] = 2;
							$data['result'] = $result;

							//Save report
							update_setting("pin_post_success_count", get_setting("pin_post_success_count", 0) + 1);
							update_setting("pin_post_count", get_setting("pin_post_count", 0) + 1);
							update_setting("pin_post_photo_count", get_setting("pin_post_photo_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							ms(array(
					        	"status"  => "success",
					        	"message" => lang("post_successfully")
					        ));
						}
					}else{
						ms(array(
				        	"status"  => "error",
				        	"message" => lang("processing_is_error_please_try_again")
				        ));
					}
				}
			}

			ms(array(
	        	"status"  => "error",
	        	"message" => lang("processing_is_error_please_try_again")
	        ));
		}else{
			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$pinterest_account = $this->model->get("pid,access_token,id", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($pinterest_account)){
						$data = array(
							"uid" => session("uid"),
							"account" => $pinterest_account->id,
							"board"   => $pinterest_account->pid,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"url"     => $url
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
	        	"message" => "Add schedule successfully"
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
				if(!permission("pinterest/post", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}

				$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
				$pin->set_access_token($schedule->access_token);
				$result = $pin->post($schedule);
 
				$data = array();
				if(is_array($result) && $result["status"] == "error"){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result["message"]));

					//
					update_setting("pin_post_error_count", get_setting("pin_post_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("pin_post_count", get_setting("pin_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					//Save report
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");
					echo $result["message"]."<br/>";
				}else{

					//Save report
					update_setting("pin_post_success_count", get_setting("pin_post_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("pin_post_count", get_setting("pin_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("pin_post_photo_count", get_setting("pin_post_photo_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result));
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'https://pinterest.com/pin/'.$result.'\'>'.lang('post_successfully').'</a><br/>';
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
			"controller" => "pinterest",
			"color" => "#cd2029",
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