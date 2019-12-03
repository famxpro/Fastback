<div class="list-board">
    <div class="account_info">
        <div class="title"><?=$account->username?> <?=lang('account')?></div>
        <div class="desc"><?=lang('select_boards_to_start_your_plan')?></div>
    </div>
    <form action="<?=cn('pinterest/ajax_add_board')?>" data-redirect="<?=cn("account_manager")?>" class="actionForm" method="post">
        <input type="hidden" name="ids" name="ids" value="<?=segment(3)?>">
        <ul class="list-group">
            <?php if(!empty($boards)){
                foreach ($boards as $key => $board) { 
            ?>
            <li class="list-group-item">
                <div class="pure-checkbox grey mr15 mb15">
                    <input type="checkbox" id="md_checkbox_<?=get_board_from_url($board->url)?>" name="boards[]" class="filled-in chk-col-red" value="<?=get_board_from_url($board->url)?>">
                    <label class="p0 m0" for="md_checkbox_<?=get_board_from_url($board->url)?>">&nbsp;</label>
                    <span class="checkbox-text-right"><?=$board->name?></span>
                </div>
            </li>
            <?php }}?>
            <li class="list-group-item text-center">
                <button type="submit" class="btn btn-success"><?=lang("add_board")?></button>
            </li>
        </ul>
    </form>
</div>