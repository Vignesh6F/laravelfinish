<?php

/**
 * Auth Via Email
 *
 * @package     Gofer
 * @subpackage  Services
 * @category    Auth Service
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services\Auth;

use Illuminate\Http\Request;
use App\Contracts\AuthInterface;
use App\Models\User;
use App\Models\DriverAddress;
use App\Models\ProfilePicture;
use App\Models\Country;
use JWTAuth;
use Auth;
use DB;

class AuthViaEmail implements AuthInterface
{
	/**
	 * get Rules to validate Request
	 *
	 * @param String $[user_type] [user_type]
	 * @return Array
	 */
	protected function getRules($user_type)
	{
		$rules = array(
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       => 'required|in:Rider,Driver,rider,driver',
            'auth_type'       => 'required|in:facebook,google,apple,email',
            'email_id'        => 'required|max:255|email',
            'password'        => 'required|min:6',
            'first_name'      => 'required',
            'last_name'       => 'required',
            'country_code'    => 'required',
            'gender'    	  => 'required|in:male,female',
            'device_type'     => 'required',
            'device_id'       => 'required',
            'referral_code'   => 'nullable|exists:users,referral_code',
        );

        if(strtolower($user_type) == 'driver') {
            $rules['city'] = 'required';
        }

        $attributes = array(
            'mobile_number' => trans('messages.user.mobile'),
            'referral_code' => trans('messages.referrals.referral_code'),
            'gender' => trans('messages.profile.gender'),
        );

        $messages = array(
            'referral_code.exists'  => trans('messages.referrals.enter_valid_referral_code'),
        );
        return compact('rules','messages','attributes');
	}

	/**
	 * validate Request
	 *
	 * @param Request $[request] [user_type]
	 * @return Mixed
	 */
	public function validate(Request $request)
	{
		$rules = $this->getRules($request->user_type);
		$validator = \Validator::make($request->all(), $rules['rules'], $rules['messages'], $rules['attributes']);

		if($validator->fails())  {
		    return response()->json([
		        'status_code' => '0',
		        'status_message' => $validator->messages()->first()
		    ]);
		}

		$referral_check = User::whereUserType(ucfirst($request->user_type))->where(DB::raw('BINARY `referral_code`'),$request->referral_code)->count();
		if($request->referral_code != '' && $referral_check == 0) {
		    return response()->json([
		        'status_code' => '0',
		        'status_message' => __('messages.referrals.enter_valid_referral_code')
		    ]);
		}

		$mobile_number 	= $request->mobile_number;
		$country_id 	= Country::whereShortName($request->country_code)->value('id');

		$user_count = User::whereCountryId($country_id)->whereMobileNumber($mobile_number)->whereUserType($request->user_type)->count();
		
		if($user_count > 0) {
		    return response()->json([
		        'status_code'     => '0',
		        'status_message' =>  trans('messages.already_have_account'),
		    ]);
		}

		$user_email_count = User::where('email', $request->email_id)->where('user_type', $request->user_type)->count();
		if($user_email_count > 0) {
		    return response()->json([
		        'status_code'     => '0',
		        'status_message' =>  trans('messages.api.email_already_exists'),
		    ]);
		}
		return false;
	}

	/**
	 * Create new User
	 *
	 * @param Request $[request] [user_type]
	 * @return user instance of Models/User
	 */
	public function create(Request $request)
	{
		$user = new User;
		$user->mobile_number    = $request->mobile_number;
		$user->first_name       = $request->first_name;
		$user->last_name        = $request->last_name;
		$user->user_type        = $request->user_type;
		$user->password         = $request->password;
		$user->device_type      = $request->device_type;
		$user->device_id        = $request->device_id;
		$user->language         = $request->language ?? 'en';;
		$user->email            = $request->email_id;
		$user->currency_code    = get_currency_from_ip();
		$user->gender       	= $request->gender=='male' ? 1:2;
		$user->used_referral_code = $request->referral_code;

		$country = Country::whereShortName($request->country_code)->first();
		$user->country_code = $country->phone_code;
		$user->country_id 	= $country->id;

		if(strtolower($request->user_type) =='rider') {
		    $user->status = "Active";
		    $user->save();                  
		}
		else {
		    $user->company_id = 1;
		    $user->status = "Car_details";
		    $user->save();

		    $driver_address = new DriverAddress;
		    $driver_address->user_id           = $user->id;
		    $driver_address->address_line1     = '';
		    $driver_address->address_line2     = '';
		    $driver_address->city              = $request->city;
		    $driver_address->state             = '';
		    $driver_address->postal_code       = '';
		    $driver_address->save();
		}

		$profile               = new ProfilePicture;
		$profile->user_id      = $user->id;
		$profile->src          = '';
		$profile->photo_source = 'Local';
		$profile->save();

		return $user;
	}

	public function login($credentials)
	{     
        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return response()->json(['error' => 'invalid_credentials']);
            }
        } catch(JWTException $e) {
            return response()->json(['error' => 'could_not_create_token']);
        }

        $return_data = array(
            'status_code'       => '1',
            'status_message'    => __('messages.user.register_successfully'),
            'access_token'      => $token,
        );

        return $return_data;
	}
}
