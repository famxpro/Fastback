<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class youtube extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		
		$this->table = YOUTUBE_ACCOUNTS;
		$this->module = get_class($this);
		$this->module_name = lang("youtube_accounts");
		$this->module_icon = "fa fa-youtube";
		$this->load->model($this->module.'_model', 'model');

	}

	public function index(){

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
			'list_account' => $this->model->fetch("id, ids, pid, username, avatar, status", $this->table, "uid = '".session("uid")."'")
		);
		$this->load->view("account/index", $data);
	}
	
	public function oauth(){
		$yt = new GoogleAPI();
		redirect($yt->login_url());
	}

	public function add_account(){
		if(!permission($this->module."_enable")){
			redirect(cn("account_manager"));
		}

		$yt = new GoogleAPI();
		$access_token = $yt->get_access_token();
		$channel_info = $yt->get_channel();
		if($channel_info){
			$channel_info = $channel_info->getItems()[0];

			$id = $channel_info->getId();
			$title = $channel_info->getSnippet()->getTitle();
			$thumbnail = $channel_info->getSnippet()->getThumbnails()->getDefault()->getUrl();

			$data = array(
				"uid"          => session("uid"),
				"ids"          => ids(),
				"pid"          => $id,
				"username"     => $title,
				"avatar"       => $thumbnail,
				"access_token" => $access_token,
				"status"       => 1,
				"changed"      => NOW
			);

			$account = $this->model->get("*", $this->table, "pid = '".$userinfo->id."' AND uid = '".session("uid")."'");
			if(empty($account)){
				if(!check_number_account($this->table)){
					redirect(cn("account_manager"));
				}
				$data["created"] = NOW;
				$this->db->insert($this->table, $data);
			}else{
				$this->db->update($this->table, $data, array("id" => $account->id));
			}
		}

		redirect(cn("account_manager"));
	}

	public function ajax_add_account(){
		
	}

	public function ajax_delete_item(){
		$item = $this->model->get("username", $this->table, "ids = '".post("id")."'");
		if(!empty($item)){
			$this->db->delete('instagram_sessions', "username = '{$item->username}'");
		}
		$this->model->delete($this->table, post("id"), false);

	}
}