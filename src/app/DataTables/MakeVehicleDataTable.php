<?php

namespace App\DataTables;

use App\Models\MakeVehicle;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use DB;


class MakeVehicleDataTable extends DataTable
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
            ->of($query)
            ->addColumn('action', function ($make_vehicle) {
                $edit = (auth('admin')->user()->can('update_vehicle_make')) ? '<a href="'.url('admin/edit-vehicle-make/'.$make_vehicle->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;': '';
                $delete = (auth('admin')->user()->can('delete_vehicle_make')) ? '<a data-href="'.url('admin/delete-vehicle_make/'.$make_vehicle->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>': '';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\MakeVehicle $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MakeVehicle $model)
    {
        return $model->all();
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
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
            ['data' => 'make_vehicle_name', 'name' => 'make_vehicle_name', 'title' => 'Make'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'MakeVehicle_' . date('YmdHis');
    }
}
