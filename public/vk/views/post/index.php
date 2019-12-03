<?php
$ids = "";
$type = "";
$caption = "";
$link = "";
$repeat = 0;
$repeat_end = "";
$time_post = "";
$account = 0;
$media = array();
if(!empty($post)){
    $data = json_decode($post->data);
    $ids = $post->ids;
    $type = @$post->type;
    $account = $post->account;
    $caption = @$data->caption;
    $media = @$data->media;
    $link = @$data->link;
    $repeat = @$data->repeat;
    $repeat_end = get_timezone_user(@$data->repeat_end);
    $time_post = get_timezone_user($post->time_post);
    $time_post = date("d/m/Y h:i", strtotime($time_post));
}
?>

<div class="wrap-content container vk-app">
    <form action="<?=PATH.segment(1)?>/post/ajax_post" method="POST" class="actionForm">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa fa-vk" aria-hidden="true"></i> <?=lang('vk_accounts')?>
                        </div>
                    </div>
                    <div class="card-block p0">
                        <div class="list-account max scrollbar scrollbar-dynamic">
                            <?php if(!empty($accounts)){
                                foreach ($accounts as $key => $row) {
                            ?>

                            <a href="javascript:void(0);" class="item <?=$row->id == $account?"active":""?>">
                                <div class="image-box">
                                    <img class="img-circle" src="<?=$row->avatar?>">
                                    <i class="icon fa <?=($row->type == "page")?"fa-flag":"fa-user"?>"></i>
                                </div>
                                <div class="checked"><i class="ft-check"></i></div>
                                <input type="checkbox" name="account[]" value="<?=$row->ids?>" class="hide" <?=$row->id == $account?"checked":""?>>
                                <div class="content">
                                    <span class="title"><?=$row->username?></span>
                                </div>
                                <div class="clearfix"></div>
                            </a>
                            <?php }}else{?>
                            <div class="empty">
                                <span><?=lang("add_an_account_to_begin")?></span>
                                <a href="<?=PATH?>account_manager" class="btn btn-primary"><?=lang("add_account")?></a>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card">
                    <?=modules::run("caption/popup")?>

                    <div class="card-overplay"><i class="pe-7s-config pe-spin"></i></div>
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa ft-edit" aria-hidden="true"></i> <?=lang('new_post')?>
                        </div>
                    </div>
                    <div class="card-block pt0">
                        <div class="row">
                            <div class="tab-type schedule-vk-type file-manager-change-type">
                                <a href="#media" class="col-xs-4 col-sm-4 col-md-4 item active" data-toggle="tab" data-type="media" data-type-image="multi">
                                    <i class="ft-camera"></i> <?=lang('media')?>
                                    <input type="radio" name="type" class="hide" value="media" checked="">
                                </a>
                                <a href="#link" class="col-xs-4 col-sm-4 col-md-4 item" data-toggle="tab" data-type="link">
                                    <i class="ft-link"></i> <?=lang('link')?>
                                    <input type="radio" name="type" class="hide" value="link">
                                </a>
                                <a href="#text" class="col-xs-4 col-sm-4 col-md-4 item" data-toggle="tab" data-type="text">
                                    <i class="ft-file"></i> <?=lang('text')?>
                                    <input type="radio" name="type" class="hide" value="text">
                                </a>
                            </div>
                        </div> 

                        <div class="tab-content mt15 b0">
                            <?=modules::run("file_manager/block_file_manager", "multi", $media)?>

                            <div class="form-group form-caption vk-text">
                                <div class="list-icon">
                                    <a href="javascript:void(0);" class="getCaption" data-toggle="tooltip" data-placement="left" title="<?=lang("get_caption")?>"><i class="ft-command"></i></a>
                                    <a href="javascript:void(0);" data-toggle="tooltip" class="saveCaption" data-placement="left" title="<?=lang("save_caption")?>"><i class="ft-save"></i></a>
                                </div>
                                <textarea class="form-control post-message" name="caption" rows="3" placeholder="<?=lang('add_a_caption')?>" style="height: 114px;"><?=$caption?></textarea>
                            </div>

                            <div id="link" class="tab-pane fade in">
                                <div class="form-group form-help">
                                    <input type="input" class="form-control" name="link" placeholder=" <?=lang('enter_link')?>" value="<?=$link?>">
                                </div>
                            </div>
                        </div>

                        <?php if($ids == ""){?>
                        <div class="form-group">
                            <div class="pure-checkbox grey mr15">
                                <input type="checkbox" id="md_checkbox_schedule" name="is_schedule" class="filled-in chk-col-red enable_vk_schedule" value="on">
                                <label class="p0 m0" for="md_checkbox_schedule">&nbsp;</label>
                                <span class="checkbox-text-right"> <?=lang('schedule')?></span>
                            </div>
                        </div>
                        <?php }else{?>
                        <input type="hidden" name="is_schedule" value="1">
                        <input type="hidden" name="default_type" value="<?=$type?>">
                        <input type="hidden" name="ids" value="<?=$ids?>">
                        <?php }?>
                        
                        <div class="schedule-option collapse in" id="schedule-option">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="time_post"><?=lang('time_post')?></label>
                                        <input type="text" name="time_post" class="form-control datetime time_post" id="time_post" <?=$ids==""?"disabled='true'":""?> value="<?=$time_post?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?php if($ids == ""){?>
                        <button type="button" class="btn btn-primary pull-right btnPostNow"> <?=lang('post_now')?></button>
                        <button type="submit" class="btn btn-primary pull-right btnSchedulePost hide"> <?=lang('schedule_post')?></button>
                        <?php }else{?>
                        <a href="<?=PATH?>vk/post/ajax_post" data-redirect="<?=PATH?>vk/post" class="btn btn-primary pull-right actionMultiItem"> <?=lang('Edit post')?></a>
                        <?php }?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 hidden-sm hidden-xs">
                <?=Modules::run("vk/post/preview")?>
            </div>
        </div>
    </form>
</div>