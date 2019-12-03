
<?php
class youtube_livestream{

    private $client;
    private $youtube;
    protected $yt_language;
    protected $googleLiveBroadcastSnippet;
    protected $googleLiveBroadcastStatus;
    protected $googleYoutubeLiveBroadcast;
    protected $googleYoutubeLiveStreamSnippet;
    protected $googleYoutubeCdnSettings;
    protected $googleYoutubeLiveStream;
    protected $googleYoutubeVideoRecordingDetails;

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
        $this->client->setPrompt('consent');
        $this->client->setScopes(array('https://www.googleapis.com/auth/youtube.readonly', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.force-ssl', 'https://www.googleapis.com/auth/userinfo.email'));

        if($access_token != ""){
            $this->client->setAccessToken($access_token);
        }

        $this->youtube = new Google_Service_YouTube($this->client);
        $this->googleLiveBroadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet;
        $this->googleLiveBroadcastStatus = new Google_Service_YouTube_LiveBroadcastStatus;
        $this->googleYoutubeLiveBroadcast = new Google_Service_YouTube_LiveBroadcast;
        $this->googleYoutubeLiveStreamSnippet = new Google_Service_YouTube_LiveStreamSnippet;
        $this->googleYoutubeCdnSettings = new Google_Service_YouTube_CdnSettings;
        $this->googleYoutubeLiveStream = new Google_Service_YouTube_LiveStream;
        $this->googleYoutubeVideoRecordingDetails = new Google_Service_YouTube_VideoRecordingDetails;
    }

    /**
     * [broadcast creating the event on youtube]
     * @param  [type] $token [auth token for youtube channel]
     * @param  [type] $data  [array of the event details]
     * @return [type]        [response array of broadcast ]
     */
    public function broadcast($schedule = null, $is_schedule = false)
    {
        $spintax = new Spintax();
        $data = (object)$schedule;
        $data->data = (object)json_decode($data->data);
        $medias = $data->data->media;
        $title = @$spintax->process($data->data->title);
        $caption = @$spintax->process($data->data->caption);
        $tags = $data->data->tags != ""? explode(",", $data->data->tags) : array();
        $category = (int)@$spintax->process($data->data->category);
        $thumbnail = @$spintax->process($data->data->thumbnail);
        $startdt = gmdate("Y-m-d\TH:i:s\Z", strtotime($data->data->time_post_default));
        $privacy_status = isset($data->data->privacy_status) ? $data->data->privacy_status : "public";
        //$language = isset($data->data->language) ? $data->data->language : 'English';
        $livestream_result = isset($data->result)? json_decode($data->result) : array();

        $file = APPPATH."../".get_path_file($medias[0]);

        $videoInfo = self::videoInfo($file);

        if(!self::checkFFMPEG()){
            return lang("Your server not support FFMPEG. Please install it to can start live stream");
        }

        try{
            $response = [];
            if((!isset($livestream_result->rtmp) && !isset($livestream_result->id)) || $is_schedule){

                /** 
                 * [$service [instance of Google_Service_YouTube ]]
                 * @var [type]
                 */         
                $youtube = new Google_Service_YouTube($this->client);
                
                /** 
                 * Create an object for the liveBroadcast resource [specify snippet's title, scheduled start time, and scheduled end time]
                 */
                $this->googleLiveBroadcastSnippet->setTitle($title);
                $this->googleLiveBroadcastSnippet->setDescription($caption);
                $this->googleLiveBroadcastSnippet->setScheduledStartTime($startdt);

                /**
                 * object for the liveBroadcast resource's status ["private, public or unlisted"]
                 */
                $this->googleLiveBroadcastStatus->setPrivacyStatus($privacy_status);

                /** 
                 * API Request [inserts the liveBroadcast resource]
                 */
                $this->googleYoutubeLiveBroadcast->setSnippet($this->googleLiveBroadcastSnippet);
                $this->googleYoutubeLiveBroadcast->setStatus($this->googleLiveBroadcastStatus);
                $this->googleYoutubeLiveBroadcast->setKind('youtube#liveBroadcast');

                /**
                 * Execute Insert LiveBroadcast Resource Api [return an object that contains information about the new broadcast]
                 */
                $broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status', $this->googleYoutubeLiveBroadcast, array());
                $response['broadcast_response'] = $broadcastsResponse;
                $youtube_event_id = $broadcastsResponse['id'];

                /**
                 * set thumbnail to the event
                 */
                if($thumbnail != ""){
                    $thumbnail = get_path_file($thumbnail);
                    $thumb = self::uploadThumbnail($thumbnail, $youtube_event_id);
                }

                /**
                 * Call the API's videos.list method to retrieve the video resource.
                 */
                $listResponse = $youtube->videos->listVideos("snippet", array('id' => $youtube_event_id));
                $video = $listResponse[0]; 

                /**
                 * update the tags and language via video resource
                 */
                $videoSnippet = $video['snippet'];
                $videoSnippet['tags'] = $tags;
                $videoSnippet['categoryId'] = $category;
                $videoSnippet['description'] = $caption;

                /*if(!is_null($language)){
                    $temp = isset($this->yt_language[$language]) ? $this->yt_language[$language] : "en"; 
                    $videoSnippet['defaultAudioLanguage'] = $temp; 
                    $videoSnippet['defaultLanguage'] = $temp;  
                }*/
                $video['snippet'] = $videoSnippet;

                /** 
                 * Update video resource [videos.update() method.]
                 */
                $updateResponse = $youtube->videos->update("snippet", $video);
                $response['video_response'] = $updateResponse;

                /**
                 * object of livestream resource [snippet][title]
                 */
                $this->googleYoutubeLiveStreamSnippet->setTitle($title);

                /**
                 * object for content distribution  [stream's format,ingestion type.]
                 */
                $this->googleYoutubeCdnSettings->setFormat($videoInfo->format);
                $this->googleYoutubeCdnSettings->setIngestionType('rtmp');
                $response['video_info'] = $videoInfo;

                /** 
                 * API request [inserts liveStream resource.]
                 */
                $this->googleYoutubeLiveStream->setSnippet($this->googleYoutubeLiveStreamSnippet);
                $this->googleYoutubeLiveStream->setCdn($this->googleYoutubeCdnSettings);
                $this->googleYoutubeLiveStream->setKind('youtube#liveStream');

                /*
                 * execute the insert request [return an object that contains information about new stream]
                 */
                $streamsResponse = $youtube->liveStreams->insert('snippet,cdn', $this->googleYoutubeLiveStream, array());
                $response['stream_response'] = $streamsResponse;

                /**
                 * Bind the broadcast to the live stream
                 */
                $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
                    $broadcastsResponse['id'],'id,contentDetails',
                    array(
                        'streamId' => $streamsResponse['id'],
                    ));
                $response['bind_broadcast_response'] = $bindBroadcastResponse;

                $broadcast_id = $broadcastsResponse['id'];
                $stream_name = $streamsResponse->getCdn()->getIngestionInfo()->getStreamName();
                $stream_url = $streamsResponse->getCdn()->getIngestionInfo()->getIngestionAddress();
                $stream_url = $stream_url."/".$stream_name;

                if($is_schedule){
                    return $response;
                }

            } else {

                $broadcast_id = $livestream_result->id;
                $stream_url = $livestream_result->rtmp;

                $response = array(
                    "broadcast_response" => (object)array(
                        "id" => $broadcast_id
                    )
                );

            }

            /**************************/
            /*  PROCCESS LIVE STREAM  */
            /**************************/

            //Watermark
            $watermark_build = "";
            $add_text_build = "";
            switch ($data->data->add_watermark) {
                case 'watermark':

                    //ADD WATERMARK AND POSITION
                    $watermark = $data->data->watermark;
                    $watermark_position = $data->data->watermark_position;
                    if($watermark != ""){
                        $watermark_padding = 15;
                        $watermark_build = '-i '.$watermark.' -filter_complex "overlay=';
                        switch ($watermark_position) {
                            case 'top_left':
                                $watermark_position = ''.$watermark_padding.':'.$watermark_padding.'"';
                                break;

                            case 'top_right':
                                $watermark_position = '(main_w-overlay_w)-'.$watermark_padding.':'.$watermark_padding.'"';
                                break;

                            case 'bottom_left':
                                $watermark_position = ''.$watermark_padding.':(main_h-overlay_h)-'.$watermark_padding.'"';
                                break;

                            case 'bottom_right':
                                $watermark_position = '(main_w-overlay_w)-'.$watermark_padding.':(main_h-overlay_h)-'.$watermark_padding.'"';
                                break;

                            case 'top_center':
                                $watermark_position = '(main_w-overlay_w)/2:'.$watermark_padding.'"';
                                break;

                            case 'bottom_center':
                                $watermark_position = '(main_w-overlay_w)/2:(main_h-overlay_h)-'.$watermark_padding.'"';
                                break;

                            default:
                                $watermark_position = '(main_w-overlay_w)/2:(main_h-overlay_h)/2"';
                                break;
                        }

                        $watermark_build .= $watermark_position;
                    }

                    break;

                case 'add_text':
                    
                    //Add Text And Postion
                    $add_text = $data->data->add_text;
                    if($add_text != ""){
                        $add_text_padding = 15;
                        $add_text_color = "white";
                        $add_text_fontsize = 20;
                        $add_text_position = $data->data->watermark_position;
                        $add_text_build = '-vf drawtext="fontsize='.$add_text_fontsize.':fontcolor='.$add_text_color.':text=\''.$add_text.'\'';
                        switch ($add_text_position) {
                            case 'top_left':
                                $add_text_position = ':x='.$add_text_padding.':y='.$add_text_padding.'"';
                                break;

                            case 'top_right':
                                $add_text_position = ':x=(w-text_w)-'.$add_text_padding.':y='.$add_text_padding.'"';
                                break;

                            case 'bottom_left':
                                $add_text_position = ':x='.$add_text_padding.':y=(h-text_h)-'.$add_text_padding.'"';
                                break;

                            case 'bottom_right':
                                $add_text_position = ':x=(w-text_w)-'.$add_text_padding.':y=(h-text_h)-'.$add_text_padding.'"';
                                break;

                            case 'top_center':
                                $add_text_position = ':x=(w-text_w)/2:y='.$add_text_padding.'"';
                                break;

                            case 'bottom_center':
                                $add_text_position = ':x=(w-text_w)/2:y=(h-text_h)-'.$add_text_padding.'"';
                                break;

                            default:
                                $add_text_position = ':x=(w-text_w)/2:y=(h-text_h)/2"';
                                break;
                        }

                        $add_text_build .= $add_text_position;
                    }

                    break;
            }

            $file = APPPATH."../".get_path_file($medias[0]);

            $stream_url = preg_replace(
                '#^rtmps://([^/]+?):443/#ui',
                'rtmp://\1:80/',
                $stream_url
            );
            
            $file_stream = $file;

            $livestream_code = sprintf(
                'ffmpeg -re -i "%s" %s %s -flags +global_header -acodec libmp3lame -ar 44100 -b:a 128k -pix_fmt yuv420p -profile:v baseline -bufsize 6000k -vb 400k -maxrate 1500k -deinterlace -vcodec libx264 -preset veryfast -g 30 -r 30 -f flv "%s" > /dev/null &',
                $file_stream,
                $add_text_build,
                $watermark_build,
                $stream_url
            );

            //Start Live Stream
            @exec($livestream_code);

            sleep(5);

            $transitionTesting = self::transitionEvent($broadcast_id, 'testing');

            if(is_string($transitionTesting)){
                return $transitionTesting;
            }

            sleep(15);

            $transitionLive = self::transitionEvent($broadcast_id, 'live');

            if(is_string($transitionLive)){
                return $transitionLive;
            }

            return $response;

        } catch ( Google_Service_Exception $e ) {
            return $e->getMessage();
        } catch ( Google_Exception $e ) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [updateBroadcast update the already created event on youtunbe channel]
     * @param  [type] $token            [channel auth token]
     * @param  [type] $data             [event details]
     * @param  [type] $youtube_event_id [eventID]
     * @return [type]                   [response array for various process in the update]
     */
    public function updateBroadcast($schedule = "", $youtube_event_id)
    {

        $spintax = new Spintax();
        $data = (object)$schedule;
        $data->data = (object)json_decode($data->data);
        $medias = $data->data->media;
        $title = @$spintax->process($data->data->title);
        $caption = @$spintax->process($data->data->caption);
        $tags = $data->data->tags != ""? explode(",", $data->data->tags) : array();
        $category = (int)@$spintax->process($data->data->category);
        $thumbnail = @$spintax->process($data->data->thumbnail);
        $startdt = gmdate("Y-m-d\TH:i:s\Z", strtotime($data->data->time_post_default));
        $privacy_status = isset($data->data->privacy_status) ? $data->data->privacy_status : "public";
        //$language = isset($data->data->language) ? $data->data->language : 'English';
        $livestream_result = isset($data->result)? json_decode($data->result) : array();

        $file = APPPATH."../".get_path_file($medias[0]);

        $videoInfo = self::videoInfo($file);

        if(!self::checkFFMPEG()){
            return lang("Your server not support FFMPEG. Please install it to can start live stream");
        }

        try{

            /** 
             * [$service [instance of Google_Service_YouTube ]]
             * @var [type]
             */         
            $youtube = new Google_Service_YouTube($this->client);
            
            /**
             * Create an object for the liveBroadcast resource's snippet [snippet's title, scheduled start time, and scheduled end time.]
             */
            $this->googleLiveBroadcastSnippet->setTitle($title);
            $this->googleLiveBroadcastSnippet->setDescription($caption);
            $this->googleLiveBroadcastSnippet->setScheduledStartTime($startdt);
            
            /** 
             * Create an object for the liveBroadcast resource's status ["private, public or unlisted".]
             */
            $this->googleLiveBroadcastStatus->setPrivacyStatus($privacy_status);

            /**
             * Create the API request  [inserts the liveBroadcast resource.]
             */
            $this->googleYoutubeLiveBroadcast->setSnippet($this->googleLiveBroadcastSnippet);
            $this->googleYoutubeLiveBroadcast->setStatus($this->googleLiveBroadcastStatus);
            $this->googleYoutubeLiveBroadcast->setKind('youtube#liveBroadcast');
            $this->googleYoutubeLiveBroadcast->setId($youtube_event_id);

            /** 
             * Execute the request [return info about the new broadcast ]
             */
            $broadcastsResponse = $youtube->liveBroadcasts->update(
                'snippet,status',
                $this->googleYoutubeLiveBroadcast, 
                array()
            );

            /**
             * set thumbnail
             */
            if($thumbnail != ""){
                $thumbnail = get_path_file($thumbnail);
                $thumb = self::uploadThumbnail($thumbnail, $youtube_event_id);
            }

            /** 
             * Call the API's videos.list method [retrieve the video resource]
             */
            $listResponse = $youtube->videos->listVideos(
                "snippet",
                array(
                    'id' => $youtube_event_id
                )
            );

            $video = $listResponse[0]; 
            $videoSnippet = $video['snippet'];
            $videoSnippet['tags'] = $tags;   

            /** 
             * set Language and other details
             */
            /*if(!is_null($language)){
                $temp = isset($this->yt_language[$language]) ? $this->yt_language[$language] : "en"; 
                $videoSnippet['defaultAudioLanguage'] = $temp; 
                $videoSnippet['defaultLanguage'] = $temp;  
            }*/
            $videoSnippet['title'] = $title; 
            $videoSnippet['description'] = $caption; 
            $videoSnippet['categoryId'] = $category;
            $videoSnippet['scheduledStartTime'] = $startdt; 
            $video['snippet'] = $videoSnippet;

            /** 
             * Update the video resource  [call videos.update() method]
             */
            $updateResponse = $youtube->videos->update(
                "snippet", 
                $video
            );

            $response['broadcast_response'] = $updateResponse;
            $youtube_event_id = $updateResponse['id'];
            
            $this->googleYoutubeLiveStreamSnippet->setTitle($title);

            /**
             * object for content distribution  [stream's format,ingestion type.]
             */
            $this->googleYoutubeCdnSettings->setFormat($videoInfo->format);
            $this->googleYoutubeCdnSettings->setIngestionType('rtmp');
            $response['video_info'] = $videoInfo;

            /** 
             * API request [inserts liveStream resource.]
             */
            $this->googleYoutubeLiveStream->setSnippet($this->googleYoutubeLiveStreamSnippet);
            $this->googleYoutubeLiveStream->setCdn($this->googleYoutubeCdnSettings);
            $this->googleYoutubeLiveStream->setKind('youtube#liveStream');

            /**
             * execute the insert request [return an object that contains information about new stream]
             */
            $streamsResponse = $youtube->liveStreams->insert(
                'snippet,cdn', 
                $this->googleYoutubeLiveStream, 
                array()
            );

            $response['stream_response'] = $streamsResponse;

            /**
             * Bind the broadcast to the live stream
             */
            $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
                $updateResponse['id'],'id,contentDetails',
                array(
                    'streamId' => $streamsResponse['id'],
                )
            );

            $response['bind_broadcast_response'] = $bindBroadcastResponse;

            return $response;

        } catch ( Google_Service_Exception $e ) {
            return $e->getMessage();
        } catch ( Google_Exception $e ) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [uploadThumbnail upload thumbnail for the event]
     * @param  string $url     [path to image]
     * @param  [type] $videoId [eventId]
     * @return [type]          [thumbnail url]
     */
    public function uploadThumbnail($url = '', $videoId)
    {
        try{
            /** 
             * [$service [instance of Google_Service_YouTube ]]
             * @var [type]
             */             
            $youtube = new Google_Service_YouTube($this->client);
            $videoId = $videoId;
            $imagePath = $url;
            /**
             * size of chunk to be uploaded  in bytes [default  1 * 1024 * 1024] (Set a higher value for reliable connection as fewer chunks lead to faster uploads)
             */             
            $chunkSizeBytes = 1 * 1024 * 1024;
            $this->client->setDefer(true);
            /**
             * Setting the defer flag to true tells the client to return a request which can be called with ->execute(); instead of making the API call immediately
             */
            $setRequest = $youtube->thumbnails->set($videoId);
            /**
             * MediaFileUpload object [resumable uploads]
             */
            $media = new Google_Http_MediaFileUpload(
                $this->client,
                $setRequest,
                'image/png',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(filesize($imagePath));
            /** 
             * Read the media file [to upload chunk by chunk]
             */
            $status = false;
            $handle = fopen($imagePath, "rb");
            while (!$status && !feof($handle)) {
              $chunk = fread($handle, $chunkSizeBytes);
              $status = $media->nextChunk($chunk);
            }
            fclose($handle);
            /**
             * set defer to false [to make other calls after the file upload]
             */
            $this->client->setDefer(false);
            $thumbnailUrl = $status['items'][0]['default']['url'];
            return $thumbnailUrl;
        } catch( Google_Exception $e ) {
            return $e->getMessage();
        }
    }

    /**
     * [transitionEvent transition the state of event [test, start streaming , stop streaming]]
     * @param  [type] $token            [auth token for the channel]
     * @param  [type] $youtube_event_id [eventId]
     * @param  [type] $broadcastStatus  [transition state - ["testing", "live", "complete"]]
     * @return [type]                   [transition status]
     */
    public function transitionEvent($youtube_event_id, $broadcastStatus)
    {
        try{

            $part = "status, id, snippet";
            /** 
             * [$service [instance of Google_Service_YouTube ]]
             * @var [type]
             */         
            $youtube = new Google_Service_YouTube($this->client);
            $liveBroadcasts = $youtube->liveBroadcasts;
            $transition = $liveBroadcasts->transition($broadcastStatus, $youtube_event_id, $part);

            return $transition;

        } catch(Google_Exception $e ) {
            return $e->getMessage();
        } catch(Exception $e){
            return $e->getMessage();
        }
    }

    /** 
     * [deleteEvent delete an event created in youtube]
     * @param  [type] $token            [auth token for channel]
     * @param  [type] $youtube_event_id [eventID]
     * @return [type]                   [deleteBroadcastsResponse]
     */
    public function deleteEvent($youtube_event_id)
    {
        try {

            /** 
             * [$service [instance of Google_Service_YouTube ]]
             * @var [type]
             */         
            $youtube = new Google_Service_YouTube($this->client);
            $deleteBroadcastsResponse = $youtube->liveBroadcasts->delete($youtube_event_id);
            return $deleteBroadcastsResponse;
                
        } catch ( Google_Service_Exception $e ) {
            return $e->getMessage();
        } catch ( Google_Exception $e ) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function videoInfo($videoPath = ""){
        @exec('ffprobe -show_streams -i "'.$videoPath.'"', $output, $statusCode);
        $format = "240p";
        $result = array();
        if($statusCode == 0 && !empty($output)){

            $result = array();
            foreach ($output as $value) {
                
                $parse_value = explode("=", $value);
                if(count($parse_value) == 2){
                    $key = $parse_value[0];
                    $val = $parse_value[1];

                    $result[$key] = $val;

                }

            }

            $result = (object)$result;

            if(!empty($result)){

                $w = $result->width;
                $h = $result->height;

                if($w >= 1920 && $h >= 1080){
                    $format = "1080p";
                }elseif ($w >= 1280 && $h >= 720) {
                    $format = "720p";
                }elseif ($w >= 854 && $h >= 480) {
                    $format = "480p";
                }elseif ($w >= 640 && $h >= 360) {
                    $format = "360p";
                }elseif ($w >= 426 && $h >= 240) {
                    $format = "240p";
                }
            }

        }

        $result->format = $format;

        return $result;

    }

    /**
     * Check for ffmpeg/avconv dependencies.
     *
     * TIP: If your binary isn't findable via the PATH environment locations,
     * you can manually set the correct path to it. Before calling any functions
     * that need FFmpeg, you must simply assign a manual value (ONCE) to tell us
     * where to find your FFmpeg, like this:
     *
     * \InstagramAPI\Utils::$ffmpegBin = '/home/exampleuser/ffmpeg/bin/ffmpeg';
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFMPEG()
    {
        $ffmpegBin = null;
        // We only resolve this once per session and then cache the result.
        if ($ffmpegBin === null) {
            @exec('ffmpeg -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                $ffmpegBin = 'ffmpeg';
            } else {
                @exec('avconv -version 2>&1', $output, $statusCode);
                if ($statusCode === 0) {
                    $ffmpegBin = 'avconv';
                } else {
                    $ffmpegBin = false; // Nothing found!
                }
            }
        }

        return $ffmpegBin;
    }

}