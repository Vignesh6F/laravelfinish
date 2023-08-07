<?php

if(!App::runningInConsole() && request()->getScheme()=='https') {
	$cash_image = secure_asset("images/icon/cash.png");
} else {
	$cash_image = asset("images/icon/cash.png");
}

if(!App::runningInConsole() && request()->getScheme()=='https') {
	$paypal_image = secure_asset("images/icon/paypal.png");
} else {
	$paypal_image = asset("images/icon/paypal.png");
}

if(!App::runningInConsole() && request()->getScheme()=='https') {
	$card_image = secure_asset("images/icon/card.png");
} else {
	$card_image = asset("images/icon/card.png");
}

$payment_methods = array(
	["key" => "cash", "value" => 'Cash', 'icon' => $cash_image],
	["key" => "paypal", "value" => 'PayPal', 'icon' => $paypal_image],
	["key" => "braintree", "value" => 'Card Payment', 'icon' => $card_image],
	["key" => "stripe", "value" => 'Card Payment', 'icon' => $card_image],
);

if(!defined('PAYMENT_METHODS')) {
	define('PAYMENT_METHODS', $payment_methods);	
}

$payout_methods = array(
	["key" => "bank_transfer", "value" => 'Bank Transfer'],
	["key" => "paypal", "value" => 'PayPal'],
	["key" => "stripe", "value" => 'Stripe'],
);

if(!defined('PAYOUT_METHODS')) {
	define('PAYOUT_METHODS', $payout_methods);	
}

if(!defined('CACHE_HOURS')) {
	define('CACHE_HOURS', 24);	
}
