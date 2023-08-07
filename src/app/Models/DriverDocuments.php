<?php

/**
 * Driver Docuemnts Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Driver Docuemnts
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Documents;

class DriverDocuments extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'driver_documents';

    public $timestamps = false;
    protected $appends = ['doc_name','document_name'];

    protected $fillable = ['user_id','document_id','status','expired_date'];

    // Join with vehicle table
    public function vehicle()
    {
        return $this->hasOne('App\Models\Vehicle','user_id','user_id')->where('default_type', '1');
    }

    public function getDocumentNameAttribute(){
        $document = Documents::find($this->attributes['document_id']);
        return $document ? $document->document_name : '';
    }

    public function getDocNameAttribute(){
        $document = Documents::find($this->attributes['document_id']);
        if($document){
            $doc = str_replace(" ", "_", strtolower($document->document_name));
            return $doc;
        }else{
            return '';
        }
    }

    /**
     * get Car type
     *
     */
    public function getCarTypeAttribute()
    {
        return optional($this->vehicle)->car_type ?? '';
    }

    /**
     * get Insurance type
     *
     */
    public function getInsuranceAttribute()
    {
        return optional($this->vehicle)->insurance ?? '';
    }

    /**
     * get Rc Value
     *
     */
    public function getRcAttribute()
    {
        return optional($this->vehicle)->rc ?? '';
    }

    /**
     * get Permit Value
     *
     */
    public function getPermitAttribute()
    {
        return optional($this->vehicle)->permit ?? '';
    }

    /**
     * get Vehicle Id Value
     *
     */
    public function getVehicleIdAttribute()
    {
        return optional($this->vehicle)->vehicle_id ?? '';
    }

    /**
     * get Vehicle Type Value
     *
     */
    public function getVehicleTypeAttribute()
    {
        return optional($this->vehicle)->vehicle_type ?? '';
    }

    /**
     * get Vehicle Name Value
     *
     */
    public function getVehicleNameAttribute()
    {
        return optional($this->vehicle)->vehicle_name ?? '';
    }

    /**
     * get Vehicle Number Value
     *
     */
    public function getVehicleNumberAttribute()
    {
        return optional($this->vehicle)->vehicle_number ?? '';
    }
    
    /**
     * documents relation
     *
     */
    public function documents() {
        return $this->belongsTo('App\Models\Documents','document_id','id');
    }
}
