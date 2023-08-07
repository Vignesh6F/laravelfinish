<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterOption extends Model {
	
	use Translatable;

	public $translatedAttributes = ['name'];

    public static function options($skip=false,$female_riders,$handicap=true,$child_seat=true,$type='driver') {
    	
    	if($skip) {
			$options = self::skip(1)->take(2)->get();
		} elseif($type=='rider') {
			$options = self::skip(1)->take(3)->get();
		} else {
			$options = self::take(3)->get();
		}

    	return $options->map(function($filter) use($female_riders,$handicap,$child_seat) {
			$data['id'] = $filter->id;
			$data['name'] = $filter->name;

			if($filter->id==1 || $filter->id==4) {
				$isSelected = $female_riders;
			}

			if($filter->id==2) {
				$isSelected = $handicap;
			}

			if($filter->id==3) {
				$isSelected = $child_seat;
			}

			$data['isSelected'] = $isSelected;
			return $data;
		});
    }
}
