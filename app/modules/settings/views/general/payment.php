<div class="row">
    <div class="col-md-12">
                                        <h3 class="head-title"><i class="ft-credit-card"></i> <?=lang('environment')?></h3><br/>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_payment_environment_enable" name="payment_environment" class="filled-in chk-col-red" <?=get_option('payment_environment', 0)==1?"checked":""?> value="1">
                                            <label class="p0 m0" for="md_checkbox_payment_environment_enable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('live')?></span>
                                        </div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_payment_environment_disable" name="payment_environment" class="filled-in chk-col-red" <?=get_option('payment_environment', 0)==0?"checked":""?> value="0">
                                            <label class="p0 m0" for="md_checkbox_payment_environment_disable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('sandbox')?></span>
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('currency')?></span> 
                                            <select name="payment_currency" class="form-control">
                                                <option value="USD" <?=get_option('payment_currency', 'USD')=="USD"?"selected":""?>>USD</option>
                                                <option value="AUD" <?=get_option('payment_currency', 'USD')=="AUD"?"selected":""?>>AUD</option>
                                                <option value="CAD" <?=get_option('payment_currency', 'USD')=="CAD"?"selected":""?>>CAD</option>
                                                <option value="EUR" <?=get_option('payment_currency', 'USD')=="EUR"?"selected":""?>>EUR</option>
                                                <option value="ILS" <?=get_option('payment_currency', 'USD')=="ILS"?"selected":""?>>ILS</option>
                                                <option value="NZD" <?=get_option('payment_currency', 'USD')=="NZD"?"selected":""?>>NZD</option>
                                                <option value="RUB" <?=get_option('payment_currency', 'USD')=="RUB"?"selected":""?>>RUB</option>
                                                <option value="SGD" <?=get_option('payment_currency', 'USD')=="SGD"?"selected":""?>>SGD</option>
                                                <option value="SEK" <?=get_option('payment_currency', 'USD')=="SEK"?"selected":""?>>SEK</option>
                                                <option value="BRL" <?=get_option('payment_currency', 'USD')=="BRL"?"selected":""?>>BRL</option>
                                                <option value="MXN" <?=get_option('payment_currency', 'USD')=="MXN"?"selected":""?>>MXN</option>
                                                <option value="THB" <?=get_option('payment_currency', 'USD')=="THB"?"selected":""?>>THB</option>
                                                <option value="JPY" <?=get_option('payment_currency', 'USD')=="JPY"?"selected":""?>>JPY</option>
                                                <option value="MYR" <?=get_option('payment_currency', 'USD')=="MYR"?"selected":""?>>MYR</option>
                                                <option value="PHP" <?=get_option('payment_currency', 'USD')=="PHP"?"selected":""?>>PHP</option>
                                                <option value="TWD" <?=get_option('payment_currency', 'USD')=="TWD"?"selected":""?>>TWD</option>
                                                <option value="CZK" <?=get_option('payment_currency', 'USD')=="CZK"?"selected":""?>>CZK</option>
                                                <option value="PLN" <?=get_option('payment_currency', 'USD')=="PLN"?"selected":""?>>PLN</option>
                                                <option value="VND" <?=get_option('payment_currency', 'USD')=="VND"?"selected":""?>>VND</option>
                                                <option value="GBP" <?=get_option('payment_currency', 'USD')=="GBP"?"selected":""?>>GBP</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('symbol')?></span> 
                                            <input type="text" class="form-control" name="payment_symbol" value="<?=get_option('payment_symbol', '$')?>">
                                        </div>

                                        <div class="lead"><?=lang('pagseguro')?></div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_pagseguro_enable" name="pagseguro_enable" class="filled-in chk-col-red" <?=get_option('pagseguro_enable', 0)==1?"checked":""?> value="1">
                                            <label class="p0 m0" for="md_checkbox_pagseguro_enable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('enable')?></span>
                                        </div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_pagseguro_enable_disable" name="pagseguro_enable" class="filled-in chk-col-red" <?=get_option('pagseguro_enable', 0)==0?"checked":""?> value="0">
                                            <label class="p0 m0" for="md_checkbox_pagseguro_enable_disable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('disable')?></span>
                                        </div>

                                        <div class="form-group">
                                            <span class="text"> <?=lang('email')?></span> 
                                            <input type="text" class="form-control" name="pagseguro_email" value="<?=get_option('pagseguro_email', '')?>">
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('token')?></span> 
                                            <input type="text" class="form-control" name="pagseguro_token" value="<?=get_option('pagseguro_token', '')?>">
                                        </div>

                                        <div class="lead"><?=lang('stripe')?></div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_stripe_enable" name="stripe_enable" class="filled-in chk-col-red" <?=get_option('stripe_enable', 0)==1?"checked":""?> value="1">
                                            <label class="p0 m0" for="md_checkbox_stripe_enable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('enable')?></span>
                                        </div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_stripe_enable_disable" name="stripe_enable" class="filled-in chk-col-red" <?=get_option('stripe_enable', 0)==0?"checked":""?> value="0">
                                            <label class="p0 m0" for="md_checkbox_stripe_enable_disable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('disable')?></span>
                                        </div>

                                        <div class="form-group">
                                            <span class="text"> <?=lang('publishable_key')?></span> 
                                            <input type="text" class="form-control" name="stripe_publishable_key" value="<?=get_option('stripe_publishable_key', '')?>">
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('secret_key')?></span> 
                                            <input type="text" class="form-control" name="stripe_secret_key" value="<?=get_option('stripe_secret_key', '')?>">
                                        </div>
                                        <div class="lead"><?=lang('paypal')?></div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_paypal_enable" name="paypal_enable" class="filled-in chk-col-red" <?=get_option('paypal_enable', 0)==1?"checked":""?> value="1">
                                            <label class="p0 m0" for="md_checkbox_paypal_enable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('enable')?></span>
                                        </div>
                                        <div class="pure-checkbox grey mr15 mb15">
                                            <input type="radio" id="md_checkbox_paypal_enable_disable" name="paypal_enable" class="filled-in chk-col-red" <?=get_option('paypal_enable', 0)==0?"checked":""?> value="0">
                                            <label class="p0 m0" for="md_checkbox_paypal_enable_disable">&nbsp;</label>
                                            <span class="checkbox-text-right"> <?=lang('disable')?></span>
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('client_id')?></span> 
                                            <input type="text" class="form-control" name="paypal_client_id" value="<?=get_option('paypal_client_id', '')?>">
                                        </div>
                                        <div class="form-group">
                                            <span class="text"> <?=lang('client_secret_key')?></span> 
                                            <input type="text" class="form-control" name="paypal_client_secret" value="<?=get_option('paypal_client_secret', '')?>">
                                        </div>
                                    </div>
</div>