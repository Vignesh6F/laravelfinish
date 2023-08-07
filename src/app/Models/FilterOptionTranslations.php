<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterOptionTranslations extends Model
{

	protected $table = 'filter_options_translations';

    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }
}
