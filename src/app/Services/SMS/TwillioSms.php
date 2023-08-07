<?php

/**
 * Send sms via Twillio
 *
 * @package     Gofer
 * @subpackage  Services
 * @category    Auth Service
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
*/

namespace App\Services\SMS;

use Illuminate\Http\Request;
use App\Contracts\SMSInterface;
use Twilio\Rest\Client;


class TwillioSms implements SMSInterface
{
	private $client,$verify;

	

		/**
     * Initialize Twillo credentials
     *
     * @return void
     */
	public function initialize()
	{
		$this->client = new Client(api_credentials('sid','Twillo'), api_credentials('token','Twillo'));
		$this->verify = $this->client->verify->v2->services(api_credentials('service_sid','Twillo'));

	}

	/**
     * Send OTP message
     *
     * @param String $phone_number
     * @return Array SMS Response
     */
	protected function sendOTP($phone_number)
	{
		try {
			$data = $this->verify->verifications->create($phone_number, "sms");
			return array('status_code' => 1,'message'=>'Success','status'=>true);
		} 
		catch(\Exception $e) {
			return array('status_code' => 0,'message'=>$e->getMessage() ,'status'=>false);
		}
	}

	/**
     * Verify OTP number
     *
     * @param String $phone_number, String $verification_code
     * @return Array SMS Response
     */
	protected function verifyOTP($phone_number, $verification_code)
	{
		try {

			$verification = $this->verify->verificationChecks->create($verification_code, array('to'=>$phone_number));

			if($verification->valid)
				return array('status_code' => 1,'message'=>'Success','status'=>true);
			else 
				return array('status_code' => 0,'message'=>__('messages.signup.wrong_otp'),'status'=>false);
		} 
		catch(\Exception $e) {
			return array('status_code' => 0,'message'=>__('messages.signup.wrong_otp'),'status'=>false);
		}
	}

	/**
     * Send Text message to mobile
     *
     * @param String $phone_number, String $verification_code
     * @return Array SMS Response
     */
	public function send($phone_number, $text='',$verification_code=false)
	{
		$this->initialize();
		if($text){
			$result = $this->SendTextMessage($phone_number,$text);
		}
		else if($verification_code) {
			$result = $this->verifyOTP($phone_number, $verification_code);
		} else {
			$result = $this->sendOTP($phone_number);
		}
		return $result;
	}
	/**
     * Send Text message to mobile
     *
     * @param String $[to] user phone number
     * @param String $[text] [message to be send]
     * @return Array SMS Response
     */
	public function SendTextMessage($to,$text)
	{
		try {
			// Use the client to do fun stuff like send text messages!
			$this->client->messages->create(
			    // the number you'd like to send the message to
			    $to,
			    [
			        // A Twilio phone number you purchased at twilio.com/console
			        'from' => api_credentials('from','Twillo'),
			        // the body of the text message you'd like to send
			        'body' => $text
			    ]
			);
			return array('status_code' => 1,'message'=>'Success','status'=>true);
		} 

		catch(\Exception $e) {
			return array('status_code' => 0,'message'=>$e->getMessage(),'status'=>false);
		}
		return array('status_code' => 1,'message'=>'Success','status'=>true);

	}
}
