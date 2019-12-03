<div class="wrap-content container">
    <form action="<?=PATH?>facebook/ajax_add_account" method="POST"  data-redirect="<?=cn("account_manager")?>" class="actionForm">
        <div class="list-add-accounts">
            <div class="account_info">
                <div class="title"><?=$userinfo->name?> <?=lang('account')?></div>
            </div>
            
            <input type="hidden" name="ids" name="ids" value="<?=segment(3)?>">
            <ul class="list-group">
                <?php if($official_api){?>
                    <div class="text-danger" style="margin-top: 5px;"><?=lang("Note: Facebook API Offical not support post to profile and just support post to page and groups as an admin with sufficient administrative permission")?></div>
                <?php }?>
                <li class="list-group-item item-header">
                    <i class="fa fa-user"></i> <?=lang('profile')?>
                </li>

                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15">
                        <input type="checkbox" id="md_checkbox_<?=$userinfo->id?>" name="accounts[]" class="filled-in chk-col-red" value="profile-<?=$userinfo->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$userinfo->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$userinfo->name?></span>
                    </div>
                </li>

                <?php if(!empty($pages) && !empty($pages->data)){?>
                <li class="list-group-item  item-header">
                    <i class="fa fa-flag"></i> <?=lang('pages')?>
                </li>

                <?php
                foreach ($pages->data as $key => $page) {
                ?>
                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15">
                        <input type="checkbox" id="md_checkbox_<?=$page->id?>" name="accounts[]" class="filled-in chk-col-red" value="page-<?=$page->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$page->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$page->name?></span>
                    </div>
                </li>
                <?php }}?>

                <?php if(!empty($groups) &&  !empty($groups->data)){?>
                <li class="list-group-item item-header">
                    <i class="fa fa-users"></i> <?=lang('groups')?>
                </li>

                <?php
                    foreach ($groups->data as $key => $group) {
                ?>
                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15">
                        <input type="checkbox" id="md_checkbox_<?=$group->id?>" name="accounts[]" class="filled-in chk-col-red" value="group-<?=$group->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$group->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$group->name?></span>
                    </div>
                </li>
                <?php }}?>
                <li class="list-group-item text-center">
                    <button type="submit" class="btn btn-success"><?=lang('add_account')?></button>
                </li>
            </ul>
        </div>
    </form>
</div>