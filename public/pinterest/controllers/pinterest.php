<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class pinterest extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		
		$this->table  = PINTEREST_ACCOUNTS;
		$this->module = get_class($this);
		$this->module_name = lang("pinterest_accounts");
		$this->module_icon = "fa fa-pinterest";
		$this->load->model($this->module.'_model', 'model');

	}

	public function block_general_settings(){
		$data = array();
		$this->load->view('account/general_settings', $data);
	}

	public function block_list_account(){
		$this->load->model($this->module.'_model');
		$data = array(
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon,
			'list_account' => $this->model->fetch("id, username, avatar, pid, ids, status", $this->table, "uid = '".session("uid")."'")
		);
		$this->load->view("account/index", $data);
	}
	
	public function oauth(){
		$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
		redirect($pin->login_url());
	}

	public function add_account(){
		$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
		$access_token = $pin->get_access_token();
		set_session("pinterest_access_token", $access_token);
		
		redirect(cn("pinterest/add_board"));
	}

	public function add_board(){
		$access_token = session("pinterest_access_token");
		$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
		$pin->set_access_token($access_token);
		$userinfo = (object)$pin->get_user_info($access_token);
		$boards = $pin->get_boards();

		if(empty($boards)) redirect(cn('account_manager'));

		$data = array(
			'boards' => $boards,
			'account' => $userinfo
		);
		$this->template->build('account/add_board', $data);

	}

	public function ajax_add_board(){
		$ids = post("ids");
		$boards = $this->input->post("boards[]");
		
		if(empty($boards)){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('please_select_at_least_one_item')
	        ));
		}
		
		$access_token = session("pinterest_access_token");
		$pin = new PinterestAPI(PINTEREST_CLIENT_ID, PINTEREST_CLIENT_SERECT);
		$pin->set_access_token($access_token);
		$userinfo     = (object)$pin->get_user_info($access_token);
		$me_boards = $pin->get_boards();

		$image = BASE."public/pinterest/img/no-avatar.jpg";
		if(!empty($userinfo->image)){
			foreach ($userinfo->image as $row) {
				$row   = (object)$row;
				$image = $row->url;
			}
		}

		foreach ($boards as $board) {
			if(!check_number_account($this->table)){
				ms(array(
					"status" => "error",
					"message" => lang("limit_social_accounts")
				));
			}

			$check_board = false;
			$boar_name = "";
			foreach ($me_boards as $key => $me_board) {
				if($board == get_board_from_url($me_board->url)){
					$check_board = true;
					$boar_name = $me_board->name;
				}
			}

			if($check_board){
				$data_board = array(
					"uid"          => session("uid"),
					"ids"          => ids(),
					"pid"          => $board,
					"username"     => $boar_name,
					"avatar"       => $image,
					"access_token" => $access_token,
					"status"       => 1,
					"changed"      => NOW
				);
				
				$this->db->insert($this->table, $data_board);
			}
		}

		ms(array(
        	"status"  => "success",
        	"message" => lang('add_boards_successfully')
        ));
	}

	public function ajax_delete_item(){
		$board = $this->model->get("*", $this->table, "ids = '".post("id")."'");
		$this->model->delete($this->table, post("id"), false);
	}
}