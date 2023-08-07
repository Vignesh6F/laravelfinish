<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;

class AppVersion extends Model
{
   protected $table = 'app_version';

    protected $appends = ['add_device_type','add_user_type','add_force_update',];


    // get Device Type as String
    public function getAddDeviceTypeAttribute()
    {
        return $this->attributes['device_type']=='1'?'Apple':'Android';
    } 

    // get User Type as String
    public function getAddUserTypeAttribute()
    {
        return $this->attributes['user_type']=='0'?'Rider':'Driver';
    }

    // get Force Update as String
    public function getAddForceUpdateAttribute()
    {
        return $this->attributes['force_update']=='0'?'No':'Yes';
    }

   
    public function getCreatedAtAttribute()
    {
        $date=new DateTime($this->attributes['created_at']);
        return $date->format('Y-m-d H:i:s');
    }
  
    public function getUpdatedAtAttribute()
    {
       $date=new DateTime($this->attributes['updated_at']);
        return $date->format('Y-m-d H:i:s');
    }
}
