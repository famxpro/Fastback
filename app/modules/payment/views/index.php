<div class="section-4" id="pricing">
    <div class="container content">
        <?php if(!empty($package)){?>
        <div class="title">  <?=lang('plan')?> <?=$package->name?></div>
        <div class="desc"><?=$package->description?></div>
        <div class="payment-wrap">
            <div class="payment-plan">
                <div class="item">
                    <a href="javascript:void(0);">
                        <div class="pure-checkbox grey mr15 mb15">
                            <input type="radio" id="plan1" name="plan" class="filled-in chk-col-red" value="1" <?=$type==1?'checked=""':""?>>
                            <label class="p0 m0" for="plan1">&nbsp;</label>
                            <span class="checkbox-text-right"><span class="bold"> <?=lang('monthly')?>:</span> <?=get_option('payment_currency','USD')." ".$package->price_monthly?> / <?=lang('month')?></span>
                        </div>
                    </a>
                </div>
                <div class="item active">
                    <a href="javascript:void(0);">
                        <div class="pure-checkbox grey mr15 mb15">
                            <input type="radio" id="plan2" name="plan" class="filled-in chk-col-red" value="2" <?=$type==2?'checked=""':""?>>
                            <label class="p0 m0" for="plan2">&nbsp;</label>
                            <span class="checkbox-text-right"><span class="bold"> <?=lang('annually')?>:</span> 
                                <div class="price"><?=get_option('payment_currency','USD')." ".$package->price_annually?> /  <?=lang('month')?>
                                    <div class="bill"><?=sprintf(lang('billed_x_annually'), get_option('payment_currency','USD')." ".($package->price_annually*12))?></div>
                                </div>
                            </span>
                            <?php if($package->price_monthly - $package->price_annually > 0){?>
                            <span class="save">  <?=lang('you_save')?><br/> <?=get_option('payment_currency','USD')?> <?=($package->price_monthly - $package->price_annually)*12?></span>
                            <?php }?>
                        </div>
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="payment-method">
                <div class="payment-tabs">
                    <?php if(get_option("paypal_enable", 0) || get_option("stripe_enable", 0) || get_option("pagseguro_enable", 0)){?>

                        <?php if(get_option("stripe_enable", 0) == 1){?>
                        <div class="item">
                            <a class="actionItem" href="<?=cn("payment/stripe?pid=".$package->ids)?>" data-content="payment-checkout" data-result="html">
                                <i class="fa fa-credit-card-alt" aria-hidden="true"></i>  <?=lang('credit_card')?>
                            </a>
                        </div>
                        <?php }?>
                        <?php if(get_option("paypal_enable", 0)){?>
                        <div class="item">
                            <a class="actionItem" href="<?=cn("payment/paypal?pid=".$package->ids)?>" data-content="payment-checkout" data-result="html">
                                <i class="fa fa-cc-paypal" aria-hidden="true"></i>  <?=lang('paypal')?>
                            </a>
                        </div>
                        <?php }?>
                        <?php if(get_option("pagseguro_enable", 0)){?>
                        <div class="item last-child">
                            <a class="actionItem" href="<?=cn("payment/pagseguro?pid=".$package->ids)?>" data-content="payment-checkout" data-result="html">
                                <i class="fa fa-credit-card" aria-hidden="true"></i> <?=lang('pagseguro')?>
                            </a>
                        </div>
                        <?php }?>
                    <?php }else{?>
                        <div class="text-center text-danger">  <?=lang('currently_all_payment_gateways_are_not_ready_please_comeback_later')?>!</div>
                    <?php }?>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="payment-checkout">
            </div>
            
        </div>
        <?php }?>
        <div class="text-center mt15"><a href="<?=PATH?>" style="color: #c7c7c7;"><i class="fa fa-angle-double-left" aria-hidden="true"></i>  <?=lang('back_to_home')?></a></div>
    </div>
</div>


