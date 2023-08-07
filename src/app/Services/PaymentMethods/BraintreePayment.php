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

class BraintreePayment implements PaymentInterface
{

    public function __construct()
    {
        $this->merchant_account_id = payment_gateway('merchant_account_id','Braintree');
        \Braintree\Configuration::environment(payment_gateway('mode','Braintree'));
        \Braintree\Configuration::merchantId(payment_gateway('merchant_id','Braintree'));
        \Braintree\Configuration::publicKey(payment_gateway('public_key','Braintree'));
        \Braintree\Configuration::privateKey(payment_gateway('private_key','Braintree')); 
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
        	
 
		    $result = \Braintree\Transaction::sale([
                'amount'             => $amount,
                'paymentMethodNonce' => $nonce,
                'options'            => [
                                            'submitForSettlement' => True
                                        ],
                'merchantAccountId' => $this->merchant_account_id

            ]);
        } catch(\Exception $e) {
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
            $customer = \Braintree\Customer::find($data['id']);
        }
        catch(\Exception $e) {
            try {
                $newCustomer = \Braintree\Customer::create($data);

                if(!$newCustomer->success) {
                    return arrayToObject([
                        'status'         => false,
                        'status_message' => $newCustomer->message,
                    ]);
                }
                $customer = $newCustomer->customer;
            }
            catch(\Exception $e) {
                if($e instanceOf \Braintree\Exception\Authentication) {
                    return arrayToObject([
                        'status'         => false,
                        'status_message' => __('messages.api.authentication_failed'),
                    ]);
                }
                return arrayToObject([
                    'status'         => false,
                    'status_message' => $e->getMessage(),
                ]);
            }
        }
        try {
            $bt_clientToken = \Braintree\ClientToken::generate([
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
}