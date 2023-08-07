<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    //
    public $timestamps = false;
    
    public $table = 'vehicle_model';

    public function vehicle_make() {
		return $this->belongsTo('App\Models\MakeVehicle', 'vehicle_make_id', 'id');
	}

	/**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query) {
        return $query->where('status', 'Active');
    }
}
