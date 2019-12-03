<?php
require "paypal/autoload.php";

class paypalapi{
    private $ClientID;
    private $ClientSecret;
    private $apiContext;

    public function __construct($ClientID = null, $ClientSecret = null){
        if($ClientID != "" && $ClientSecret != ""){
            $this->apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential($ClientID, $ClientSecret)
            );

            if(get_option("payment_environment", 0)){
                $this->apiContext->setConfig(
                    array(
                        'mode' => 'live',
                    )
                );
            }
        }
    }

    public function createPayment($data = array()){
        if(!empty($data)){
            $data = (object)$data;
            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');
            $amount = new \PayPal\Api\Amount();
            $amount->setTotal($data->amount);
            $amount->setCurrency($data->currency);
            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount);
            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl($data->redirect_url)
                ->setCancelUrl($data->cancel_url);
            $payment = new \PayPal\Api\Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls);

            try {
                $payment->create($this->apiContext);

                set_session("paypal_payment_id", $payment->getId());
                redirect($payment->getApprovalLink());
            }
            catch (\PayPal\Exception\PayPalConnectionException $ex) {
                redirect(cn('pricing'));
            }
        }
    }

    public function getPaymentDetails($PaymentId = ""){
        try {
            $payment = \PayPal\Api\Payment::get($PaymentId, $this->apiContext);
        } catch (Exception $ex) {
            redirect(cn('pricing'));
        }

        return $payment;
    }

    public function proccessPayment($PaymentId, $PayerId = ""){
        // Get payment object by passing paymentId
        $payment = $this->getPaymentDetails($PaymentId);

        // Execute payment with payer id
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($PayerId);

        try {
            // Execute payment
            $result = $payment->execute($execution, $this->apiContext);

            // Extract order
            $order = $payment->transactions[0]->related_resources[0]->order;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            $error_data = json_decode($ex->getData());
            redirect(cn('payment_unsuccessfully')."?message=".urlencode($error_data->message));
        } catch (Exception $ex) {
            redirect(cn('payment_unsuccessfully')."?message=".urlencode($ex->getMessage()));
            die($ex);
        }

        return $order;
    }
}