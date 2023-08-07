<?php

/**
 * Company OWE DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Company OWE
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Currency;
use Yajra\DataTables\Services\DataTable;
use DB;

class CompanyOweDataTable extends DataTable
{
    protected $currency_rate,$currency_symbol;
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $default_currency = view()->shared('default_currency');
        $this->currency_symbol = html_entity_decode($default_currency->symbol);
        return datatables()
             
            ->of($query)
            ->addColumn('trip_ids', function ($owe) {
                return '<div class="min_width">'.$owe->trip_ids.'</div>';
            })
         
            ->addColumn('action', function ($owe) {

                $paid_btn = '<form action="'.route('update_owe_payment').'" method="POST">
                            <input type="hidden" name="driver_id" value="'.$owe->id.'">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <button type="submit" class="btn btn-xs btn-primary"> Paid </button>
                            </form>';
                return str_replace($this->currency_symbol, '',$owe->remaining_owe_amount) > 0 ?$paid_btn : '';
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
        $company_id = request()->id;

         $default_currency = Currency::active()->defaultCurrency()->first();
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {  //if login user is company then get session currency
            $default_currency = Currency::whereCode(session()->get('currency'))->first();

        }
        $this->currency_symbol = $default_currency->symbol;
        $currency_rate = $default_currency->rate;
        $this->currency_rate = $currency_rate;


        $owe = DB::table('users')
        ->join('trips', function($join) {
            $join->on('users.id', '=', 'trips.driver_id');
        })
        ->join('currency', function($join) {
                $join->on('trips.currency_code', '=', 'currency.code');
            })

        ->leftJoin('driver_owe_amounts', function($join) {
            $join->on('users.id', '=', 'driver_owe_amounts.user_id');
        })

        ->join('currency as owe_amounts_currency', function($join) {
                $join->on('driver_owe_amounts.currency_code', '=', 'owe_amounts_currency.code');
        })

        ->leftJoin('driver_owe_amount_payments', function($join) {
            $join->on('users.id', '=', 'driver_owe_amount_payments.user_id');
        })

        ->leftJoin('currency as owe_amounts_payment_currency', function($join) {
                $join->on('driver_owe_amount_payments.currency_code', '=', 'owe_amounts_payment_currency.code');
            })
        ->select('trips.id as trip_id','users.id As id', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'trips.currency_code as currency_code',DB::raw("GROUP_CONCAT(DISTINCT trips.id) as trip_ids"), DB::raw(' concat("'.$this->currency_symbol.'",sum(ROUND((trips.owe_amount / currency.rate) * '.$currency_rate.',2))) as owe_amount '), DB::raw(' concat("'.$this->currency_symbol.'", sum(ROUND((driver_owe_amounts.amount / owe_amounts_currency.rate) * '.$currency_rate.',2))) as remaining_owe_amount '), DB::raw('sum(ROUND((trips.applied_owe_amount / currency.rate) * '.$currency_rate.',2)) as applied_owe_amount '), DB::raw(' concat("'.$this->currency_symbol.'",sum(ROUND((driver_owe_amount_payments.amount / owe_amounts_payment_currency.rate) * '.$currency_rate.',2))) as paid_amount'))

        ->where('company_id',$company_id)
        ->whereIn('trips.payment_mode',['Cash & Wallet','Cash'])
        ->where('trips.owe_amount','>','0')
        ->whereIn('trips.status',['Payment','Completed'])

        ->whereNotNull('company_id');
        //for search  filter
        $keyword =  '%'.request()->search['value'].'%';
        if(isset(request()->search['value'])){
            $owe->havingRaw(' owe_amount like "'.$keyword.'" or  paid_amount like "'.$keyword.'" or  trip_ids like "'.$keyword.'" or  remaining_owe_amount like "'.$keyword.'" or  remaining_owe_amount like "'.$keyword.'" or users.first_name like "'.$keyword.'" or users.id like "'.$keyword.'"  ');
        }

       
        return  $owe->groupBy('id');


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
                    ->addAction()
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
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'Driver Id','searchable' => false],
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'First Name','searchable' => false],
            ['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false],
            ['data' => 'owe_amount', 'name' => 'owe_amount', 'title' => 'Owe Amount' ,'searchable' => false],
            ['data' => 'paid_amount', 'name' => 'paid_amount', 'title' => 'Paid Amount','searchable' => false],
            ['data' => 'remaining_owe_amount', 'name' => 'remaining_owe_amount', 'title' => 'Remaining Owe Amount','searchable' => false],
        ];
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
