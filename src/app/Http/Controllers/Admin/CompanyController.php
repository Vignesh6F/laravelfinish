<?php

/**
 * Company Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Company
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\CompanyDataTable;
use App\Models\Country;
use App\Models\Company;
use App\Models\Documents;
use App\Models\CompanyDocuments;
use App\Models\Vehicle;
use App\Models\CompanyPayoutPreference;
use App\Models\CompanyPayoutCredentials;
use App\Models\ScheduleRide;
use App\Models\User;
use Validator;
use DB;
use Image;
use Auth;

class CompanyController extends Controller
{
    /**
     * Load Datatable for Company
     *
     * @param array $dataTable  Instance of Company DataTable
     * @return datatable
     */
    public function index(CompanyDataTable $dataTable)
    {
        return $dataTable->render('admin.company.view');
    }

    /**
     * Add a New Company
     *
     * @param array $request  Input values
     * @return redirect     to Company view
     */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['country_code_option'] = Country::select('long_name','phone_code','id')->get();
            $data['country_name_option'] = Country::pluck('long_name', 'short_name');
            return view('admin.company.add',$data);
        }

        $rules = array(
            'name'          => 'required|unique:companies,name,'.$request->id,
            'email'         => 'required|email',
            'country_code'  => 'required',
            'mobile_number' => 'required|regex:/[0-9]{6}/',
            'status'        => 'required',
            'password'      => 'required|min:6',
            'profile'       => 'mimes:jpg,jpeg,png',
            'address_line'  => 'required',
            'postal_code'   => 'required',
            'company_commission' => 'required|numeric|max:100',
        );

        $attributes = array(
            'name'          => 'Name',
            'email'         => 'Email',
            'country_code'  => 'Country Code',
            'mobile_number' => 'Mobile Number',
            'status'        => 'Status',
            'password'      => 'Password',
            'profile'       => 'Profile',
            'address_line'  => 'Address Line',
            'postal_code'   => 'Postal Code',
            'company_commission' => 'Company Commission',
        );
        
        $messages = array(
            'required'            => ':attribute is required.',
            'mobile_number.regex' => trans('messages.user.mobile_no'),
        );

        $company_doc = CheckDocument('Company',$request->country_code ?? 'all');

        if($company_doc->count() > 0){
            foreach($company_doc as $key => $value) {
                $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif';
                $attributes['file_'.$value->id] = $value->doc_name;
                if($value->expire_on_date=='Yes') {
                    $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                    $attributes['expired_date_'.$value->id] = 'Expired Date';
                }
            }
        }

        $validator = Validator::make($request->all(), $rules,$messages,$attributes);

        $validator->after(function ($validator) use($request) {
            $company = Company::where('mobile_number', $request->mobile_number)->where('country_id', $request->country_id)->count();
            $company_email = Company::where('email', $request->email)->count();

            if($company) {
               $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
            }

            if($company_email) {
               $validator->errors()->add('email',trans('messages.user.email_exists'));
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = new Company;
        $company->name          = $request->name;
        $company->vat_number    = $request->vat_number;
        $company->email         = $request->email;
        $company->country_code  = $request->country_code;
        $company->country_id    = $request->country_id;
        $company->mobile_number = $request->mobile_number;
        $company->password      = $request->password;
        $company->status        = $request->status;
        $company->address       = $request->address_line;
        $company->city          = $request->city;
        $company->state         = $request->state;
        $company->country       = $request->country_code;
        $company->postal_code   = $request->postal_code;
        $company->company_commission  = $request->company_commission;
        $company->save();

        $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
        $target_dir = '/images/companies/'.$company->id;
        $target_path = asset($target_dir).'/';

        if($request->hasFile('profile')) {
            $profile    =   $request->file('profile');

            $extension = $profile->getClientOriginalExtension();
            $file_name = "profile_".time().".".$extension;
            $options = compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($profile,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }

            $company->profile = $target_path.$upload_result['file_name'];
            $company->save();
        }

        if($company_doc){                
            foreach($company_doc as $key => $value) {
                $document_name = $value->doc_name;
                $document = $request->file('file_'.$value->id);
                $extension = $document->getClientOriginalExtension();
                $file_name = $document_name."_".time().".".$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($document,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }

                $company_doc = new CompanyDocuments;
                $company_doc->company_id = $company->id;
                $company_doc->document_id = $value->id;
                $company_doc->document = $target_path.$upload_result['file_name'];
                $document_status = $value->doc_name."_status";
                $company_doc->status = $request->$document_status;

                $expired_date_key = 'expired_date_'.$value->id;
                $company_doc->expired_date = $request->$expired_date_key;
                $company_doc->save();
            }
        }

       
        flashMessage('success', trans('messages.user.add_success'));

        return redirect(LOGIN_USER_TYPE.'/company');
    }

    /**
     * Update Driver Details
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result'] = $company_info = Company::find($request->id);

            if (LOGIN_USER_TYPE=='company' && $request->id != Auth::guard('company')->user()->id) {
                abort(404);
            }

            if($data['result']) {
                $data['country_code_option']= Country::select('long_name','phone_code','id')->get();
                $data['path']               = url('images/users/'.$request->id);
                return view('admin.company.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
            return redirect(LOGIN_USER_TYPE.'/company');
        }

        $rules = array(
            'name'          => 'required|unique:companies,name,'.$request->id,
            'email'         => 'required|email',
            'country_code'  => 'required',
            'password'      => 'nullable|min:6',
            'profile'       => 'mimes:jpg,jpeg,png',
            'address_line'  => 'required',
            'postal_code'   => 'required',
            'mobile_number' => 'nullable|regex:/[0-9]{6}/',
        );

        //Admin only can update status and company commission.Company could not update
        if (LOGIN_USER_TYPE != 'company') {
            $rules['status'] = 'required';
            if ($request->id != 1) {
                $rules['company_commission'] = 'required|numeric|max:100';
            }
        }

        $attributes = array(
            'name'          => 'Name',
            'email'         => 'Email',
            'country_code'  => 'Country Code',
            'mobile_number' => 'Mobile Number',
            'status'        => 'Status',
            'password'      => 'Password',
            'profile'       => 'Profile',
            'address_line'  => 'Address Line',
            'postal_code'   => 'Postal Code',
            'company_commission' => 'Company Commission',
        );
        
        $messages = array(
            'required'            => ':attribute is required.',
            'mobile_number.regex' => trans('messages.user.mobile_no'),
        );

        $company_info = Company::find($request->id);
        if($company_info->country_code != $request->country_code) {
            $company_doc = CheckDocument('Company',$request->country_code ?? 'all');
            if($company_doc->count() > 0){
                foreach($company_doc as $key => $value) {
                    $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif';
                    $attributes['file_'.$value->id] = $value->doc_name;
                    if($value->expire_on_date=='Yes') {
                        $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                        $attributes['expired_date_'.$value->id] = 'Expired Date';
                    }
                }
            }
        } else {
            $company_document = UserDocuments('Company',$company_info,0);
            $result = json_decode($company_document, true);
            foreach($result as $key => $value) {
                if($value['document'] == ''){
                    $rules['file_'.$value['id']] = 'required|mimes:jpg,jpeg,png,gif';
                    $attributes['file_'.$value['id']] = $value['doc_name'];
                }
                if($value['expiry_required']==1) {
                    $rules['expired_date_'.$value['id']] = 'required|date|date_format:Y-m-d|after_or_equal:today';
                    $attributes['expired_date_'.$value['id']] = 'Expired Date';
                }
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        $validator->after(function ($validator) use($request) {
            if ($request->mobile_number != '') {
                $company = Company::where('mobile_number', $request->mobile_number)->where('country_id', $request->country_id)->where('id','!=',$request->id)->count();
                if($company) {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }
            }

            $company_email = Company::where('email', $request->email)->where('id','!=',$request->id)->count();
            if($company_email) {
               $validator->errors()->add('email',trans('messages.user.email_exists'));
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = Company::find($request->id);
        $company->name         = $request->name;
        $company->vat_number   = $request->vat_number;
        $company->email        = $request->email;
        $company->country_code = $request->country_code;
        $company->country_id   = $request->country_id;
        if($request->mobile_number != "") {
            $company->mobile_number= $request->mobile_number;
        }
        if (isset($request->password)) {
            $company->password = $request->password;
        }
        if (LOGIN_USER_TYPE != 'company') {
            $company->status       = $request->status;
            $company->company_commission  = $request->company_commission;
        }
        $company->address      = $request->address_line;
        $company->city         = $request->city;
        $company->state        = $request->state;
        $company->country      = $request->country_code;
        $company->postal_code  = $request->postal_code;
        $company->save();

        $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
        $target_dir = '/images/companies/'.$company->id;
        $target_path = asset($target_dir).'/';

        if($request->hasFile('profile')) {
            $profile    =   $request->file('profile');

            $extension = $profile->getClientOriginalExtension();
            $file_name = "profile_".time().".".$extension;
            $options = compact('target_dir','file_name');

            $upload_result = $image_uploader->upload($profile,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }

            $company->profile = $target_path.$upload_result['file_name'];
            $company->save();
        }

        $company_doc = CheckDocument('Company',$request->country_code);
        if($company_doc){
            foreach($company_doc as $key => $value) {
                if($request->hasFile('file_'.$value->id)){
                    $document_name = $value->doc_name;
                    $document = $request->file('file_'.$value->id);
                    $extension = $document->getClientOriginalExtension();
                    $file_name = $document_name."_".time().".".$extension;
                    $options = compact('target_dir','file_name');
                    $upload_result = $image_uploader->upload($document,$options);
                    
                    if(!$upload_result['status']) {
                        flashMessage('danger', $upload_result['status_message']);
                        return back();
                    }

                    $user_doc = CompanyDocuments::where('company_id',$company_info->id)->where('document_id',$value->id)->first();
                    if(!$user_doc){
                        $user_doc = new CompanyDocuments;
                    }

                    $user_doc->company_id  = $company_info->id;
                    $user_doc->document_id = $value->id;
                    $user_doc->document = $target_path.$upload_result['file_name'];
                    $document_status    = $value->doc_name."_status";
                    if(LOGIN_USER_TYPE=='company')
                        $user_doc->status = '0';
                    else
                        $user_doc->status = $request->$document_status; 
                    $user_doc->save();
                }                   
            }

            $deleteOldDocument = CompanyDocuments::where('company_id',$company_info->id)->whereNotIn('document_id',$company_doc->pluck('id')->toArray())->pluck('id');
            /*Delete document from table and folder*/
            if($deleteOldDocument){
                foreach ($deleteOldDocument as $key => $value) {
                    $company = CompanyDocuments::find($value);
                    $company_doc = resolve('App\Contracts\ImageHandlerInterface');
                    $company_doc->delete($company->document,['file_path' => '/images/companies/'.$company->id.'/']);
                    $company->delete();
                }
            }
            /*End Here*/

            foreach($company_doc as $key => $value) {
                $user_doc = CompanyDocuments::where('company_id',$company_info->id)->where('document_id',$value->id)->first();

                $expired_date_key = 'expired_date_'.$value->id;

                if(LOGIN_USER_TYPE=='company' && $user_doc->expired_date!=$request->$expired_date_key) {
                    $document_status = $value->doc_name."_status";
                    $user_doc->status = '0';
                } else if(LOGIN_USER_TYPE!='company') {
                    $document_status = $value->doc_name."_status";
                    $user_doc->status = $request->$document_status;
                }

                $user_doc->expired_date = $request->$expired_date_key;
                $user_doc->save();
            }
        }

        flashMessage('success', 'Updated Successfully');
        
        if (LOGIN_USER_TYPE == 'company') {
            return redirect('company/edit_company/'.Auth::guard('company')->user()->id);
        }
        return redirect(LOGIN_USER_TYPE.'/company');
    }

    /**
     * Delete Driver
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function delete(Request $request)
    {     
        if($request->id == 1) {
            flashMessage('danger', 'Could not delete default company');
            return redirect(LOGIN_USER_TYPE.'/company');
        }
        
        $company_drivers = User::where('user_type','Driver')->where('company_id',$request->id)->count();
        
        if($company_drivers>=1) {
            flashMessage('danger', 'Company have some drivers, So can\'t delete this company');
            return redirect(LOGIN_USER_TYPE.'/company');
        }

        $company_schedule = ScheduleRide::where('company_id',$request->id)->count();
        if($company_schedule) {
            flashMessage('danger', 'Company have some schedule rides, So can\'t delete this company');
            return redirect(LOGIN_USER_TYPE.'/company');
        }
        
        Vehicle::where('company_id',$request->id)->delete();
        
        $companyDocument = CompanyDocuments::where('company_id',$request->id)->pluck('id');
        if($companyDocument){
            foreach ($companyDocument as $key => $value) {
                $company = CompanyDocuments::find($value);
                $company_doc = resolve('App\Contracts\ImageHandlerInterface');
                $company_doc->delete($company->document,['file_path' => '/images/companies/'.$request->id.'/']);
                $company->delete();
            }
        }

        CompanyPayoutPreference::where('company_id',$request->id)->delete();
        CompanyPayoutCredentials::where('company_id',$request->id)->delete();
        
        Company::find($request->id)->delete();
        flashMessage('success', 'Deleted Successfully');
        return redirect(LOGIN_USER_TYPE.'/company');
    }
}
