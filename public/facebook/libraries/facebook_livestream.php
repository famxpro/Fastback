<?php

if(!class_exists("facebook_livestream")){
    require "Facebook/autoload.php";

    class facebook_livestream{
        private $fb;

        public function __construct($fb){
            $this->fb = $fb;
        }
        /*
        * Live Stream
        */
        function livestream($data, $time_post = 1, $videoId = 0){
            $spintax = new Spintax();
            $data = (object)$data;

            if($data->type == 'page'){
                $access_token_page = $this->fb->get_access_token_page($data->group);
                if($access_token_page){
                    $this->fb->set_access_token($access_token_page);
                }
            }

            $data->data = (object)json_decode($data->data);
            $medias = $data->data->media;
            $title = @$spintax->process($data->data->title);
            $caption = @$spintax->process($data->data->caption);
            $this->account_id = $data->account;

            //Get GID
            switch ($data->type) {
                case 'profile':
                    $gid = "me";
                    break;
                
                default:
                    $gid = $data->group;
                    break;
            }

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
                        $add_text_fontsize = 30;
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
            
            try {
                $params = ['title' => $title];

                if($caption != ""){
                    $params['description'] = $caption;
                }

                $file = realpath(APPPATH."../".get_path_file($medias[0]));

                if(validateYoutubeVideoId($file)){
                    $video = download_youtube_video($file);
                    if($video){
                        $file = $video['url'];
                    } 
                }

                if($time_post == 1){

                    $params['status'] = "LIVE_NOW";

                }else{

                    /*$screen_file = "assets/tmp/".ids().".jpg";
                    $screen_file_root = $_SERVER['DOCUMENT_ROOT']."/".$screen_file;

                    $screen_code = sprintf(
                        'ffmpeg -ss 00:00:05 -i "%s" -vf scale=320:200 -vframes 1 "%s"',
                        $file,
                        $screen_file_root
                    );

                    @exec($screen_code);*/

                    $params['status'] = "SCHEDULED_UNPUBLISHED";
                    $params['planned_start_time'] = strtotime($time_post);
                    //$params['schedule_custom_profile_image'] = $this->fb->upload(PATH.$screen_file);
                    //@unlink($screen_file);

                }

                //Create Live Video
                if($videoId == 0){
                    $createLiveVideo = $this->fb->post('/'.$gid.'/live_videos', $params);
                }else{
                    $this->fb->post('/'.$videoId , $params);
                    $createLiveVideo = $this->fb->get('/'.$videoId);
                }

                if(is_string($createLiveVideo)){
                    return $createLiveVideo;
                }

                $videoId = $createLiveVideo->id;
                $stream_url = $createLiveVideo->stream_url;
                $file_stream = $file;

                //-protocol_whitelist "file,http,https,tcp,tls" 
                if($params['status'] == "LIVE_NOW"){

                    $livestream_code = sprintf(
                        'ffmpeg -re -i "%s" %s %s -flags +global_header -acodec libmp3lame -ar 44100 -b:a 128k -pix_fmt yuv420p -profile:v baseline -s 1280x720 -bufsize 6000k -vb 400k -maxrate 1500k -deinterlace -vcodec libx264 -preset veryfast -g 30 -r 30 -f flv "%s"> /dev/null &',
                        $file_stream,
                        $add_text_build,
                        $watermark_build,
                        $stream_url
                    );

                    //Start Live Stream
                    @exec($livestream_code);
                }

                return $createLiveVideo;
                
            } catch (Exception $e) {
                return $e->getMessage();
            }
            
            /*//Update Live Video
            $LiveVideo = $this->fb->post('/'.$videoId , ['title' => 'title of the new video']);
            $LiveVideo = $LiveVideo->getGraphNode()->asArray();
            pr($LiveVideo);

            //Delete Live Video
            $LiveVideo = $this->fb->delete('/'.$videoId);
            $LiveVideo = $LiveVideo->getGraphNode()->asArray();
            print_r($LiveVideo);*/
        }

        function livestream_delete($videoId){
            try {
                return $this->fb->post('/'.$videoId , ['status' => 'SCHEDULED_CANCELED']);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }
}
