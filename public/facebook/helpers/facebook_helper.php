<?php
	if(!function_exists("get_first_access_token")){
		function get_first_access_token(){
			$CI = &get_instance();
			$fb_account = $CI->model->get("access_token", FACEBOOK_ACCOUNTS, "uid = '".session("uid")."' AND status = 1", "fbapp", "asc");
			if(!empty($fb_account)){
				return $fb_account->access_token;
			}else{
				return false;
			}
		}
	}

	if(!function_exists("get_facebook_paging")){
		function get_facebook_paging($data){
			$next = "";
			$prev = "";
			if(isset($data->paging) && !empty($data->paging)){

				if(isset($data->paging->cursors)){
					if(isset($data->paging->previous)){
						$prev = $data->paging->cursors->before;
					}
					
					if(isset($data->paging->next)){
						$next = $data->paging->cursors->after;
					}
				}else{
					if(isset($data->paging->previous)){
						parse_str($data->paging->previous, $get_array);
						if(row($get_array, 'offset') != ""){
							$prev = row($get_array, 'offset');
						}
					}
					
					if(isset($data->paging->next)){
						parse_str($data->paging->next, $get_array);
						if(row($get_array, 'offset') != ""){
							$next = row($get_array, 'offset');
						}
					}
				}
			}

			return (object)array(
				"next" => $next,
				"prev" => $prev
			);
		}
	}
?>