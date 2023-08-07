<?php

/**
 * ScheduleRide Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    ScheduleRide
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRide extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schedule_ride';

    public $timestamps = false;
    
    protected $appends = ['schedule_display_date','icon','car_name','default_icon','schedule_display_time','currency_symbol','rider_name'];

    public static $withoutAppends = false;

    protected function getArrayableAppends() {
        if(self::$withoutAppends){
            $this->convert_fields = [];
            return [];
        }
        return parent::getArrayableAppends();
    }

    // Joins the users Table for rider
    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    // Joins the users Table for driver
    public function driver()
    {
        return $this->hasOne('App\Models\User','id','driver_id');
    }

    // Joins the schedule cancel Table
    public function schedule_cancel()
    {
        return $this->hasOne('App\Models\ScheduleCancel','schedule_ride_id','id');
    }

    // Joins the request Table
    public function request()
    {
        return $this->hasOne('App\Models\Request','schedule_id','id')->where('status', 'Accepted');
    }

    // Get Schedule display date and time value
    public function getScheduleDisplayDateAttribute()
    {
        return date('D M j g:i a',strtotime($this->attributes['schedule_date'].''.$this->attributes['schedule_time']));
    }

    // Get Schedule display time value
    public function getScheduleDisplayTimeAttribute()
    {
        return date('g:i a',strtotime($this->attributes['schedule_date'].''.$this->attributes['schedule_time']));
    }

    // Get Icon Attribute
    public function getIconAttribute()
    {
        $caricon =  CarType::find($this->attributes['car_id']);
        return optional($caricon)->icon;
    }
   
    // Get Default Icon Attribute
    public function getDefaultIconAttribute()
    {
        return url('images/user.jpeg');
    }

    // Get the Name of the Car
    public function getCarNameAttribute()
    {
        $caricon =  CarType::find($this->attributes['car_id']);
        return $caricon->car_name;
    }

    // Get the Base fare of the car
    public static function getFareEstimation($schedule_ride)
    {
        $estimate = ManageFare::where('vehicle_id',$schedule_ride->car_id)->where('location_id',$schedule_ride->location_id)->first();

        $request_helper = resolve('App\Http\Helper\RequestHelper');
        LogDistanceMatrix("Schedule Ride","fare estimate");
        $get_fare_estimation = $request_helper->GetDrivingDistance($schedule_ride->pickup_latitude, $schedule_ride->drop_latitude, $schedule_ride->pickup_longitude, $schedule_ride->drop_longitude);

        $fare_estimation = 0;

        if ($get_fare_estimation['status'] == "success") {
            if ($get_fare_estimation['distance'] == '') {
                $get_fare_estimation['distance'] = 0;
            }

            $minutes = round(floor(round($get_fare_estimation['time'] / 60)));
            $km = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));

            $base_fare = round($estimate->base_fare + $estimate->per_km * $km);

            $fare_estimation = number_format(($base_fare + round($estimate->per_min * $minutes)), 2, '.', '');
        }

        return $fare_estimation;
    }

    // Get the Currency Symbol for Base fare of the car
    public function getCurrencySymbolAttribute()
    {
        if(isset($this->attributes['car_id']) && isset($this->attributes['location_id'])) {
            $car_type = ManageFare::where('vehicle_id',$this->attributes['car_id'])->where('location_id',$this->attributes['location_id'])->first();

            $currency_symbol = "$";
            if(!is_null($car_type)) {
                $code =  $car_type->currency_code;
                $currency_symbol = Currency::where('code',$code)->first()->symbol;
            }

            return $currency_symbol;
        }
        
    }

    public function getRiderThumbImageAttribute()
    {
        $profile_picture=ProfilePicture::find($this->attributes['user_id']);
        return isset($profile_picture)?$profile_picture->src: url('images/user.jpeg');
    }

    // get Rider name
    public function getRiderNameAttribute()
    {
        $user_details = User::find($this->attributes['user_id']);
        return optional($user_details)->first_name;
    }

    // get Driver name
    public function getDriverNameAttribute()
    {
        if($this->attributes['driver_id'] == 0) {
            return '';
        }
        $user_details = User::find($this->attributes['driver_id']);
        return optional($user_details)->first_name;
    }

    public static function getTripLists($user, $paginate_limit, $status) {
        $trip_lists = self::select(
            'schedule_ride.status',
            'schedule_ride.id as trip_id',
            'schedule_ride.pickup_location as pickup',
            'schedule_ride.drop_location as drop',
            'car_type.car_name as car_type',
            'currency.symbol as currency_symbol',
            'schedule_ride.fare_estimation as total_fare',
            'schedule_ride.schedule_date',
            'schedule_ride.schedule_time',
            'schedule_ride.booking_type',
        )
        ->addSelect(
            \DB::raw("'' as schedule_display_date"),
            \DB::raw("'false' as is_pool"),
            \DB::raw("0 as seats"),
            \DB::raw("'' as map_image"),
            \DB::raw("'".$user->currency->symbol."0.00' as driver_earnings"),
            \DB::raw("'' as driver_image"),
        )
        ->join('car_type','car_type.id','=','schedule_ride.car_id')
        ->leftjoin('users', 'users.id', '=', 'schedule_ride.driver_id')
        ->join('manage_fare', function($join) {
            $join->whereColumn('manage_fare.vehicle_id','schedule_ride.car_id')
            ->whereColumn('manage_fare.location_id','schedule_ride.location_id');
        })
        ->join('currency', 'currency.code', '=', 'manage_fare.currency_code');

        if($user->user_type=='Driver')
            $trip_lists->where('schedule_ride.driver_id', $user->id);
        else
            $trip_lists->where('schedule_ride.user_id', $user->id);
        
        return $trip_lists->where('schedule_ride.status',$status)
        ->orderBy('schedule_ride.id','DESC')->paginate($paginate_limit);
    }
}
