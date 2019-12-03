<div class="wrap-content container vk-app">
    <form action="<?=PATH?>vk/ajax_add_account" method="POST"  data-redirect="<?=cn("account_manager")?>" class="actionForm">
        <div class="list-linkedin-accounts">
        <div class="account_info">
            <div class="title"><?=$userinfo->first_name?> <?=$userinfo->last_name?> <?=lang('account')?></div>
            <div class="desc"><?=lang('select_profile_or_pages_to_start_your_plan')?></div>
        </div>
        <form action="<?=cn('linkedin/ajax_add_account')?>" data-redirect="<?=cn("account_manager")?>" class="actionForm" method="post">
            <input type="hidden" name="ids" name="ids" value="<?=segment(3)?>">
            <ul class="list-group">
                <li class="list-group-item">
                    <i class="fa fa-user"></i> <?=lang('profile')?>
                </li>
                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15 mb15">
                        <input type="checkbox" id="md_checkbox_<?=$userinfo->id?>" name="accounts[]" class="filled-in chk-col-red" value="<?=$userinfo->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$userinfo->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$userinfo->screen_name?></span>
                    </div>
                </li>
                <?php if(!empty($groups) && $groups->items != 0){?>
                <li class="list-group-item">
                    <i class="fa fa-flag"></i> <?=lang('pages')?>
                </li>

                <?php
                    foreach ($groups->items as $key => $group) {
                        if($group->type == "page"){
                ?>
                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15 mb15">
                        <input type="checkbox" id="md_checkbox_<?=$group->id?>" name="accounts[]" class="filled-in chk-col-red" value="<?=$group->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$group->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$group->name?></span>
                    </div>
                </li>
                <?php }}}?>

                <?php if(!empty($groups) && $groups->items != 0){?>
                <li class="list-group-item">
                    <i class="fa fa-users"></i> <?=lang('groups')?>
                </li>

                <?php
                    foreach ($groups->items as $key => $group) {
                        if($group->type == "group"){
                ?>
                <li class="list-group-item">
                    <div class="pure-checkbox grey mr15 mb15">
                        <input type="checkbox" id="md_checkbox_<?=$group->id?>" name="accounts[]" class="filled-in chk-col-red" value="<?=$group->id?>">
                        <label class="p0 m0" for="md_checkbox_<?=$group->id?>">&nbsp;</label>
                        <span class="checkbox-text-right"><?=$group->name?></span>
                    </div>
                </li>
                <?php }}}?>
                <li class="list-group-item text-center">
                    <button type="submit" class="btn btn-success"><?=lang('add_account')?></button>
                </li>
            </ul>
        </form>
    </div>
    </form>
</div>