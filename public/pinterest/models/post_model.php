<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class post_model extends MY_Model {
	public $tb_accounts;
	public $tb_posts;

	public function __construct(){
		parent::__construct();
		$this->tb_accounts = PINTEREST_ACCOUNTS;
		$this->tb_posts = PINTEREST_POSTS;
	}

	public function getBoards(){
		$this->db->select("account.ids as ids, account.username, .account.avatar, boards.name, boards.pid as board_id, boards.name");
		$this->db->from(PINTEREST_ACCOUNTS." as account");
		$this->db->join(PINTEREST_BOARDS." as boards", 'boards.account = account.id');
		$this->db->where("account.status = 1 AND account.uid = '".session("uid")."'");
		$this->db->order_by("boards.id", "asc");
		$query = $this->db->get();
		if($query->result()){
			$result =  $query->result();
			return $result;
		}else{
			return false;
		}
	}
}
