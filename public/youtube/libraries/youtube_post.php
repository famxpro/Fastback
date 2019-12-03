<?php
class youtube_post{

    private $client;
    private $youtube;

    public function __construct($access_token = null){
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

        if($access_token != ""){
            $this->client->setAccessToken($access_token);
        }

        $this->youtube = new Google_Service_YouTube($this->client);
    }
    
    function post($schedule){
        $spintax = new Spintax();
        $data = (object)$schedule;
        $data->data = (object)json_decode($data->data);
        $medias = $data->data->media;
        $title = @$spintax->process($data->data->title);
        $caption = @$spintax->process($data->data->caption);
        $tags = @$spintax->process($data->data->tags);
        $category = (int)@$spintax->process($data->data->category);


        $videoPath = get_path_file($medias[0]);

        try {

            $snippet = new Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle($title);
            $snippet->setDescription($caption);
            if($tags != ""){
                $tags = explode(",", $tags);
                $snippet->setTags($tags);
            }

            if($category != 0){
                $snippet->setCategoryId($category);
            }

            $status = new Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = "public";

            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            // Specify the size of each chunk of data, in bytes. Set a higher value for
            // reliable connection as fewer chunks lead to faster uploads. Set a lower
            // value for better recovery on less reliable connections.
            $chunkSizeBytes = 1 * 1024 * 1024;

            // Setting the defer flag to true tells the client to return a request which can be called
            // with ->execute(); instead of making the API call immediately.
            $this->client->setDefer(true);

            // Create a request for the API's videos.insert method to create and upload the video.
            $insertRequest = $this->youtube->videos->insert("status,snippet", $video);

            // Create a MediaFileUpload object for resumable uploads.
            $media = new Google_Http_MediaFileUpload(
                $this->client,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(filesize($videoPath));


            // Read the media file and upload it chunk by chunk.
            $status = false;
            $handle = fopen($videoPath, "rb");
            while (!$status && !feof($handle)) {
              $chunk = fread($handle, $chunkSizeBytes);
              $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false
            $this->client->setDefer(false);

            return $status;

        } catch (Google_Service_Exception $e) {
            return "A service error occurred: ".$e->getMessage();
        } catch (Google_Exception $e) {
            return "An client error occurred: ".$e->getMessage();
        }

    }

}