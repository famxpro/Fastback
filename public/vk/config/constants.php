<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Table
define('VK_ACCOUNTS', "vk_accounts");
define('VK_POSTS', "vk_posts");
define('VK_GROUPS', "vk_groups");


$vk_client_id = get_setting("vk_client_id", "");
$vk_client_secret = get_setting("vk_client_secret", "");

if($vk_client_id != "" && $vk_client_secret != ""){
	define('VK_CLIENT_ID', $vk_client_id);
	define('VK_CLIENT_SERECT', $vk_client_secret);
}else{
	define('VK_CLIENT_ID', get_option("vk_client_id", ""));
	define('VK_CLIENT_SERECT', get_option("vk_client_secret", ""));
}