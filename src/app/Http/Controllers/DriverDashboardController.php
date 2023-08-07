<?php

/**
 * Dashboard Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Dashboard
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\Request as RideRequest;
use App\Models\ProfilePicture;
use App\Models\DriverDocuments;
use App\Models\Documents;
use App\Models\Vehicle;
use App\Models\Country;
use Auth;
use DB;
use Validator;
use PDF;
use App\Models\MakeVehicle;
use App\Models\VehicleModel;
use App\Models\CarType;
use App\Models\FilterObject;

class DriverDashboardController extends Controller
{
    public function __construct()
    {
        $this->invoice_helper = resolve('App\Http\Helper\InvoiceHelper');
        $this->otp_helper = resolve('App\Http\Helper\OtpHelper');
    }

    /*
    * Driver Profile
    */
	public function driver_profile()
    {
        $data['result'] = Auth::user();
        return view('driver_dashboard.driver_profile',$data);
    }

    public function showvehicle(Request $request) {
        $data['vehicle_documents'] = Vehicle::where('user_id',Auth::id())->get();
        return view('driver_dashboard.vehicle',$data);
    }

    public function add_vehicle()
    {
        $data['result'] = Auth::user();
        $data['make'] = MakeVehicle::Active()->pluck('make_vehicle_name','id')->toArray();
        $data['model'] = VehicleModel::Active()->pluck('model_name','id')->toArray();
        $data['vehicle_type'] = CarType::where('status','Active')->get();
        $data['documents'] = CheckDocument('Vehicle',Auth::user()->country_code);
        return view('driver_dashboard.add_vehicle_new',$data);
    }

    public function makeListValue(Request $request)
    {
        $model = VehicleModel::Active()->where('vehicle_make_id',$request->make_id)->pluck('model_name','id')->toArray();
        return response()->json($model);
    }

    public function edit_vehicle(Request $request)
    {
        $user = User::find(Auth::id());
        $data['result'] = Vehicle::findVehicleExist($request->id,$user->id);

        if(!$data['result']){
            return view('errors.404');
            // flashMessage('danger', 'Invalid Id');
            // return back();
        }
       
        $vehicle_doc = CheckDocument('Vehicle',$user->country_code);
        if($vehicle_doc){
          $document = DriverDocuments::where('type','Vehicle')->where('vehicle_id',$request->id)->where('user_id',$user->id)->whereNotIn('document_id',$vehicle_doc->pluck('id')->toArray())->pluck('id');
            if(count($document) > 0){
                foreach ($document as $key => $value) {
                    $vehicle = DriverDocuments::find($value);
                    $driver_doc = resolve('App\Contracts\ImageHandlerInterface');
                    $driver_doc->delete($vehicle->document,['file_path' => '/images/vehicle/'.$request->id.'/']);
                    $vehicle->delete();
                }
            }
        }

        $data['make'] = MakeVehicle::Active()->pluck('make_vehicle_name','id')->toArray();
        $data['model'] = VehicleModel::Active()->pluck('model_name','id')->toArray();
        $data['vehicle_type'] = CarType::where('status','Active')->get();
        $documents  = UserDocuments('Vehicle',$user,$data['result']->id);
        $data['vehicle_documents'] = json_decode($documents);
        $data['options'] = FilterObject::options('vehicle',$request->id);
        return view('driver_dashboard.edit_vehicle',$data);
    }

    public function update_vehicle(Request $request) {

        $rules = array(
            'vehicle_make_id'   => 'required',
            'vehicle_model_id'  => 'required',
            'color'             => 'required',
            'year'              => 'required',
            'vehicle_type'      => 'required',
            'handicap'          => 'required',
            'child_seat'        => 'required',
            'request_from'      => 'required',
        );

        $vehicle_id = '';
        if(!$request->hasFile('vehicle_id') && array_key_exists('vehicle_id', $request->post())) {
            $vehicle_id = $request->vehicle_id;
        } elseif($request->hasFile('vehicle_id') && array_key_exists('vehicle_id', $request->post())) {
            $vehicle_id = $request->post()['vehicle_id'];
        }

        if($vehicle_id) {
            $rules['vehicle_number'] = 'required|unique:vehicle,vehicle_number,'.$vehicle_id;
        } else {
            $rules['vehicle_number'] = 'required|unique:vehicle';
        }
        
        $messages = array(
            'required' => ':attribute is required.',
        );

        $user = User::find(Auth::id());
        $vehicle_doc = CheckDocument('Vehicle',Auth::user()->country_code);
        if($vehicle_id =='') {            
            if($vehicle_doc->count() > 0){
                foreach ($vehicle_doc as $key => $value) {
                    $rules[$value->doc_name] = 'required|mimes:jpg,jpeg,png,gif';
                    if($value->expire_on_date=='Yes') {
                        $rules['expired_date_'.$value->id] = 'required|date';
                        $attributes['expired_date_'.$value->id] = 'expired date';
                    }
                }
            }
        } else {
            $vehicle_documents = UserDocuments('Vehicle',$user,$vehicle_id);
            $result = json_decode($vehicle_documents, true);
            foreach ($result as $key => $value) {
                if($value['document'] == ''){
                    $rules[$value['doc_name']] = 'required|mimes:jpg,jpeg,png,gif';
                }
                if($value['expiry_required']=='1') {
                    if($value['expired_date'] == '' || $value['expired_date']=='0000-00-00') {
                        $rules['expired_date_'.$value['id']] = 'required|date';
                        $attributes['expired_date_'.$value['id']] = 'Expired Date';
                    }
                }
            } 
        }

        $validator = Validator::make($request->all(),$rules,$messages,$attributes ?? []);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user_id = Auth::id();
        $type_name = '';
        foreach($request->vehicle_type as $vehicl_id) {
            $delimeter = ($type_name!='') ? ',' : '';
            $car_name = CarType::find($vehicl_id)->car_name;
            $type_name .= $delimeter.$car_name;
        }

        $make_name = MakeVehicle::whereId($request->vehicle_make_id)->value('make_vehicle_name');
        $model_name = VehicleModel::whereId($request->vehicle_model_id)->value('model_name');

        $other_update = 0;

        if(!$vehicle_id) {
            $other_update = 1;
            $vehicle = new Vehicle; 
        } else {
            $vehicle = Vehicle::find($vehicle_id);

            $request_option = FilterObject::exist('vehicle',$vehicle_id,1) ? '1':'0';
            $handicap = FilterObject::exist('vehicle',$vehicle_id,2) ? '1':'0';
            $child_seat = FilterObject::exist('vehicle',$vehicle_id,3) ? '1':'0';

            if($request->vehicle_number!=$vehicle->vehicle_number || $request->vehicle_make_id!=$vehicle->vehicle_make_id || $request->vehicle_model_id!=$vehicle->vehicle_model_id || $request->year!=$vehicle->year || $request->color!=$vehicle->color) {
                $other_update = 1;
            }
        }


        $vehicle->user_id           = $user_id;
        $vehicle->company_id        = Auth::user()->company_id;        
        $vehicle->vehicle_name      = $make_name.' '.$model_name;           
        $vehicle->vehicle_id        = implode(',', $request->vehicle_type);
        $vehicle->vehicle_type      = $type_name;
        $vehicle->vehicle_make_id   = $request->vehicle_make_id; 
        $vehicle->vehicle_model_id  = $request->vehicle_model_id; 
        $vehicle->color             = $request->color;
        $vehicle->year              = $request->year;
        $vehicle->vehicle_number    = $request->vehicle_number;
        $vehicle->save();

        $options = array();
        if($request->has('request_from') && $request->request_from=='1') {
            $options[] = 1;
        }
        if($request->handicap=='1') {
            $options[] = 2;
        }
        if($request->child_seat=='1') {
            $options[] = 3;
        }
        $filter_insert = FilterObject::optionsInsert('vehicle',$vehicle->id,$options);

        if($vehicle_doc->count()){
            $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
            $target_dir = '/images/vehicle/'.$vehicle->id;
            $target_path = asset($target_dir).'/';
            foreach ($vehicle_doc as $key => $value){
                if($request->hasFile($value->doc_name)){
                    $document_name = $value->doc_name;
                    $document = $request->file($document_name);
                    $extension = $document->getClientOriginalExtension();
                    $file_name = $document_name."_".time().".".$extension;
                    $options = compact('target_dir','file_name');
                    $upload_result = $image_uploader->upload($document,$options);

                    if(!$upload_result['status']) {
                        flashMessage('danger', $upload_result['status_message']);
                        return back();
                    }

                    $vehicle_doc_update = DriverDocuments::where('type','Vehicle')->where('vehicle_id',$vehicle->id)->where('user_id',$user_id)->where('document_id',$value->id)->first();

                    if($vehicle_doc_update == ''){
                        $vehicle_doc_update = new DriverDocuments;
                    }
                    $vehicle_doc_update->type       = 'Vehicle';
                    $vehicle_doc_update->vehicle_id = $vehicle->id;
                    $vehicle_doc_update->user_id    = $user_id;
                    $vehicle_doc_update->document_id= $value->id;
                    $vehicle_doc_update->document   = $target_path.$upload_result['file_name'];
                    $vehicle_doc_update->status     = '0';
                    $expired_date_key               = 'expired_date_'.$value->id;
                    $vehicle_doc_update->expired_date = $request->$expired_date_key;
                    $vehicle_doc_update->save();    
                    $other_update = 1;             
                }
            }

            if($vehicle_id !='') {
                $expired_date_key = 'expired_date_'.$value->id;

                $vehicleArr = DriverDocuments::where('type','Vehicle')->where('vehicle_id',$vehicle->id)->where('user_id',$user->id)->where('document_id',$value->id)->first();

                if($request->$expired_date_key != $vehicleArr->expired_date) {
                    $vehicleArr->status = '0'; 
                    $vehicleArr->expired_date = $request->$expired_date_key;
                    $vehicleArr->save();
                    $other_update = 1;  
                }
            }
        }

        if($other_update==1) {

            $user = User::find($user->id);
            if(!$user->vehicle || $vehicle->default_type=='1') {
                $user->status = UserStatusUpdate($user);
                $user->save();
            }

            $vehicle->is_active = '0';
            $vehicle->status = "Inactive";
            $vehicle->default_type = '0';

            if(isLiveEnv()) {
                $vehicle_documents = $user->driver_documents('Vehicle')->count();
                if($user->vehicles->count()==1 && $vehicle_documents) {
                    $vehicle->is_active = '1';
                    $vehicle->status = "Active";
                    $vehicle->default_type = '1';
                }
            }
            
            $vehicle->save();
        }

        flashMessage('success', trans('messages.user.update_success'));
        return redirect('vehicle/'.$user_id);
    }

    public function default_vehicle(Request $request) {

        $user_id = Auth::user()->id;

        $vehicle = Vehicle::find($request->id);
        if($vehicle->status == 'Inactive'){
            flashMessage('danger',trans('messages.driver_dashboard.default_vehicle_inactive_error'));
            return back();            
        }

        $default = checkDefault($user_id,$request->id,'1');
        if($default==1) {
            flashMessage('danger',trans('messages.driver_dashboard.default_vehicle_intrip_error'));
            return back();
        }

        Vehicle::where('user_id',$user_id)->update(['default_type'=>'0']);

        $vehicle->default_type = '1';
        $vehicle->save();

        flashMessage('success',trans('messages.driver_dashboard.default_success_msg'));
        return redirect('vehicle/'.$user_id);

    }

    public function delete_vehicle(Request $request)
    {    
        $vehicle = Vehicle::find($request->id);
        if($vehicle->default_type == '1'){
           flashMessage('danger', trans('messages.user.default_vehicle_delete_msg')); 
        }else{
            $documentDelete = DriverDocuments::where('vehicle_id',$request->id)->get();
            if($documentDelete){
                foreach ($documentDelete as $key => $value) {
                    $driver = DriverDocuments::find($value->id);
                    $driver_doc = resolve('App\Contracts\ImageHandlerInterface');
                    $driver_doc->delete($driver->document,['file_path' => '/images/users/'.$value->user_id.'/']);
                    $driver->delete();
                }
            }
            $vehicle->delete();
            $filters_delete = FilterObject::whereObjectId($request->id)->delete();
            flashMessage('success', trans('messages.user.delete_success'));        
        }
        return back();
    }

    /**
     * Driver Download invoice Page
     */
    public function download_invoice(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);
        
        $pdf = PDF::loadView('dashboard.download_invoice', compact('trip','invoice_data'));

        set_time_limit(300); // Extends to 5 minutes.
        return $pdf->download('invoice.pdf');
    }

    /**
    * Driver print invoice Page
    */
    public function print_invoice(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);

        return view('dashboard.print_invoice',compact('trip','invoice_data'));
    }

    /**
    *    Driver Profile update
    **/
    public function driver_update_profile(Request $request)
    {
        $rules = array(
            'email'             => 'required|email',
            'mobile_number'     => 'required|numeric|regex:/[0-9]{6}/',
            'city'              => 'required',
        );
       
        $messages = array(
            'required' => ':attribute '.trans('messages.home.field_is_required').'',
            'mobile_number.regex'   => trans('messages.user.mobile_no'),
        );

        $attributes = array(
            'email'         => trans('messages.user.email'),
            'mobile_number' => trans('messages.profile.phone'),
            'city' => trans('messages.account.city'),
        );

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
      
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=',$request->id)->count();

        if($user_email) {
            return back()->withErrors(['email' => trans('messages.user.email_exists')])->withInput();
        }

        $user = User::find($request->id);
        $user->email = $request->email;
        $user->save();

        $driver_address = DriverAddress::where('user_id',$user->id)->first();
        if(!$driver_address) {
            $driver_address = new DriverAddress;
            $driver_address->user_id = $user->id;
        }
        $driver_address->city = $request->city ?? '';
        $driver_address->address_line1 = $request->address_line1 ?? '';
        $driver_address->address_line2 = $request->address_line2 ?? '';
        $driver_address->state = $request->state ?? '';
        $driver_address->postal_code = $request->postal_code ?? '';
        $driver_address->save();

        flashMessage('success', trans('messages.user.update_success'));

        return redirect('driver_profile');
    }

    /*
    * Profile upload
    */
    public function profile_upload(Request $request)
    {
        $user = Auth::user();
        $user_profile_image = ProfilePicture::find($user->id);
        
        if(!$user_profile_image) {
            $user_profile_image = new ProfilePicture;
            $user_profile_image->user_id = $user->id;
        }

        $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
        $target_dir = '/images/users/'.$user->id;

        if(!$request->hasFile('file')) {
            return [
                'success' => 'false',
                'status_message' => trans('messages.user.invalid_file_type')
            ];
        }

        $image = $request->file('file');

        $extension = $image->getClientOriginalExtension();
        $file_name = "profile_pic_".time().".".$extension;
        $compress_size = array(
            ["height" => 225, "width" => 225],
            ["height" => 450, "width" => 250],
        );
        $options = compact('target_dir','file_name','compress_size');

        $upload_result = $image_uploader->upload($image,$options);
        if(!$upload_result['status']) {
            return response()->json([
                'status_code'       => "0",
                'status_message'    => $upload_result['status_message'],
            ]);
        }

        $user_profile_image->photo_source = 'Local';
        $user_profile_image->src = $file_name;
        $user_profile_image->save();

        return ['success' => 'true','profile_url' => $user_profile_image->src,'status_message'=>'Uploaded Successfully'];
    }


    public function documents(Request $request) {
        $data['user'] = $user = Auth::user();
        $data['image'] = $user->profile_picture->src == '' ? "https://d1w2poirtb3as9.cloudfront.net/default.jpeg" : $user->profile_picture->src;
        $driver_documents = UserDocuments('Driver',$user,0);
        $data['driver_documents'] = json_decode($driver_documents);
        $driver_doc = CheckDocument('Driver',$user->country_code);
        if($driver_doc){            
            $document = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->whereNotIn('document_id',$driver_doc->pluck('id')->toArray())->pluck('id');
            $this->deletDriveDocument($document,$user->id);
        }
        return view('driver_dashboard.documents',$data);
    }

    public function driver_document_upload(Request $request)
    {        
        $user = User::find(Auth::id());
        $driver_doc = CheckDocument('Driver',$user->country_code);
        $driver_documents = UserDocuments('Driver',$user,0);
        $result = json_decode($driver_documents, true);

        $rules = array();
        foreach ($result as $key => $value) {
            if($value['document'] == ''){
                $rules[$value['doc_name']] = 'required|mimes:jpg,jpeg,png,gif';
            }
            
            if($value['expiry_required']=='1') {
                if($value['expired_date'] == '' || $value['expired_date']=='0000-00-00') {
                    $rules['expired_date_'.$value['id']] = 'required|date';
                    $attributes['expired_date_'.$value['id']] = 'Expired Date';
                }
            }
        }

        $messages =array(
            'required'  => ':attribute is required.',
        );

        $validator = Validator::make($request->all(),$rules,$messages,$attributes ?? []);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if($driver_doc->count()){
            $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
            $target_dir = '/images/users/'.$user->id;
            $target_path = asset($target_dir).'/';
            foreach ($driver_doc as $key => $value){
                if($request->hasFile($value->doc_name)){

                    $document_name = $value->doc_name;
                    $document = $request->file($document_name);
                    $extension = $document->getClientOriginalExtension();
                    $file_name = $document_name."_".time().".".$extension;
                    $options = compact('target_dir','file_name');
                    $upload_result = $image_uploader->upload($document,$options);
                    
                    if(!$upload_result['status']) {
                        flashMessage('danger', $upload_result['status_message']);
                        return back();
                    }
                    
                    $driver_document = DriverDocuments::where('type','Driver')->where('user_id',$user->id)->where('document_id',$value->id)->first();
                    
                    if($driver_document == ''){
                        $driver_document            = new DriverDocuments;
                    }
                    $driver_document->type          = 'Driver';
                    $driver_document->vehicle_id    = 0;
                    $driver_document->user_id       = $user->id;
                    $driver_document->document_id   = $value->id;
                    $driver_document->document      = $target_path.$upload_result['file_name'];
                    $driver_document->status        = '0';
                    $driver_document->save();                 
                }
                    
                $driver_document = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->where('document_id',$value->id)->first();

                $expired_date_key = 'expired_date_'.$value->id;
                if($request->$expired_date_key != $driver_document->expired_date) {
                    $driver_document->status = '0'; 
                    $driver_document->expired_date = $request->$expired_date_key;
                    $driver_document->save();
                }
            }

            $document = DriverDocuments::where('type','Driver')->where('vehicle_id',0)->where('user_id',$user->id)->whereNotIn('document_id',$driver_doc->pluck('id')->toArray())->pluck('id');
            $this->deletDriveDocument($document,$user->id);

            $user->status  = UserStatusUpdate($user);
            $user->save();
        }
        flashMessage('success', trans('messages.user.update_success'));
        return redirect('documents/'.$user->id);
    }


    public function deletDriveDocument($document, $user_id)
    {
        if(count($document) > 0){
            foreach ($document as $key => $value) {
                $driver = DriverDocuments::find($value);
                $driver_doc = resolve('App\Contracts\ImageHandlerInterface');
                $driver_doc->delete($driver->document,['file_path' => '/images/users/'.$user_id.'/']);
                $driver->delete();
            }
        }
    }

    /*
    * Driver payment page
    */
    public function driver_payment()
    {
        $data['total_earnings'] = Trips::where('driver_id',Auth::id())
                     ->where('status','Completed')
                     ->get()
                     ->sum('company_driver_earnings');

        $total_count = RideRequest::where('driver_id',Auth::id())->count();
        $acceptance_count = RideRequest::where('driver_id',Auth::id())->where('status','Accepted')->count();
        if($acceptance_count != '0' || $total_count != '0') {
            $data['acceptance_rate'] = round(($acceptance_count/$total_count)*100).'%';
        }
        else {
            $data['acceptance_rate'] = '0%';
        }
        $data['completed_trips'] = Trips::where('driver_id',Auth::id())->where('status','Completed')->count();
        $data['cancelled_trips'] = Trips::where('driver_id',Auth::id())->where('status','Cancelled')->count();
        $data['all_trips'] = Trips::with(['currency'])->where('driver_id',Auth::id())->orderBy('created_at', 'desc');
        $data['all_trips'] = $data['all_trips']->paginate(4)->toJson();
        
        return view('driver_dashboard.driver_payment',$data);
    }

    /*
    * Driver invoice page
    */
    public function driver_invoice(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);
        $all_invoice = false;

        return view('driver_dashboard.driver_invoice',compact('trip','invoice_data','all_invoice')); 
    }

    /*
    * Show all trips
    */
    public function show_invoice(Request $request)
    {
        if($request->limit == 'undefined') {
            return ['status' => false];
        }

        if($request->limit) {
            $data = Trips::where('driver_id',Auth::id())->with(['currency'])->orderBy('created_at', 'desc')->paginate($request->limit);
            return $data;
        }
        $data['trips'] = Trips::where('driver_id',Auth::id())->with(['currency'])->orderBy('created_at', 'desc')->paginate(10)->toJson();
        $data['all_invoice'] = true;
        return view('driver_dashboard.driver_invoice',$data);
    }

    /*
    * Driver Trip Details
    */
    public function driver_trip_detail(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);
        return view('driver_dashboard.driver_trip_detail',compact('trip','invoice_data'));
    }

    /*
    * Get payment information
    */
    public function ajax_payment(Request $request)    
    {
        if($request->data == 'all') {
            $data['completed_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Completed')
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Cancelled')
                                ->count();
            return $data;
        }
        elseif($request->data == 'current') {
            $from = date('Y-m-d');
            $to   = date('Y-m-d');
            $data['completed_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Completed')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Cancelled')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            return $data;
        }
        elseif($request->data == 'all_trips') {
            if($request->begin_trip != '' || $request->end_trip != '')
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())
                        ->where('created_at','>=',$request->begin_trip)
                        ->where('created_at','<=',$request->end_trip)->orderBy('created_at', 'desc');
            else
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())->orderBy('created_at', 'desc');

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        elseif($request->data == 'completed_trips') {
            if($request->begin_trip != '' || $request->end_trip != '') {
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())
                    ->where('created_at','>=',$request->begin_trip)
                    ->where('created_at','<=',$request->end_trip)
                    ->where('status','Completed')->orderBy('created_at', 'desc');
            }
            else {
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())->where('status','Completed')->orderBy('created_at', 'desc');
            }

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        elseif($request->data == 'cancelled_trips') {   
            if($request->begin_trip != '' || $request->end_trip != '') {
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())
                    ->where('created_at','>=',$request->begin_trip)
                    ->where('created_at','<=',$request->end_trip)
                    ->where('status','Cancelled')->orderBy('created_at', 'desc');
            }
            else {
                $data = Trips::with(['currency'])->where('driver_id',Auth::id())
                    ->where('status','Cancelled')->orderBy('created_at', 'desc');
            }

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        else {
            $date = explode('/', $request->data);
            $from = date('Y-m-d',strtotime($date[0]));
            $to   = date('Y-m-d',strtotime($date[1]));
            $data['completed_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Completed')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',Auth::id())
                                ->where('status','Cancelled')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            return $data;
        }
    }

    /** 
    * Change Mobile Number
    **/
    public function change_mobile_number(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
        );

        $messages = array(
            'required'              => ':attribute '.trans('messages.home.field_is_required').'',
            'mobile_number.regex'   => trans('messages.user.mobile_no'),
        );

        $attributes = array(
            'mobile_number' => trans('messages.user.mobile'),
        );

        if($request->request_type == 'send_otp') {

            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                
            $validator->after(function ($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('country_code', $request->country_code)->where('user_type', Auth::user()->user_type)->count();

                if ($user) {
                    $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }
            });

            if (count($validator->errors())) {
                return json_encode(['status_code' => 0,'messages' => $validator->errors()]);
            }

            $otp_responce = $this->otp_helper->sendOtp($request->mobile_number,$request->country_code);
            if ($otp_responce['status_code'] == 0) {
                $data = [
                    'status_code' => 0,
                    'messages' => ['mobile_number' => [$otp_responce['message']]],
                ];
                return json_encode($data);
            }

            $return_data = ['status_code' => 1,'messages' => 'success'];
            if(canDisplayCredentials()) {
                $return_data['session_data'] = [
                    'signup_mobile' => session('signup_mobile'),
                    'signup_country_code' => session('signup_country_code'),
                    'signup_otp' => session('signup_otp'),
                ];
            }

            return json_encode($return_data);
        } elseif($request->request_type == 'check_otp') {

            if(site_settings('otp_verification')) {
                $rules['otp'] = 'required';

                $validator = Validator::make($request->all(), $rules, $messages, $attributes);

                if($validator->fails()) {
                    $messages = $validator->messages();
                    if($messages->has('mobile_number')) {
                        return json_encode([
                            'status_code' => 1,
                            'message' => $validator->messages()->first()
                        ]);
                    }
                    if($messages->has('otp')) {
                        return json_encode([
                            'status_code' => 0,
                            'message' => $validator->messages()->first()
                        ]);
                    }
                }
            } else {
                $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                
                $validator->after(function ($validator) use($request) {
                    $user = User::where('mobile_number', $request->mobile_number)->where('country_code', $request->country_code)->where('user_type', Auth::user()->user_type)->count();

                    if ($user) {
                        $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                    }
                });

                if (count($validator->errors())) {
                    return json_encode(['status_code' => 0,'messages' => $validator->errors()]);
                }
            }

            $check_otp_responce = $this->otp_helper->checkOtp($request->otp,$request->mobile_number,$request->country_code);
            
            if($check_otp_responce['status_code'] == 1) {
                $user                   = Auth::user();
                $user->mobile_number    = session('signup_mobile');
                $user->country_code     = session('signup_country_code');

                $country = Country::whereShortName($request->country_name)->first();
                $user->country_id   = $country->id;

                $user->save();
            }
            $check_otp_responce['status_code'] = 2;
            return json_encode($check_otp_responce);
        }
    }
}
