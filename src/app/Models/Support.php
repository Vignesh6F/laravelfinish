<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model {

    /**
    * The database table used by the model.
    *
    * @var string
    */

     protected $fillable = ['name','status','cancelled_by'];

    public $timestamps = false;

    public function getImageSrcAttribute() {
        return url('images/support/'.$this->attributes['image']);
    }

    public function scopeActive($query) {
        return $query->whereStatus('Active');
    }    
}


