<div class="lead"><?=lang('general')?></div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <span class="text"> <?=lang("login_facebook_via")?></span> <br/>
            <div class="pure-checkbox grey mr15 mb15 mt15">
                <input type="hidden" name="facebook_login_offical" value="0">
                <input type="checkbox" id="md_checkbox_facebook_login" name="facebook_login_offical" class="filled-in chk-col-red" <?=get_option('facebook_login_offical', 1)==1?"checked":""?> value="1">
                <label class="p0 m0" for="md_checkbox_facebook_login">&nbsp;</label>
                <span class="checkbox-text-right"> <?=lang('Facebook Login')?></span>
            </div>
            <div class="pure-checkbox grey mr15 mb15">
                <input type="hidden" name="facebook_login_username_password" value="0">
                <input type="checkbox" id="md_checkbox_facebook_android_iphone" name="facebook_login_username_password" class="filled-in chk-col-red" <?=get_option('facebook_login_username_password', 1)==1?"checked":""?> value="1">
                <label class="p0 m0" for="md_checkbox_facebook_android_iphone">&nbsp;</label>
                <span class="checkbox-text-right"> <?=lang('facebook_for_iphone/android')?></span>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('facebook_app_id')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="facebook_app_id" value="<?=get_option("facebook_app_id", "")?>">
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('facebook_app_secret')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="facebook_app_secret" value="<?=get_option("facebook_app_secret", "")?>">
          </div>
        </div>
    </div>
</div>