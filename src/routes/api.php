<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// cron request for schedule ride
Route::match(['post','get'],'web_payment', 'PaymentController@payment')->name('paypal.view');
Route::post('payment/success', 'PaymentController@success')->name('payment.success');
Route::get('payment/cancel', 'PaymentController@cancel')->name('payment.cancel');
Route::get('cron_request_car', 'CronController@requestCars');
Route::get('cron_offline', 'CronController@updateOfflineUsers');
Route::get('currency_cron', 'CronController@updateCurrency');
Route::get('update_referral_cron', 'CronController@updateReferralStatus');
Route::match(['get', 'post'], 'paypal_payout', 'CronController@updatePaypalPayouts');

Route::get('check_version', 'RiderController@check_version');

//TokenAuthController
Route::get('register', 'TokenAuthController@register');
Route::get('socialsignup', 'TokenAuthController@socialsignup');
Route::match(array('GET', 'POST'),'apple_callback', 'TokenAuthController@apple_callback');

Route::get('login', 'TokenAuthController@login');
Route::get('otp_verification', 'TokenAuthController@otp_verification');
Route::get('numbervalidation', 'TokenAuthController@numbervalidation');
Route::get('emailvalidation', 'TokenAuthController@emailvalidation');
Route::get('forgotpassword', 'TokenAuthController@forgotpassword');

Route::get('language_list', 'TokenAuthController@language_list');
Route::get('currency_list', 'TokenAuthController@currency_list');

// With Login Routes
Route::group(['middleware' => 'jwt.verify'], function () {
	
	Route::match(array('GET', 'POST'),'common_data','HomeController@commonData');
	Route::post('get_payment_list','HomeController@getPaymentList');

	Route::get('logout', 'TokenAuthController@logout');
	
	Route::get('language','TokenAuthController@language');
	Route::get('update_device', 'TokenAuthController@updateDevice');
	Route::get('updatelocation', 'DriverController@updateLocation');
	Route::get('check_status', 'DriverController@checkStatus');

	// Common API for Both Driver & Rider
	Route::post('update_document','DriverController@update_document');
	Route::get('country_list', 'DriverController@country_list');
	Route::get('toll_reasons', 'TripController@toll_reasons');
	Route::get('cancel_reasons', 'TripController@cancel_reasons');
	Route::get('get_referral_details', 'ReferralsController@get_referral_details');
	Route::get('get_trip_details', 'TripController@get_trip_details');
	Route::get('send_message', 'TripController@send_message');

	// Rider Only APIs
	Route::get('get_nearest_vehicles', 'RiderController@get_nearest_vehicles');
	Route::get('search_cars', 'RiderController@searchCars');
	Route::post('request_cars', 'RiderController@requestCars');
	Route::get('track_driver', 'RiderController@track_driver');
	Route::get('updateriderlocation', 'RiderController@updateriderlocation');
	Route::get('promo_details','RiderController@promo_details');
	Route::get('sos','RiderController@sos');
	Route::get('sosalert','RiderController@sosalert');
	Route::post('save_schedule_ride', 'RiderController@save_schedule_ride');
	Route::get('schedule_ride_cancel', 'RiderController@schedule_ride_cancel');
	Route::post('add_wallet', 'AppPaymentController@appPaymentSuccess');
	Route::post('after_payment', 'AppPaymentController@appPaymentSuccess');
	Route::get('get_past_trips','TripController@get_past_trips');
	Route::get('get_upcoming_trips','TripController@get_upcoming_trips');
	Route::match(['GET', 'POST'],'currency_conversion', 'TokenAuthController@currency_conversion');

	// Driver Only APIs
	Route::get('get_pending_trips','TripController@get_pending_trips');
	Route::get('get_completed_trips','TripController@get_completed_trips');
	Route::get('arive_now', 'TripController@arriveNow');
	Route::get('begin_trip', 'TripController@beginTrip');
	Route::get('accept_request', 'TripController@acceptTrip');
	Route::get('cash_collected', 'DriverController@cash_collected');
	
	Route::match(array('GET', 'POST'), 'map_upload','TripController@map_upload');
	Route::match(array('GET', 'POST'), 'end_trip','TripController@end_trip');
	Route::match(array('GET', 'POST'), 'upload_profile_image','ProfileController@upload_profile_image');
	
	Route::get('heat_map', 'MapController@heat_map');
	Route::post('pay_to_admin', 'AppPaymentController@appPaymentSuccess');

	// TripController
	Route::get('cancel_trip', 'TripController@cancel_trip');
	
	// Earning Controller
	Route::get('earning_chart', 'EarningController@earning_chart');
	Route::get('add_promo_code', 'EarningController@add_promo_code');

	// Rating Controller
	Route::get('driver_rating', 'RatingController@driver_rating');
	Route::get('rider_feedback', 'RatingController@rider_feedback');
	Route::get('trip_rating', 'RatingController@trip_rating');
	Route::get('get_invoice', 'RatingController@getinvoice');

	//profile Controller
	Route::get('get_rider_profile', 'ProfileController@get_rider_profile');
	Route::get('update_rider_profile', 'ProfileController@update_rider_profile');
	Route::get('get_driver_profile', 'ProfileController@get_driver_profile');
	Route::get('update_driver_profile', 'ProfileController@update_driver_profile');
	Route::get('update_vehicle_details', 'ProfileController@updateVehicleDetails');
	Route::get('vehicle_details', 'ProfileController@vehicleDetails');
	Route::get('update_rider_location', 'ProfileController@update_rider_location');
	Route::get('update_user_currency', 'ProfileController@update_user_currency');
	Route::get('get_caller_detail', 'ProfileController@get_caller_detail');
	Route::get('vehicle_descriptions', 'ProfileController@vehicleDescriptions');

	Route::get('add_card_details', 'AppPaymentController@add_card_details');
	Route::get('get_card_details', 'AppPaymentController@get_card_details');

	// Manage Driver Payout Routes
	Route::get('stripe_supported_country_list', 'PayoutDetailController@stripeSupportedCountryList');
	Route::post('update_payout_preference','PayoutDetailController@updatePayoutPreference');
	Route::get('get_payout_list','PayoutDetailController@getPayoutPreference');
	Route::get('earning_list', 'PayoutDetailController@earningList');
	Route::get('weekly_trip', 'PayoutDetailController@weeklyTrip');
	Route::get('weekly_statement', 'PayoutDetailController@weeklyStatement');
	Route::get('daily_statement', 'PayoutDetailController@dailyStatement');
	Route::post('update_vehicle', 'DriverController@updateVehicle');
	Route::post('delete_vehicle','DriverController@deleteVehicle');
	Route::get('update_default_vehicle','DriverController@updateDefaultVehicle');
});
