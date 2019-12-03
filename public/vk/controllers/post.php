<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class post extends MX_Controller {
	public $tb_accounts;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();

		$this->tb_accounts = VK_ACCOUNTS;
		$this->tb_posts = VK_POSTS;
		$this->module = get_class($this);
		$this->module_name = lang("vk_accounts");
		$this->module_icon = "fa fa-vk";
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
			'accounts'     => $this->model->fetch("id, username, avatar, type, ids", $this->tb_accounts, "uid = '".session("uid")."'", "id", "asc")
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

	public function ajax_get_link(){
		$link = post("link");
		if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$link)) {
			return ms(array(
				"status" => "error",
				"message" => lang("invalid_url")
			));
		}

		$parse = parse_url($link);
		$data = get_info_link($link);
		$data['host'] = str_replace("www.", "", $parse['host']);
		$data['status'] = "success";
		ms($data);
	}

	public function ajax_post(){
		$ids = post("ids");
		$accounts  = $this->input->post("account");
		$media     = $this->input->post("media");
		$link      = post("link");
		$type      = post("type");
		$time_post = post("time_post");
		$caption   = post("caption");
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

		if(!Vk_Post_Type($type)){
			ms(array(
	        	"status"  => "error",
	        	"stop"    => true,
	        	"message" => lang('please_select_a_post_type')
	        ));
		}

		switch ($type) {
			case 'media':
				if(empty($media)){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('please_select_an_image')
			        ));
				}
				break;

			case 'link':
				if($link == ""){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('link_is_required')
			        ));
				}

				if (!filter_var($link, FILTER_VALIDATE_URL)) {
					$errors[] = lang('the_url_is_not_a_valid');
				}
				break;

			default:
				if($caption == ""){
					ms(array(
			        	"status"  => "error",
			        	"stop"    => true,
			        	"message" => lang('the_caption_is_required')
			        ));
				}
				break;
		}

		if(!post("is_schedule")){
			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$vk_account = $this->model->get("id, access_token, pid", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($vk_account)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $vk_account->id,
							"group_id" => $vk_account->pid,
							"type" => $type,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"link"    => $link
									)),
							"time_post" => NOW,
							"delay" => 0,
							"time_delete" => 0,
							"changed" => NOW,
							"created" => NOW
						);

						$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
						$vk->set_access_token($vk_account->access_token);
						$result = $vk->post($data);
						if(is_array($result)){
							$data['status'] = 3;
							$data['result'] = $result['message'];

							//Save report
							update_setting("vk_post_error_count", get_setting("vk_post_error_count", 0) + 1);
							update_setting("vk_post_count", get_setting("vk_post_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							ms($result);
						}else{
							$data['status'] = 2;
							$data['result'] = $result;
							$this->db->insert($this->tb_posts, $data);

							//Save report
							update_setting("vk_post_success_count", get_setting("vk_post_success_count", 0) + 1);
							update_setting("vk_post_count", get_setting("vk_post_count", 0) + 1);
							update_setting("vk_post_photo_count", get_setting("vk_post_photo_count", 0) + 1);

							ms(array(
					        	"status"  => "success",
					        	"message" => lang('post_successfully')
					        ));
						}

					}else{
						ms(array(
				        	"status"  => "error",
				        	"message" => lang('the_vk_account_not_exists')
				        ));
					}
				}
			}

			ms(array(
	        	"status"  => "error",
	        	"message" => "Processing is error please try again"
	        ));
		}else{
			if(!empty($accounts)){
				foreach ($accounts as $account_id) {
					$vk_account = $this->model->get("id, access_token, pid", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($vk_account)){
						$data = array(
							"uid" => session("uid"),
							"account" => $vk_account->id,
							"group_id" => $vk_account->pid,
							"type" => $type,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"link"    => $link
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
				if(!permission("pinterest/post", $schedule->uid)){
					$this->db->delete($this->tb_posts, array("uid" => $schedule->uid, "time_post >=" => NOW));
				}

				$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
				$vk->set_access_token($schedule->access_token);
				$result = $vk->post($schedule);
 
				$data = array();
				if(is_array($result) && $result["status"] == "error"){
					$data['status'] = 3;
					$data['result'] = json_encode(array("message" => $result["message"]));

					//
					update_setting("vk_post_error_count", get_setting("vk_post_error_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("vk_post_count", get_setting("vk_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					
					//Save report
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");
					echo $result["message"]."<br/>";
				}else{

					//Save report
					update_setting("vk_post_success_count", get_setting("vk_post_success_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("vk_post_count", get_setting("vk_post_count", 0, $schedule->uid) + 1, $schedule->uid);
					update_setting("vk_post_photo_count", get_setting("vk_post_photo_count", 0, $schedule->uid) + 1, $schedule->uid);

					$data['status'] = 2;
					$data['result'] = json_encode(array("message" => "successfully", "id" => $result));
					$this->db->update($this->tb_posts, $data, "id = '{$schedule->id}'");

					echo '<a target=\'_blank\' href=\'https://vk.com/'.$result.'\'>'.lang('post_successfully').'</a><br/>';
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
			"controller" => "vk",
			"color" => "#4b729c",
			"name"  => lang("auto_post"),
			"icon"  => "fa fa-vk"
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