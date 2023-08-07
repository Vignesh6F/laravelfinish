<?php

/**
 * Braintree Payment Service
 *
 * @package     Gofer
 * @subpackage  Services\Payments
 * @category    Braintree
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Services\PaymentMethods\BraintreePayment as BraintreeGateway;

class BraintreePayment extends BraintreeGateway implements PaymentInterface 
{

	public function view($user)
	{
        $data['tokenization_key'] = payment_gateway('tokenization_key','Braintree');
        $data['get_user_token'] = $this->CreateCustomerToken($user);
		return array('view'=>'payment.braintree','data'=>$data);
	}

    public function Payment($amount,$nonce)
    {
    	\Log::error($amount);
        $responce = $this->makePayment($amount,$nonce);
        return $this->returnResponce($responce);
    }

    public function returnResponce($responce)
    {
      $responce->status_code =  $responce->status ? "1":"0";
      return $responce;
    }

    public function CreateCustomerToken($user_details)
    {
        $data  = array(
                'id'        => $user_details->id.$user_details->mobile_number,
                'firstName' => $user_details->first_name,
                'lastName'  => $user_details->last_name,
                'email'     => $user_details->email,
                'phone'     => $user_details->phone_number,
            );
        return  $this->returnResponce($this->CreateCustomer($data));
        
    }

}