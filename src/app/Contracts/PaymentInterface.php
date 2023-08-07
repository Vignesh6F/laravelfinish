<?php

/**
 * Payment Interface
 *
 * @package     Gofer
 * @subpackage  Contracts
 * @category    Payment Interface
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Contracts;

interface PaymentInterface
{
	function makePayment($payment_data,$nonce);
}