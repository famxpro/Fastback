<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class vk_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		$this->tb_accounts = "vk_accounts";
		$this->tb_posts = "vk_posts";
		include APPPATH."../public/vk/libraries/vkapi.php";
	}

	public function post_validator(){
		$accounts  = get_social_accounts("vk");
		$caption = post("caption");
		$link = post("link");
		$type = post("type");
		$medias = $this->input->post("media");
		$errors = array();

		if(empty($accounts)) return $errors = 0;

		switch ($type) {
			case 'text':
				if($caption == ""){ 
					$errors[] = lang("This caption is required");
				}

				break;

			case 'link':
				if($link == ""){
					$errors[] = lang("The URL is required");
				}
				
				if (!filter_var($link, FILTER_VALIDATE_URL)) {
					$errors[] = lang("The URL is not a valid");
				}
				break;

			case 'photo':
				if(empty($medias)){
					$errors[] = lang("The images is required");
				}

				break;

			case 'video':
				if(empty($medias)){
					$errors[] = lang("The video is required");
				}
				
				break;
			
			default:
				$errors[] = lang("Please select a type to post");
				break;
		}

		return $errors;
	}

	public function post_handler($ids = array()){
		$accounts  = get_social_accounts("vk");
		$type = post("type");
		$caption = post("caption");
		$link = post("link");
		$media = $this->input->post("media");
		$time_post = post("time_post");
		$delay = post("delay");
		$repeat = (int)post("repeat_every");
		$repeat_end = post("repeat_end");
		$type = ($type=="photo" || $type == "video")?"media":$type;
		
		if(!empty($accounts)){
			if(!post("is_schedule")){
				$result_all = array();
				foreach ($accounts as $account_id) {
					$item = $this->model->get("*", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");

					if(!empty($item)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $item->id,
							"group_id" => $item->pid,
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

						$vk = new VkAPI(get_option("vk_client_id", ""), get_option("vk_client_secret", ""));
						$vk->set_access_token($item->access_token);
						$result = $vk->post($data);

						if(is_array($result)){
							$data['status'] = 3;
							$data['result'] = $result['message'];

							//Save report
							update_setting("vk_post_error_count", get_setting("vk_post_error_count", 0) + 1);
							update_setting("vk_post_count", get_setting("vk_post_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							$result_all[] = $result;
						}else{
							$data['status'] = 2;
							$data['result'] = $result;
							$this->db->insert($this->tb_posts, $data);

							//Save report
							update_setting("vk_post_success_count", get_setting("vk_post_success_count", 0) + 1);
							update_setting("vk_post_count", get_setting("vk_post_count", 0) + 1);
							update_setting("vk_post_photo_count", get_setting("vk_post_photo_count", 0) + 1);

							$result_all[] = array(
					        	"status"  => "success",
					        	"message" => lang('post_successfully')
					        );
						}

					}else{
						$result_all[] = array(
				        	"status"  => "error",
				        	"message" => lang("the_vk_account_not_exists")
				        );
					}
				}

				return $result_all;

			}else{
				foreach ($accounts as $key => $account_id) {
					$item = $this->model->get("*", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");

					if(!empty($item)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $item->id,
							"group_id" => $item->pid,
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
							"changed" => NOW,
							"created" => NOW
						);

						$this->db->insert($this->tb_posts, $data);
					}
				}

				return array(
		        	"status"  => "success",
		        	"message" => lang('add_schedule_successfully')
		        );
			}
		}else{

			return array(
	        	"status"  => "error",
	        	"message" => lang("processing_is_error_please_try_again")
	        );

		}
	}

	public function post_previewer($link_info){
		$caption = post("caption");
		$link = post("link");
		$type = post("type");
		$medias = $this->input->post("media");

		$data = array(
			"ajax_load" => true,
			"type" => $type,
			"caption" => $caption,
			"medias" => $medias,
			"link_info" => $link_info
		);

		return array(
			"name" => "vk",
			"icon" => "fa fa-vk",
			"color" => "#4b729c",
			"content" => $this->load->view("../../../public/vk/views/post/preview", $data, true)
		);
	}
}
