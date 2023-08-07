<?php

/**
 * Documents Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Documents
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;
 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\DocumentsDataTable;;
use App\Http\Start\Helpers;
use Validator;
use App\Models\Documents;
use App\Models\DriverDocuments;
use App\Models\Country;
use App\Models\Language;


class DocumentsController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Currency
     *
     * @param array $dataTable  Instance of CurrencyDataTable
     * @return datatable
     */
    public function index(DocumentsDataTable $dataTable)
    {
        return $dataTable->render('admin.documents.view');
    }

    /**
     * Add a New Currency
     *
     * @param array $request  Input values
     * @return redirect     to Currency view
     */
    public function add(Request $request)
    {
    	if($request->isMethod("GET")) {
        	$data['country'] = Country::GetId();
            $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');        	
            return view('admin.documents.add',$data);
        } elseif($request->submit) {

            $rules['document_name'] = 'required';
            $rules['expire_on_date'] = 'required';

            $niceNames['document_name'] = 'Document Name';
            $niceNames['expire_on_date'] = 'Expire On Date';

            foreach($request->translations ?: array() as $k => $translation) {
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.document_name'] = 'required';
                $niceNames['translations.'.$k.'.locale'] = 'Translation Language';
                $niceNames['translations.'.$k.'.document_name'] = 'Translation Document Name';
            }

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames);            
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            if($request->document_id=='') {
            	$documents = new Documents;
            } else {
            	$documents = Documents::find($request->document_id);
                $removed_translations = explode(',', $request->removed_translations);
                foreach(array_values($removed_translations) as $id) {
                    $documents->deleteTranslationById($id);
                }
            }

            $documents->type      		= $request->type;
            $documents->document_name   = $request->document_name;
            $documents->country_code    = $request->country;
            $documents->expire_on_date  = $request->expire_on_date;
            $documents->status      	= $request->status;
            $documents->save();

            if($request->translations) {
                foreach($request->translations ?: array() as $value) {
                    $id = ($request->document_id=='') ? $documents->id : $value['id'];
                    $doc_translation = $documents->getTranslationById($value['locale'],$id);
                    $doc_translation->document_name = $value['document_name'];
                    $doc_translation->save();
                }
            } 

            flashMessage('success', $request->document_id =='' ? 'Added Successfully' : 'Updated Successfully');
        }
        return redirect('admin/documents');
    }

    /**
     * Edit Documents
     *
     * @param array $request Input values
     * @return redirect to Documents view
     */
    public function edit(Request $request)
    {
    	$data['country'] = Country::GetId();
    	$data['result'] = Documents::find($request->id); 
        $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');      	
        return view('admin.documents.add',$data);
    }

    /**
     * Delete Documents
     *
     * @param array $request Input values
     * @return redirect to Documents View
     */
    public function delete(Request $request)
    {
    	$document = Documents::find($request->id);
        $count = DriverDocuments::where('document_id',$document->id)->count();
        if($count > 0){
            $this->helper->flash_message('danger', "The document used by some users. So can't delete this document");
            return redirect('admin/documents');
        }

        $document->translate()->delete();
        $delete_document = $document->delete();
        $delete_driver_documents = $document->driver_documents()->delete();
    	$this->helper->flash_message('success', 'Deleted Successfully');
    	return redirect('admin/documents');
    }
}
