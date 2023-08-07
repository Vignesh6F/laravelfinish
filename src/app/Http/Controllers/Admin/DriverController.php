<?php

/**
 * Driver Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Driver
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\DriverDataTable;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\Country;
use App\Models\CarType;
use App\Models\ProfilePicture;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\ReferralUser;
use App\Models\DriverOweAmount;
use App\Models\PayoutPreference;
use App\Models\PayoutCredentials;
use App\Models\Documents;
use Validator;
use DB;
use Image;
use Auth;

class DriverController extends Controller
{
    /**
     * Load Datatable for Driver
     *
     * @param array $dataTable  Instance of Driver DataTable
     * @return datatable
     */
    public function index(DriverDataTable $dataTable)
    {
        return $dataTable->render('admin.driver.view');
    }

    public function get_documents(Request $request) {

        $country_code = $request->country_code;

        $vehicle_id = 0;
        if(isset($request->vehicle_id)) {
            $vehicle_id = $request->vehicle_id;
        }

        if($request->document_for=='Company') {
            $user = Company::find($request->user_id);
        } else {
            $user = User::find($request->user_id);
        }

        if(isset($user->country_code) && $country_code==$user->country_code) {
            $driver_doc = UserDocuments($request->document_for,$user,$vehicle_id);
            $driver_doc = json_decode($driver_doc);

            if($request->document_for=='Vehicle') {
                foreach($driver_doc as $key=>$doc) {
                    if(isset($doc->country_code)){
                        if($doc->country_code!='all' && $doc->country_code!=$user->country_code) {
                            unset($driver_doc[$key]);
                        }
                    }
                }
            }
        } else {
            $driver_doc = CheckDocument($request->document_for,$country_code);
        }

        return json_encode($driver_doc);  
    }

    /**
     * Add a New Driver
     *
     * @param array $request  Input values
     * @return redirect     to Driver view
     */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            //Inactive Company could not add driver
            if (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status != 'Active') {
                abort(404);
            }
            $data['country_code_option'] = Country::select('long_name','phone_code','id')->get();
            $data['country_name_option'] = Country::pluck('long_name', 'short_name');
            $data['company'] = Company::where('status','Active')->pluck('name','id');
            $driver_doc = Documents::Active()->where(['type' =>'Driver','country_code' =>'all'])->select('id','document_name','expire_on_date')->get();
            $data['driver_doc'] = json_encode($driver_doc);
            return view('admin.driver.add',$data);
        }

        if($request->submit) {
            // Add Driver Validation Rules
            $rules = array(
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'mobile_number' => 'required|regex:/[0-9]{6}/',
                'password'      => 'required',
                'country_code'  => 'required',
                'gender'        => 'required',
                'user_type'     => 'required',
                'status'        => 'required',
            );

            $driver_doc = CheckDocument('Driver',$request->country_code ?? 'all');

            if($driver_doc->count() > 0){
                foreach($driver_doc as $key => $value) {
                    $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif';
                    $attributes['file_'.$value->id] = $value->document_name;
                    if($value->expire_on_date=='Yes') {
                        $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                        $attributes['expired_date_'.$value->id] = 'Expired Date';
                    }
                }
            }
            
            //Bank details are required only for company drivers & Not required for Admin drivers
            if ((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

            if (LOGIN_USER_TYPE!='company') {
                $rules['company_name'] = 'required';
            }

            // Add Driver Validation Custom Names
            $attributes['first_name']   = trans('messages.user.firstname');
            $attributes['last_name']    = trans('messages.user.lastname');
            $attributes['email']        = trans('messages.user.email');
            $attributes['password']     = trans('messages.user.paswrd');
            $attributes['country_code'] = trans('messages.user.country_code');
            $attributes['gender']       = trans('messages.profile.gender');
            $attributes['user_type']    = trans('messages.user.user_type');
            $attributes['status']       = trans('messages.driver_dashboard.status');
            $attributes['account_holder_name'] = 'Account Holder Name';
            $attributes['account_number'] = 'Account Number';
            $attributes['bank_name']    = 'Name of Bank';
            $attributes['bank_location']= 'Bank Location';
            $attributes['bank_code']    = 'BIC/SWIFT Code';

            // Edit Rider Validation Custom Fields message
            $messages = array(
                'required'            => ':attribute is required.',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            );

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);

            $validator->after(function ($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('country_id', $request->country_id)->count();

                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->count();

                if($user) {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                if($user_email) {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });

            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            if($request->status=="Active"){
                flashMessage('danger', 'Please ensure the driver has atleast one default vehicle, if not you can\'t activate.');
                return back()->withInput();
            }

            $user = new User;

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->country_code = $request->country_code;
            $user->country_id   = $request->country_id;
            $user->gender       = $request->gender;
            $user->mobile_number= $request->mobile_number;
            $user->password     = $request->password;
            $user->status       = $request->status;
            $user->user_type    = $request->user_type;
            if(LOGIN_USER_TYPE=='company') {
                $user->company_id = Auth::guard('company')->user()->id;
            } else {
                $user->company_id = $request->company_name;
            }
            $user->save();

            $user_pic = new ProfilePicture;
            $user_pic->user_id      = $user->id;
            $user_pic->src          = '';
            $user_pic->photo_source = 'Local';
            $user_pic->save();

            $user_address = new DriverAddress;
            $user_address->user_id       = $user->id;
            $user_address->address_line1 = $request->address_line1 ? $request->address_line1 :'';
            $user_address->address_line2 = $request->address_line2 ? $request->address_line2:'';
            $user_address->city          = $request->city ? $request->city:'';
            $user_address->state         = $request->state ? $request->state:'';
            $user_address->postal_code   = $request->postal_code ? $request->postal_code:'';
            $user_address->save();

            if($user->company_id != null && $user->company_id != 1) {
                $payout_preference = PayoutPreference::firstOrNew(['user_id' => $user->id,'payout_method' => "BankTransfer"]);
                $payout_preference->user_id = $user->id;
                $payout_preference->country = "IN";
                $payout_preference->account_number  = $request->account_number;
                $payout_preference->holder_name     = $request->account_holder_name;
                $payout_preference->holder_type     = "company";
                $payout_preference->paypal_email    = $request->account_number;

                $payout_preference->phone_number    = $request->mobile_number ?? '';
                $payout_preference->branch_code     = $request->bank_code ?? '';
                $payout_preference->bank_name       = $request->bank_name ?? '';
                $payout_preference->bank_location   = $request->bank_location ?? '';
                $payout_preference->payout_method   = "BankTransfer";
                $payout_preference->address_kanji   = json_encode([]);
                $payout_preference->save();

                $payout_credentials = PayoutCredentials::firstOrNew(['user_id' => $user->id,'type' => "BankTransfer"]);
                $payout_credentials->user_id = $user->id;
                $payout_credentials->preference_id = $payout_preference->id;
                $payout_credentials->payout_id = $request->account_number;
                $payout_credentials->type = "BankTransfer";
                $payout_credentials->default = 'yes';
                $payout_credentials->save();
            }

            $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
            $target_dir = '/images/users/'.$user->id;
            $target_path = asset($target_dir).'/';

            if($driver_doc){                
                foreach ($driver_doc as $key => $value) {
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
                    $user_doc = new DriverDocuments;
                    $user_doc->user_id = $user->id;
                    $user_doc->document_id = $value->id;
                    $user_doc->document = $target_path.$upload_result['file_name'];
                    $document_status = $value->doc_name."_status";
                    $user_doc->status = $request->$document_status;

                    $expired_date_key = 'expired_date_'.$value->id;
                    $user_doc->expired_date = $request->$expired_date_key;
                    $user_doc->save();
                }
            }
         
            flashMessage('success', trans('messages.user.add_success'));
            return redirect(LOGIN_USER_TYPE.'/driver');
        }

        return redirect(LOGIN_USER_TYPE.'/driver');
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
            $data['result']  = $user  = User::find($request->id);

            //If login user is company then company can edit only that company's driver details
            if($data['result']) {
                $data['address']            = DriverAddress::where('user_id',$request->id)->first();
                $data['country_code_option']= Country::select('long_name','phone_code','id')->get();
                $data['company']            = Company::where('status','Active')->pluck('name','id');
                $data['path']               = url('images/users/'.$request->id);
                return view('admin.driver.edit', $data);
            } else {
                flashMessage('danger', 'Invalid ID');
                return redirect(LOGIN_USER_TYPE.'/driver'); 
            }
        }
        
        if($request->submit) {
            // Edit Driver Validation Rules
            $rules = array(
                'first_name'    => 'required',
                'last_name'     => 'required',
                'email'         => 'required|email',
                'status'        => 'required',
                'country_code'  => 'required',
                'gender'        => 'required',
            );

            //Bank details are updated only for company's drivers.
            if((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

            if(LOGIN_USER_TYPE!='company') {
                $rules['company_name'] = 'required';
            }

            // Edit Driver Validation Custom Fields Name
            $attributes = array(
                'first_name'    => trans('messages.user.firstname'),
                'last_name'     => trans('messages.user.lastname'),
                'email'         => trans('messages.user.email'),
                'status'        => trans('messages.driver_dashboard.status'),
                'mobile_number' => trans('messages.profile.phone'),
                'country_ode'   => trans('messages.user.country_code'),
                'gender'        => trans('messages.profile.gender'),
                'account_holder_name' => 'Account Holder Name',
                'account_number'=> 'Account Number',
                'bank_name'     => 'Name of Bank',
                'bank_location' => 'Bank Location',
                'bank_code'     => 'BIC/SWIFT Code',
            );

            // Edit Rider Validation Custom Fields message
            $messages = array(
                'required'            => ':attribute is required.',
                'mobile_number.regex' => trans('messages.user.mobile_no'),
            );

            $user = User::find($request->id);
            if($user->country_code != $request->country_code) {
                $driver_doc = CheckDocument('Driver',$request->country_code ?? 'all');
                if($driver_doc->count() > 0){
                    foreach ($driver_doc as $key => $value) {
                        $rules['file_'.$value->id] = 'required|mimes:jpg,jpeg,png,gif';
                        $attributes['file_'.$value->id] = $value->document_name;
                        if($value->expire_on_date=='Yes') {
                            $rules['expired_date_'.$value->id] = 'required|date|date_format:Y-m-d';
                            $attributes['expired_date_'.$value->id] = 'Expired Date';
                        }
                    }
                }
            } else {
                $driver_document = UserDocuments('Driver',$user,0);
                $result = json_decode($driver_document, true);
                foreach ($result as $key => $value) {
                    if($value['document'] == ''){
                        $rules['file_'.$value['id']] = 'required|mimes:jpg,jpeg,png,gif';
                        $attributes['file_'.$value['id']] = $value['document_name'];
                    }
                    if($value['expiry_required']==1) {
                        $rules['expired_date_'.$value['id']] = 'required|date|date_format:Y-m-d|after_or_equal:today';
                        $attributes['expired_date_'.$value['id']] = 'Expired Date';
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules,$messages, $attributes);
            if($request->mobile_number!="") {
                $validator->after(function ($validator) use($request) {
                    $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('country_id', $request->country_id)->where('id','!=', $request->id)->count();
                    if($user) {
                       $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                    }
                });
            }
           
            $validator->after(function ($validator) use($request) {
                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=', $request->id)->count();
                if($user_email) {
                    $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });

            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user = User::find($request->id);
            if($request->status =="Active" && !$user->vehicle){
                flashMessage('danger', 'Please ensure the driver has atleast one default vehicle, if not you can\'t activate.');
                return back();
            }

            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->status       = $request->status;
            $user->country_code = $request->country_code;
            $user->gender       = $request->gender;
            if($request->mobile_number!="") {
                $user->mobile_number = $request->mobile_number;
            }
            $user->user_type    = $request->user_type;
            if($request->password != '') {
                $user->password = $request->password;
            }
            if(LOGIN_USER_TYPE=='company') {
                $user->company_id = Auth::guard('company')->user()->id;
            } else {
                $user->company_id = $request->company_name;
            }
            $user->country_id = $request->country_id;
            $user->save();

            Vehicle::where('user_id',$user->id)->update(['company_id'=>$user->company_id]);

            $user_address = DriverAddress::where('user_id',  $user->id)->first();
            if($user_address == '') {
                $user_address = new DriverAddress;
            }
            $user_address->user_id       = $user->id;
            $user_address->address_line1 = $request->address_line1;
            $user_address->address_line2 = $request->address_line2;
            $user_address->city          = $request->city;
            $user_address->state         = $request->state;
            $user_address->postal_code   = $request->postal_code;
            $user_address->save();

            if($user->company_id != null && $user->company_id != 1) {
                $payout_preference = PayoutPreference::firstOrNew(['user_id' => $user->id,'payout_method' => "BankTransfer"]);
                $payout_preference->user_id = $user->id;
                $payout_preference->country = "IN";
                $payout_preference->account_number  = $request->account_number;
                $payout_preference->holder_name     = $request->account_holder_name;
                $payout_preference->holder_type     = "company";
                $payout_preference->paypal_email    = $request->account_number;
                $payout_preference->phone_number    = $request->mobile_number ?? '';
                $payout_preference->branch_code     = $request->bank_code ?? '';
                $payout_preference->bank_name       = $request->bank_name ?? '';
                $payout_preference->bank_location   = $request->bank_location ?? '';
                $payout_preference->payout_method   = "BankTransfer";
                $payout_preference->address_kanji   = json_encode([]);
                $payout_preference->save();

                $payout_credentials = PayoutCredentials::firstOrNew(['user_id' => $user->id,'type' => "BankTransfer"]);
                $payout_credentials->user_id = $user->id;
                $payout_credentials->preference_id = $payout_preference->id;
                $payout_credentials->payout_id = $request->account_number;
                $payout_credentials->type = "BankTransfer";                
                $payout_credentials->default = 'yes';
                $payout_credentials->save();
            }

            $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
            $target_dir = '/images/users/'.$user->id;
            $target_path = asset($target_dir).'/';

            $driver_doc = CheckDocument('Driver',$request->country_code);
            if($driver_doc){
                foreach ($driver_doc as $key => $value) {
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

                        $user_doc = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->where('document_id',$value->id)->first();

                        if($user_doc == ''){
                            $user_doc = new DriverDocuments;
                        }
                        $user_doc->user_id = $user->id;
                        $user_doc->document_id = $value->id;
                        $user_doc->document = $target_path.$upload_result['file_name'];
                        $document_status = $value->doc_name."_status";
                        $user_doc->status = $request->$document_status; 
                        $user_doc->save();
                    }                   
                }

                $oldDocuments = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->whereNotIn('document_id',$driver_doc->pluck('id')->toArray())->pluck('id');
                /*Delete document from table and folder*/
                if(count($oldDocuments) > 0){
                    foreach ($oldDocuments as $key => $value) {
                        $driver = DriverDocuments::find($value);
                        $driver_doc = resolve('App\Contracts\ImageHandlerInterface');
                        $driver_doc->delete($driver->document,['file_path' => '/images/users/'.$user->id.'/']);
                        $driver->delete();
                    }
                }
                /*End Here*/

                foreach ($driver_doc as $key => $value) {
                    $document_status = $value->doc_name."_status";
                    $user_doc = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->where('document_id',$value->id)->first();
                    $user_doc->status = $request->$document_status; 
                    $expired_date_key = 'expired_date_'.$value->id;
                    $user_doc->expired_date = $request->$expired_date_key;
                    $user_doc->save();
                }
            }
            flashMessage('success', 'Updated Successfully');
        }
        return redirect(LOGIN_USER_TYPE.'/driver');
    }

    /**
     * Delete Driver
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function delete(Request $request)
    {
        $result= $this->canDestroy($request->id);

        if($result['status'] == 0) {
            flashMessage('error',$result['message']);
            return back();
        }
        $driver_owe_amount = DriverOweAmount::where('user_id',$request->id)->first();
        if($driver_owe_amount->amount == 0) {
            $driver_owe_amount->delete();
        }
        try {
            User::find($request->id)->delete();
        }
        catch(\Exception $e) {
            $driver_owe_amount = DriverOweAmount::where('user_id',$request->id)->first();
            if($driver_owe_amount == '') {
                DriverOweAmount::create([
                    'user_id' => $request->id,
                    'amount' => 0,
                    'currency_code' => 'USD',
                ]);
            }
            flashMessage('error','Driver have some trips, So can\'t delete this driver');
            // flashMessage('error',$e->getMessage());
            return back();
        }

        flashMessage('success', 'Deleted Successfully');
        return redirect(LOGIN_USER_TYPE.'/driver');
    }

    // Check Given User deletable or not
    public function canDestroy($user_id)
    {
        $return  = array('status' => '1', 'message' => '');

        //Company can delete only this company's drivers.
        if(LOGIN_USER_TYPE=='company') {
            $user = User::find($user_id);
            if ($user->company_id != Auth::guard('company')->user()->id) {
                $return = ['status' => 0, 'message' => 'Invalid ID'];
                return $return;
            }
        }

        $driver_trips   = Trips::where('driver_id',$user_id)->count();
        $user_referral  = ReferralUser::where('user_id',$user_id)->orWhere('referral_id',$user_id)->count();

        if($driver_trips) {
            $return = ['status' => 0, 'message' => 'Driver have some trips, So can\'t delete this driver'];
        }
        else if($user_referral) {
            $return = ['status' => 0, 'message' => 'Rider have referrals, So can\'t delete this driver'];
        }
        return $return;
    }


}
