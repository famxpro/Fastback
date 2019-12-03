<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Table
define('PINTEREST_ACCOUNTS', "pinterest_accounts");
define('PINTEREST_BOARDS', "pinterest_boards");
define('PINTEREST_POSTS', "pinterest_posts");

$pinterest_app_id = get_setting("pinterest_app_id", "");
$pinterest_app_secret = get_setting("pinterest_app_secret", "");

if($pinterest_app_id != "" && $pinterest_app_secret != ""){
	define('PINTEREST_CLIENT_ID', $pinterest_app_id);
	define('PINTEREST_CLIENT_SERECT', $pinterest_app_secret);
}else{
	define('PINTEREST_CLIENT_ID', get_option("pinterest_app_id", ""));
	define('PINTEREST_CLIENT_SERECT', get_option("pinterest_app_secret", ""));
}