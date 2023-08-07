<?php

/**
 * Driver Location Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Driver Location
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'driver_location';

    protected $fillable = ['user_id','latitude','longitude','status','car_id','pool_trip_id','created_at','updated_at'];

    // Join with Car Type table
    public function car_type()
    {
        return $this->belongsTo('App\Models\CarType','car_id','id');
    }

    // Join with Pool Trip table
    public function pool_trip()
    {
        return $this->belongsTo('App\Models\PoolTrip','pool_trip_id','id');
    }

    // Join with profile_picture table
    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    // Join with profile_picture table
    public function request()
    {
        return $this->belongsTo('App\Models\Request','user_id','driver_id');
    }
    
    // Join with manage_fare table
    public function manage_fare()
    {
        return $this->belongsTo('App\Models\ManageFare','car_id','vehicle_id');
    }

    /**
     * Set the pool trip id.
     *
     * @param  int  $value
     * @return void
     */
    public function setPoolTripIdAttribute($value) {
        if(($this->attributes['status']=='Pool Trip' && $value!=null) || ($this->attributes['status']!='Pool Trip' && $value==null))
            $this->attributes['pool_trip_id'] = $value;
        else if($this->attributes['status']=='Trip' || $this->attributes['status']=='Online' || $this->attributes['status']=='Offline')
            $this->attributes['pool_trip_id'] = null;

    }
}
