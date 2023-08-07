<?php

/**
 * Stripe Payment Service
 *
 * @package     Gofer
 * @subpackage  Services\Payments
 * @category    Stripe
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Models\PaymentMethod;
use App\Services\PaymentMethods\StripePayment as StripeGateway;


class StripePayment extends StripeGateway implements PaymentInterface
{

    public function view($user)
    {
        $data['save_card']  = PaymentMethod::select('id','brand','last4')->where('user_id',$user->id)->get();
        $data['public_key'] = payment_gateway('publish','Stripe');
        return array('view' => 'payment.stripe','data' => $data);
    }


    public function Payment($payment_data)
    {
        $responce =  $this->makePayment($payment_data,request()->pay_key);
        return $this->returnResponce($responce);
    }


    public function returnResponce($responce)
    {
      $responce->status_code =  $responce->status == "success" ? "1":"0";
      return $responce;
    }

    public function attachCustomer($user,$customer_id,$paymentMethod_id)
    {
        $setup_intent = $this->createSetupIntent($customer_id,$paymentMethod_id);
        if($setup_intent->status == 'failed') {
            return [
                'status_code'   => '0',
                'intent_status' => $setup_intent->status,
                'status_message'=> $setup_intent->status_message ?? '',
            ];
        }

       $payment =  $this->attachPaymentToCustomer($customer_id,$setup_intent->payment_method);
        if($payment->status == 'failed') {
            return [
                'status_code'   => '0',
                'intent_status' => $payment->status,
                'status_message'=> $payment->status_message ?? '',
            ];
        }
        $payment_method            = $this->getPaymentMethod($setup_intent->payment_method);
        $data['intent_id']         = $setup_intent->intent_id;
        $data['payment_method_id'] = $setup_intent->payment_method;
        $data['brand']             = $payment_method['card']['brand'];
        $data['last4']             = $payment_method['card']['last4'];
        return $data;
    }

}