<div class="lead"><?=lang('general')?></div>

<div class="row">
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('pinterest_app_ID')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="pinterest_app_id" value="<?=get_option("pinterest_app_id", "")?>">
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('pinterest_app_secret')?></span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="pinterest_app_secret" value="<?=get_option("pinterest_app_secret", "")?>">
          </div>
        </div>
    </div>
</div>
