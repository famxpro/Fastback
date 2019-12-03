<?php
$ids = "";
$type = "";
$caption = "";
$title = "";
$time_post = "";
$add_watermark = "";
$watermark_position = "";
$add_text = "";
$watermark = "";
$account = 0;
$media = array();
$video_id = 0;
if(!empty($post)){
    $data = json_decode($post->data);
    $ids = $post->ids;
    $type = $post->type; 
    $account = $post->account;
    $caption = @$data->caption;
    $media = @$data->media;
    $title = @$data->title;
    $add_watermark = @$data->add_watermark;
    $watermark_position = @$data->watermark_position;
    $add_text = @$data->add_text;
    $watermark = @$data->watermark;
    $repeat_end = get_timezone_user(@$data->repeat_end);
    $time_post = get_timezone_user($post->time_post);
    $time_post = date("d/m/Y h:i", strtotime($time_post));
    $result = json_decode($post->result);
    if(isset($result->stream_url) && isset($result->id)){
        $video_id = $result->id;
    }
}
?>

<form class="actionForm" action="<?=cn("facebook/livestream/save")?>">
<div class="wrap-content facebook-app row app-mod facebook-livestream">
    <ul class="am-mobile-menu">
        <li><a href="javascript:void(0);" class="active" data-am-open="account"><?=lang("Accounts")?></a></li>
        <li><a href="javascript:void(0);" data-am-open="content"><?=lang("Content")?></a></li>
        <li><a href="javascript:void(0);" data-am-open="preview"><?=lang("Preview")?></a></li>
    </ul>
    <div class="clearfix"></div>

    <div class="am-sidebar active">
        <?php if(!empty($accounts)){?>
        <div class="box-search">
            <div class="input-group">
              <input type="text" class="form-control am-search" placeholder="<?=lang("search")?>" aria-describedby="basic-addon2">
              <span class="input-group-addon" id="basic-addon2"><i class="ft-search"></i></span>
            </div>
        </div>
        <ul class="box-list am-scroll">
            <?php
            foreach ($accounts as $key => $row) {

            if($account == 0 || $row->id == $account){
            ?>
            <li class="item <?=$row->id == $account?"active":""?>">
                <a href="javascript:void(0);">
                    <div class="box-img">
                        <img src="<?=$row->avatar?>">
                        <div class="checked"><i class="fa fa-check"></i></div>
                    </div>
                    <div class="pure-checkbox grey mr15">
                        <input type="radio" name="account" class="filled-in chk-col-red" value="<?=$row->ids?>" <?=$row->id == $account?"checked":""?>>
                        <label class="p0 m0" for="md_checkbox_<?=$row->pid?>">&nbsp;</label>
                    </div>
                    <div class="box-info">
                        <div class="title"><?=$row->fullname?></div>
                        <div class="desc"><?=ucfirst($row->type)?> </div>
                    </div>
                </a> 
            </li>
            <?php }}?>
        </ul>
        <?php }else{?>

        <div class="empty">
            <span><?=lang("add_an_account_to_begin")?></span>
            <a href="<?=PATH?>account_manager" class="btn btn-primary"><?=lang("add_account")?></a>
        </div>

        <?php }?>
    </div>
    <div class="am-wrapper">

        <div class="am-content col-md-6 am-scroll">
            
            <?=modules::run("caption/popup")?>

            <div class="head-title">
                <i class="fa ft-edit" aria-hidden="true"></i> <?=lang("Create new")?>
            </div>

            <div class="form-group">
                <div class="image-manage" data-type="single">
                    <div class="image-manage-content">
                        <div class="file-manager-list-images">
                            <div class="add-image" <?=!empty($media)?"style='display:none'":""?>> <?=lang('add_video')?></div>

                            <?php if(!empty($media)){
                            foreach ($media as $image) {
                            ?>
                            <div class="item" style="<?=check_image($image)?"background-image: url('".$image."')":""?>">
                                <?php if(!check_image($image)){?>
                                <video src="<?=$image?>" playsinline="" muted="" loop=""></video>
                                <?php }?>
                                <input type="hidden" name="media[]" value="<?=$image?>">
                                <button type="button" class="close" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <?php }}?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="image-manage-footer">
                        <a href="<?=PATH?>file_manager/popup_add_files/video" class="item btnOpenFileManager">
                            <i class="fa fa-laptop" aria-hidden="true"></i> <?=lang('file_manager')?>
                        </a>
                        <a href="javascript:void(0);" class="item fileinput-button">
                            <i class="fa fa-upload" aria-hidden="true"></i>
                            <input id="fileupload" type="file" name="files[]">
                        </a>
                        <?php if(get_option('google_drive_api_key', '') != "" && get_option('google_drive_client_id', '') != ""){?>
                        <a href="javascript:void(0);" class="item" onclick="onApiLoad()">
                            <i class="fa fa-google-drive" aria-hidden="true"></i>
                        </a>
                        <?php }?>
                        <?php if(get_option('dropbox_api_key', '') != ""){?>
                        <a href="javascript:void(0);" class="item" id="chooser-image" data-multi-files="false" >
                            <i class="fa fa-dropbox" aria-hidden="true"></i>
                        </a>
                        <?php }?>
                        <a href="javascript:void(0);" class="item show-pop-yt-video" data-placement="auto">
                            <i class="fa fa-youtube-play" aria-hidden="true"></i>
                        </a>

                        <div class="webui-popover-content">
                            <div class="add_youtube_link p15">
                                <div class="input-group" style="max-width: 250px;">
                                    <input type="text" class="form-control" name="youtube_link" placeholder="<?=lang("Enter youtube video url")?>">
                                    <span class="input-group-btn">
                                        <a href="<?=cn("file_manager/get_youtube_video_info")?>" data-id="" class="btnActionGetYoutubeInfo btn btn-primary"><i class="ft-plus"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="title" id="title" placeholder="<?=lang("Enter your title")?>" value="<?=$title?>">
                </div>

                <div class="form-group form-caption">
                    <div class="list-icon">
                        <a href="javascript:void(0);" class="getCaption" data-toggle="tooltip" data-placement="left" title="<?=lang("get_caption")?>"><i class="ft-command"></i></a>
                        <a href="javascript:void(0);" data-toggle="tooltip" class="saveCaption" data-placement="left" title="<?=lang("save_caption")?>"><i class="ft-save"></i></a>
                    </div>
                    <textarea class="form-control post-message" name="caption" rows="3" placeholder="<?=lang('add_a_caption')?>" style="height: 114px;"><?=$caption?></textarea>
                </div>

                <div class="form-group">
                    <?php if($ids == ""){?>
                    <div class="pure-checkbox grey mr15">
                        <input type="checkbox" id="md_checkbox_schedule" name="is_schedule" class="filled-in chk-col-red enable_post_all_schedule" value="on">
                        <label class="p0 m0" for="md_checkbox_schedule">&nbsp;</label>
                        <span class="checkbox-text-right"> <?=lang('schedule')?></span>
                    </div>
                    <?php }else{?>
                    <input type="hidden" name="is_schedule" value="1">
                    <input type="hidden" name="video_id" value="<?=$video_id?>">
                    <input type="hidden" name="ids" value="<?=$ids?>">
                    <?php }?>

                    <div class="pure-checkbox grey">
                        <input type="checkbox" id="md_checkbox_advance" name="advance" class="filled-in chk-col-red" <?=$ids!=""?"checked='true'":""?> value="on">
                        <label class="p0 m0" for="md_checkbox_advance" data-toggle="collapse" data-target="#advance-option">&nbsp;</label>
                        <span class="checkbox-text-right"> <?=lang('Advance option')?></span>
                    </div>
                </div>

                <div class="form-group collapse form-caption <?=$ids!=""?"in":""?>" id="advance-option" style="padding: 15px; background: #ecf7ff; border-radius: 4px;">
                    <div class="radio_tab">
                        <div class="pure-checkbox mb15 grey mr15">
                            <input type="checkbox" id="md_checkbox_show_livestream" name="show_livestream" class="filled-in chk-col-red" data-target="" <?=$video_id != 0?"checked=''":""?> value="yes">
                            <label class="p0 m0" for="md_checkbox_show_livestream">&nbsp;</label>
                            <span class="checkbox-text-right"> <?=lang('Show schedule livestream on this account')?></span>
                        </div>
                    </div>
                    <span class="text"> <?=lang("Watermark")?></span>
                    <div class="radio_tab">
                        <div class="pure-checkbox mb15 grey mr15">
                            <input type="radio" id="md_checkbox_add_no" name="add_watermark" class="filled-in chk-col-red" data-target="" <?=($add_watermark=="" || $add_watermark == "no")?"checked=''":""?> value="no">
                            <label class="p0 m0" for="md_checkbox_add_no">&nbsp;</label>
                            <span class="checkbox-text-right"> <?=lang('Default')?></span>
                        </div>

                        <div class="pure-checkbox mb15 grey mr15">
                            <input type="radio" id="md_checkbox_add_watermark" name="add_watermark" class="filled-in chk-col-red" data-target="#tab_watermark" <?=$add_watermark == "watermark"?"checked=''":""?> value="watermark">
                            <label class="p0 m0" for="md_checkbox_add_watermark">&nbsp;</label>
                            <span class="checkbox-text-right"> <?=lang('Add watermark')?></span>
                        </div>

                        <div class="pure-checkbox mb15 grey">
                            <input type="radio" id="md_checkbox_add_text" name="add_watermark" class="filled-in chk-col-red" data-target="#tab_add_text" <?=$add_watermark == "add_text"?"checked=''":""?> value="add_text">
                            <label class="p0 m0" for="md_checkbox_add_text">&nbsp;</label>
                            <span class="checkbox-text-right"> <?=lang('Add Text')?></span>
                        </div>
                    </div>
                    <div class="tab-content radio_tab_content">
                        <div id="tab_watermark" class="tab-pane fade <?=$add_watermark == "watermark"?"active in":""?>">
                            <div class="form-group">
                                <span class="text"> <?=lang("Add watermark")?></span>
                                <div class="input-group p0">
                                    <input type="text" class="form-control" name="watermark" id="watermark" placeholder="" value="<?=$watermark?>">
                                    <span class="input-group-btn" id="button-addon">
                                        <a class="btn btn-primary btnOpenFileManager" href="<?=cn("file_manager/popup_add_files/photo?id=watermark")?>">
                                            <i class="ft-folder"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div id="tab_add_text" class="tab-pane fade <?=$add_watermark == "add_text"?"active in":""?>">
                            <div class="form-group">
                                <span class="text"> <?=lang("Add Text")?></span>
                                <input type="text" class="form-control" name="add_text" id="add_text" placeholder="" value="<?=$add_text?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <span class="text"> <?=lang("Position")?></span>
                        <select class="form-control" name="watermark_position">
                            <option value="top_left"><?=lang("Top Left")?></option>
                            <option value="top_right" <?=$watermark_position == "top_right"?"selected=''":""?>><?=lang("Top Right")?></option>
                            <option value="bottom_left" <?=$watermark_position == "bottom_left"?"selected=''":""?>><?=lang("Bottom Left")?></option>
                            <option value="bottom_right" <?=$watermark_position == "bottom_right"?"selected=''":""?>><?=lang("Bottom Right")?></option>
                            <option value="top_center" <?=$watermark_position == "top_center"?"selected=''":""?>><?=lang("Top Center")?></option>
                            <option value="bottom_center" <?=$watermark_position == "bottom_center"?"selected=''":""?>><?=lang("Bottom Center")?></option>
                        </select>
                    </div>
                </div>
                
                <div class="schedule-option collapse in" id="schedule-option">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="time_post"> <?=lang('time_post')?></label>
                                <input type="text" name="time_post" class="form-control datetime time_post" id="time_post" <?=$ids==""?"disabled='true'":""?> value="<?=$time_post?>">
                            </div>
                        </div>
                    </div>
                </div> 
                <?php if($ids == ""){?>
                <button type="submit" class="btn btn-primary pull-right border-circle btnGoNow"> <?=lang("Post now")?></button>
                <button type="submit" class="btn btn-primary pull-right border-circle btnSchedulePost hide"> <?=lang("Schedule post")?></button>
                <?php }else{?>
                <a href="<?=PATH?>facebook/livestream/save" data-redirect="<?=PATH?>facebook/livestream" class="btn btn-primary pull-right actionMultiItem"> <?=lang('Edit post')?></a>
                <?php }?>
                <div class="clearfix"></div>
            </div>


        </div>


        <div class="am-preview col-md-6 am-scroll">

            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-sm-12">
                    <div class="card">
                        <div class="card-block p0">
                            <div class="preview-fb preview-fb-media preview-fb-livestream">
                                <div class="preview-header">
                                    <div class="fb-logo"><i class="fa fa-facebook"></i></div>
                                </div>
                                <div class="preview-content">
                                    <div class="user-info">
                                        <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                                        <div class="text">
                                            <div class="name"> <?=lang('anonymous')?> <span class="small" style="font-weight: 400;"><?=lang("is live now.")?></span></div>
                                            <span> <?=lang('just_now')?> . <i class="fa fa-globe" aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                    <div class="caption-info">
                                        <div class="line-no-text"></div>
                                        <div class="line-no-text"></div>
                                        <div class="line-no-text w50"></div>
                                    </div>
                                    <div class="box-live" style="position: relative;">
                                        
                                        <div class="live" style="position: absolute; z-index: 15; top: 13px; left: 25px; background: red; color: #fff; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; font-size: 16px;"><?=lang("Live")?></div>
                                        <div class="preview-image">
                                        </div>

                                    </div>

                                    <div class="preview-comment">
                                        <div class="item">
                                            <i class="fb-icon like" aria-hidden="true"></i> <?=lang('like')?>
                                        </div>
                                        <div class="item">
                                            <i class="fb-icon comment" aria-hidden="true"></i> <?=lang('comment')?>
                                        </div>
                                        <div class="item">
                                            <i class="fb-icon share" aria-hidden="true"></i> <?=lang('share')?>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
    </div>

</div>
</form>

<script type="text/javascript">
    $(function(){
        $(document).on("change", ".radio_tab input", function(){
            _that = $(this);
            _value = _that.data("target");
            $(".radio_tab_content .tab-pane").removeClass("in active");
            $(_value).addClass("in active");
            console.log(_value);
        });
    });
</script>