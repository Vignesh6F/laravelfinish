<?php

/**
 * Company Docuemnts Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Company Docuemnts
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDocuments extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_documents';

    public $timestamps = false;

    protected $fillable = ['company_id','license_photo','license_exp_date','insurance_photo','insurance_exp_date'];
   	protected $appends = ['doc_name','document_name'];

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
     * documents relation
     *
     */
    public function documents() {
        return $this->belongsTo('App\Models\Documents','document_id','id');
    }
}
