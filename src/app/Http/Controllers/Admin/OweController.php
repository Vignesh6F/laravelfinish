<?php

/**
 * Owe Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Owe Ammount
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DriverPayment;
use App\Models\DriverOweAmount;
use App\Models\Currency;
use App\Models\DriverOweAmountPayment;
use App\DataTables\OweDataTable;
use App\DataTables\DriverPaymentDataTable;
use App\DataTables\CompanyOweDataTable;
use DB; 
class OweController extends Controller
{
    public function __construct()
    {
        $this->view_data = array();
    }

    /**
     * Load Datatable for Owe Amount
     *
     * @return view file
     */
    public function index(DriverPaymentDataTable $driver_payment, OweDataTable $owe_amount)
    {
        $this->view_data['main_title'] = 'Owe Amount';
        if(LOGIN_USER_TYPE == 'company') {
            $company = auth()->guard('company')->user();

            $default_currency = Currency::whereCode(session()->get('currency'))->first();

        $currency_rate = $default_currency->rate;


        $owe = DB::table('users')
        ->where('company_id',auth('company')->user()->id)
        ->join('trips', function($join) {
            $join->on('users.id', '=', 'trips.driver_id');
        })
        ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })
        ->join('companies', function($join) {
            $join->on('users.company_id', '=', 'companies.id');
        })
        ->join('driver_owe_amounts', function($join) {
            $join->on('users.id', '=', 'driver_owe_amounts.user_id');
        })

        ->join('currency as owe_amounts_currency', function($join) {
                $join->on('driver_owe_amounts.currency_code', '=', 'owe_amounts_currency.code');
            })

        ->whereIn('trips.payment_mode',['Cash & Wallet','Cash'])
        ->whereIn('trips.status',['Payment','Completed'])
        ->select( DB::raw(' sum(ROUND((trips.owe_amount / currency.rate) * '.$currency_rate.',2)) as total_owe_amount '), DB::raw('  sum(ROUND((driver_owe_amounts.amount / owe_amounts_currency.rate) * '.$currency_rate.',2)) as remaining_owe_amount '), DB::raw('sum(ROUND((trips.applied_owe_amount / currency.rate) * '.$currency_rate.',2)) as applied_owe_amount '),DB::raw('Group_concat(trips.owe_amount) as asdasd'),DB::raw('Group_concat(trips.currency_code) as aasdsdasd'))



        ->whereNotNull('company_id')->groupBy('company_id')->first();

        // dd($owe);


            $this->view_data['sub_title'] = 'Manage Payment To Company';
            $this->view_data['currency_code']          = currency_symbol();
            $this->view_data['total_owe_amount']       = $owe ? $owe->total_owe_amount:0;
            $this->view_data['applied_owe_amount']     = $owe ? $owe->applied_owe_amount:0;
            $this->view_data['remaining_owe_amount']   = $owe ? $owe->remaining_owe_amount:0;
            return $driver_payment->render('admin.owe.index',$this->view_data);
        }

        return $owe_amount->setFilterType('overall')->render('admin.owe.index',$this->view_data);
    }

    /**
     * Load Datatable for Company Owe
     *
     * @return view file
     */
    public function company_index(CompanyOweDataTable $owe_datatable)
    {
        if(request()->id != 1) {
            abort(404);
        }
        $this->view_data['main_title'] = 'Owe Amount';
        return $owe_datatable->render('admin.owe.index',$this->view_data);
    }

    public function owe_details(OweDataTable $dataTable,Request $request)
    {
        $type = $request->type;
        $this->view_data['main_title']  = ucfirst($type).' Owe Amount';
        return $dataTable->setFilterType($type)->render('admin.owe.index',$this->view_data);
    }

    public function update_payment(Request $request)
    {
        if(!auth()->guard('company')->check()) {
            abort(404);
        }

        $driver_id = $request->driver_id;
        $payable_amount = $request->payable_amount;
        $currency_code = $request->currency_code;

        if($payable_amount <= 0 ) {
            flashMessage('danger', 'Driver Payment Failed.');
            return back();
        }

        $driver_payment = DriverPayment::firstOrNew(['driver_id' => $driver_id]);

        if($driver_payment->paid_amount > 0) {
            $payable_amount = $driver_payment->paid_amount + $payable_amount;
        }
        $driver_payment->driver_id = $driver_id;
        $driver_payment->currency_code = $currency_code;
        $driver_payment->paid_amount = $payable_amount;
        $driver_payment->save();

        flashMessage('success', 'Payment Details Updated.');
        return back();
    }

    public function updateOwePayment(Request $request)
    {
        if(!auth('admin')->check()) {
            abort(404);
        }
        $driver_id = $request->driver_id;

        $driver_owe_amount = DriverOweAmount::where('user_id',$driver_id)->first();
        $payable_amount = $driver_owe_amount->amount;

        if($driver_owe_amount->amount <= 0 ) {
            flashMessage('danger', 'Driver Payment Failed.');
            return back();
        }

        $driver_owe_amount->amount = 0;
        $driver_owe_amount->save();

        $payment = new DriverOweAmountPayment;
        $payment->user_id = $driver_id;
        $payment->transaction_id = "";
        $payment->amount = $payable_amount;
        $payment->status = 1;
        $payment->currency_code = $driver_owe_amount->currency_code;
        $payment->save();
        
        flashMessage('success', 'Payment Details Updated.');
        return back();
    }

    public function update_company_payment(Request $request)
    {
        $company_id = $request->company_id;

        DriverOweAmount::whereHas('user',function($q) use ($company_id){
            $q->where('company_id',$company_id);
        })->update(['amount' => 0]);

        flashMessage('success', 'Payment Details Updated.');
        return back();
    }
}