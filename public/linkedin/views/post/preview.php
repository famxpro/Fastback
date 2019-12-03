<!-- <?php if(isset($ajax_load)){?>
<div class="linkedin-app">
    <div class="card">
        <div class="card-block p0">
            <div class="preview-linkedin preview-linkedin-link">
                <div class="preview-header">
                    <div class="linkedin-logo"><i class="fa fa-linkedin"></i></div>
                </div>
                <div class="preview-content">
                    <div class="user-info">
                        <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                        <div class="text">
                            <div class="name"> <?=lang('anonymous')?></div>
                            <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                        </div>
                    </div>
                    <div class="caption-info">
                        <div class="line-no-text"></div>
                        <div class="line-no-text"></div>
                        <div class="line-no-text w50"></div>
                    </div>

                    <div class="preview-link preview-linkedin-view hide">
                        <div class="image"></div>
                        <div class="info">
                            <div class="title"><div class="line-no-text"></div></div>
                            <div class="website">
                                <div class="line-no-text w50"></div>
                            </div>
                        </div>
                    </div>

                    <div class="preview-media preview-linkedin-view">
                        <div class="image"></div>
                    </div>
                    
                    <div class="preview-comment">
                        <div class="item">
                            <i class="linkedin-icon like" aria-hidden="true"></i> <?=lang('like')?>
                        </div>
                        <div class="item">
                            <i class="linkedin-icon comment" aria-hidden="true"></i> <?=lang('comment')?>
                        </div>
                        <div class="item">
                            <i class="linkedin-icon share" aria-hidden="true"></i> <?=lang('share')?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
<?php }else{?>
<div class="card">
    <div class="card-block p0">
        <div class="preview-linkedin preview-linkedin-link">
            <div class="preview-header">
                <div class="linkedin-logo"><i class="fa fa-linkedin"></i></div>
            </div>
            <div class="preview-content">
                <div class="user-info">
                    <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                    <div class="text">
                        <div class="name"> <?=lang('anonymous')?></div>
                        <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                    </div>
                </div>
                <div class="caption-info">
                    <div class="line-no-text"></div>
                    <div class="line-no-text"></div>
                    <div class="line-no-text w50"></div>
                </div>

                <div class="preview-link preview-linkedin-view hide">
                    <div class="image"></div>
                    <div class="info">
                        <div class="title"><div class="line-no-text"></div></div>
                        <div class="website">
                            <div class="line-no-text w50"></div>
                        </div>
                    </div>
                </div>

                <div class="preview-media preview-linkedin-view">
                    <div class="image"></div>
                </div>
                
                <div class="preview-comment">
                    <div class="item">
                        <i class="linkedin-icon like" aria-hidden="true"></i> <?=lang('like')?>
                    </div>
                    <div class="item">
                        <i class="linkedin-icon comment" aria-hidden="true"></i> <?=lang('comment')?>
                    </div>
                    <div class="item">
                        <i class="linkedin-icon share" aria-hidden="true"></i> <?=lang('share')?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }?> -->

<?php
    $module_name = "linkedin";
?>
<div class="<?=$module_name?>-app">

    <div class="preview-<?=$module_name?>">
        <div class="card">
            <div class="card-block p0">

                <!--TEXT-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-text hide">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">

                        <div class="preview-user">
                            <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                                <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                            </div>
                        </div>

                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>

                    </div>

                    <div class="preview-footer">
                        <ul>
                            <li><i class="like"></i> <?=lang('like')?></li>
                            <li><i class="comment"></i> <?=lang('comment')?></li>
                            <li><i class="share"></i> <?=lang('share')?></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--IMAGE-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-media">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">

                        <div class="preview-user">
                            <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                                <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                            </div>
                        </div>

                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>

                        <div class="preview-image"></div>

                    </div>

                    <div class="preview-footer">
                        <ul>
                            <li><i class="like"></i> <?=lang('like')?></li>
                            <li><i class="comment"></i> <?=lang('comment')?></li>
                            <li><i class="share"></i> <?=lang('share')?></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--LINK-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-link hide">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">

                        <div class="preview-user">
                            <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                                <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                            </div>
                        </div>

                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>

                        <div class="preview-image"></div>
                        <div class="preview-link-info">
                            <div class="title"><div class="line-no-text"></div></div>
                            <div class="website"><div class="line-no-text w50"></div></div>
                        </div>
                    </div>

                    <div class="preview-footer">
                        <ul>
                            <li><i class="like"></i> <?=lang('like')?></li>
                            <li><i class="comment"></i> <?=lang('comment')?></li>
                            <li><i class="share"></i> <?=lang('share')?></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--NONE-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-none hide">
                    
                    <div class="preview-error">
                        This social network not support post this type post
                    </div>

                </div>

                <!-- <div class="preview-linkedin preview-linkedin-link">
                    <div class="preview-header">
                        <div class="linkedin-logo"><i class="fa fa-linkedin"></i></div>
                    </div>
                    <div class="preview-content">
                        <div class="user-info">
                            <img class="img-circle" src="<?=BASE?>public/facebook/assets/img/avatar.png">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                                <span> <?=lang('Web_Developer_at_Home_Work')?> <br/><?=lang('just_now')?></span>
                            </div>
                        </div>
                        <div class="caption-info">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>

                        <div class="preview-link preview-linkedin-view hide">
                            <div class="image"></div>
                            <div class="info">
                                <div class="title"><div class="line-no-text"></div></div>
                                <div class="website">
                                    <div class="line-no-text w50"></div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-media preview-linkedin-view">
                            <div class="image"></div>
                        </div>
                        
                        <div class="preview-comment">
                            <div class="item">
                                <i class="linkedin-icon like" aria-hidden="true"></i> <?=lang('like')?>
                            </div>
                            <div class="item">
                                <i class="linkedin-icon comment" aria-hidden="true"></i> <?=lang('comment')?>
                            </div>
                            <div class="item">
                                <i class="linkedin-icon share" aria-hidden="true"></i> <?=lang('share')?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div> -->

            </div>
        </div>
    </div>
</div>   