<?php

/**
 * Trip Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Trip
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use JWTAuth;
use Validator;

class AppPaymentController extends Controller
{
	public function getData()
	{
        $data['pay_for'] = $this->getPayFor();
    	$data['amount'] = request()->amount;
    	$data['original_amount'] = request()->amount;
    	$data['currency_code'] = request()->currency_code ?? site_settings('payment_currency');
    	$data['payment_type'] = request()->payment_type;
    	$data['user_token'] = request()->user_token;
     	$data['key'] = request()->pay_key;
     	$data['applied_referral_amount'] = request()->applied_referral_amount;
     	$data['trip_id'] = request()->trip_id;
     	return $data;
	}

	public function get_user_deatils()
	{
		return \JWTAuth::parseToken()->authenticate();
	}


	public function getPayFor()
	{
		if(request()->segment(2)=='pay_to_admin')
			return 'pay_to_admin';
		else if(request()->segment(2)=='add_wallet')
			return 'wallet';
		else
			return 'trip';
	}

	public function appPaymentSuccess()
	{
		$business_logic = resolve('App\Services\BusinessLogic');
		$validate = $business_logic->validate($this->getData(),$this->get_user_deatils());
		// only referal in pay to admin 
		if($validate['status_code']=='2')
			return $business_logic->pay_to_admin($this->getData(),$this->get_user_deatils());
		else if($validate['status_code']=='3')
			return $business_logic->tripPayment($this->getData(),$this->get_user_deatils());
		else if($validate['status_code']=='0')
			return response($validate);

		$user = \JWTAuth::parseToken()->authenticate();
		$service = 'App\Services\Payments\\'.ucfirst(request()->payment_type).'Payment';
		$this->payment = resolve($service);
		
		$method = 'get'.request()->payment_type.'Data';
     	$paymentData = ($this)->$method($user);
		
		$payment_status = $this->payment->Payment($paymentData['payment_data'],request()->pay_key);
		
		if($payment_status->status_code == "1" && !($payment_status->is_two_step ?? false) ){
			$payment_status = $this->payment_action($payment_status,$paymentData['all_data'],request()->payment_type);
		}
		elseif ($payment_status->is_two_step ?? false) {
			return response()->json([
	                'status_code' => '2',
	                'status_message' => $payment_status->status_message,
	                'two_step_id' => $payment_status->two_step_id,
	            ]);
		}
		return response()->json($payment_status);
	} 

	public function getSaveCardData($user,$payment_method)
    {
        $data['payment_data'] = array(
		        'payment_method'        => $payment_method->payment_method_id,
		        'customer'              => $payment_method->customer_id,
                'confirm'               => true,
                'off_session'           => true,
                'confirmation_method'   => 'manual',
                "amount"        		=> intval((request()->amount) * 100),
                'currency'      		=> $user->currency_code,
                'description'   		=> request()->pay_for.' Payment by '.$user->first_name,
            );
        $data['all_data'] = $this->getData();
        return $data;
    }


	public function getStripeData($user)
	{
		$card_details   = '';
        if(request()->save_card_id){
            $card_details = PaymentMethod::where('user_id',$user->id)->where('id',request()->save_card_id)->first();
        }else{
            $card_details = PaymentMethod::where('user_id',$user->id)->Payment()->first();
        }
        return  $this->getSaveCardData($user,$card_details);

	}

	public function getbraintreeData($user)
	{
       	$data['payment_data'] = currencyConvert($user->currency_code,site_settings('payment_currency'),request()->amount);

        $data['key'] = request()->pay_key;
        $data['all_data'] = $this->getData();
        return $data;

	}

	public function getPaypalData($user)
	{
		$data['payment_data'] = currencyConvert($user->currency_code,site_settings('payment_currency'),request()->amount);
        $data['key'] = request()->pay_key;
		$data['all_data'] = $this->getData();
        return $data;
	}



	public function payment_action($payment,$paymentData)
	{
		$payment = json_decode(json_encode($payment),true);
		$data = array_merge($payment,$paymentData);

		$business_logic = resolve('App\Services\BusinessLogic');
		if($paymentData['pay_for']=='wallet')
			return $business_logic->add_wallet($data,$this->get_user_deatils());
		else if($paymentData['pay_for']=='pay_to_admin')
			return $business_logic->pay_to_admin($data,$this->get_user_deatils());
		else
			return $business_logic->tripPayment($data,$this->get_user_deatils());
	}

	/**
	 * API for create a customer id  based on card details using stripe payment gateway
	 *
	 * @return Response Json response with status
	 */
	public function add_card_details(Request $request)
	{
		$rules = array(
            'intent_id'			=> 'required',
        );

        $attributes = array(
            'intent_id'     	=> 'Setup Intent Id',
        );

        $validator = Validator::make($request->all(), $rules,$attributes);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

		$user_details = JWTAuth::parseToken()->authenticate();
		$stripe_payment = resolve('App\Services\PaymentMethods\StripePayment');

		/*$payment_details = PaymentMethod::where('user_id',$user_details->id)->Payment()->first();
		if(!$payment_details)
			$payment_details = new PaymentMethod;*/

		$payment_details = PaymentMethod::firstOrNew(['user_id' => $user_details->id]);

		$setup_intent = $stripe_payment->getSetupIntent($request->intent_id);

		if($setup_intent->status != 'succeeded') {
			return response()->json([
				'status_code' => '0',
				'intent_status' => $setup_intent->status,
				'status_message' => $setup_intent->status_message ?? '',
			]);
		}

		if($payment_details->payment_method_id != '') {
			$stripe_payment->detachPaymentToCustomer($payment_details->payment_method_id);
		}

		$stripe_payment->attachPaymentToCustomer($payment_details->customer_id,$setup_intent->payment_method);

		$payment_method = $stripe_payment->getPaymentMethod($setup_intent->payment_method);
		$payment_details->user_id = $user_details->id;
		$payment_details->intent_id = $setup_intent->id;
		$payment_details->payment_method_id = $setup_intent->payment_method;
		$payment_details->brand = $payment_method['card']['brand'];
		$payment_details->last4 = $payment_method['card']['last4'];
		$payment_details->save();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> 'Added Successfully',
			'brand' 			=> $payment_details->brand,
			'last4' 			=> strval($payment_details->last4),
		]);
	}


	/**
	 * API for payment card details
	 *
	 * @return Response Json response with status
	 */
	public function get_card_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$stripe_payment = resolve('App\Services\PaymentMethods\StripePayment');

		$payment_details = PaymentMethod::where('user_id',$user_details->id)->orderBy('id', 'desc')->first();
		//$payment_details = PaymentMethod::where('user_id',$user_details->id)->Payment()->orderBy('id', 'desc')->first();
		if(!$payment_details)
			$payment_details = new PaymentMethod;

		$payment_details->user_id = $user_details->id;

		if(!isset($payment_details->customer_id)) {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->customer_id = $stripe_customer->customer_id;
			$payment_details->save();
		}
		$customer_id = $payment_details->customer_id;

		// Check New Customer if customer not exists
		$customer_details = $stripe_payment->getCustomer($customer_id);
		if($customer_details->status == "failed" && $customer_details->status_message == "resource_missing") {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->customer_id = $stripe_customer->customer_id;
			$payment_details->save();
			$customer_id = $payment_details->customer_id;
		}

		$status_code = "1";
		if($payment_details->intent_id == '') {
			$status_code = "2";
		}

		$setup_intent = $stripe_payment->createSetupIntent($customer_id);
		if($setup_intent->status == 'failed') {
			return response()->json([
				'status_code' 		=> "0",
				'status_message' 	=> $setup_intent->status_message,
			]);
		}

		return response()->json([
			'status_code' 		=> $status_code,
			'status_message' 	=> 'Listed Successfully',
			'intent_client_secret'=> $setup_intent->intent_client_secret,
			'brand' 			=> $payment_details->brand ?? '',
			'last4' 			=> (string)$payment_details->last4 ?? '',
		]);
	}



}