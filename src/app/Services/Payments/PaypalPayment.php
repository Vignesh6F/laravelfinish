<?php

/**
 * Paypal Payment Service
 *
 * @package     Gofer
 * @subpackage  Services\Payments
 * @category    Paypal
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Services\PaymentMethods\PaypalPayment as PaypalGateway;

class PaypalPayment extends PaypalGateway implements PaymentInterface 
{

	public function view($user)
	{
        $data['tokenization_key'] = payment_gateway('tokenization_key','Braintree');
        $data['merchant_account_id'] = payment_gateway('merchant_account_id','Braintree');
        $data['client_id'] = payment_gateway('client','Paypal');
		return array('view'=>'payment.paypal','data'=>$data);
	}

    public function Payment($amount,$nonce)
    {   
        //for web payment 
        if(payment_gateway('is_web_payment','Common'))
            $responce = $this->ValidateTransactionId($nonce,$amount);
        else
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