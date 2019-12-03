<?php
	if(!function_exists("get_board_from_url")){
		function get_board_from_url($url){
			$board = str_replace("https://www.pinterest.com/", "", $url );
			$board = explode("/", $board);
			array_pop($board);
			$board = implode('/', $board); 
			return $board;
		}
	}