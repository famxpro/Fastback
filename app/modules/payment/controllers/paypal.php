<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class paypal extends MX_Controller {

	public $tb_packages;
	public $tb_payment_history;
	public $tb_users;
	public $pp;

	public function __construct(){
		parent::__construct();
		$this->tb_packages = PACKAGES;
		$this->tb_payment_history = PAYMENT_HISTORY;
		$this->tb_users = USERS;
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->library('paypalapi');
		$this->pp = new PaypalAPI(get_option('paypal_client_id'), get_option('paypal_client_secret'));
	}

	public function index(){
		if(ajax_page()){
			$package = $this->model->get("*", PACKAGES, "ids = '".get_secure("pid")."'  AND status = 1");
			if(empty($package)){
				echo  "Cannot processing this getway, Please try again later.";
				exit(0);
			}
			$data = array(
				'package' => $package
			);
			$this->load->view('paypal', $data);
		}
	}

	public function process(){
		$ids  = segment(4);
		$plan  = segment(5);
		$package = $this->model->get("*", $this->tb_packages, "ids = '".$ids."'  AND status = 1");

		if(!empty($package)){
			$amount = $package->price_monthly;
			if($plan == 2){
				$amount = $package->price_annually*12;
			}else{
				$plan = 1;
			}

			set_session('paypal_package', $ids);
			set_session('paypal_plan', $plan);

			$this->pp->createPayment(array(
				"amount" => $amount,
				"currency" => get_option('payment_currency','USD'),
				"redirect_url" => cn("payment/paypal/complete"),
				"cancel_url" => cn("payment_unsuccessfully")
			));

		}

	}

	public function complete(){
		$ids = session('paypal_package');
		$plan = session('paypal_plan');
		$paymentId = get('paymentId');
		$PayerID = get('PayerID');
		$order = $this->pp->proccessPayment($paymentId, $PayerID);
		$result = $this->pp->getPaymentDetails($paymentId);

		$package = $this->model->get("*", $this->tb_packages, "ids = '".$ids."'  AND status = 1");

		if(!empty($package) && !empty($result) && session("paypal_payment_id") == $paymentId && $result->state == "approved"){
			$data = array(
				'ids' => ids(),
				'uid' => session("uid"),
				'package' => $package->id,
				'type' => 'paypal_charge',
				'transaction_id' => $result->id,
				'amount' => $result->transactions[0]->amount->total,
				'plan' => $plan,
				'status' => 1,
				'created' => NOW
			);
			$this->db->insert($this->tb_payment_history, $data);
			$this->update_package($package, $plan);

			redirect(cn('thank_you'));
		}else{
			redirect(cn("payment_unsuccessfully"));
		}
	}

	public function cancel(){
		
	}

	public function update_package($package_new, $plan){
		$user = $this->model->get("*", $this->tb_users, "id = '".session("uid")."'");
		if(!empty($user)){
			$package_old = $this->model->get("*", $this->tb_packages, "id = '".$user->package."'");
			$package_id = $package_new->id;

			$new_days  = 30;
			if($plan == 2){
				$new_days  = 365;
			}

			if(!empty($package_old)){
				if(strtotime(NOW) < strtotime($user->expiration_date)){
					$date_now = date("Y-m-d", strtotime(NOW));
					$date_expiration = date("Y-m-d", strtotime($user->expiration_date));
					$diff = abs(strtotime($date_expiration) - strtotime($date_now));
					$left_days = floor($diff/86400);

					if($plan == 2){
						$day_added = round(($package_old->price_annually/$package_new->price_annually)*$left_days);
					}else{
						$day_added = round(($package_old->price_monthly/$package_new->price_monthly)*$left_days);
					}

					$total_day = $new_days + $day_added;
					$expiration_date = date('Y-m-d', strtotime(NOW." +".$total_day." days"));
				}else{
					$expiration_date = date('Y-m-d', strtotime(NOW." +".$new_days." days"));
				}
			}else{
				$expiration_date = date('Y-m-d', strtotime(NOW." +".$new_days." days"));
			}

			$data = array(
				"package"      => $package_id,
				"expiration_date" => $expiration_date
			);

			$this->db->update($this->tb_users, $data, "id = '".session("uid")."'");
		}
	}
}