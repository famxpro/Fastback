<?php
require "pinterestapi/autoload.php";
use DirkGroenen\Pinterest\Pinterest;
if(!class_exists("pinterestapi")){

    class pinterestapi{
        private $ClientID;
        private $ClientSecret;
        private $pin;

        public function __construct($client_id = null, $client_secret = null){
            $this->ClientID = $client_id;
            $this->ClientSecret = $client_secret;
            $this->pin = new Pinterest($client_id, $client_secret);
        }

        function login_url(){
            return $this->pin->auth->getLoginUrl(cn("pinterest/add_account"), array('read_public,write_public,read_relationships,write_relationships'));
        }

        function get_access_token(){
            try {
                if(get("code")){
                    $token = $this->pin->auth->getOAuthToken(get("code"));
                    $this->pin->auth->setOAuthToken($token->access_token);
                    return $token->access_token;
                }else{
                    redirect(cn("pinterest/oauth"));
                }
                
            } catch (Exception $e) {
                redirect(cn("pinterest/oauth"));
            }
        } 

        function set_access_token($access_token = ""){
            $this->pin->auth->setOAuthToken($access_token);
        }

        function get_state(){
            try {
                return $this->pin->auth->getState();
            } catch (Exception $e) {
                return false;
            }
        }

        function get_user_info($access_token){
            try {
                $this->pin->auth->setOAuthToken($access_token);
                return $this->pin->users->me(array('access_token' => $access_token, "fields" => 'id, first_name, last_name, username, bio, image, account_type, url, counts, created_at'));
            } catch (Exception $e) {
                echo $e->getMessage();
                if(strpos($e->getMessage(), "401")){
                    echo "<br/><strong style='color: red;'>This pinterest app has not been reviewed by pinterst so it is only for developers.</strong>";
                }
                exit(0);
            }
        }

        function get_boards(){
            try {
                return $this->pin->users->getMeBoards();     
            } catch (Exception $e) {
                return false;
            }
        }

        function post($data){
            $data     = (object)$data;
            $response = array();
            $data->data = (object)json_decode($data->data);
            
            try {
                $media      = $data->data->media;
                $caption    = $data->data->caption;
                $link       = $data->data->url;
                $board      = $data->board;

                //Auto Resize
                if(permission("watermark", $data->uid)){
                    $new_image_path = get_tmp_path(ids().".jpg");
                    Watermark($media[0], $new_image_path);
                    $media[0] = get_link_tmp($new_image_path);
                }

                $response = $this->pin->pins->create(array(
                    "image_url"  => $media[0],
                    "note"       => $caption,
                    "board"      => $board,
                    "link"       => $link
                ));
                return $response->id;
            } catch (Exception $e) {
                return array(
                    "status"  => "error",
                    "message" => $e->getMessage()
                );
            }
        }


        function get_search($keyword){
            try {
                return $this->pin->users->searchMePins($keyword);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        function search_pin_by_keyword($q)
        {
            $url = "https://pinterest.com/search/pins/?q=$q";

            $html = file_get_contents($url);

            $domd = new \DOMDocument();
            libxml_use_internal_errors(true);
            $domd->loadHTML($html);
            libxml_use_internal_errors(false);

            $items = $domd->getElementsByTagName('script');
            $data = array();

            foreach ($items as $item) {
                $data[] = [
                    'src' => $item->getAttribute('src'),
                    'outerHTML' => $domd->saveHTML($item),
                    'innerHTML' => $domd->saveHTML($item->firstChild),
                ];
            }

            foreach ($data as $key => $value) {
                $response = json_decode($value['innerHTML']);
                if (!$response) {
                    continue;
                }
                if (isset($response->tree->data->results)) {
                    foreach ($response->tree->data->results as $obj) {
                        pr($obj,1);
                        pr($obj->like_count);
                        $images = (Array) $obj->images;
                        pr($images['736x']->url);

                    }
                }
            }
        }

    }

}