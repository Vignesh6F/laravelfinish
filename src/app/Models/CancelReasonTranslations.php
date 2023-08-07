<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancelReasonTranslations extends Model
{
    public $timestamps = false;

    protected $fillable = ['reason'];

    public function language()
    {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
