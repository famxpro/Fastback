<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class facebook extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct()
	{
		parent::__construct();

		$this->table = FACEBOOK_ACCOUNTS;
		$this->module = get_class($this);
		$this->module_name = lang("facebook_accounts");
		$this->module_icon = "fa fa-facebook-square";
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
			'list_account' => $this->model->fetch("id, fullname, avatar, ids, status, pid, type", $this->table, "uid = '".session("uid")."'")
		);

		$this->load->view("account/index", $data);
	}

	public function oauth(){
		$fb = new FacebookAPI(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET);
		redirect($fb->login_url());
	}
	
	public function popup_add_account()
	{
		$this->load->library('user_agent');

		$data = array(
			"user_agent" => $this->agent->browser()
		);
		$this->load->view('account/popup_add_account', $data);
	}

	public function ajax_get_access(){
		$ids = ids();
		$access_token = $this->input->post("access_token");

		if(!permission("facebook_enable")){
			ms(array(
				"status" => "error",
				"message" => lang("disable_feature")
			));
		}

		if($access_token == ""){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('access_token_is_required')
	        ));
		}

		$generate_access_token = json_decode($access_token);

		if(is_object($generate_access_token)){
			if(isset($generate_access_token->access_token)){

				$access_token = $generate_access_token->access_token;

			}else if(isset($generate_access_token->error_msg)){

				ms(array(
		        	"status"  => "error",
		        	"message" => $generate_access_token->error_msg
		        ));

			}else{

				ms(array(
		        	"status"  => "error",
		        	"message" => lang('can_not_generate_token_please_try_again')
		        ));

			}
		}

		if (strrpos($access_token, "#") == true && strrpos($access_token, "&")) {
			$link_token = explode("#", $access_token);
			if(count($link_token) == 2){
				parse_str($link_token[1], $param_token);
				if(is_array($param_token) && isset($param_token['access_token'])){
					$access_token = $param_token['access_token'];
				}
			}
		}

		$fb   = new FacebookAPI();
		$fb->set_access_token($access_token);
		$user = $fb->get_current_user();

		if(is_string($user)){
			ms(array(
	        	"status"  => "error",
	        	"message" => $user
	        ));
		}

		set_session("facebook_access_token", $access_token);

		ms(array(
			"status"  => "success",
			"message" => lang("successfully")
		));
	}

	public function add_account(){
		$fb = new FacebookAPI(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET);
		$official_api = false;
		if(get("code")){
			$access_token = $fb->get_access_token();
			$official_api = true;
			set_session("facebook_official_api", $official_api?1:0 );
			set_session("facebook_access_token", $access_token);
		}else{
			unset_session("facebook_official_api");
		}
		
		$access_token = session("facebook_access_token");
		if(empty($access_token)){
			redirect(cn("account_manager"));
		}
		
		$fb->set_access_token($access_token);
		$userinfo = $fb->get_current_user();


		$groups = $fb->get_groups("group", $official_api);
		$pages = $fb->get_groups("page");

		$data = array(
			'module'        => $this->module,
			'module_name'   => $this->module_name,
			'module_icon'   => $this->module_icon,
			'userinfo'      => $userinfo,
			'pages'         => $pages,
			'groups'        => $groups,
			'official_api'  => $official_api
		);

		$this->template->build('account/add_account', $data);
	}

	public function ajax_add_account()
	{
		$accounts = $this->input->post("accounts");
		$access_token = session("facebook_access_token");
		$official = (int)session("facebook_official_api");

		if(empty($accounts)){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('please_select_at_least_one_item')
	        ));
		}

		$fb = new FacebookAPI();
		$fb->set_access_token($access_token);
		$userinfo = $fb->get_current_user();
		$groups = $fb->get_groups("group");
		$pages = $fb->get_groups("page");

		if(!empty($accounts)){
			if(!empty($userinfo)){
				$data = array();
				if(in_array("profile-".$userinfo->id, $accounts)){
					$data = array(
						"ids" => ids(),
						"uid" => session("uid"),
						"pid" => $userinfo->id,
						"type" => "profile",
						"fullname" => $userinfo->name,
						"url" => "https://fb.com/".$userinfo->id,
						"official" => ($official == 1)?3:0,
						"avatar" => "https://graph.facebook.com/{$userinfo->id}/picture",
						"access_token" => $access_token,
						"status" => 1,
						"changed" => NOW
					);

					$fb_account = $this->model->get("id", $this->table, "pid = '".$userinfo->id."' AND uid = '".session("uid")."'");
					if(empty($fb_account)){
						$data['created'] = NOW;

						if(!check_number_account($this->table)){
							ms(array(
								"status" => "error",
								"message" => lang("limit_social_accounts")
							));
						}

						$this->db->insert($this->table, $data);
					}else{
						$this->db->update($this->table, $data, "id = '{$fb_account->id}'");			
					}
				}
			}

			if(!empty($groups->data)){
				foreach ($groups->data as $key => $group) {
					$data = array();
					if(in_array("group-".$group->id, $accounts)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"pid" => $group->id,
							"type" => "group",
							"fullname" => $group->name,
							"url" => "https://fb.com/".$group->id,
							"official" => $official,
							"avatar" => $group->icon,
							"access_token" => $access_token,
							"status" => 1,
							"changed" => NOW
						);
						
						$fb_account = $this->model->get("id", $this->table, "pid = '".$group->id."' AND uid = '".session("uid")."'");
						if(empty($fb_account)){
							$data['created'] = NOW;

							if(!check_number_account($this->table)){
								ms(array(
									"status" => "error",
									"message" => lang("limit_social_accounts")
								));
							}

							$this->db->insert($this->table, $data);
						}else{
							$this->db->update($this->table, $data, "id = '{$fb_account->id}'");			
						}
					}

				}
			}

			if(!empty($pages->data)){
				foreach ($pages->data as $key => $page) {
					$data = array();
					if(in_array("page-".$page->id, $accounts)){
						$avatar = is_string($page->picture)?$page->picture:$page->picture->data->url;
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"pid" => $page->id,
							"type" => "page",
							"fullname" => $page->name,
							"url" => $page->link,
							"official" => $official,
							"avatar" => $avatar,
							"access_token" => $access_token,
							"status" => 1,
							"changed" => NOW
						);

						$fb_account = $this->model->get("id", $this->table, "pid = '".$page->id."' AND uid = '".session("uid")."'");
						if(empty($fb_account)){
							$data['created'] = NOW;

							if(!check_number_account($this->table)){
								ms(array(
									"status" => "error",
									"message" => lang("limit_social_accounts")
								));
							}

							$this->db->insert($this->table, $data);
						}else{
							$this->db->update($this->table, $data, "id = '{$fb_account->id}'");			
						}
					}
				}
			}
		}

		ms(array(
			"status"  => "success",
			"message" => lang("successfully")
		));
	}

	public function ajax_get_accounts()
	{
		$fb_accounts = $this->model->fetch("*", FACEBOOK_ACCOUNTS, "uid = '".session("uid")."' AND status = 1");
		$this->load->view("account/ajax_get_accounts", array("accounts" => $fb_accounts));
	}

	public function ajax_get_access_token()
	{
		$username = post("username");
		$password = post("password");
		$proxy    = post("proxy");
		$app      = post("app");

		if($username == ""){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('facebook_username_is_required')
	        ));
		}

		if($password == ""){
	        ms(array(
	        	"status"  => "error",
	        	"message" => lang('facebook_password_is_required')
	        ));
	    }

	    if(!$app){
	        ms(array(
	        	"status"  => "error",
	        	"message" => lang('facebook_application_is_required')
	        ));
	    }

	    $fb = new FacebookAPI();

	    //Get Link Create Token
		$page = $fb->get_page_access_token($username, encrypt_encode($password), $app);

		ms(array(
        	"status"   => "success",
        	"callback" => '<script>$(".iframe_access_token").html("<iframe src=\''.$page.'\'></iframe>");</script>'
        ));
	}

	public function ajax_delete_item(){
		$this->model->delete($this->table, post("id"), false);
	}
}