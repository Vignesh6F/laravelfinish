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
use App\Models\Rating;
use App\Models\ProfilePicture;
use App\Models\ReferralSetting;
use App\Models\ReferralUser;
use App\Models\Currency;
use App\Models\RiderLocation;
use Validator;
use Auth;
use PDF;

class DashboardController extends Controller
{    
    public function __construct()
    {
        $this->invoice_helper = resolve('App\Http\Helper\InvoiceHelper');
        $this->request_helper = resolve('App\Http\Helper\RequestHelper');
        $this->helper = resolve('App\Http\Start\Helpers');
    }

    /** 
    * Rider Trips page
    **/
	public function trip()
    {   
        session()->forget('Account_kit');
        return view('dashboard.trip');
    }

    /** 
    * Get All Trips using ajax
    **/
    public function ajax_trips(Request $request)
    {
        $user = User::find($request->id);

        if(!$user) {
            return ['status'=>false];
        }
        $search_col = ($user->user_type == 'Rider') ? 'user_id' : 'driver_id';

        if($request->month) {
            $data = explode('-', $request->month);
            $result = Trips::with(['currency','rating'])
                        ->where($search_col,$request->id)
                        ->whereYear('created_at', $data[0])
                        ->whereMonth('created_at', $data[1])
                        ->orderByDesc('created_at');
        }
        else {
            $result = Trips::with(['currency','rating'])->where($search_col,$request->id)->orderByDesc('created_at');
        }
        $result =  $result->paginate(20);
        $result->getCollection()->transformWithAppends(['trip_image']);
        return $result->toJson();
    }

    /** 
    * Rider Profile Page
    **/
    public function profile()
    {
        $data['result'] = Auth::user();
        return view('dashboard.profile',$data);
    }

    /** 
    * Rider Trip Details Page
    **/
    public function trip_detail(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);

        return view('dashboard.trip_detail',compact('trip','invoice_data'));       
    }

    /**
    * Rider Rating
    **/
    public function rider_rating(Request $request)
    {
        $rating = Rating::where('trip_id',$request->trip_id)->first();
        $trips = Trips::where('id',$request->trip_id)->first();
        if(Auth::user()->user_type != 'Rider') {
            return ['success' => 'false'];            
        }
        $data = [   
            'trip_id'       => $request->trip_id,
            'user_id'       => $trips->user_id,
            'driver_id'     => $trips->driver_id,
            'rider_rating'  => $request->rating,
        ];
        $rating = Rating::updateOrCreate(['trip_id' => $request->trip_id], $data);

        Trips::where('id',$request->trip_id)->update(['status'   => 'Payment']);
        return [
            'success' => 'true',
            'user_rating' => $rating->rider_rating
        ];
    }

    /**
    * Rider invoice Page
    **/
    public function trip_invoice(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);

        return view('dashboard.rider_invoice', compact('trip','invoice_data'));
    }

    /**
    * Driver Download invoice Page
    **/
    public function download_rider_invoice(Request $request)
    {
        $trip = Trips::findOrFail($request->id);

        $invoice_data = $this->invoice_helper->getWebInvoice($trip);
        $pdf = PDF::loadView('dashboard.download_rider_invoice', compact('trip','invoice_data'));
        return $pdf->download('invoice.pdf');
    }

    /**
    * Update Profile
    **/
    public function update_profile(Request $request) {
        
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required|email',
            'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
            'profile_image' => 'mimes:jpg,jpeg,png,gif'
        );
       
        $messages = array(
            'required'                => ':attribute '.trans('messages.home.field_is_required').'',
            'mobile_number.regex'   => trans('messages.user.mobile_no'),
        );

        $attributes = array(
            'first_name' => trans('messages.user.firstname'),
            'last_name' => trans('messages.user.lastname'),
            'email' => trans('messages.user.email'),
            'mobile_number' => trans('messages.profile.mobile'),
            'profile_image' => trans('messages.user.profile_image'),
        );

        $validator = Validator::make($request->all(), $rules, $messages,$attributes);
      
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=',$request->id)->count();

        if($user_email) {
            return back()->withErrors(['email' => trans('messages.user.email_exists')])->withInput();
        }

        $user = User::find($request->id);
        
        $user->first_name   = $request->first_name;
        $user->last_name    = $request->last_name;
        $user->email        = $request->email;
        $user->save();

        $this->helper->flash_message('success', trans('messages.user.update_success'));
        return redirect('profile');
    }

    /*
    * Referral related details
    */
    public function referral(Request $request)
    {
        $data['result'] = User::findOrFail(Auth::id());

        $admin_referral_settings = ReferralSetting::where('user_type','Rider')->where('name','apply_referral')->first();
        $data['apply_referral']  = $admin_referral_settings->value;
        
        $default_currency = Currency::active()->defaultCurrency()->first();
        $session_currency = session('currency');
        
        $currency_code = isset($session_currency) ? $session_currency : $default_currency->code;
        $currency_symbol = Currency::original_symbol($currency_code);

        $referral_amount = $currency_symbol .'0';
        if($data['apply_referral']) {
            $referral_amount = $admin_referral_settings->rider_referral_amount;
        }
        $data['rider_referral_amount'] = $referral_amount;
        $referral_users = ReferralUser::where('user_id', Auth::user()->id);

        $data['all_referral_details'] =  $referral_users->paginate(4)->toJson();

        return view('dashboard.referral',$data);
    }

    /*
    * Invite or Referral related details
    */
    public function driver_referral(Request $request)
    {
        $data['result'] = User::adminCompany()->findOrFail(Auth::id());

        $admin_referral_settings = ReferralSetting::where('user_type','Driver')->where('name','apply_referral')->first();
        $data['apply_referral']  = $admin_referral_settings->value;

        $default_currency = Currency::active()->defaultCurrency()->first();
        $session_currency = session('currency');

        $currency_code = isset($session_currency) ? $session_currency : $default_currency->code;
        $currency_symbol = Currency::original_symbol($currency_code);

        $referral_amount = $currency_symbol .'0';
        if( $data['apply_referral']) {
            $referral_amount = $admin_referral_settings->driver_referral_amount;
        }
        $data['driver_referral_amount'] = $referral_amount;

        $referral_users = ReferralUser::where('user_id', Auth::user()->id);

        $data['all_referral_details'] =  $referral_users->paginate(4)->toJson();

        return view('driver_dashboard.referral',$data);
    }

    /** 
    * Get Invite Details using ajax
    **/
    public function ajax_referral_data(Request $request)
    {
        $referral_users = ReferralUser::where('user_id', $request->id);
        $result =  $referral_users->paginate(4)->toJson();

        return $result;
    }

    /** 
    * Import csv data
    **/
    public function importCsvData() {
        $file = fopen(public_path('riders.csv'),"r");

        $column = fgetcsv($file);
        while(!feof($file)){
            $row_data[] = fgetcsv($file);
        }

        $insert_data = array();
        foreach($row_data as $key => $value) {

            if(is_array($value)) {

                $user_data = array();

                $exist_user_id = User::whereCountryId(99)->whereMobileNumber($value[0])->value('id');
                if($exist_user_id)
                    $delete_user = User::find($exist_user_id)->delete();

                $user = new User;
                $user->first_name   = 'Test'.$key;
                $user->last_name    = 'Test'.$key;
                $user->email        = 'test'.$key.'@yopmail.com';
                $user->country_code = '91';
                $user->country_id   = 99;
                $user->mobile_number= $value[0];
                $user->gender       = $key%2==0 ? 1:2;
                $user->password     = $value[1];
                $user->user_type    = 'Rider';
                $user->status       = 'Active';
                $user->save();

                $user_data['id'] = User::whereId($user->id)->value('id');
                $user_data['first_name'] = User::whereId($user->id)->value('first_name');
                $user_data['last_name'] = User::whereId($user->id)->value('last_name');
                $user_data['email'] = User::whereId($user->id)->value('email');
                $user_data['country_code'] = User::whereId($user->id)->value('country_code');
                $user_data['gender'] = User::whereId($user->id)->value('gender');
                $user_data['user_type'] = User::whereId($user->id)->value('user_type');
                $user_data['status'] = User::whereId($user->id)->value('status');
                $insert_data[$key] = $user_data;

                $user_pic = new ProfilePicture;
                $user_pic->user_id      = $user->id;
                $user_pic->src          = "";
                $user_pic->photo_source = 'Local';
                $user_pic->save();

                $location = new RiderLocation;
                $location->user_id          = $user->id;
                $location->home             = '';
                $location->work             = '';
                $location->home_latitude    = '';
                $location->home_longitude   = '';
                $location->work_latitude    = '';
                $location->work_longitude   = '';
                $location->save();
            }
        }

        return json_encode($insert_data);
    }
}
