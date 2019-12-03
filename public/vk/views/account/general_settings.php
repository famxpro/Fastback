<div class="lead"><?=lang('general')?></div>

<div class="row">
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('VK_App_Id')?>:</span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="vk_client_id" value="<?=get_option("vk_client_id", "")?>">
          </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="item form-group">
            <span class="text"><?=lang('VK_Client_Secret')?>:</span> 
            <div class="activity-option-input">
                <input type="text" class="form-control" name="vk_client_secret" value="<?=get_option("vk_client_secret", "")?>">
          </div>
        </div>
    </div>
</div>
 