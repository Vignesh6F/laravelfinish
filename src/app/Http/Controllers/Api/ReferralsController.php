<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\User;
use App\Models\ReferralSetting;
use App\Models\ReferralUser;
use App\Models\Currency;
use JWTAuth;
use App;

class ReferralsController extends Controller
{
	// Global variable for Helpers instance
	protected $request_helper;

    public function __construct(RequestHelper $request)
    {
    	$this->request_helper = $request;
		$this->helper = new Helpers;
	}

	/**
	 * To Get the referral Users Details
	 * @param  Request $request Get values
	 * @return Response Json
	 */
	public function get_referral_details(Request $request)
	{
		$user = JWTAuth::parseToken()->authenticate();

		$user_type = $user->user_type;
		$admin_referral_settings = ReferralSetting::whereUserType($user_type)->where('name','apply_referral')->first();

		$referral_amount = 0;
    	if($admin_referral_settings->value) {
        	$referral_amount = $admin_referral_settings->get_referral_amount($user_type);
		}

		$to_currency = $user->currency_code;
        $to_currency = Currency::whereCode($to_currency)->first();

		ReferralUser::$withoutAppends = true;
		$referral_users = ReferralUser::getReferralDetails($user->id, $to_currency->rate, $to_currency->symbol);

		return response()->json([
			'status_code' 			=> '1',
			'status_message' 		=> trans('messages.success'),
			'referral_link' 		=> url('app/'.strtolower($user_type)),
			'referral_code'  		=> $user->referral_code,
			'referral_amount' 		=> $referral_amount,
			'total_earning'  		=> $user->total_referral_earnings,
			'pending_referrals' 	=> isset($referral_users['Pending']) ? $referral_users['Pending']:[],
			'completed_referrals' 	=> isset($referral_users['Completed']) ? $referral_users['Completed']:[],
		]);
	}
}
