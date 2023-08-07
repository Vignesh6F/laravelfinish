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

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use JWTAuth;
class PaypalPayment extends Controller
{
	public function view()
	{
		$data['amount'] = 100;
		$data['currency_code'] = 'USD';
		$data['payment_type'] = 'paypal';
		$data['pay_for'] = 'wallet';
		$data['token'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdmlub3RoLmNvbS9nb2Zlci9wdWJsaWMvYXBpL2xvZ2luIiwiaWF0IjoxNjE2MTMzNjg0LCJleHAiOjE2MTg3NjE2ODQsIm5iZiI6MTYxNjEzMzY4NCwianRpIjoiMzY4ejZXNnhLWWJDdGdKeCIsInN1YiI6MTAxMDcsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.IIJaQNbwXXvZzjmKD8TYB3ktPLPacEtobdWcNo-YKMc';

		return array('view'=>'paypal','data'=>$data);
	}
}