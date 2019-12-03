<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class vk extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		
		$this->table = VK_ACCOUNTS;
		$this->module = get_class($this);
		$this->module_name = lang("vk_accounts");
		$this->module_icon = "fa fa-vk";
		$this->load->model($this->module.'_model', 'model');

	}

	public function block_general_settings(){
		$data = array();
		$this->load->view('account/general_settings', $data);
	}
	
	public function block_list_account(){
		$data = array(
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon,
			'list_account' => $this->model->fetch("id, ids, username, type, url, status, avatar", $this->table, "uid = '".session("uid")."'", "id", "asc")
		);
		$this->load->view("account/index", $data);
	}

	public function popup_add_account(){
		$data = array(
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon
		);
		$this->load->view('account/popup_add_account', $data);
	}
	
	public function oauth(){
		$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
		redirect($vk->login_url());
	}

	public function add_account(){
		$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
		$access_token = session("vk_access_token");
		$vk->set_access_token($access_token);
		$userinfo     = $vk->get_user_info()[0];
		$groups       = $vk->get_groups();

		//pr($userinfo,1);
		//pr($groups,1);

		$data = array(
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon,
			'userinfo'     => $userinfo,
			'groups'       => $groups
		);

		$this->template->build('account/add_account', $data);
	}

	public function ajax_get_access(){
		$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
		$access_token = $vk->get_access_token();

		set_session("vk_access_token", $access_token);

		ms(array(
			"status"  => "success",
			"message" => lang("successfully")
		));
	}

	public function ajax_add_account(){
		$accounts = $this->input->post("accounts");
		$access_token = session("vk_access_token");

		if(empty($accounts)){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('please_select_at_least_one_item')
	        ));
		}

		if($access_token){
			$vk = new VkAPI(VK_CLIENT_ID, VK_CLIENT_SERECT);
			$vk->set_access_token($access_token);
			$userinfo     = $vk->get_user_info()[0];
			$groups       = $vk->get_groups();

			foreach ($accounts as $account) {
				if(!check_number_account($this->table)){
					ms(array(
						"status" => "error",
						"message" => lang("limit_social_accounts")
					));
				}


				$data = array();
				if($account == $userinfo->id){
					$data = array(
						"uid"          => session("uid"),
						"ids"          => ids(),
						"pid"          => $userinfo->id,
						"type"     	   => "profile",
						"username"     => $userinfo->screen_name,
						"avatar"       => $userinfo->photo_big,
						"url"          => "https://vk.com/".$userinfo->screen_name,
						"access_token" => $access_token,
						"status"       => 1
					);

					$ac = $this->model->get("*", $this->table, "pid = '".$userinfo->id."' AND uid = '".session("uid")."'");
					if(empty($ac)){
						$data["created"] = NOW;
						$this->db->insert($this->table, $data);
					}else{
						$this->db->update($this->table, $data, array("id" => $ac->id));
					}
				}

				if($account != $userinfo->id){
					if(!empty($groups) && $groups->count != 0){
						foreach ($groups->items as $group) {
							if($group->id == $account){
								$data = array(
									"uid"          => session("uid"),
									"ids"          => ids(),
									"pid"          => -1*$group->id,
									"type"     	   => $group->type,
									"username"     => $group->name,
									"avatar"       => $group->photo_100,
									"url"          => "https://vk.com/".$group->screen_name,
									"access_token" => $access_token,
									"status"       => 1
								);

								$ac = $this->model->get("*", $this->table, "pid = '".$group->id."' AND uid = '".session("uid")."'");
								if(empty($ac)){
									$data["created"] = NOW;
									$this->db->insert($this->table, $data);
								}else{
									$this->db->update($this->table, $data, array("id" => $ac->id));
								}
							}
						}
					}
				}
			}
		
			ms(array(
				"status" => "success",
				"message" => lang('add_account_successfully')
			));
		}else{
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('an_error_occurred_during_processing_please_try_again')
	        ));
		}
	}

	public function ajax_delete_item(){
		$this->model->delete($this->table, post("id"), false);
	}
}