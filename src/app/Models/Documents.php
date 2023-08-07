<?php

/**
* Documents Model
*
* @package     Gofer
* @subpackage  Controller
* @category    Documents
* @author      Trioangle Product Team
* @version     2.2.1
* @link        http://trioangle.com
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use Session;
use Request;

class Documents extends Model
{
  use Translatable; 

  public $translatedAttributes = ['document_name'];

  protected $appends = ['doc_name'];

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);

    if(Request::segment(1) == 'admin') {
      $this->defaultLocale = 'en';
    }
    else {
      $this->defaultLocale = Session::get('language');
    }
  }

  public function translate()
  {
    return $this->hasmany('App\Models\DocumentsTranslations','documents_id','id');
  }

  public function getDocumentNameAttribute()
  {
    $lan = Session::get('language');
    if($lan=='en')
      return $this->attributes['document_name'];
    else{ 
      $get = DocumentsTranslations::where('documents_id',$this->attributes['id'])->where('locale',$lan)->first();
      if($get)
        return $get->document_name;
      else
        return $this->attributes['document_name'];
    }
  }
  
  /**
  * Scope to get Active records Only
  *
  */
  public function scopeActive($query)
  {
    return $query->where('status', 'Active');
  }

  public function getDocNameAttribute(){
    if($this->document_name){
      $doc = str_replace(" ", "_", strtolower($this->document_name));
      return $doc;
    }else{
      return '';
    }
  }

  public function scopeDocumentCheck($query,$document_for,$country_code){
    if($country_code !='all'){

      return $query->where('type',$document_for)->whereIn('country_code',[$country_code,'all'])->select('id','document_name','expire_on_date','country_code');
    }else{
      return $query->where(['type' =>$document_for,'country_code' => $country_code])->select('id','document_name','expire_on_date','country_code');
    }
  }

  public function getDocumentForAttribute()
  {
    if($this->attributes['country_code']=='all'){
      return 'All';
    }else{
      $data = Country::where('id',$this->attributes['country_code'])->first();

      return $data?$data->long_name:'';
    }
  }

  /**
  * Scope to get all related documents
  *
  */
  public function driver_documents() {
    return $this->hasMany('App\Models\DriverDocuments','document_id','id');
  }

}
