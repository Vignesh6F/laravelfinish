<?php

/**
 * Referral User Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Referral User
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;

class ReferralUser extends Model
{
    use CurrencyConversion;

    public $convert_fields = ['amount','pending_amount'];

    protected $appends = ['referred_user_name', 'remaining_days', 'remaining_trips','earnable_amount','trans_payment_status','payment_status'];

    public $disable_admin_panel_convertion = true;

    public static $withoutAppends = false;

    protected function getArrayableAppends() {
        if(self::$withoutAppends){
            $this->convert_fields = [];
            return [];
        }
        return parent::getArrayableAppends();
    }

    // Join with user table
    public function referral_user()
    {
        return $this->belongsTo('App\Models\User','referral_id','id');
    }

    // Join with user table
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    // Get the referred user name
    public function getReferredUserNameAttribute()
    {
        return $this->referral_user->first_name;
    }

    // Get the referred user name
    public function getReferredUserProfilePictureSrcAttribute()
    {
        return $this->referral_user->profile_picture->src;
    }

    // Get the Remaining days to get payment
    public function getRemainingDaysAttribute()
    {
        if($this->attributes['payment_status'] == 'Expired') {
            return 0;
        }
        $end_date = new DateTime($this->attributes['end_date']);
        $now = new DateTime(date('Y-m-d'));
        $interval = $end_date->diff($now);
        $remaining_days = $interval->days;
        return ($remaining_days < 0) ? 0 : $remaining_days;
    }

    // Get the Remaining trips to get payment
    public function getRemainingTripsAttribute()
    {
        $start_date = date('Y-m-d H:i:s',strtotime($this->attributes['start_date']));
        $end_date = date('Y-m-d 23:59:59',strtotime($this->attributes['end_date']));
        $trip_col = 'user_id';

        if($this->attributes['user_type'] == 'Driver') {
            $trip_col = 'driver_id';
        }

        $prev_trips_count = DB::Table('trips')->selectRaw("count('id') as trips_count")->where($trip_col,$this->attributes['referral_id'])->whereBetween('updated_at',[$start_date, $end_date])->whereStatus('Completed')->first()->trips_count;

        $trips_count = $this->attributes['trips'] - $prev_trips_count;
        return ($trips_count < 0) ? 0 : $trips_count;
    }

    // Get the Remaining trips to get payment
    public function getEarnableAmountAttribute()
    {
        return html_entity_decode($this->currency_symbol).''.$this->amount;
    }

    /**
     * Get Translated payment status
     *  
     */
    public function getTransPaymentStatusAttribute()
    {
        return \Lang::get('messages.referrals.'.$this->attributes['payment_status']);
    }

    public function getPaymentStatusAttribute()
    {
        $end_date = new DateTime($this->attributes['end_date']);
        $now = new DateTime(date('Y-m-d'));
        $interval = $end_date->diff($now);
        $remaining_days = $interval->days;
        if($remaining_days==0 && $this->getRemainingTripsAttribute())
            return $this->attributes['payment_status']='Expired';
        else
            return \Lang::get('messages.referrals.'.$this->attributes['payment_status']);
    }

    /**
     * Get referral details.
     *
     * @param  $user_id | int
     * @param  $currency_rate | int
     * @param  $currency_rate | string
     * @return array
     */
    public static function getReferralDetails($user_id, $currency_rate, $currency_symbol) {
        $referral_users = self::select(
            'referral_users.id',
            'users.first_name as name',
            \DB::raw("CASE 
                WHEN 
                profile_picture.src IS NOT NULL AND profile_picture.src!=''
                THEN 
                profile_picture.src
                ELSE 
                '".url('images/user.jpeg')."'
                END 
                as profile_image"
            ),
            'referral_users.start_date',
            'referral_users.end_date',
            'referral_users.days',
            'referral_users.trips',
            'referral_users.payment_status',
            'referral_users.user_type',
            'referral_users.referral_id',
            \DB::raw('CONCAT("'.$currency_symbol.'","",FORMAT((((referral_users.amount) / currency.rate) * '.$currency_rate.'),2)) AS earnable_amounts')
        )
        ->addSelect(
            \DB::raw("'' as remaining_days"),
            \DB::raw("'' as remaining_trips"),
        )
        ->join('currency', 'currency.code', '=', 'referral_users.currency_code')
        ->join('users', 'users.id', '=', 'referral_users.referral_id')
        ->leftJoin('profile_picture', 'profile_picture.user_id', '=', 'users.id')
        ->where('referral_users.user_id',$user_id)
        ->whereIn('referral_users.payment_status',['Pending','Completed'])
        ->get()->groupBy('payment_status');

        return $referral_users->makeHidden(['user_type','referral_id']);
    }
}
