<?php

/**
 * OWE DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    OWE
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Currency;
use Yajra\DataTables\Services\DataTable;
use DB;

class OweDataTable extends DataTable
{
    protected $filter_type;
    protected $currency_symbol;

    // Set the value for User Type 
    public function setFilterType($filter_type){
        $this->filter_type = $filter_type;
        return $this;
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
        ->of($query)
        ->addColumn('trip_ids', function ($owe) {

            return '<div class="min_width">'.$owe->trip_ids.'</div>';
        })
        ->addColumn('action', function ($owe) {
            if($owe->company_id != '1') {
             
                $paid_btn = '<form action="'.route('update_company_payment').'" method="POST">
                <input type="hidden" name="company_id" value="'.$owe->company_id.'">
                <input type="hidden" name="_token" value="'.csrf_token().'">
                <button type="submit" class="btn btn-xs btn-primary"> Paid </button>
                </form>';
                return str_replace($this->currency_symbol, '',$owe->remaining_owe_amount) > 0 ? $paid_btn:'';
            }
            $view = '<a href="'.url(LOGIN_USER_TYPE.'/company_owe/'.$owe->company_id).'" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';

            return $view;
        })
        ->rawcolumns(['trip_ids','action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $default_currency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {  //if login user is company then get session currency
            $default_currency = Currency::whereCode(session()->get('currency'))->first();

        }
        $this->currency_symbol = $default_currency->symbol;
        $currency_rate = $default_currency->rate;


        $owe = DB::table('users')
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') {
                //If login user is company then get that company drivers only
                $query->where('company_id',auth('company')->user()->id);
            }
        })
        ->join('trips', function($join) {
            $join->on('users.id', '=', 'trips.driver_id');
        })
        ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })
        ->leftJoin('companies', function($join) {
            $join->on('users.company_id', '=', 'companies.id');
        })
        ->leftJoin('driver_owe_amounts', function($join) {
            $join->on('users.id', '=', 'driver_owe_amounts.user_id');
        })

        ->join('currency as owe_amounts_currency', function($join) {
                $join->on('driver_owe_amounts.currency_code', '=', 'owe_amounts_currency.code');
            })
        ->select('trips.id as trip_id','users.id As id', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'trips.currency_code as currency_code',DB::raw("GROUP_CONCAT(DISTINCT trips.id) as trip_ids"),'companies.name as driver_company_name','companies.id as company_id', DB::raw(' concat("'.$this->currency_symbol.'",sum(ROUND((trips.owe_amount / currency.rate) * '.$currency_rate.',2))) as owe_amount '), DB::raw(' concat("'.$this->currency_symbol.'", sum(ROUND((driver_owe_amounts.amount / owe_amounts_currency.rate) * '.$currency_rate.',2))) as remaining_owe_amount '), DB::raw('sum(ROUND((trips.applied_owe_amount / currency.rate) * '.$currency_rate.',2)) as applied_owe_amount '))



        ->whereNotNull('company_id');

        if($this->filter_type == 'applied') {
            $owe = $owe->where('applied_owe_amount','>','0');
        }
        else {
            $owe = $owe->where('owe_amount','>','0');
        }

        if(LOGIN_USER_TYPE=='company') {
            $owe = $owe->groupBy('id');
        }
        else {
            $owe = $owe->groupBy('company_id');
        }

         //for search  filter
        $keyword =  '%'.request()->search['value'].'%';
        if(isset(request()->search['value'])){
            $owe->havingRaw(' owe_amount like "'.$keyword.'" or  trip_ids like "'.$keyword.'" or  remaining_owe_amount like "'.$keyword.'" or  remaining_owe_amount like "'.$keyword.'" or companies.name like "'.$keyword.'" or companies.id like "'.$keyword.'"  ');
        }

        return $owe;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->columns($this->getColumns())
        ->minifiedAjax()
        ->addAction()
        ->dom('lBfr<"table-responsive"t>ip')
        ->orderBy(0,'DESC')
        ->buttons(
            ['csv', 'excel', 'print', 'reset']
        );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $owe_columns = array();

        if(LOGIN_USER_TYPE == 'admin') {
            $columns = array(
                ['data' => 'company_id', 'name' => 'companies.id', 'title' => 'Company Id','searchable' => false],
                ['data' => 'driver_company_name', 'name' => 'companies.name', 'title' => 'Company Name' ,'searchable' => false],
                ['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false],
                ['data' => 'owe_amount', 'name' => 'owe_amount', 'title' => 'Owe Amount', 'searchable' => false],
            );

            $owe_columns = array(
                ['data' => 'remaining_owe_amount', 'name' => 'remaining_owe_amount', 'title' => 'Remaining Owe Amount', 'searchable' => false]
            );
        }
        else {
            $columns = array(
                ['data' => 'id', 'name' => 'users.id', 'title' => 'Driver Id','searchable' => false],
                ['data' => 'first_name', 'name' => 'users.first_name', 'title' => 'First Name','searchable' => false],
                ['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false],
            );
            if($this->filter_type != 'applied') {
                $owe_columns = array(['data' => 'owe_amount', 'name' => 'owe_amount', 'title' => 'Owe Amount', 'searchable' => false]);
            }
        }
        return array_merge($columns, $owe_columns);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'owe_' . date('YmdHis');
    }
}
