<?php

/**
 * Google API Service
 *
 * @package     Gofer
 * @subpackage  Services
 * @category    Google API
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services;

use Cache;
use Carbon\Carbon;

class GoogleAPIService
{
	/**
	 * Constructor
	 * 
	 */
	public function __construct()
	{
		$this->base_url = "https://maps.googleapis.com/maps/api";
		$this->map_key = MAP_KEY;
		$this->map_server_key = MAP_SERVER_KEY;
	}

	/**
	 * Get Static Map
	 *
	 * @param Float $lat1
	 * @param Float $long1
	 * @param Float $lat2
	 * @param Float $long2
	 * @param String $trip_path
	 * @return array $distance_data
	 */
	public function GetStaticMap($lat1, $long1, $lat2, $long2, $trip_path)
	{
		return $this->base_url."/staticmap?size=640x480&zoom=14&path=color:0x000000ff%7Cweight:4%7Cenc:".$trip_path."&markers=size:mid|icon:". url('images/pickup.png')."|".$lat1.",".$long1."&markers=size:mid|icon:".url('images/drop.png')."|".$lat2.",".$long2."&sensor=false&key=".$this->map_key;
	}

	/**
	 * Get Driving Distance
	 *
	 * @param Float $lat1
	 * @param Float $lat2
	 * @param Float $long1
	 * @param Float $long2
	 * @return array $distance_data
	 */
	public function GetDrivingDistance($lat1, $lat2, $long1, $long2)
	{
		$url = $this->base_url."/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL&key=" . MAP_SERVER_KEY;

		$geocode = file_get_contents_curl($url);
		if(!$geocode) {
			return array('status' => "fail", 'msg' => trans('messages.api.something_went_wrong'), 'time' => '0', 'distance' => "0");
		}
		$response_a = json_decode($geocode);
		if ($response_a->status == "REQUEST_DENIED" || $response_a->status == "OVER_QUERY_LIMIT") {
			return array('status' => "fail", 'msg' => $response_a->error_message, 'time' => '0', 'distance' => "0");
		}
		elseif (isset($response_a->rows[0]->elements[0]->status) && $response_a->rows[0]->elements[0]->status == 'ZERO_RESULTS') {
			return array('status' => "fail", 'msg' => 'No Route Found', 'time' => '0', 'distance' => "0");
		}
		elseif ($response_a->status == "OK" ) {
			$dist_find = $response_a->rows[0]->elements[0]->distance->value;
			$time_find = $response_a->rows[0]->elements[0]->duration->value;

			$dist = @$dist_find != '' ? $dist_find : '';
			$time = @$time_find != '' ? $time_find : '';
			$return_data = array('status' => 'success', 'distance' => $dist, 'time' => (int) $time);

			return $return_data;
		}
		else {
			return array('status' => 'success', 'distance' => "1", 'time' => "1");
		}
	}

	/**
	 * Get Polyline
	 *
	 * @param Float $lat1
	 * @param Float $lat2
	 * @param Float $long1
	 * @param Float $long2
	 * @return String $polyline
	 */
	public function GetPolyline($lat1, $lat2, $long1, $long2)
	{
		$cache_key = 'polyline_'.$lat1.'_'.$lat2.'_'.$long1.'_'.$long2;
		$cacheExpireAt = Carbon::now()->addHours(CACHE_HOURS);
		
		if(Cache::has($cache_key)) {
			return Cache::get($cache_key);
		}

		$url = $this->base_url."/directions/json?origin=" . $lat1 . "," . $long1 . "&destination=" . $lat2 . "," . $long2 . "&mode=driving&units=metric&sensor=true&&language=pl-PL&key=" . MAP_SERVER_KEY;

		$geocode = @file_get_contents($url);
		$response_a = json_decode($geocode);
		$polyline_find = @$response_a->routes[0]->overview_polyline->points;

		$polyline = @$polyline_find != '' ? $polyline_find : '';

		Cache::put($cache_key, $polyline , $cacheExpireAt);

		return $polyline;
	}

	/**
	 * Get Country
	 *
	 * @param Float $lat1
	 * @param Float $long1
	 * @return String $country
	 */
	public function GetCountry($lat1, $long1)
	{
		$cache_key = 'location_'.numberFormat($lat1,3).'_'.numberFormat($long1,3);
		$cacheExpireAt = Carbon::now()->addHours(CACHE_HOURS);
		
		if(Cache::has($cache_key)) {
			return Cache::get($cache_key);
		}

		$pickup_geocode = file_get_contents($this->base_url.'/geocode/json?latlng=' . $lat1 . ',' . $long1 . '&key=' . MAP_SERVER_KEY);

		$pickup_check = json_decode($pickup_geocode);

		$country = '';

		if (@$pickup_check->results) {
			foreach ($pickup_check->results as $result) {
				foreach ($result->address_components as $addressPart) {

					if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
						$country = $addressPart->long_name;

					}
				}
			}
		}

		Cache::put($cache_key, $country, $cacheExpireAt);

		return $country;
	}

	/**
	 * Get Location data with Geocode
	 *
	 * @param Float $lat1
	 * @param Float $long1
	 * @return String $address
	 */
	public function GetLocation($lat1, $long1)
	{
		$cache_key = 'location_'.$lat1.'_'.$long1;
		$cacheExpireAt = Carbon::now()->addHours(CACHE_HOURS);
		
		if(Cache::has($cache_key)) {
			return Cache::get($cache_key);
		}

		$drop_geocode = file_get_contents_curl($this->base_url.'/geocode/json?latlng=' . $lat1 . ',' . $long1 . '&key=' . MAP_SERVER_KEY);

		$drop_check = json_decode($drop_geocode);
		$location = '';
		if (@$drop_check->results) {
			$location = @$drop_check->results[0]->formatted_address;
		}

		Cache::put($cache_key, $location, $cacheExpireAt);
		return $location;
	}

	/**
	 * Get Timezone
	 *
	 * @param Float $lat1
	 * @param Float $long1
	 * @return String $timezone
	 */
    public function getTimeZone($lat1, $long1)
    {
    	$cache_key = 'timezone_'.numberFormat($lat1,5).'_'.numberFormat($long1,5);
		$cacheExpireAt = Carbon::now()->addHours(CACHE_HOURS);
		
		if(Cache::has($cache_key)) {
			return Cache::get($cache_key);
		}

        $timestamp = strtotime(date('Y-m-d H:i:s'));

        $geo_timezone = file_get_contents_curl($this->base_url.'/timezone/json?location=' . @$lat1 . ',' . @$long1 . '&timestamp=' . $timestamp . '&key=' . MAP_SERVER_KEY);

        $timezone = json_decode($geo_timezone);

        if ($timezone->status == 'OK') {
        	Cache::put($cache_key, $timezone->timeZoneId , $cacheExpireAt);
            return $timezone->timeZoneId;
        }
        return 'Asia/Kolkata';
    }
}