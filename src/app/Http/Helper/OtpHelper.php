<?php

/**
 * OTP Helper
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Helper
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */
namespace App\Http\Helper;

class OtpHelper
{
	/**
	 * Send OTP
	 *
	 * @param integer $country_code
	 * @param integer $mobile_number
	 * @return Array $response
	 */
	public function sendOtp($mobile_number,$country_code)
	{		
		if(canDisplayCredentials()){
			$response = [
				'status_code' => 1,
				'message' => 'success',
				'signup_otp' => "1244",
			];

		}
		else{
	        $to = '+'.$country_code.$mobile_number;
	        $sms_gateway = resolve("App\Contracts\SMSInterface");
	        $response = $sms_gateway->send($to);
    	}

    	 if($response['status_code']==1) 
	        {
	        	session([
					'signup_mobile' => $mobile_number,
					'signup_country_code' => $country_code,
					'signup_otp' => canDisplayCredentials() ? "1244":'',
				]);
	        }
	        

        	return $response;
    
    }
	
		
	

	/**
	 * Resend OTP
	 *
	 * @return Array $response
	 */
	public function resendOtp()
	{
		if(canDisplayCredentials()){
			$response = [
				'status_code' => 1,
				'message' => 'success',
				'signup_otp' => "1244",
			];
		}

		$otp = rand(1000,9999);
        $text = 'Your OTP number is '.$otp;
        $to = '+'.session('signup_country_code').session('signup_mobile');
        $sms_gateway = resolve("App\Contracts\SMSInterface");
        $response = $sms_gateway->send($to);

        if ($response['status_code']==1) {
            session(['signup_otp' => $otp]);
            $response['message'] = trans('messages.signup.otp_resended');
        }

		return $response;
	}

	/**
	 * Check Given OTP
	 *
	 * @param integer $otp
	 * @param integer $mobile_number
	 * @return Array $response
	 */
	public function checkOtp($otp,$mobile_number = null,$country_code='')
	{
		if(canDisplayCredentials())
		{
			return  [
					'status_code' => 1,
					'message' => 'success',
				];
		}
		else{
			$sms_gateway = resolve("App\Contracts\SMSInterface");
	        return  $sms_gateway->send('+'.$country_code.$mobile_number,'',$otp);
    	}
	}
}