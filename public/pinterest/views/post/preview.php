<?php if(isset($ajax_load)){?>

<div class="pinterest-app">   
    <div class="card">
        <div class="card-block p0">
            <div class="preview-pinterest preview-pinterest-photo">
                <div class="preview-header">
                    <div class="pinterest-logo"><i class="fa fa-pinterest"></i></div>
                </div>
                <div class="preview-content">
                    <div class="preview-image">
                    </div>
                    <div class="post-info">
                        <div class="info-active pull-left"><?=lang('upload_by')?> <strong><?=lang('anonymous')?></strong></div>
                        <div class="clearfix"></div>
                    </div>
                    
                   <div class="user-info">
                        <div class="img"><img class="img-circle" src="<?=BASE?>public/pinterest/assets/img/avatar.png"></div>
                        <div class="desc">
                            <span><strong><?=lang('anonymous')?></strong> <?=lang('saved')?> <strong> <?=lang('board')?></strong></span>
                            <div class="caption-info pt0">
                                <div class="line-no-text"></div>
                                <div class="line-no-text"></div>
                                <div class="line-no-text w50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php }else{?>
<div class="card">
    <div class="card-block p0">
        <div class="preview-pinterest preview-pinterest-photo">
            <div class="preview-header">
                <div class="pinterest-logo"><i class="fa fa-pinterest"></i></div>
            </div>
            <div class="preview-content">
                <div class="preview-image">
                </div>
                <div class="post-info">
                    <div class="info-active pull-left"><?=lang('upload_by')?> <strong><?=lang('anonymous')?></strong></div>
                    <div class="clearfix"></div>
                </div>
                
               <div class="user-info">
                    <div class="img"><img class="img-circle" src="<?=BASE?>public/pinterest/assets/img/avatar.png"></div>
                    <div class="desc">
                        <span><strong><?=lang('anonymous')?></strong> <?=lang('saved')?> <strong> <?=lang('board')?></strong></span>
                        <div class="caption-info pt0">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }?>