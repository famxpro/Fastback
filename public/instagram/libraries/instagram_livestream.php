<?php
class instagram_livestream{
    public function __construct($parent){
        $this->_parent  = $parent;
        $this->ci       = &get_instance();
    }

    public function process($schedule){
        $spintax = new Spintax();
        $data = (object)$schedule;
        $data->data = (object)json_decode($data->data);
        $medias = $data->data->media;
        $title = @$spintax->process($data->data->title);
        $caption = @$spintax->process($data->data->caption);

        try {
            $createLive = $this->_parent->ig->live->create();
            $createLive = json_decode( $createLive );
            $broadcastId = $createLive->broadcast_id;

            //Start Live
            $startLive = $this->_parent->ig->live->start($broadcastId);
            $startLive = json_decode( $startLive );

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

            $file = realpath(APPPATH."../".get_path_file($medias[0]));
            $videoId = $broadcastId;

            $stream_url = $createLive->upload_url;
            $file_stream = $file;

            $livestream_code = sprintf(
                'ffmpeg -re -i "%s" %s %s -b:v 0 -flags +global_header -acodec libmp3lame -ar 44100 -b:a 128k -pix_fmt yuv420p -profile:v baseline -bufsize 6000k -vb 400k -maxrate 1500k -deinterlace -vcodec libx264 -preset veryfast -g 30 -r 30 -f flv "%s" > /dev/null &',
                $file_stream,
                $add_text_build,
                $watermark_build,
                $stream_url
            );

            //Start Live Stream
            @exec($livestream_code);

            return $createLive;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }




}