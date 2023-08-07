<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterObject extends Model
{
    public static function exist($type,$object_id,$filter_id) {
    	return self::whereType($type)->whereObjectId($object_id)->whereFilterId($filter_id)->value('id');
    }

    public static function options($type,$object_id) {
    	return self::whereType($type)->whereObjectId($object_id)->orderBy('filter_id')->pluck('filter_id')->toArray();
    }

    public static function optionsInsert($type,$object_id,$options) {
    	$request_from_id = FilterObject::exist($type,$object_id,1);
		if(in_array('1', $options)) {
			if(!$request_from_id) {
                $request_from_object = new FilterObject;
                $request_from_object->type = $type;
                $request_from_object->object_id = $object_id;
                $request_from_object->filter_id = 1;
                $request_from_object->save();
            }
		} else {
			if($request_from_id) {
            	$delete = FilterObject::find($request_from_id)->delete();
            }
        }

        $handicap_id = FilterObject::exist($type,$object_id,2);
		if(in_array('2', $options)) {
			if(!$handicap_id) {
                $handicap_object = new FilterObject;
                $handicap_object->type = $type;
                $handicap_object->object_id = $object_id;
                $handicap_object->filter_id = 2;
                $handicap_object->save();
            }
		} else {
			if($handicap_id) {
            	$delete = FilterObject::find($handicap_id)->delete();
            }
        }

        $child_seat_id = FilterObject::exist($type,$object_id,3);
		if(in_array('3', $options)) {
			if(!$child_seat_id) {
                $child_seat_object = new FilterObject;
                $child_seat_object->type = $type;
                $child_seat_object->object_id = $object_id;
                $child_seat_object->filter_id = 3;
                $child_seat_object->save();
            }
		} else {
			if($child_seat_id) {
            	$delete = FilterObject::find($child_seat_id)->delete();
            }
        }

        $prefer_female_driver_id = FilterObject::exist($type,$object_id,4);
        if(in_array('4', $options)) {
            if(!$prefer_female_driver_id) {
                $female_driver_object = new FilterObject;
                $female_driver_object->type = $type;
                $female_driver_object->object_id = $object_id;
                $female_driver_object->filter_id = 4;
                $female_driver_object->save();
            }
        } else {
            if($prefer_female_driver_id) {
                $delete = FilterObject::find($prefer_female_driver_id)->delete();
            }
        }
    }
}
