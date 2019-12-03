<?php
require APPPATH."libraries/Google/autoload.php";

class googleapi{
    private $ClientID;
    private $ClientSecret;
    private $client;
    private $youtube;

    public function __construct(){
        $api_key = get_option("youtube_api_key", "");
        $client_id = get_option("youtube_client_id", "");
        $client_secret = get_option("youtube_client_secret", "");

        $this->client = new Google_Client();
        $this->client->setAccessType("offline");
        $this->client->setApprovalPrompt("force");
        $this->client->setApplicationName('YouTube Tools');
        $this->client->setRedirectUri(cn("youtube/add_account"));
        $this->client->setClientId($client_id);
        $this->client->setClientSecret($client_secret);
        $this->client->setDeveloperKey($api_key);
        $this->client->setScopes(array('https://www.googleapis.com/auth/youtube.readonly', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.force-ssl', 'https://www.googleapis.com/auth/userinfo.email'));

        $this->youtube = new Google_Service_YouTube($this->client);
    }

    function login_url(){
        return $this->client->createAuthUrl();
    }

    function get_access_token(){
        try {
            if(get("code")){
                $this->client->authenticate(get("code"));
                $oauth2 = new Google_Service_Oauth2($this->client);
                $token = $this->client->getAccessToken();
                $this->client->setAccessToken($token);
                return $token;
            }else{
                redirect(cn("youtube/oauth"));
            }
            
        } catch (Exception $e) {
            redirect(cn("youtube/oauth"));
        }
    }

    function set_access_token($access_token){
        $this->client->setAccessToken($access_token);
    }

    function get_user_info($access_token){
        try {
            $oauth2 = new Google_Service_Oauth2($this->client);
            $userinfo = $oauth2->userinfo->get();
            return $userinfo;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_channel(){
        try {
            $part = 'brandingSettings,status,id,snippet,contentDetails,contentOwnerDetails,statistics';
            $optionalParams = array(
                'mine' => true
            );
            return $this->youtube->channels->listChannels($part, $optionalParams);
        } catch (Exception $e) {
            return false;
        }
    }
}