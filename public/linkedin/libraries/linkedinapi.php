<?php
require "linkedin-api-v2/autoload.php";

if(!class_exists("linkedinapi")){
    class linkedinapi{
        private $ClientID;
        private $ClientSecret;
        private $CallbackURL;
        private $client;
        private $access_token;

        public function __construct($client_id = null, $client_secret = null){
            $this->ClientID = $client_id;
            $this->ClientSecret = $client_secret;
            $this->CallbackURL = cn("linkedin/add_account");
            $this->client = new \Phillipsdata\LinkedIn\LinkedIn($client_id, $client_secret, cn("linkedin/add_account"));
        }

        function login_url(){
            $login_url = $this->client->getPermissionUrl( "r_emailaddress r_liteprofile w_member_social" );
            return $login_url;
        }

        function relogin($e, $account){
            if(isset($e->status) && $e->status == 401){
                $CI = &get_instance();
                $CI->db->update("linkedin_accounts", array("status" => 0), "id = '{$account}'");
            }
        }

        function get_access_token(){
            try {
                if(get("code")){
                    $tokenResponse = $this->client->getAccessToken(get("code"));

                    if($tokenResponse->status() == 200){
                        $tokenResponse = $tokenResponse->response();
                        $this->access_token = $tokenResponse->access_token;
                        return $this->access_token;
                    }else{
                        redirect(cn("linkedin/oauth"));
                    }
                    
                }else{
                    redirect(cn("linkedin/oauth"));
                }
                
            } catch (Exception $e) {
                redirect(cn("linkedin/oauth"));
            }
        }

        function set_access_token($access_token){
            $this->access_token = (object)array(
                "access_token" => $access_token
            );

            $this->client->setAccessToken($this->access_token);
        }

        function get_user_info(){
            try {
                $profile = $this->client->getUser();
                if($profile->status() == 200){
                    return $profile->response();
                }else{
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        function get_company(){
            try {
                $companies = $this->client->getCompanies();
                if($companies->status() == 200){
                    return $companies->response();
                }else{
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        function get_post(){
            try {
                $this->client->setAccessToken($this->access_token);
                $profile = $this->client->get(
                    'people/~:(id,email-address,first-name,last-name,picture-url,public-profile-url)'
                );

                return $profile;
            } catch (Exception $e) {
                return false;
            }
        }

        function get_title($url){
            $result = get_curl($url);
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding($result, 'HTML-ENTITIES', 'UTF-8'));
            $title = $doc->getElementsByTagName('title');
            return isset($title->item(0)->nodeValue) ? $title->item(0)->nodeValue : "&nbsp;";
        }

        function post($data, $account = array()){
            $data     = (object)$data;
            $response = array();
            $data->data = (object)json_decode($data->data);
            
            try {
                $media      = $data->data->media;
                $caption    = $data->data->caption;
                $url        = $data->data->url;

                switch ($data->type) {
                    case 'link':
                        $content = [
                            'lifecycleState' => 'PUBLISHED',
                            'specificContent' => [
                                'com.linkedin.ugc.ShareContent' => [
                                    'shareCommentary' => [
                                        'text' => $caption
                                    ],
                                    "shareMediaCategory" => "ARTICLE",
                                    "media" => [
                                        [
                                            "status" => "READY",
                                            "description"=> [
                                                "text" => $caption
                                            ],
                                            "originalUrl" => $url,
                                            "title" => [
                                                "text" => $this->get_title($url)
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC']
                        ];
                        break;

                    case 'media':
                        //Auto Resize
                        if(permission("watermark", $data->uid)){
                            $new_image_path = get_tmp_path(ids().".jpg");
                            $new_image_path = Watermark($media[0], $new_image_path, $data->uid);
                            $media[0] = $new_image_path;
                        }

                        $media = APPPATH."../".get_path_file($media[0]);
                        $media = $this->client->upload($media);

                        $content = [
                            'lifecycleState' => 'PUBLISHED',
                            'specificContent' => [
                                'com.linkedin.ugc.ShareContent' => [
                                    'shareCommentary' => [
                                        'text' => $caption
                                    ],
                                    "shareMediaCategory" => "IMAGE",
                                    "media" => [
                                        [
                                            "status" => "READY",
                                            "description"=> [
                                                "text" => $caption
                                            ],
                                            "media" => $media,
                                            "title" => [
                                                "text" => $caption
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC']
                        ];
                        break;
                    
                    default:
                        $content = [
                            'lifecycleState' => 'PUBLISHED',
                            'specificContent' => [
                                'com.linkedin.ugc.ShareContent' => [
                                    'shareCommentary' => [
                                        'text' => $caption
                                    ],
                                    "shareMediaCategory" => "NONE"
                                ]
                            ],
                            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC']
                        ];
                        break;
                }

                $response = (object)$this->client->share($content);
                $response = $response->response();

                if(isset($response->id)){
                    
                    return $response->id;

                }else if(isset($response->message)){
                    $this->relogin($response, $data->account);
                    $message = explode(": ", $response->message);
                    return array(
                        "status"  => "error",
                        "message" => end($message)
                    );

                }

                return array(
                    "status"  => "error",
                    "message" => lang("Unknow error")
                );
            } catch (Exception $e) {
                pr($e,1);
                return array(
                    "status"  => "error",
                    "message" => $e->getDescription()
                );
            }
        }
    }
}