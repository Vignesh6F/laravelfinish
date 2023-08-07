<?php

/**
 * Payments DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Payments
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Trips;
use App\Models\Currency;
use Yajra\DataTables\Services\DataTable;
use DB;

class PaymentsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->of($query);
          
    }

    /**
     * Get query source of dataTable.
     *
     * @param Trips $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {

        $default_currency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {  //if login user is company then get session currency
            $default_currency = Currency::whereCode(session()->get('currency'))->first();
        }
        $symbol = $default_currency->symbol;
        $currency_rate = $default_currency->rate;

        $trips = DB::table('trips')
                        ->join('currency', function($join) {
                                $join->on('currency.code', '=', 'trips.currency_code');
                            })
                        ->leftJoin('users as u', function($join) {
                                $join->on('u.id', '=', 'trips.driver_id');
                            })
                        ->leftJoin('users as rider', function($join) {
                            $join->on('rider.id', '=', 'trips.user_id');
                        })
                        ->leftJoin('companies', function($join) {
                            $join->on('u.company_id', '=', 'companies.id');
                        })
                        ->select(['trips.id as id','trips.begin_trip as begin_trip', 'u.first_name as driver_name','rider.first_name as rider_name','trips.payment_status','trips.status','trips.created_at as trip_date','companies.name as company_name',

                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.time_fare / currency.rate) * '.$currency_rate.',2)))  as time_fare'),
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.distance_fare / currency.rate) * '.$currency_rate.',2)))  as distance_fare'),
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.base_fare / currency.rate) * '.$currency_rate.',2)))  as base_fare'),
                            DB::raw('concat("'.$symbol.'", ( 
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.driver_peak_amount / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.',2))
                        - sum(ROUND((trips.driver_or_company_commission / currency.rate) * '.$currency_rate.',2))
                        + sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.',2))  )) as driver_or_company_commission'),
                        
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.additional_rider_amount / currency.rate) * '.$currency_rate.',2)))  as 2nd_rider_amount'),
                          DB::raw('concat("'.$symbol.'", 
                        (
                        sum(ROUND((trips.subtotal_fare / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.peak_amount / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND((trips.schedule_fare / currency.rate) * '.$currency_rate.',2)) 
                        + sum(ROUND(( if((trips.payment_status="Completed"),(trips.tips / currency.rate),0 ) * '.$currency_rate.'),2))
                        - sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.',2))
                        + sum(ROUND((trips.waiting_charge / currency.rate) * '.$currency_rate.',2))  )) as total_fare '),

                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.access_fee / currency.rate) * '.$currency_rate.',2)))  as access_fee'),
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.tips / currency.rate) * '.$currency_rate.',2)))  as tips'),
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.toll_fee / currency.rate) * '.$currency_rate.',2)))  as toll_fee'),
                        DB::raw('concat("'.$symbol.'", sum(ROUND((trips.driver_payout / currency.rate) * '.$currency_rate.',2)))  as driver_payout'),
                    ]);
                        if (LOGIN_USER_TYPE=='company') {
                            $trips->where('u.company_id',auth('company')->user()->id);
                        }
                    $trips->groupBy('trips.id');


        //for search  filter
        $keyword =  '%'.request()->search['value'].'%';
        if(isset(request()->search['value'])){
            $trips->havingRaw(' id like "'.$keyword.'" or  trip_date   like "'.$keyword.'" or  driver_name like "'.$keyword.'" or  rider_name like "'.$keyword.'" or  time_fare like "'.$keyword.'" or distance_fare like "'.$keyword.'" or base_fare like "'.$keyword.'"  or 2nd_rider_amount like "'.$keyword.'"  or tips like "'.$keyword.'"  or toll_fee like "'.$keyword.'"  or driver_payout like "'.$keyword.'"  or status like "'.$keyword.'"  or total_fare like "'.$keyword.'"   or driver_or_company_commission  like "'.$keyword.'" or access_fee  like "'.$keyword.'"  ');
        }

        return $trips;
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
                    ->dom('lBfr<"table-responsive"t>ip')
                    ->orderBy(0)
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
        $company_columns = array();
        if(LOGIN_USER_TYPE == 'company') {
            $payout_columns = array(
                 ['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare','searchable' => false],
                ['data' => 'driver_or_company_commission', 'name' => 'driver_or_company_commission', 'title' => 'Admin Commission','searchable' => false],
               
            );                
        }
        else {
            $payout_columns = array(
                ['data' => 'access_fee', 'name' => 'access_fee', 'title' => 'Access Fare','searchable' => false],
                ['data' => 'driver_or_company_commission', 'name' => 'driver_or_company_commission', 'title' => 'Admin Commission','searchable' => false],
                ['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare','searchable' => false]
            );
            $company_columns = array(
                ['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name','searchable' => false]
            );
        }

        $col_list_1 = [
            ['data' => 'id', 'name' => 'trips.id', 'title' => 'Id','searchable' => false],
            ['data' => 'trip_date', 'name' => 'trips.created_at', 'title' => 'Trip Date','searchable' => false],
        ];

        $col_list_2 = [
            ['data' => 'driver_name', 'name' => 'u.first_name', 'title' => 'Driver Name','searchable' => false],
            ['data' => 'rider_name', 'name' => 'rider.first_name', 'title' => 'Rider Name','searchable' => false],
            ['data' => 'time_fare', 'name' => 'time_fare', 'title' => 'Time Fare','searchable' => false],
            ['data' => 'distance_fare', 'name' => 'distance_fare', 'title' => 'Distance Fare','searchable' => false],
            ['data' => 'base_fare', 'name' => 'base_fare', 'title' => 'Base Fare','searchable' => false],
            ['data' => '2nd_rider_amount', 'title' => '2nd Rider Amount(Pool)','searchable' => false],
            ['data' => 'tips', 'name' => 'tips', 'title' => 'Tips','searchable' => false],
            ['data' => 'toll_fee', 'name' => 'toll_fee', 'title' => 'Additional Fee','searchable' => false],
        ];

        $col_list_3 = [
            ['data' => 'driver_payout', 'name' => 'driver_payout', 'title' => 'Earnings','searchable' => false],
            ['data' => 'status', 'name' => 'trips.status', 'title' => 'Status','searchable' => false],
        ];

        return array_merge($col_list_1,$company_columns,$col_list_2,$payout_columns,$col_list_3);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'payments_' . date('YmdHis');
    }
}