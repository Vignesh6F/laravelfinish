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

namespace App\Services\PaymentMethods;

use App\Contracts\PaymentInterface;

class PaypalPayment implements PaymentInterface
{
    protected $gateway;
    public function __construct()
    {
        /*$this->merchant_account_id = payment_gateway('merchant_account_id','Braintree');
        \Braintree\Configuration::environment(payment_gateway('mode','Braintree'));
        \Braintree\Configuration::merchantId(payment_gateway('merchant_id','Braintree'));
        \Braintree\Configuration::publicKey(payment_gateway('public_key','Braintree'));
        \Braintree\Configuration::privateKey(payment_gateway('private_key','Braintree')); */

        $this->gateway = new \Braintree\Gateway([
                    'accessToken' => payment_gateway('access_token','Paypal')
                ]);

    }
	/**
	 * Make Paypal Payment
	 *
	 * @param Array $payment_data [payment_data includes currency, amount]
	 * @param String $[nonce] [nonce get it from Braintree gateway]
	 * @return Boolean
	 */
	public function makePayment($amount,$nonce) {
        try {
		    $result = $this->gateway->transaction()->sale([
                'amount'             => $amount,
                'paymentMethodNonce' => $nonce,
                'merchantAccountId' => site_settings('payment_currency'),

            ]);
        } catch(\Exception $e) {
            \Log::error(json_encode($e));
            return arrayToObject(array(
                'status'         => false,
                'status_message' => $e->getMessage(),
            ));
        }
 
        
        $return_data['status']      = false;
        $return_data['is_two_step'] = false;
        if($result->success) {
            $return_data['status']         = true;
            $return_data['transaction_id'] = $result->transaction->id;
        } else {
        	if(isset($result->transaction->processorResponseCode) && $result->transaction->processorResponseCode=='2091') {
        		$return_data['status_message'] = \Lang::get('messages.order.merchant_currency_error');
        	} else {
        		$return_data['status_message'] = $result->message;
        	}
        }
        return arrayToObject($return_data);
    }


    public function CreateCustomer($data)
    {
        try {
            $customer = $this->gateway->customer()->find($data['id']);
        }
        catch(\Exception $e) {
            try {
                $newCustomer = $this->gateway->customer()->create($data);

                if(!$newCustomer->success) {
                    return arrayToObject([
                        'status'         => false,
                        'status_message' => $newCustomer->message,
                    ]);
                }
                $customer = $newCustomer->customer;
            }
            catch(\Exception $e) {
                return arrayToObject([
                    'status'         => false,
                    'status_message' => $e->getMessage(),
                ]);
            }
        }
        try {
            $bt_clientToken = $this->gateway->clientToken()->generate([
                "customerId" => $customer->id
            ]);
        }
        catch(\Exception $e) {
            return arrayToObject([
                    'status'         => false,
                    'status_message' => $e->getMessage(),
                ]);   
        }
        return arrayToObject([
                                'bt_clientToken' => $bt_clientToken,
                                'status'         => true,
                                'status_message' => 'success',
                            ]);
    }


    public function ValidateTransactionId($transaction_id)
    {

        $environment = payment_gateway('mode','Paypal')=='sandbox' ? '.sandbox.':'.';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api".$environment."paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, payment_gateway('client','Paypal').':'.payment_gateway('secret','Paypal'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

                $result = curl_exec($ch);

        $json = json_decode($result);

        $curl = curl_init("https://api".$environment."paypal.com/v1/checkout/orders/".$transaction_id);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $json->access_token,
            'Accept: application/json',
            'Content-Type: application/json'
        ));
        $response = curl_exec($curl);
        $result = json_decode($response);
        if(isset($result->id))
        {
              return arrayToObject([
                                'transaction_id' => $transaction_id,
                                'status'         => true,
                                'is_two_step'    => false,
                                'status_message' => 'success',
                            ]);
        }
        return arrayToObject([
                                'is_two_step'    => false,
                                'status'         => false,
                                'status_message' => 'Invalid transaction id',
                            ]);


    }
}