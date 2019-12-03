<div id="load_popup_modal_contant" class="" role="dialog">
    <div class="modal-dialog modal-md">
        <form action="<?=cn($module."/ajax_get_access")?>" data-type-message="text" data-redirect="<?=cn($module."/add_account")?>" data-async role="form" class="form-horizontal actionForm" role="form" method="POST">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="modal-title"><i class="<?=$module_icon?>"></i> <?=lang("vk_accounts")?></div>
            </div>
            <div class="modal-body p15">
                <div class="col-sm-12">
                    <div class="row form-notify"></div>
                    <div class="form-group">
                        <label class="control-label" for="username"><?=lang("enter_vk_code")?></label>
                        <input type="text" name="code" class="form-control">
                    </div>
                </div>
                <a href="<?=cn($module."/oauth")?>" target="_blank" class="openOauthVK hide"></a>
                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <input name="submit_popup" id="submit_popup" type="submit" value="<?=lang('add_account')?>" class="btn btn-primary" />
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("close")?></button>
            </div>
        </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        setTimeout(function(){
            $('.openOauthVK')[0].click();
        },1000);
    });
</script>

