<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class post_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		$this->tb_accounts = FACEBOOK_ACCOUNTS;
		$this->tb_posts = FACEBOOK_POSTS;
	}
}
