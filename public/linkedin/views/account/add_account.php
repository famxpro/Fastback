<div class="wrap-content container">
    <form action="<?=cn('linkedin/ajax_add_account')?>" data-redirect="<?=cn("account_manager")?>" class="actionForm" method="post">
    <div class="list-add-accounts">
        <?php
        $firstName_param = (array)$userinfo->firstName->localized;
        $lastName_param = (array)$userinfo->lastName->localized;

        $firstName = reset($firstName_param);
        $lastName = reset($lastName_param);
        $fullname = $firstName." ".$lastName;
        ?>
        <div class="account_info">
            <div class="title"><?=$firstName." ".$lastName?> <?=lang('account')?></div>
            <div class="desc"><?=lang('select_profile_or_pages_to_start_your_plan')?></div>
        </div>

        <input type="hidden" name="ids" name="ids" value="<?=segment(3)?>">
        <ul class="list-group">
            <li class="list-group-item item-header">
                <i class="fa fa-user"></i> <?=lang('profile')?>
            </li>
            <li class="list-group-item">
                <div class="pure-checkbox grey mr15">
                    <input type="checkbox" id="md_checkbox_<?=$userinfo->id?>" name="accounts[]" class="filled-in chk-col-red" value="<?=$userinfo->id?>">
                    <label class="p0 m0" for="md_checkbox_<?=$userinfo->id?>">&nbsp;</label>
                    <span class="checkbox-text-right"><?=$firstName." ".$lastName?></span>
                </div>
            </li>
            <?php if(!empty($companies)){?>
            <li class="list-group-item item-header">
                <i class="fa fa-flag"></i> <?=lang('pages')?>
            </li>

            <?php
                foreach ($companies->values as $key => $company) {
            ?>
            <li class="list-group-item">
                <div class="pure-checkbox grey mr15">
                    <input type="checkbox" id="md_checkbox_<?=$company['id']?>" name="accounts[]" class="filled-in chk-col-red" value="<?=$company['id']?>">
                    <label class="p0 m0" for="md_checkbox_<?=$company['id']?>">&nbsp;</label>
                    <span class="checkbox-text-right"><?=$company['name']?></span>
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