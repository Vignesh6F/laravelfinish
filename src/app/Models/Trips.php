<?php

/**
 * Trips Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Trips
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;
use Auth;

class Trips extends Model
{
    use CurrencyConversion;

    public $convert_fields = ['time_fare', 'distance_fare', 'base_fare', 'total_fare', 'access_fee', 'driver_payout', 'owe_amount', 'remaining_owe_amount', 'applied_owe_amount', 'wallet_amount','promo_amount','payable_driver_payout','cash_collectable','commission','company_admin_commission','total_trip_fare','total_invoice','total_payout_frontend','cash_collect_frontend','driver_front_payout','rider_paid_amount','subtotal_fare','peak_amount','schedule_fare','driver_peak_amount','company_commission','driver_service_fee','driver_or_company_commission','driver_or_company_earning','tips','admin_total_amount', 'waiting_charge','toll_fee','driver_earnings','additional_rider_amount'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'trips';

    protected $appends = ['vehicle_name','driver_name','rider_name','rider_profile_picture','driver_thumb_image','rider_thumb_image','date','pickup_time','pickup_time_formatted','drop_time','pickup_date_time','trip_time','begin_date','payout_status','date_time_trip','driver_joined_at','payable_driver_payout','cash_collectable','commission','company_admin_commission','total_trip_fare','total_invoice','total_payout_frontend','cash_collect_frontend','driver_front_payout','rider_paid_amount','map_image','currency_symbol','status','toll_fee_reason','company_driver_earnings'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'fare_estimation' => 'array',
    ];

    public static $withoutAppends = false;

    protected function getArrayableAppends() {
        if(self::$withoutAppends){
            $this->convert_fields = [];
            return [];
        }
        return parent::getArrayableAppends();
    }

    // Join with profile_picture table
    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
      // Join with Pool Trip table
    public function trips() {
        return $this->hasMany('App\Models\Trips','pool_id','id');
    }
    
    // Join with user table
    public function driver()
    {
        return $this->belongsTo('App\Models\User','driver_id','id');
    }
    // Join with cancel table
    public function cancel()
    {
        return $this->belongsTo('App\Models\Cancel','id','trip_id');
    }
    // Join with payment table
    public function payment()
    {
        return $this->belongsTo('App\Models\Payment','id','trip_id');
    }
    // Join with Currency table
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency','currency_code','code');
    }
    public function language()
    {
        return $this->belongsTo('App\Models\Language','language_code','value');
    } 
    // Join with profile_picture table
    public function profile_picture()
    {
        return $this->belongsTo('App\Models\ProfilePicture','user_id','user_id');
    }

    // Join with car_type table
    public function car_type()
    {
        return $this->belongsTo('App\Models\CarType','car_id','id');
    }

    // Join with driver_location table
    public function driver_location()
    {
        return $this->belongsTo('App\Models\DriverLocation','driver_id','user_id');
    }
    // Join with rating table
    public function rating()
    {
        return $this->belongsTo('App\Models\Rating','user_id','user_id');
    }
     public function trip_rating()
    {
        return $this->belongsTo('App\Models\Rating','id','trip_id');
    }

    // Join with request table
    public function ride_request()
    {
        return $this->belongsTo('App\Models\Request','request_id','id');
    }
    
    // Join with Driver Address table
    public function driver_address()
    {
        return $this->belongsTo('App\Models\DriverAddress','driver_id','user_id');
    }

    // Join with payment table
    public function driver_payment()
    {
        return $this->hasOne('App\Models\Payment','trip_id','id');
    }

    // Join with payment table
    public function toll_reason()
    {
        return $this->hasOne('App\Models\TollReason','id','toll_reason_id');
    }

     // Join with payment table
    public function trip_toll_reason()
    {
        return $this->hasOne('App\Models\TripTollReason','trip_id','id');
    }

    public function scopePaymentTripsOnly($query)
    {
        return $query->whereNotIn('payment_mode',['Cash','Cash & Wallet']);
    }

    public function scopeCashTripsOnly($query)
    {
        return $query->whereIn('payment_mode',['Cash','Cash & Wallet']);
    }

    public function scopeDriverPayoutTripsOnly($query) {
        return $query->with(['payment'])
            ->whereHas('driver_payment', function ($query) {
                $query->where('driver_payout_status', 'Pending');
            })
            ->where(function($query)  {
                if(LOGIN_USER_TYPE=='company') {
                    $query->whereHas('driver',function($q1){
                        $q1->where('company_id',Auth::guard('company')->user()->id);
                    });
                }else{
                    $query->whereHas('driver',function($q1){
                        $q1->where('company_id',1);
                    });
                }
            })
            ->where('trips.status','Completed')
            ->where('driver_payout','>',0)
            ->where('payment_mode','<>','Cash');
    }

    public function scopeCompanyPayoutTripsOnly($query) {
        return $query->with(['payment'])
            ->whereHas('driver_payment', function ($query) {
                if(LOGIN_USER_TYPE == 'admin') {
                    $query->where('admin_payout_status','Pending');
                }
                else {
                    $query->where('driver_payout_status', 'Pending');
                }
            })
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'trips.driver_id')
                    ->where('users.company_id', '!=', 1);
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'users.company_id')
                    ->where('users.company_id', '!=', 1);
            })
            ->where('trips.status','Completed')
            ->where('trips.driver_payout','>',0)
            ->where('trips.payment_mode','<>','Cash');
    }

    // Get vehicle name
    public function getVehicleNameAttribute()
    {
        return CarType::find($this->attributes['car_id'])->car_name;
    } 

     // Get status
    public function getStatusAttribute()
    {
        $status = $this->attributes['status'];
        if(LOGIN_USER_TYPE == 'company' || LOGIN_USER_TYPE == 'admin' || $status == "Completed") {
            return $status;
        }

         if($status == "Payment" ) {
            if (Auth::user()) {
                $trip_id = $this->attributes['id'] ?? $this->attributes['trip_id'];
                $rating = Rating::where('trip_id',$trip_id)->count();
                if($rating){
                    return $status;
                }
            }
            return "Rating";
        }

        return $status;
    }

    public function getCommissionAttribute()
    {
        return $this->attributes['access_fee'] + ( $this->attributes['peak_amount'] - $this->attributes['driver_peak_amount'] ) + $this->attributes['schedule_fare'] + $this->attributes['driver_or_company_commission'];
    }
    public function getCompanyAdminCommissionAttribute()
    {
        return ( $this->attributes['peak_amount'] - $this->attributes['driver_peak_amount'] ) + $this->attributes['driver_or_company_commission'];
    }
    public function getDriverOrCompanyEarningAttribute()
    {
        return ( $this->attributes['subtotal_fare'] + $this->attributes['driver_peak_amount'] + $this->attributes['tips'] + $this->attributes['waiting_charge'] + $this->attributes['toll_fee'] + $this->attributes['additional_rider_amount']) - $this->attributes['driver_or_company_commission'];
    }
    
    public function getAdminTotalAmountAttribute()
    {
        if( $this->attributes['payment_status'] == 'Completed' && $this->attributes['status'] == 'Completed' ){
            return ( $this->attributes['subtotal_fare'] + $this->attributes['peak_amount'] + $this->attributes['access_fee'] + $this->attributes['schedule_fare'] + $this->attributes['tips'] + $this->attributes['toll_fee'] + $this->attributes['waiting_charge']);
        }else
        {
            return ( $this->attributes['subtotal_fare'] + $this->attributes['peak_amount'] + $this->attributes['access_fee'] + $this->attributes['schedule_fare'] + $this->attributes['toll_fee'] + $this->attributes['waiting_charge']);
        }
    }

    public function getPayableDriverPayoutAttribute()
    {
        if($this->attributes['payment_mode']=="Cash" && $this->attributes['wallet_amount']==0 && $this->attributes['promo_amount']==0){
            return 0;
        }
        
        if(($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet") && ($this->attributes['wallet_amount']!=0 || $this->attributes['promo_amount']!=0)) {
            $promo_wallet=$this->attributes['wallet_amount']+$this->attributes['promo_amount'];
            $cash_collectable = $this->total_fare()-$promo_wallet;
            if($promo_wallet > $this->total_fare()) {
                $cash_collectable= 0;
            }

            return number_format(($this->attributes['driver_payout'] + $this->attributes['access_fee'] -$cash_collectable),2, '.', '');
        }

        return number_format((($this->total_fare()-$this->attributes['access_fee'])-$this->attributes['applied_owe_amount']),2, '.', '');
    }

    public function getRiderPaidAmountAttribute()
    {
        return number_format(($this->attributes['total_fare'])-($this->attributes['wallet_amount']+$this->attributes['promo_amount']),2, '.', '');   
    }
    public function getCashCollectableAttribute()
    {
        $cashcollect=0;

        if($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet")
        {  
            if($this->attributes['promo_amount']+$this->attributes['wallet_amount'] > $this->total_fare())
            {
                $cashcollect = 0 ; 
            } 
            else
            $cashcollect= ($this->attributes['additional_rider_amount'] + $this->total_fare())-($this->attributes['promo_amount']+$this->attributes['wallet_amount']);
        }
        return number_format($cashcollect,2, '.', '');
    }

    public function total_fare()
    {
        return $total_fare = $this->attributes['base_fare'] + $this->attributes['time_fare'] + $this->attributes['distance_fare'] + $this->attributes['schedule_fare'] + $this->attributes['access_fee'] + $this->attributes['peak_amount'] + $this->attributes['tips'] + $this->attributes['waiting_charge'] + $this->attributes['toll_fee'];
    }
    public function getDriverFrontPayoutAttribute()
    {
        return number_format((($this->attributes['wallet_amount']+$this->attributes['promo_amount'])-($this->attributes['access_fee']+$this->attributes['applied_owe_amount'])),2, '.', '');
    }
    public function getCashCollectFrontendAttribute()
    {
        $cashcollect=0;
        if($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet")
        {
            $cashcollect=$this->attributes['total_fare']-($this->attributes['promo_amount']+$this->attributes['wallet_amount']);
        }
        return number_format($cashcollect,2, '.', '');
    }

    public function getTotalPayoutFrontendAttribute()
    {
        return number_format($this->attributes['driver_payout'],2, '.', '');
    }

    public function getPayoutStatusAttribute()
    {
        $payout=Payment::where('trip_id',$this->attributes['id']);
        if($payout->count())
        {
            return Payment::where('trip_id',$this->attributes['id'])->first()->driver_payout_status;    
        }
        else
        {
            return "";
        }
        
    } 
    // get begin trip value
    public function getDateAttribute()
    {
        return strtotime($this->attributes['begin_trip']);
    }

    public function getMapImageAttribute()
    {   
        $map_image = @$this->attributes['map_image'];       
        $id = array_key_exists('id', $this->attributes) ? $this->attributes['id']:$this->attributes['trip_id'];
        if($map_image != '') {
            $map_image = url('images/map/'.$id.'/'.$map_image);
        }
        return $map_image;
    }

    public function getTripImageAttribute()
    {   
        $map_image = @$this->attributes['map_image'];

        if($map_image != '') {
            return url('images/map/'.$this->attributes['id'].'/'.$map_image);
        }

        $google_service = resolve("google_service");
        return $google_service->GetStaticMap($this->attributes['pickup_latitude'], $this->attributes['pickup_longitude'], $this->attributes['drop_latitude'], $this->attributes['drop_longitude'],$this->attributes['trip_path']);
    }

    // get trip currency code
    public function getCurrencySymbolAttribute()
    {
        if(array_key_exists('id', $this->attributes)) {
            $trips = Trips::where('request_id',$this->attributes['id']);
            if($trips->count()) {
                $code =  @$trips->value('currency_code');
                return Currency::where('code',$code)->value('symbol');
            } else {
                return "$";
            }
        }

        return html_entity_decode($this->attributes['currency_symbol']);
    }

    // get begin trip value with the format: yyyy-mm-dd
    public function getBeginDateAttribute()
    {
        return date('Y-m-d',strtotime($this->attributes['created_at']));
    }
    // get pickup date with the format: Thursday, July 20, 2017 11:58 AM
    public function getPickupDateTimeAttribute()
    {
      return date('l, F d, Y h:i A',strtotime($this->attributes['created_at']));
    }
    // get pickup time with the format: 11:58 AM
    public function getPickupTimeAttribute()
    {
      return date('h:i A',strtotime($this->attributes['begin_trip']));
    }

    // get pickup time with the format: 11:58 AM
    public function getPickupTimeFormattedAttribute()
    {
        $begin_trip = $this->getFormattedTime('begin_trip');
        if($begin_trip == '-') {
            return '';
        }
        return $begin_trip;
    }
    // get drop time with the format: 11:58 AM
    public function getDropTimeAttribute()
    {
      return date('h:i A',strtotime($this->attributes['end_trip']));
    }
    // get Driver name
    public function getDriverNameAttribute()
    {
      return User::find($this->attributes['driver_id'])->first_name;
    }
    // get Rider name
    public function getRiderNameAttribute()
    {
      return User::find($this->attributes['user_id'])->first_name;
    }
    // get Rider Profile Picture
    public function getRiderProfilePictureAttribute()
    {
      $profile_picture=ProfilePicture::where('user_id',$this->attributes['user_id'])->first();
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get DriverThumb image
    public function getDriverThumbImageAttribute()
    {
      $profile_picture=ProfilePicture::find($this->attributes['driver_id']);
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get DriverThumb image
    public function getRiderThumbImageAttribute()
    {
      $profile_picture=ProfilePicture::find($this->attributes['user_id']);
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get total trip time
    public function getTripTimeAttribute()
    {      
      $begin_time = new DateTime($this->attributes['begin_trip']);
      $end_time   = new DateTime($this->attributes['end_trip']);
      $timeDiff   = date_diff($begin_time,$end_time);
      return $timeDiff->format('%H').':'.$timeDiff->format('%I').':'.$timeDiff->format('%S');
               
    }

    /**
     * get Total Trip Fare Attribute
     * 
     */
    public function getTotalTripFareAttribute()
    {
        return number_format(($this->attributes['total_fare']-$this->attributes['access_fee']),2, '.', '');    
    }

    /**
     * get Total Invoice Attribute
     * 
     */
    public function getTotalInvoiceAttribute()
    {
        return $this->total_fare();
    }

    /**
     * get Total Fare Attribute
     * 
     */
    public function getTotalFareAttribute()
    {
        return number_format(($this->attributes['total_fare']),2, '.', ''); 
    }

    /**
     * get Driver Payout Attribute
     * 
     */
    public function getDriverPayoutAttribute()
    {
        return number_format(($this->attributes['driver_payout']),2, '.', ''); 
    }

    /**
     * get Access Fee Attribute
     * 
     */
    public function getAccessFeeAttribute()
    {
        return number_format(($this->attributes['access_fee']),2, '.', ''); 
    }

    /**
     * get Owe Amount Attribute
     * 
     */
    public function getOweAmountAttribute()
    {
        return number_format(($this->attributes['owe_amount']),2, '.', ''); 
    }

    /**
     * get Wallet Amount Attribute
     * 
     */
    public function getWalletAmountAttribute()
    {
        return number_format(($this->attributes['wallet_amount']),2, '.', ''); 
    }

    /**
     * get Applied Owe Amount Attribute
     * 
     */
    public function getAppliedOweAmountAttribute()
    {
        return number_format(($this->attributes['applied_owe_amount']),2, '.', ''); 
    }

    /**
     * get Remaining Owe Amount Attribute
     * 
     */
    public function getRemainingOweAmountAttribute()
    {
        return number_format(($this->attributes['remaining_owe_amount']),2, '.', ''); 
    }

    /**
     * get Promo Amount Attribute
     * 
     */
    public function getPromoAmountAttribute()
    {
        return number_format(($this->attributes['promo_amount']),2, '.', ''); 
    }

    /**
     * get Date Time Trip Attribute
     * 
     */
    public function getDateTimeTripAttribute()
    {
        $full = false;

        $now = new DateTime;
        $ago = new DateTime($this->attributes['created_at']);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => __('messages.date_time.year'),
            'm' => __('messages.date_time.month'),
            'w' => __('messages.date_time.week'),
            'd' => __('messages.date_time.day'),
            'h' => __('messages.date_time.hour'),
            'i' => __('messages.date_time.minute'),
            's' => __('messages.date_time.second'),
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            }
            else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        if($string) {
            return implode(', ', $string) . ' '.__('messages.ago');
        }
        return __('messages.just_now');
    }

     /**
     * Get driver Joined date
     * 
     */
    public function getDriverJoinedAtAttribute()
    {
        $full = false;
        $driver_created_at=DB::table('users')->where('id',$this->attributes['driver_id'])->get()->first()->created_at;
        $now = new DateTime;
        $ago = new DateTime($driver_created_at);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    // get Formatted Time with the format: 11:58 AM
    public function getFormattedTime($attribute)
    {
        $formatted_time = '-';
        $trip_time = strtotime($this->attributes[$attribute]);
        if($trip_time > 0) {
            $formatted_time = date('g:i A',$trip_time);
        }

        return $formatted_time;
    }

     /**
     * Get Peak subtotal
     * 
     */
    public function getPeakSubtotalFareAttribute()
    {
        return $this->peak_amount + $this->subtotal_fare;
    }

     /**
     * Get week days
     * 
     */
    public function getWeekDaysAttribute()
    {
        $week_no = 0;
        $year = date('Y', strtotime($this->attributes['created_at']));
        $week_no = date('W', strtotime($this->attributes['created_at']));
        $week_days = \App\Http\Start\Helpers::getWeekDates($year, $week_no);

        return $week_days;
    }

     /**
     * Scope to get company trips only
     * 
     */
    public function scopeCompanyTripsOnly($query, $company_id)
    {
        $company_trips = $query->whereHas('driver', function ($query) use ($company_id) {
            $query->where('company_id',$company_id);
        });
        return $company_trips;
    }

    /**
     * Get Company driver amount
     * 
     */
    public function getCompanyDriverAmountAttribute()
    {
        if($this->driver->company_id == 1) {
           return  $this->driver_payout;
        }
        $payment_mode  = $this->attributes['payment_mode'];

        $subtotal_fare = ($payment_mode == 'Cash' || $payment_mode == 'Cash & Wallet') ? $this->total_fare : $this->subtotal_fare;
        return $subtotal_fare;
    }

    /**
     * Get Company driver earnings
     * 
     */
    public function getCompanyDriverEarningsAttribute()
    {
        return  $this->driver_or_company_earning;
    }

    /**
     * Get Trip additional fee reson
     * 
     */
    public function getTollFeeReasonAttribute()
    {
        $reason = '';
        if ($this->toll_reason_id) {
            $reason = $this->toll_reason->reason;
        }
        return $reason;
    }

    /**
     * Get Trip Other Reason
     * 
     */
    public function getTripTollFeeReasonAttribute()
    {
        $reason = '';
        if ($this->toll_reason_id && $this->toll_reason_id==1) {
            $reason = $this->trip_toll_reason->reason;
        }

        return $reason;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get trip details.
     *
     * @param  $user | json
     * @param  $paginate_limit | int
     * @param  $status | string
     * @param  $in | boolean
     * @return array
     */
    public static function getTripLists($user,$paginate_limit,$status,$in=true) {
        $trip_lists = self::select(
            \DB::raw("CASE WHEN trips.pool_id > 0 THEN 'true' ELSE 'false' END as is_pool"),
            'trips.seats',
            'trips.status',
            'trips.id as trip_id',
            'trips.pickup_location as pickup',
            'trips.drop_location as drop',
            'trips.map_image',
            \DB::raw("CASE 
                WHEN 
                schedule_ride.booking_type IS NOT NULL 
                THEN 
                schedule_ride.booking_type
                ELSE 
                ''
                END 
                as booking_type"
            ),
            'car_type.car_name as car_type',
            \DB::raw('"'.$user->currency->symbol.'" as currency_symbol'),
            \DB::raw("CASE 
                WHEN 
                users.company_id!=1 AND (trips.payment_mode='Cash & Wallet' OR trips.payment_mode='Cash') AND (trips.base_fare + trips.time_fare + trips.distance_fare + trips.schedule_fare + trips.access_fee + trips.peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee)=0 
                THEN 
                ((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * ".$user->currency->rate." 
                ELSE 
                    CASE 
                    WHEN 
                    trips.payment_status='Completed' AND trips.status='Completed' 
                    THEN 
                    ((trips.subtotal_fare + trips.peak_amount + trips.access_fee + trips.schedule_fare + trips.tips + trips.toll_fee + trips.waiting_charge) / currency.rate) * ".$user->currency->rate." 
                    ELSE 
                    ((trips.subtotal_fare + trips.peak_amount + trips.access_fee + trips.schedule_fare + trips.toll_fee + trips.waiting_charge) / currency.rate) * ".$user->currency->rate." 
                    END 
                END 
                as total_fare"
            ),
            \DB::raw("CASE 
                WHEN 
                trips.status!='Completed' 
                THEN 
                '".$user->currency->symbol."0.00' 
                ELSE 
                CONCAT('".$user->currency->symbol."','',FORMAT((((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * ".$user->currency->rate."),2))
                END 
                as driver_earnings"
            ),
            \DB::raw("CASE 
                WHEN 
                profile_picture.src IS NOT NULL AND profile_picture.src!=''
                THEN 
                profile_picture.src
                ELSE 
                '".url('images/user.jpeg')."'
                END 
                as driver_image"
            ),
        )
        ->addSelect(\DB::raw("'' as schedule_display_date"))
        ->join('car_type','car_type.id','=','trips.car_id')
        ->join('currency', 'currency.code', '=', 'trips.currency_code')
        ->join('users', 'users.id', '=', 'trips.driver_id')
        ->leftJoin('profile_picture', 'profile_picture.user_id', '=', 'users.id')
        ->leftJoin('request', 'request.id', '=', 'trips.request_id')
        ->leftJoin('schedule_ride', 'schedule_ride.id', '=', 'request.schedule_id');

        if($user->user_type=='Driver')
            $trip_lists->where('trips.driver_id', $user->id);
        else
            $trip_lists->where('trips.user_id', $user->id);

        if($in)
            $trip_lists->whereIn('trips.status',$status);
        else
            $trip_lists->whereNotIn('trips.status',$status);

        return $trip_lists->orderBy('trips.id','DESC')->paginate($paginate_limit);
    }

    /**
     * Get bank deposits.
     *
     * @param  $user_id | int
     * @param  $currency_rate | int
     * @param  $from_date | date
     * @param  $to_date | date
     * @param  $type | string
     * @return array
     */
    public static function getBankDeposits($user_id, $currency_rate, $from_date, $to_date, $type) {
        $bank_deposits = self::join('currency', 'currency.code', '=', 'trips.currency_code')
        ->where('payment_mode','<>','Cash')->where('payment_status','Completed')
        ->where('driver_id',$user_id)
        ->whereHas('payment',function($q) use($from_date, $to_date, $type){
            $q->where('driver_payout_status','Pending');
            if($type=='weekly')
                $q->whereBetween('updated_at', [$from_date, $to_date]);
        });

        if($type=='daily')
            $bank_deposits->whereBetween('created_at', [$from_date, $to_date]);

        return $bank_deposits->sum(\DB::raw('FORMAT(((trips.driver_payout / currency.rate) * '.$currency_rate.'),2)'));
    }

    /**
     * Get weekly statements.
     *
     * @param  $user_id | int
     * @param  $currency_rate | int
     * @param  $currency_symbol | string
     * @param  $from_date | date
     * @param  $to_date | date
     * @return array
     */
    public static function getWeeklyStatement($user_id, $currency_rate, $currency_symbol, $from_date, $to_date) {
        return self::select(
            \DB::raw('CONCAT("'.$currency_symbol.'","",SUM(FORMAT((((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * '.$currency_rate.'),2))) AS driver_earning'),
            \DB::raw('DATE_FORMAT(trips.created_at,"%d/%m") as format'),
            \DB::raw('DATE_FORMAT(trips.created_at,"%Y-%m-%d") as created_date'),
            \DB::raw('CONCAT("'.$currency_symbol.'","",SUM(FORMAT((((trips.driver_or_company_commission) / currency.rate) * '.$currency_rate.'),2))) AS driver_company_commission')
        )
        ->join('currency', 'currency.code', '=', 'trips.currency_code')
        ->where('trips.driver_id',$user_id)->where('trips.status','Completed')
        ->whereBetween('trips.created_at', [$from_date, $to_date])->whereHas('payment')
        ->groupBy('trips.created_at')->orderBy('trips.created_at','DESC')->get();
    }

    /**
     * Get daily statements.
     *
     * @param  $user_id | int
     * @param  $currency_rate | int
     * @param  $currency_symbol | string
     * @param  $from_date | date
     * @param  $to_date | date
     * @param  $per_page | int
     * @return array
     */
    public static function getDailyStatement($user_id,$currency_rate,$currency_symbol,$from_date,$to_date,$per_page) {
        return self::select(
            'trips.id',
            \DB::raw('CONCAT("'.$currency_symbol.'","",FORMAT((((trips.subtotal_fare + trips.driver_peak_amount + trips.tips + trips.waiting_charge + trips.toll_fee + trips.additional_rider_amount - trips.driver_or_company_commission) / currency.rate) * '.$currency_rate.'),2)) AS driver_earning'),
            \DB::raw('DATE_FORMAT(trips.created_at,"%h:%i %p") as time'),
            \DB::raw('CONCAT("'.$currency_symbol.'","",FORMAT((((trips.driver_or_company_commission) / currency.rate) * '.$currency_rate.'),2)) AS driver_commission')
        )
        ->join('currency', 'currency.code', '=', 'trips.currency_code')
        ->where('trips.driver_id',$user_id)->where('trips.status','Completed')->whereHas('payment')
        ->whereBetween('trips.created_at', [$from_date, $to_date])->orderBy('trips.id','DESC')->paginate($per_page);
    }
}
