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

namespace App\Services;

use App\Models\Payment;
use App\Models\Wallet;
use App\Models\Trips;
use App\Models\PoolTrip;
use App\Models\DriverOweAmount;
use App\Models\ReferralUser;
use App\Models\AppliedReferrals;
use App\Models\DriverOweAmountPayment;
use App\Http\Helper\InvoiceHelper;
use App\Http\Helper\RequestHelper;
use Validator;
class BusinessLogic 
{

	public function __construct(InvoiceHelper $invoice_helper)
	{
		$this->invoice_helper = $invoice_helper;
	}

	public function validate($data,$user)
	{
		$method = 'for'.$data['pay_for'];
		return ($this)->$method($data,$user);
	}

	public function Fortrip($data,$user)
	{
		$rules = array(
			'trip_id' => 'required|exists:trips,id',
		);

        $validator = Validator::make(request()->all(), $rules);

        if ($validator->fails()) {
            return [
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ];
        }
        $trip = Trips::find(request()->trip_id);
        if($trip->is_calculation == 0) {
			$data = [
				'trip_id' => $trip->id,
				'user_id' => $user->id,
				'save_to_trip_table' => 0,
			];
			$trip = $this->invoice_helper->calculation($data);
		}
		if($trip->total_fare<1){
			return [
				'status_code' 		=> '3',
				'status_message' 	=> __('messages.success'),
			];
		}

        return [
			'status_code' 		=> '1',
			'status_message' 	=> __('messages.success'),
		];

	}

	public function Forwallet($data,$user)
	{
		return [
			'status_code' 		=> '1',
			'status_message' 	=> __('messages.success'),
		];
	}
	public function Forpay_to_admin($data,$user)
	{
		$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();

		$amount = $data['original_amount'];
		
		$return  = array (
							'status_code' => '1',
							'status_message' => __('messages.success'),
						);
		if($amount > 0  && $owe_amount) {
			if($owe_amount->amount < $amount) {
				$return  = array (
								'status_code' => '0',
								'status_message' => trans('messages.api.invalid')
							);
			}
		}
		if($data['applied_referral_amount']==1 && !$amount){
			$return  = array (
								'status_code' 		=> '2',
								'status_message' 	=> trans('messages.success')
							);
		}
        return $return;

	}
	

	public function add_wallet($data,$user)
	{

		$wallet = Wallet::whereUserId($user->id)->first();

        $wallet_amount = $data['original_amount'];
		if($wallet) {
			$wallet_amount = floatval($wallet->original_amount) + floatval($wallet_amount);
		}
		else {
			$wallet = new Wallet;
		}

		$wallet->amount = $wallet_amount;
		$wallet->paykey = $data['transaction_id'];
		$wallet->currency_code = $user->currency_code;
		$wallet->user_id = $user->id;
		$wallet->save();

		return [
			'status_code' 		=> '1',
			'status_message' 	=> __('messages.wallet_add_success'),
			'wallet_amount' 	=>  (string) floatval($wallet_amount),
		];
	}



	/**
	 * After Payment update the trip status
	 * @param  
	 *
	 * @return Response Json
	 */
	public function tripPayment($payment_data,$user)
	{

		$trip = Trips::find($payment_data['trip_id']); 

		if($trip->is_calculation == 0) {
			$data = [
				'trip_id' => $trip->id,
				'user_id' => $user->id,
				'save_to_trip_table' => 1,
			];
			$trip = $this->invoice_helper->calculation($data);
		}

		$trip->status = 'Completed';
		$trip->paykey = $payment_data['transaction_id'] ?? '';
		$trip->payment_status = 'Completed';
		$trip->save();

		if($trip->pool_id>0) {

			$pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
			$trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])->count();
			
			if(!$trips) {
				// update status
				$pool_trip->status = 'Completed';
				$pool_trip->save();
			}
		}

		$data = [
			'trip_id' => $trip->id,
			'correlation_id' => $pay_result->transaction_id ?? '',
			'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
		];
		Payment::updateOrCreate(['trip_id' => $trip->id], $data);

		//Push notification 
		$push_title = "Payment Completed";
		$push_data['push_title'] = $push_title;
        $push_data['data'] = array(
        	'trip_payment' => array(
        		'status' => 'Paid',
        		'trip_id' => $trip->id,
        		'rider_thumb_image' => $trip->rider_profile_picture
        	)
        );
        $request_helper = new RequestHelper();
        $request_helper->SendPushNotification($trip->driver,$push_data);

		return [
			'status_code' 		=> '1',
			'status_message' 	=> __('messages.payment_complete_success'),
			
		];
	}

	public function pay_to_admin($data,$user)
	{

		$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
		if ($owe_amount && $owe_amount->amount > 0) {
			//applying referral amount start
			if ($data['applied_referral_amount']==1) {

				$total_referral_amount = ReferralUser::where('user_id',$user->id)
					->where('payment_status','Completed')
					->where('pending_amount','>',0)
					->get()
					->sum('pending_amount');

				if ($owe_amount->amount < $total_referral_amount) {
					$total_referral_amount = $owe_amount->amount;
				}

				if ($total_referral_amount > 0) {
					$applied_referrals = new AppliedReferrals;
					$applied_referrals->amount = $total_referral_amount;
					$applied_referrals->user_id = $user->id;
					$applied_referrals->currency_code = $user->currency->code;
					$applied_referrals->save();
					$this->invoice_helper->referralUpdate($user->id,$total_referral_amount,$user->currency->code);

					//owe amount
					$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
					$currency_code = $owe_amount->currency_code;
					$owe_amount->amount = $owe_amount->amount - $total_referral_amount;
					$owe_amount->currency_code = $currency_code;
					$owe_amount->save();

					$payment = new DriverOweAmountPayment;
					$payment->user_id = $user->id;
					$payment->transaction_id = "";
					$payment->amount = $total_referral_amount;
					$payment->currency_code = $currency_code;
					$payment->status = 1;
					$payment->save();

					$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				}
			}
			//applying referral amount

			//pay to admin from payout preference start
			$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();

			$amount = $data['original_amount'];


			if($amount > 0) {
				if($owe_amount->amount < $amount) {
					return [
						'status_code' => '0',
						'status_message' => trans('messages.api.invalid'),
					];
				}


				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
				$total_owe_amount = $owe_amount->amount;
				$currency_code = $owe_amount->currency_code;
				$remaining_amount = $total_owe_amount - $amount;

				$payment_data['currency_code'] = $user->currency_code;
				$payment_data['amount'] = $amount;
				$payment_data['user_id'] = $user->id;


				//owe amount
				$owe_amount->amount = $remaining_amount;
				$owe_amount->currency_code = $currency_code;
				$owe_amount->save();

				$payment = new DriverOweAmountPayment;
				$payment->user_id = $user->id;
				$payment->transaction_id = $data['transaction_id'];
				$payment->amount = $amount;
				$payment->status = 1;
				$payment->currency_code = $currency_code;
				$payment->save();

				$owe_amount = DriverOweAmount::where('user_id', $user->id)->first();
			}

			$referral_amount = ReferralUser::where('user_id',$user->id)->where('payment_status','Completed')->where('pending_amount','>',0)->get();
			$referral_amount = number_format($referral_amount->sum('pending_amount'),2,'.','');

			return [
				'status_code' 	=> '1',
				'status_message'=> __('messages.payment_complete_success'),
				'referral_amount' => $referral_amount,
				'owe_amount' 	=> $owe_amount->amount,
				'currency_code' => $owe_amount->currency_code
			];
		}
		
		return [
			'status_code' => '0',
			'status_message' => __('messages.api.not_generate_amount'),
		];
	}

}