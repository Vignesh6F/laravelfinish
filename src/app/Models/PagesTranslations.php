<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagesTranslations extends Model
{
    //
    public $timestamps = false;
    protected $fillable = ['name', 'description'];
    
    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }
}
