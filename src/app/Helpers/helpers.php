<?php

/**
 * Helpers
 *
 * @package     Gofer
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */


use App\Models\Currency;
use App\Models\Rating;
use App\Models\Wallet;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use App\Models\Vehicle;
use App\Models\ManageFare;
use App\Models\Location;
use App\Models\CarType;
use App\Models\Documents;
use App\Models\DriverDocuments;
use App\Models\CompanyDocuments;
use App\Models\DriverLocation;

/**
 * Convert String to htmlable instance
 *
 * @param  string $type      Type of the image
 * @return instance of \Illuminate\Contracts\Support\Htmlable
 */
if (!function_exists('html_string')) {

	function html_string($str)
	{
		return new HtmlString($str);
	}
}

/**
 * File Get Content by using CURL
 *
 * @param  string $url  Url
 * @return string $data Response of URL
 */
if (!function_exists('file_get_contents_curl')) {

	function file_get_contents_curl($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
}

/**
 * Do CURL With POST
 *
 * @param  String $url  Url
 * @param  Array $params  Url Parameters
 * @return string $data Response of URL
 */
if (!function_exists('curlPost')) {

	function curlPost($url,$params)
	{
		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER, false); 
		curl_setopt($ch,CURLOPT_POST, count($params));
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($params));    
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'User-Agent: curl',
		]);
		$output = curl_exec($ch);

		curl_close($ch);
		return json_decode($output,true);
	}
}

/**
 * Convert Given Array To Object
 * 
 * @return Object
 */
if (!function_exists('arrayToObject')) {
	function arrayToObject($arr)
	{
		$arr = Arr::wrap($arr);
		return json_decode(json_encode($arr));
	}
}

/**
 * Convert Given Float To Nearest Half Integer
 *
 * @return Int
 */
if (!function_exists('roundHalfInteger')) {
	function roundHalfInteger($value)
	{
		return floor($value * 2) / 2;
	}
}

/**
 * Format Invoice Item
 * 
 * @param [Array] $[item]
 * @return [Array] [formated invoice item]
 */
if (!function_exists('formatInvoiceItem')) {
	function formatInvoiceItem($item)
	{
		return array(
			'key' 		=> $item['key'],
			'value' 	=> strval($item['value']),
			'bar'		=> $item['bar'] ?? 0,
			'colour'	=> $item['colour'] ?? '',
			'comment' 	=> $item['comment'] ?? '',
		);
	}
}

/**
 * Format Driver Statement Item
 * 
 * @param [Array] $[item]
 * @param [String] $[type]
 * @return [Array] [formated invoice item]
 */
if (!function_exists('formatStatementItem')) {
	function formatStatementItem($item,$type = '')
	{
		return array(
			'key' 		=> $item['key'],
			'value' 	=> strval($item['value']),
			'bar'		=> $item['bar'] ?? false,
			'colour'	=> $item['colour'] ?? '',
			'tooltip' 	=> $item['tooltip'] ?? '',
		);
	}
}

/**
 * Currency Convert
 *
 * @param int $from   Currency Code From
 * @param int $to     Currency Code To
 * @param int $price  Price Amount
 * @return int Converted amount
 */
if (!function_exists('currencyConvert')) {
	function currencyConvert($from, $to, $price = 0)
	{
		$price = floatval($price);
		if($from == $to) {
			return number_format($price, 2, '.', '');
		}

		if($price == 0) {
			return number_format(0, 2, '.', '');
		}

		$rate = Currency::whereCode($from)->first()->rate;
		$session_rate = Currency::whereCode($to)->first()->rate;

		$usd_amount = $price / $rate;
		return number_format($usd_amount * $session_rate, 2, '.', '');
	}
}

/**
 * Check if a string is a valid timezone
 *
 * @param string $timezone
 * @return bool
 */
if (!function_exists('isValidTimezone')) {
	function isValidTimezone($timezone)
	{
		return in_array($timezone, timezone_identifiers_list());
	}
}

/**
 * Get Given Rider Rating
 *
 * @param String $driver_id
 * @return String $rider_rating
 */
if (!function_exists('getRiderRating')) {
	function getRiderRating($driver_id)
	{
		$total_rating = \DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
		->where('driver_id', $driver_id)->where('rider_rating', '>', 0)->first()->rating;

		$total_rating_count = Rating::where('driver_id', $driver_id)->where('rider_rating', '>', 0)->count();
		
		$rider_rating = '0.0';
		if ($total_rating_count != 0) {
			$rider_rating = round(($total_rating / $total_rating_count), 2);
		}
		return strval($rider_rating);
	}
}

/**
 * Get Given Driver Rating
 *
 * @param String $rider_id
 * @return String $driver_rating
 */
if (!function_exists('getDriverRating')) {
	function getDriverRating($rider_id)
	{
		$total_rating = \DB::table('rating')->select(DB::raw('sum(driver_rating) as rating'))
		->where('user_id', $rider_id)->where('driver_rating', '>', 0)->first()->rating;

		$total_rating_count = Rating::where('user_id', $rider_id)->where('driver_rating', '>', 0)->count();
		
		$driver_rating = '0.0';
		if ($total_rating_count != 0) {
			$driver_rating = round(($total_rating / $total_rating_count), 2);
		}
		return strval($driver_rating);
	}
}

/**
 * Get User Wallet Amount
 *
 * @param String $user_id
 * @return String $wallet_amount
 */
if (!function_exists('getUserWalletAmount')) {
	function getUserWalletAmount($user_id)
	{
		$wallet = Wallet::whereUserId($user_id)->first();
		$wallet_amount = $wallet->original_amount ?? "0";

		return strval($wallet_amount);
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key The searched value
 * 
 * @return if key found, return particular value of key.
 */
if (!function_exists('site_settings')) {
	
	function site_settings($key) {
		$site_settings = resolve('site_settings');
		$site_setting = $site_settings->where('name',$key)->first();

		return $site_setting->value ?? '';
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key The searched value
 * 
 * @return if key found, return particular value of key.
 */
if (!function_exists('email_settings')) {
	
	function email_settings($key) {
		$email_settings = resolve('email_settings');
		$email_setting = $email_settings->where('name',$key)->first();

		return $email_setting->value ?? '';
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key The searched value
 * 
 * @return if key found, return particular value of key.
 */
if (!function_exists('payment_gateway')) {
	
	function payment_gateway($key, $site) {
		$payment_gateway = resolve('payment_gateway');
		$gateway = $payment_gateway->where('name',$key)->where('site',$site)->first();

		return $gateway->value ?? '';
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key The searched value
 * 
 * @return if key found, return particular value of key.
 */
if (!function_exists('api_credentials')) {
	
	function api_credentials($key, $site) {
		$api_credentials = resolve('api_credentials');
		$credentials = $api_credentials->where('name',$key)->where('site',$site)->first();

		return $credentials->value ?? '';
	}
}

/**
 * Checks if a value exists in an array in a case-insensitive manner
 *
 * @param string $key The searched value
 * 
 * @return if key found, return particular value of key.
 */
if (!function_exists('fees')) {
	
	function fees($key) {
		$fees = resolve('fees');
		$fee = $fees->where('name',$key)->first();

		return $fee->value ?? '';
	}
}

/**
 * Set Flash Message function
 *
 * @param  string $class     Type of the class ['danger','success','warning']
 * @param  string $message   message to be displayed
 */
if (!function_exists('flashMessage')) {

	function flashMessage($class, $message)
	{
		Session::flash('alert-class', 'alert-'.$class);
		Session::flash('message', $message);
	}
}

/**
 * Get Admin default Currency Symbole
 *
 * @return currency symbol
 */
if (!function_exists('currency_symbol')) {
	function currency_symbol()
	{
		$default_currency = view()->shared('default_currency');
		if (LOGIN_USER_TYPE == 'company' && session('currency') != null) {
			$default_currency = Currency::whereCode(session('currency'))->first();
		}
		return html_entity_decode($default_currency->symbol);
	}
}

/**
 * Get a Facebook Login URL
 *
 * @return URL from Facebook API
 */
if (!function_exists('getAppleLoginUrl')) {
	function getAppleLoginUrl()
	{
		$params = [
			'response_type' 	=> 'code',
			'response_mode' 	=> 'form_post',
			'client_id' 		=> api_credentials('service_id','Apple'),
			'redirect_uri' 		=> url('apple_callback'),
			'state' 			=> bin2hex(random_bytes(5)),
			'scope' 			=> 'name email',
		];
		$authorize_url = 'https://appleid.apple.com/auth/authorize?'.http_build_query($params);

		return $authorize_url;
	}
}

/**
 * Generate Apple Client Secret
 *
 * @return String $token
 */
if (!function_exists('getAppleClientSecret')) {
	function getAppleClientSecret()
	{
		$key_file = base_path().api_credentials("key_file","Apple");

		$algorithmManager = new AlgorithmManager([new ES256()]);
		$jwsBuilder = new JWSBuilder($algorithmManager);
		$jws = $jwsBuilder
		->create()
		->withPayload(json_encode([
			'iat' => time(),
			'exp' => time() + 86400*180,
			'iss' => api_credentials('team_id','Apple'),
			'aud' => 'https://appleid.apple.com',
			'sub' => api_credentials('service_id','Apple'),
		]))
		->addSignature(JWKFactory::createFromKeyFile($key_file), [
			'alg' => 'ES256',
			'kid' => api_credentials('key_id','Apple')
		])
		->build();

		$serializer = new CompactSerializer();
		$token = $serializer->serialize($jws, 0);
		
		return $token;
	}
}

/**
 * Get Currency Code From IP address
 *
 * @param  $ip_address [current IP]
 * @return String $currency_code
 */
if (!function_exists('get_currency_from_ip')) {
	function get_currency_from_ip($ip_address = '')
	{
		$ip_address = $ip_address ?: request()->getClientIp();
		$default_currency = Currency::active()->defaultCurrency()->first();
		$currency_code    = $default_currency->code;
		if(session('currency_code')) {
			$currency_code = session('currency_code');
		}
		else if($ip_address != '') {
			try {
				$result = unserialize(file_get_contents_curl('http://www.geoplugin.net/php.gp?ip='.$ip_address));
			}
			catch(\Exception $e) {
                // 
			}
            // Default Currency code for footer
			if(isset($result['geoplugin_currencyCode'])) {
				$check_currency = Currency::whereCode($result['geoplugin_currencyCode'])->count();
				if($check_currency) {
					$currency_code =  $result['geoplugin_currencyCode'];
				}
			}
			session(['currency_code' => $currency_code]);
		}
		return $currency_code;
	}
}

/**
 * Get Currency Code From IP address
 *
 * @param  $date_obj [Carbon date object]
 * @param  $format [Return Date Format]
 * @return String $currency_code
 */
if (!function_exists('getWeekStartEnd')) {
	function getWeekStartEnd($date_obj,$format = 'd M')
	{
		$result['start'] = $date_obj->startOfWeek()->format($format);
		$result['end'] = $date_obj->endOfWeek()->format($format);

		return $result;
	}
}

/**
 * Check Cash trip or not
 *
 * @return Boolean true or false
 */
if (!function_exists('checkIsCashTrip')) {
	function checkIsCashTrip($payment_mode)
	{
		return in_array($payment_mode,['Cash & Wallet','Cash']);
	}
}

/**
 * Check Current Environment
 *
 * @return Boolean true or false
 */
if (!function_exists('isLiveEnv')) {
	function isLiveEnv($environments = [])
	{
		if(count($environments) > 0) {
			array_push($environments, 'live');
			return in_array(env('APP_ENV'),$environments);
		}
		return env('APP_ENV') == 'live';
	}
}

/**
 * Get driver request seconds
 *
 * @return Boolean true or false
 */
if (!function_exists('getDriverSec')) {
	function getDriverSec()
	{
		return site_settings('driver_request_seconds');
	}
}

/**
 * Check Can display credentials or not
 *
 * @return Boolean true or false
 */
if (!function_exists('canDisplayCredentials')) {
	function canDisplayCredentials()
	{
		return env('SHOW_CREDENTIALS','false') == 'true';
	}
}

/**
 * Convert underscore_strings to camelCase (medial capitals).
 *
 * @param {string} $str
 *
 * @return {string}
 */
if (!function_exists('snakeToCamel')) {
	
	function snakeToCamel($str,$removeSpace = false) {
		// Remove underscores, capitalize words, squash.
		$camelCaseStr =  ucwords(str_replace('_', ' ', $str));
		if($removeSpace) {
			$camelCaseStr =  str_replace(' ', '', $camelCaseStr);
		}
		return $camelCaseStr;
	}
}

/**
 * get protected String or normal based on env
 *
 * @param {string} $str
 *
 * @return {string}
 */
if (!function_exists('protectedString')) {
	
	function protectedString($str) {
		if(isLiveEnv()) {
			return substr($str, 0, 1) . '****' . substr($str,  -4);
		}
		return $str;
	}
}

if ( ! function_exists('updateEnvConfig'))
{
	function updateEnvConfig($envKey, $envValue)
	{
		$envFile = app()->environmentFilePath();
		$str = file_get_contents($envFile);

		try {
			$str .= "\n";
			$keyPosition = strpos($str, "{$envKey}=");
			$endOfLinePosition = strpos($str, "\n", $keyPosition);
			$oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

			if(!$keyPosition || !$endOfLinePosition || !$oldLine) {
				$str .= "{$envKey}={$envValue}\n";
			} else {

				if($envKey=='IP_ADDRESS') {
					if($envValue=='delete') {
						$envValue = '';
					} else {

						$oldValue = substr(strrchr($oldLine, '='), 1);

						if(!$oldValue) {
							$oldValues = array();
						} else {
							$oldValues = explode(',', $oldValue);
						}

						$envValue_delete = explode('_', $envValue);

						if(count($envValue_delete)>1 && $envValue_delete[1]=='delete') {
							$oldKey = array_search($envValue_delete[0], $oldValues);
							if($oldKey!==false) {
								unset($oldValues[$oldKey]);
							}
							if(count($oldValues)) {
								$envValue = implode(',', $oldValues);
							} else {
								$envValue = '';
							}
						} else {

							$envValue = filter_var($envValue, FILTER_VALIDATE_IP);

							if($envValue && !in_array($envValue, $oldValues)) {
								$oldValues[count($oldValues)] = $envValue;
								if(count($oldValues)>1) {
									$envValue = implode(',', $oldValues);
								}
							} else {
								$envValue = $oldValue;
							}	
						}
					}
                }
                // logger($envKey.' - '.$envValue);
				$str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
			}
			$str = substr($str, 0, -1);
			file_put_contents($envFile, $str);
		} catch(\Exception $e) {
			// \Log::error($e->getMessage());
		}
	}
}

/**
 * Check Given Request is from API or not
 *
 * @return Boolean
 */
if (!function_exists('isApiRequest')) {

	function isApiRequest()
	{
		return request()->segment(1) == 'api';
	}
}

/**
 * Check Given Request is from API or not
 *
 * @return Boolean
 */
if (!function_exists('camelCaseToString')) {

	function camelCaseToString($string)
	{
		$pieces = preg_split('/(?=[A-Z])/',$string);
		$word = implode(" ", $pieces);
		return ucwords($word);
	}
}

/**
 * Check Given Request is from API or not
 *
 * @return Boolean
 */
if (!function_exists('getPayoutMethods')) {

	function getPayoutMethods($company_id = 1)
	{
		if($company_id != 1) {
			$payout_methods = ['bank_transfer'];
		}
		else {
			$payout_methods = payment_gateway('payout_methods','Common');
			$payout_methods = explode(',',$payout_methods);
		}
		return $payout_methods;
	}
}

/**
 * Check Given Request is from API or not
 *
 * @return Boolean
 */
if (!function_exists('isPayoutEnabled')) {

	function isPayoutEnabled($method)
	{
		$payout_methods = getPayoutMethods();
		return in_array($method, $payout_methods);
	}
}

if (!function_exists('isAdmin')) {
	function isAdmin()
	{
		return request()->segment(1) == 'admin';
	}
}

/**
 * numberFormat Function
 *
 * @param {Float} $value
 *
 * @return {string}
 */
if (!function_exists('numberFormat')) {
	
	function numberFormat($value,$precision = 2) {
		return number_format($value,$precision,'.','');
	}
}

/**
 * Calculates the distance between two points, given their 
 * latitude and longitude, and returns an array of values 
 * of the most common distance units
 *
 * @param  {coord} $lat1 Latitude of the first point
 * @param  {coord} $lon1 Longitude of the first point
 * @param  {coord} $lat2 Latitude of the second point
 * @param  {coord} $lon2 Longitude of the second point
 * @return {string} value in given distance unit
 */
if (!function_exists('getDistanceBetweenPoints')) {
	function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2,$unit = "K")
	{
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "M") {
			return numberFormat($miles);
		}
		if ($unit == "K") {
			return numberFormat($miles * 1.609344); 
		}
		if ($unit == "N") {
			return numberFormat($miles * 0.8684);
		}
	}
}

if (!function_exists('LogDistanceMatrix')) {
	function LogDistanceMatrix($functionName,$notes = '')
	{
		$destinationPath = storage_path('logs/distance.json');
		try {
			$jsonString = file_get_contents($destinationPath);    		
		}
		catch (\Exception $e) {
			$jsonString = "";	
		}

		$prev_log = json_decode($jsonString, true);

		$dateString = \Carbon\carbon::now()->setTimezone('Asia/Kolkata');

		$prev_log[] = array(
			'url' 		=> request()->fullUrl(),
			'function' 	=> $functionName,
			'date'		=> $dateString->format('Y-m-d'),
			'time' 		=> $dateString->format('H:i:s'),
			'notes'		=> $notes,
		);
		$log_data = json_encode($prev_log ,JSON_PRETTY_PRINT);
		\File::put($destinationPath,$log_data);
	}

	if (!function_exists('getVehicleLocation')) {
		function getVehicleLocation($vehicle) {
			$location = ManageFare::where('vehicle_id',$vehicle)->get();
			$result=[];
			foreach ($location as $key => $value) {
				$locationValue = Location::where('id',$value->location_id)->select('name')->first();
	    		// array_push($result[$key], $locationValue->name);
				$result[$key]['location_name']= $locationValue->name; 
			}
			$location_Value = array_column($result, 'location_name');
			return implode(', ',$location_Value);
		}
	}

	if (!function_exists('getPoolValue')) {
		function getPoolValue($poolValue) {
			if($poolValue == 'No' )
			{
				return False;
			}
			else if ($poolValue == 'Yes')
			{
				return True;
			}
		}
	}

	if (!function_exists('getVehicleDetails')) {
		function getVehicleDetails($vehicle) {
			if($poolValue == 'No' )
			{
				return False;
			}
			else if ($poolValue == 'Yes')
			{
				return True;
			}
		}
	}

	if (!function_exists('getVehicleType')) {
		function getVehicleType($vehicle) {
			$cartype = CarType::select('car_name as type','id','is_pool')->whereIn('id',$vehicle)->active()->get();
			foreach ($cartype as $key => $value) {
				$vehicle_type[$key]['id'] = $value->id;
				$vehicle_type[$key]['type'] = $value->type;
				//$vehicle_type[$key]['isPooled'] = getPoolValue($value->is_pool);
				$vehicle_type[$key]['location'] = getVehicleLocation($value->id);
			}
			return $vehicle_type;
		}
	}

	if(!function_exists('CheckDocument')){
		function CheckDocument($document_for,$country_code){
			$data = Documents::Active()->DocumentCheck($document_for,$country_code)->get();
			if($data->count() == 0){
				$data = Documents::Active()->DocumentCheck($document_for,'all')->get();
			}
			foreach ($data as $key => $value) {
				$data[$key]['status'] = 0;
				if($value->expire_on_date=='Yes') {
					$data[$key]['expiry_required'] = 1;
				} else {
					$data[$key]['expiry_required'] = 0;
				}
			}
			return $data;
		}
	}

	if(!function_exists('UserStatusUpdate')){
		function UserStatusUpdate($user){

			$vehicle_documents = $user->driver_documents('Vehicle')->count();
			$driver_documents = $user->driver_documents('Driver')->count();

			if(!$user->vehicles->count()){
				$user_status = 'Car_details';
			} elseif(!$vehicle_documents) {
				$user_status = 'Car_details';
			} elseif(!$driver_documents) {
				$user_status = 'Document_details';
			} else {
				$user_status = isLiveEnv() ? 'Active' : 'Pending';
			}
			return $user_status;
		}
	}	

	if(!function_exists('UserDocuments')) {
		function UserDocuments($type,$user,$vehicle_id=0) {

			if($type=='Company') {
				$docs = CompanyDocuments::whereHas('documents', function($q) {
					$q->active();
				})->where('company_id',$user->id)->get();
			} else {
				$docs = DriverDocuments::whereHas('documents', function($q) {
					$q->active();
				})->where('type',$type)->where('user_id',$user->id)->where('vehicle_id',$vehicle_id)->get();
			}

			$docArr = array();
			if($docs->count() > 0){
				foreach($docs as $key => $value) {
					$docArr[$key]['id'] = $value->document_id;
					$docArr[$key]['name'] = $value->document_name;
					$docArr[$key]['document'] = $value->document;
					$docArr[$key]['expired_date'] = $value->expired_date;

					$document = Documents::find($value->document_id);
					if($document->expire_on_date=='Yes') {
						$docArr[$key]['expiry_required'] = 1;
					} else {
						$docArr[$key]['expiry_required'] = 0;
					}

				//	$docArr[$key]['country_code'] = $document->country_code;
					$docArr[$key]['status'] = $value->status;	
					if(!isApiRequest()){
						$docArr[$key]['document_name'] = $value->document_name;
						$docArr[$key]['doc_name'] = $value->doc_name;
					}	
				}
				$upload_document_count = $docs->count();
				$documents = Documents::Active()->DocumentCheck($type,$user->country_code)->get();
				if($documents->count() == 0){
					$documents = Documents::Active()->DocumentCheck($type,'all')->get();
				}
				if($upload_document_count != $documents->count()){
					$remain_doc = array_diff($documents->pluck('id')->toArray(),$docs->pluck('document_id')->toArray());
					$RemainDoc = Documents::whereIn('id', $remain_doc)->get();
					$RemainDocArr = array();
					foreach($RemainDoc as $key => $value) {
						$RemainDocArr[$key]['id'] = $value->id;
						$RemainDocArr[$key]['name'] = $value->document_name;
						$RemainDocArr[$key]['document'] = '';
						$RemainDocArr[$key]['expired_date'] = '';
						if($value->expire_on_date=='Yes') {
							$RemainDocArr[$key]['expiry_required'] = 1;
						} else {
							$RemainDocArr[$key]['expiry_required'] = 0;
						}
						$RemainDocArr[$key]['country_code'] = $value->country_code;
						$RemainDocArr[$key]['status'] = 0;
						if(!isApiRequest()){
							$RemainDocArr[$key]['document_name'] = $value->document_name;
							$RemainDocArr[$key]['doc_name'] = $value->doc_name;
						}
					}
					$docArr = array_merge($docArr,$RemainDocArr);

					if(isApiRequest()){
						$inactive_doc = array_diff($docs->pluck('document_id')->toArray(),$documents->pluck('id')->toArray());
						if(count($inactive_doc)) {
							foreach($inactive_doc as $inactive_id) {
								$inactive_key = array_search($inactive_id, array_column($docArr, 'id'));
								unset($docArr[$inactive_key]);
							}
							$docArr = array_values($docArr);
						}
					}
				}
			}else{
				$data = Documents::Active()->DocumentCheck($type,$user->country_code)->get();
				if($data->count() == 0){
					$data = Documents::Active()->DocumentCheck($type,'all')->get();
				}
				foreach($data as $key => $value) {
					$docArr[$key]['id'] = $value->id;
					$docArr[$key]['name'] = $value->document_name;
					$docArr[$key]['document'] = '';
					$docArr[$key]['expired_date'] = '';
					if($value->expire_on_date=='Yes') {
						$docArr[$key]['expiry_required'] = 1;
					} else {
						$docArr[$key]['expiry_required'] = 0;
					}
					$docArr[$key]['country_code'] = $value->country_code;
					$docArr[$key]['status'] = 0;
					if(!isApiRequest()){
						$docArr[$key]['document_name'] = $value->document_name;
						$docArr[$key]['doc_name'] = $value->doc_name;
					}					
				}
			}
			return isApiRequest() ? $docArr : json_encode($docArr);		
		}
	}

}

if(!function_exists('checkDefault')) {
	/**
	 * Check pre default vehicle is in ride or not
	 *
	 * @param  {driver_id} $driver_id id of the driver
	 * @param  {vehicle_id} $vehicle_id id of the vehicle
	 * @param  {default} $default set default(1) or non-default(0)
	 * @return {numeric} value in given numeric
	 */
	function checkDefault($driver_id, $vehicle_id, $default) {

		$vehicle = Vehicle::find($vehicle_id);
		$driver_location = DriverLocation::where('user_id', $driver_id)->first();

		if($default=='1') {
        	// update default to the same vehicle not restricted
			if($vehicle && $vehicle->default_type=='1') {
				return 0;
			} else {
            	// update default to the another vehicle, check driver status in trip or not if trip means avoid to update
				if($driver_location && ($driver_location->status=='Pool Trip' || $driver_location->status=='Trip')) {
					return 1;
				} else {
					return 0;
				}
			}
		} else {
        	// update non-default to the default vehicle, check driver status in trip or not if trip means avoid to update
			if($driver_location && ($driver_location->status=='Pool Trip' || $driver_location->status=='Trip'))
				if($vehicle && $vehicle->default_type=='1')
					return 2;
			}
			return 0;
		}
	}

/**
 * Check Current Environment
 *
 * @return Boolean true or false
 */
if (!function_exists('CheckGetInTuchpopup')) {
	function CheckGetInTuchpopup($environments = [])
	{
		return strtolower(env('GET_IN_TUCH_POPUP')) == 'yes';
	}
}
