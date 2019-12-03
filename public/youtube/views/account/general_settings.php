<div class="lead"><?=lang('general')?></div>
<div class="row">
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('Google Api Key')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="youtube_api_key" value="<?=get_option("youtube_api_key", "")?>">
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('Youtube Client ID')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="youtube_client_id" value="<?=get_option("youtube_client_id", "")?>">
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('Youtube Client Secret')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="youtube_client_secret" value="<?=get_option("youtube_client_secret", "")?>">
          </div>
        </div>
    </div>
</div>