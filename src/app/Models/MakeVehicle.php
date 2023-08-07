<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MakeVehicle extends Model
{
    //
    public $timestamps = false;
    
    public $table = 'vehicle_make';
    
    public function getMakeNameAttribute($query) {
        return $this->attributes['make_vehicle_name'];
    }

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query) {
        return $query->where('status', 'Active');
    }

    // Join with model table
    public function vehicle_model() {
        return $this->hasMany('App\Models\VehicleModel', 'vehicle_make_id', 'id')->active();
    }

    public static function getMakeModel() {
    	return MakeVehicle::with('vehicle_model')->active()->get();
    }
}
