<?php

/**
 * Locations DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Locations
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\AppVersion;
use Yajra\DataTables\Services\DataTable;
use DB;

class AppVersionDataTables extends DataTable
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
           ->addColumn('action', function ($version) {
                $edit = '<a href="'.url('admin/edit_app_version/'.$version->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;';

                $delete = '<a data-href="'.url('admin/delete_app_version/'.$version->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Location $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AppVersion $model)
    {
        $version = AppVersion::get();
        return $version;
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
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
            ['data' => 'version', 'name' => 'version', 'title' => 'Version'],
            ['data' => 'add_device_type', 'name' => 'add_device_type', 'title' => 'Device Type'],
            ['data' => 'add_user_type', 'name' => 'add_user_type', 'title' => 'User Type'],
            ['data' => 'add_force_update', 'name' => 'add_force_update', 'title' => 'Force Update'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'AppVersion_' . date('YmdHis');
    }
}