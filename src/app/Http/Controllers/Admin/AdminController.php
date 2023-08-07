<?php

/**
 * Admin Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\AdminusersDataTable;
use Auth;
use DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\Country;
use App\Models\Role;
use App\Models\Currency;
use App\Models\Company;
use App\Http\Start\Helpers;
use Validator;
use Session;

class AdminController extends Controller
{
    /**
     * Load Index View for Dashboard
     *
     * @return view index
     */
    public function index()
    {
        $users = DB::table('users');
        $data['users_count'] = with(clone $users)->count();

        //if login user is company then only get company user
        $data['total_driver'] = with(clone $users)->where('user_type','Driver')
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { 
                $query->where('company_id',Auth::guard('company')->user()->id);
            }
        })
        ->count();
        $data['total_rider'] = with(clone $users)->where('user_type','Rider')->count();

        //if login user is company then only get company drivers
        $data['today_driver_count'] = with(clone $users)->whereDate('created_at', '=', date('Y-m-d'))
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { 
                $query->where('company_id',Auth::guard('company')->user()->id);
            }
        })
        ->where('user_type','Driver')
        ->count();
        $data['today_rider_count'] = with(clone $users)->whereDate('created_at', '=', date('Y-m-d'))->where('user_type','Rider')->count();

        $default_currency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {  //if login user is company then get session currency
            $default_currency = Currency::whereCode(session()->get('currency'))->first();
        }
        $data['currency_code'] = $default_currency->symbol;
        $currency_rate = $default_currency->rate;
        
        $all_trips = DB::table('trips')
            ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            });

        $revenue_trips = DB::table('trips')
            ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })->select(DB::raw('( 
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.'))
                        - sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))
                        + sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.'))  ) as company_revenue '),
                    DB::raw('( sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.peak_amount / currency.rate) * '.$currency_rate.')) - sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.schedule_fare / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))  ) as admin_revenue ')
                        );


        if(LOGIN_USER_TYPE=='company') {  //if login user is company then revenue calculated from company trips
            $data['today_revenue'] = with(clone $revenue_trips)
                ->whereDate('trips.created_at', '=', date('Y-m-d'))
                ->where('trips.status','Completed')
                ->join('users', function($join) {
                    $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
                })->first()->company_revenue;
        } else {
            $data['today_revenue'] =  with(clone $revenue_trips)
                ->whereDate('trips.created_at', '=', date('Y-m-d'))
                ->where('trips.status','Completed')
                ->first()->admin_revenue;

        }
        //if login user is company then get only company driver's trip
        $today_trips = with(clone $all_trips)->whereDate('trips.created_at', '=', date('Y-m-d'));
        if(LOGIN_USER_TYPE=='company') {   
            $today_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['today_trips'] = $today_trips->count();

        //if login user is company then get only company driver's trip

        $total_trips = with(clone $all_trips);
        if(LOGIN_USER_TYPE=='company') {   
            $total_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['total_trips'] = $total_trips->count();


        //if login user is company then get only company driver's trip

        $total_success_trips = with(clone $all_trips)->where('trips.status','Completed');
        if(LOGIN_USER_TYPE=='company') {   
            $total_success_trips->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            });
        }
        $data['total_success_trips'] = $total_success_trips->count();

        $total_revenue = with(clone $revenue_trips)->where('trips.status','Completed');
        if(LOGIN_USER_TYPE=='company') {   
            $total_revenue->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            });
        }

        $data['total_revenue'] = LOGIN_USER_TYPE=='company' ? $total_revenue->first()->company_revenue:$total_revenue->first()->admin_revenue;
        
     

        if(LOGIN_USER_TYPE=='company') {
            $admin_driver_paid_amount = with(clone $revenue_trips)
            ->where('trips.status','Completed')
            ->where('driver_payout','>',0)
            ->where('payment_mode','<>','Cash')
            ->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            })
            ->join('payment', function($join) {
                $join->on('trips.id', '=', 'payment.trip_id');
            })
            ->select(DB::raw('sum(ROUND((trips.driver_payout/currency.rate) * '.$currency_rate.')) as driver_payout'));

            $admin_paid_amount = with(clone $admin_driver_paid_amount)->where('payment.admin_payout_status','Paid')->first();
            $admin_pending_amount = with(clone $admin_driver_paid_amount)->where('payment.admin_payout_status','Pending')->first();

            $data['admin_paid_amount'] = $admin_paid_amount ? $admin_paid_amount->driver_payout:0;
            $data['admin_pending_amount'] = $admin_pending_amount ? $admin_pending_amount->driver_payout:0;

        }
       

        $data['recent_trips'] = RideRequest::
        with(['trips','users','car_type','request'])
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') { //if login user is company then get only company driver's trip
                $query->whereHas('driver',function($q1){
                    $q1->where('company_id',Auth::guard('company')->user()->id);
                });
            }
        })
        ->groupBy('group_id')
        ->orderBy('group_id','desc')
        ->limit(10)->get();


        $quarter1 = ['01', '02', '03'];
        $quarter2 = ['04', '05', '06'];
        $quarter3 = ['07', '08', '09'];
        $quarter4 = ['10', '11', '12'];
        $chart = Trips::
        whereRaw('YEAR(trips.created_at) = ?',[date('Y')])
        ->where('trips.status', 'Completed')
        ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })
        ->select(DB::raw('( 
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.')) 
                        + sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.'))
                        - sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))
                        + sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.'))  ) as company_revenue '),
                    DB::raw('( sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.peak_amount / currency.rate) * '.$currency_rate.')) - sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.schedule_fare / currency.rate) * '.$currency_rate.')) + sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.'))  ) as admin_revenue ')
                        );

        if(LOGIN_USER_TYPE=='company') {   
            $chart->join('users', function($join) {
                $join->on('trips.driver_id', '=', 'users.id')->where('company_id',Auth::guard('company')->user()->id);
            });
        }





        $quarter1_chart=clone($chart);
        $quarter2_chart=clone($chart);
        $quarter3_chart=clone($chart);
        $quarter4_chart=clone($chart);

        //if login user is company then total earning is sum of trip amount .If login user is admin then total revenue is sum of admin commission

        $quarter_amount[1]=floatval($quarter1_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter1)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[2]=floatval($quarter2_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter2)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[3]=floatval($quarter3_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter3)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));
        $quarter_amount[4]=floatval($quarter4_chart->wherein(DB::raw('MONTH(trips.created_at)'),$quarter4)->get()->sum(LOGIN_USER_TYPE=='company'?'company_revenue':'admin_revenue'));

        $chart_array = [];
        $year = date('Y');
        for($quarter=1;$quarter<=4;$quarter++)
        {
            $array['y'] = $year.' Q'.$quarter;
            $array['amount'] = number_format($quarter_amount[$quarter],2,'.','');
            $chart_array[] = $array;
        }
        $data['line_chart_data'] = json_encode($chart_array);

        return view('admin.index', $data);
    }

    /**
     * Load Datatable for Admin Users
     *
     * @param array $dataTable  Instance of AdminuserDataTable
     * @return datatable
     */
    public function view(AdminusersDataTable $dataTable)
    {
        return $dataTable->render('admin.admin_users.view');
    }

    /**
     * Load Login View
     *
     * @return view login
     */
    public function login()
    {
        return view('admin.login');
    }

    /**
     * Add Admin User Details
     *
     * @param array $request    Input values
     * @return redirect     to Admin Users View
     */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['roles'] = Role::all()->pluck('name','id');
            $data['countries'] = Country::codeSelect();

            return view('admin.admin_users.add', $data);  
        }

        if($request->submit) {
            // Add Admin User Validation Rules
            $rules = array(
                'username'      => 'required|unique:admins',
                'email'         => 'required|email|unique:admins',
                'password'      => 'required',
                'role'          => 'required',
                'status'        => 'required',
                'country_code'  => 'required',
                'mobile_number' => 'required|numeric',
            );

            // Add Admin User Validation Custom Names
            $attributes = array(
                'username'      => 'Username',
                'email'         => 'Email',
                'password'      => 'Password',
                'role'          => 'Role',
                'status'        => 'Status',
                'country_code'  => 'Country Code',
                'mobile_number' => 'Mobile Number',
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $admin = new Admin;
            $admin->username = $request->username;
            $admin->email    = $request->email;
            $admin->password = $request->password;
            $admin->status   = $request->status;
            $admin->country_code = Country::find($request->country_code)->phone_code;
            $admin->country_id = $request->country_code;
            $admin->mobile_number   = $request->mobile_number;
            $admin->save();

            $admin->attachRole($request->role); 
           
            flashMessage('success', 'Added Successfully'); 
        }

        return redirect('admin/admin_user');
    }

    /**
     * Update Admin User Details
     *
     * @param array $request    Input values
     * @return redirect     to Admin Users View
     */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result']  = Admin::find($request->id);
            $data['roles'] = Role::all()->pluck('name','id');
            $data['countries'] = Country::codeSelect();
            if($data['result']) {
                return view('admin.admin_users.edit', $data);    
            }
            flashMessage('danger', 'Invalid ID');
            return redirect('admin/admin_user');
        }
        if($request->submit) {
            // Edit Admin User Validation Rules
            $rules = array(
                'username'   => 'required|unique:admins,username,'.$request->id,
                'email'      => 'required|email|unique:admins,email,'.$request->id,
                'country_code'     => 'required',
                'mobile_number'     => 'required|numeric',
                'role'       => 'required',
                'status'     => 'required'
            );

            // Edit Admin User Validation Custom Fields Name
            $attributes = array(
                'username'   => 'Username',
                'email'      => 'Email',
                'country_code' => 'Country Code',
                'mobile_number' => 'Mobile Number',
                'role'       => 'Role',
                'status'     => 'Status'
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $admins = Admin::active()->count();
            if($admins==1 && $request->status=='Inactive') {
                flashMessage('danger', 'You can\'t inactive the last one. Atleast one should be available.');
                return back();
            }

            $admin = Admin::find($request->id);

            $admin->username = $request->username;
            $admin->email    = $request->email;
           $admin->country_code = Country::find($request->country_code)->phone_code;
            $admin->country_id = $request->country_code;
            $admin->mobile_number = $request->mobile_number;
            $admin->status   = $request->status;
            
            if($request->filled("password")) {
                $admin->password = $request->password;
            }
            $admin->save();

            $role_id = Role::role_user($request->id)->role_id;

            if($role_id!=$request->role) {
                $admin->detachRole($role_id);
                $admin->attachRole($request->role);
            }
        
            flashMessage('success', 'Updated Successfully');

            // Redirect to dashboard when current user not have a permission to view admin users
            if(!Auth::guard('admin')->user()->can('manage_admin')) {
                return redirect('admin/dashboard');
            }

        }
        return redirect('admin/admin_user');
    }

    /**
     * Login Authentication
     *
     * @param array $request Input values
     * @return redirect     to dashboard
     */
    public function authenticate(Request $request)
    {
        if($request->getmethod() == 'GET') {
            return redirect()->route('admin_login');
        }

        if ($request->user_type == 'Company') {
            $login_column = is_numeric($request->username)?'mobile_number':'email';

            $company = Company::where($login_column, $request->username)->first();
            if ($company && $company->status != "Inactive") {
                
                $guard = Auth::guard('company')->attempt([$login_column => $request->username, 'password' => $request->password]);
                if ($guard) {
                    return redirect('company/dashboard');
                }
                flashMessage('danger', 'Log In Failed. Please Check Your Email(or)Mobile/Password');
                request()->flashExcept('password');
                return redirect('admin/login')->withInput(request()->except('password'));
            }

        }
        else{
            $admin = Admin::where('username', $request->username)->first();

            if(isset($admin) && $admin->status != 'Inactive') {
                if(Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
                    return redirect()->intended('admin/dashboard');
                }

                flashMessage('danger', 'Log In Failed. Please Check Your Username/Password');
                request()->flashExcept('password');
                return redirect('admin/login')->withInput(request()->except('password'));
            }
        }

        flashMessage('danger', 'Log In Failed. You are Blocked by Admin.');
        request()->flashExcept('password');
        return redirect('admin/login')->withInput(request()->except('password'));
    }

    /**
     * Admin Logout
     */
    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect('admin/login');
    }


    public function delete(Request $request)
    {
        $admins = Admin::active()->count();
        if($admins==1) {
            flashMessage('danger', 'You can\'t delete the last one. Atleast one should be available.');
            return back();
        }

        $admin = Admin::where('id',$request->id)->first();
        if($admin) {
            $roles_user = DB::table('role_user')->where('user_id',$request->id)->delete();
            $admin = $admin->delete();
            flashMessage('success', 'Deleted Successfully');
        } else {
            flashMessage('danger', 'You can\'t able to delete');
        }
        return redirect('admin/admin_user');
    }
}
