<?php 
$active_tab = 0;
if( 
    get_option('facebook_login_offical', 1) == 1 && 
    get_option("facebook_app_id", "") != "" && 
    get_option("facebook_app_id", "") != ""
) {
    $active_tab = 1;
}

if( 
    get_option('facebook_login_username_password', 1) == 1 && 
    $active_tab == 0
) {
    $active_tab = 2;
}
?>

<div id="load_popup_modal_contant" class="facebook_popup_add" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="modal-title"><i class="fa fa-facebook-square"></i> <?=lang('add_facebook_account')?> </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            <?php if(get_option('facebook_login_offical', 1) == 1){?>
                            <li class="<?=$active_tab==1?"active":""?>"><a data-toggle="tab" href="#fb_login"> <?=lang('Facebook Login')?></a></li>
                            <?php }?>

                            <?php if(get_option('facebook_login_username_password', 1) == 1){?>
                            <li class="<?=$active_tab==2?"active":""?>"><a data-toggle="tab" href="#home"> <?=lang('facebook_for_iphone/android')?></a></li>
                            <?php }?>
                        </ul>

                        <div class="tab-content p15">
                            <?php if(get_option('facebook_login_offical', 1) == 1){?>
                            <div id="fb_login" class="tab-pane fade in text-center <?=$active_tab==1?"active":""?>" style="padding: 50px;">
                                <a href="<?=cn("facebook/oauth")?>" class="" style="background-color: #4267b2; border-radius: 4px; color: #fff; display: inline-block; height: 45px;">
                                    <img src="<?=BASE."public/facebook/assets/img/fb_icon.png"?>" style="height: 100%; padding: 7px; padding-right: 15px;"> 
                                    <span style="display: inline-block; padding-right: 15px;"><?=lang('Continue with Facebook')?></span>
                                </a>
                            </div>
                            <?php }?>

                            <?php if(get_option('facebook_login_username_password', 1) == 1){?>
                            <div id="home" class="tab-pane fade in <?=$active_tab==2?"active":""?>">
                                <form action="<?=BASE?>facebook/ajax_get_access_token" data-type-message="text" data-async role="form" class="actionForm" role="form" method="POST">
                                    <div class="form-notify"></div>
                                    <div class="form-group">
                                        <label class="control-label" for="username"><?=lang("facebook_username")?></label>
                                            <input type="text" name="username" id="username" class="form-control" id="username" value="">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label" for="password"><?=lang("facebook_password")?></label>
                                        <input type="password" name="password" id="password" class="form-control" id="password" value="">
                                    </div>
                                    <div class="form-group hide">
                                        <label class="control-label"><?=lang("facebook_application")?></label>
                                        <select class="form-control" name="app">
                                            <option value="iphone"> <?=lang('facebook_for_iphone')?></option>
                                            <option value="android"> <?=lang('facebook_for_android')?></option>
                                        </select>
                                    </div>
                                    <div class="form-group mb0">
                                        <input type="submit" value="<?=lang('get_access_token')?>" class="btn btn-primary" />
                                    </div>
                                    <div class="mb0 iframe_access_token">

                                    </div>
                                </form>

                                <form action="<?=PATH?>facebook/ajax_get_access" data-redirect="<?=PATH?>facebook/add_account" data-type-message="text" data-async role="form" class="actionForm" role="form" method="POST">
                                <div class="pt15">
                                    <div class="form-group mb0">
                                        <label class="control-label"><?=lang("facebook_access_token")?></label>
                                        <textarea class="form-control" rows="5" name="access_token"></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer row mt15 pb0">
                                    <input name="submit_popup" type="submit" value="<?=lang('add_account')?>" class="btn btn-primary" />
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("close")?></button>
                                </div>
                                </form>
                            </div>
                            <?php }?>
                            <div class="clearfix"></div>
                        </div>

                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

