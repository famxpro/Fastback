<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('FACEBOOK_ACCOUNTS', "facebook_accounts");
define('FACEBOOK_POSTS', "facebook_posts");
define('FACEBOOK_GROUPS', "facebook_groups");

$facebook_app_id = get_setting("facebook_app_id", "");
$facebook_app_secret = get_setting("facebook_app_secret", "");

if($facebook_app_id != "" && $facebook_app_secret != ""){
	define('FACEBOOK_APP_ID', $facebook_app_id);
	define('FACEBOOK_APP_SECRET', $facebook_app_secret);
}else{
	define('FACEBOOK_APP_ID', get_option("facebook_app_id", ""));
	define('FACEBOOK_APP_SECRET', get_option("facebook_app_secret", ""));
}