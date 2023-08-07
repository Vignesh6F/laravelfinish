<?php

/**
 * Auth Interface
 *
 * @package     Gofer
 * @subpackage  Contracts
 * @category    Auth Interface
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Contracts;

use Illuminate\Http\Request;

interface AuthInterface
{
	public function create(Request $request);
	public function login($credentials);
}