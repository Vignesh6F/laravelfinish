<?php

/**
 * Driver Docuemnts Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Driver Docuemnts
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vehicle';

    public $timestamps = false;

    protected $fillable = ['user_id','company_id','vehicle_id','vehicle_type','vehicle_name','vehicle_number'];

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
    
    /**
     * Join with car_type table
     *
     */    
    public function car_type()
    {
        return $this->belongsTo('App\Models\CarType','vehicle_id','id');
    }

    /**
     * Join with user table
     *
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * Join with user table
     *
     */
    public function female_driver()
    {
        return $this->belongsTo('App\Models\User','user_id','id')->whereUserType('Driver')->whereGender('2');
    }

    public function make()
    {
        return $this->belongsTo('App\Models\MakeVehicle', 'vehicle_make_id', 'id');
    }

    public function makeWithSelected() {
        return $this->belongsTo('App\Models\MakeVehicle', 'vehicle_make_id', 'id')->select('id','make_vehicle_name as name');
    }

    public function model()
    {
        return $this->belongsTo('App\Models\VehicleModel', 'vehicle_model_id', 'id');
    }

    public function modelWithSelected() {
        return $this->belongsTo('App\Models\VehicleModel', 'vehicle_model_id', 'id')->select('id','model_name as name');
    }

    public static function findVehicleExist($id,$user_id) {
        return Vehicle::where('id',$id)
        ->where('user_id',$user_id)
        ->first();
    }

    public static function getPreDefaultVehicle($user_id) {
        return Vehicle::where('user_id',$user_id)
        ->where('default_type', '1')
        ->first();
    }

    // Get Translated Status Attribute
    public function getTransStatusAttribute()
    {
        return trans('messages.driver_dashboard.'.$this->attributes['status']);
    }

    /**
     * Join with filter objects table
     *
     */
    public function female() {
        return $this->hasMany('App\Models\FilterObject','object_id','id')->whereType('vehicle')->whereFilterId(1);
    }

    /**
     * Join with filter objects table
     *
     */
    public function handicap() {
        return $this->hasMany('App\Models\FilterObject','object_id','id')->whereType('vehicle')->whereFilterId(2);
    }

    /**
     * Join with filter objects table
     *
     */
    public function child_seat() {
        return $this->hasMany('App\Models\FilterObject','object_id','id')->whereType('vehicle')->whereFilterId(3);
    }
}
