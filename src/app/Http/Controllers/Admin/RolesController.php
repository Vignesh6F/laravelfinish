<?php

/**
 * Roles Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Roles
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\RoleDataTable;
use App\Models\Role;
use App\Models\Admin;
use App\Models\Permission;
use Auth;
use Validator;
use DB;

class RolesController extends Controller
{
    /**
    * Load Datatable for Roles
    *
    * @param array $dataTable  Instance of RolesDataTable
    * @return datatable
    */
    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('admin.roles.view');
    }

    /**
    * Add a New Role
    *
    * @param array $request  Input values
    * @return redirect     to Roles view
    */
    public function add(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['permissions'] = Permission::get();
            return view('admin.roles.add', $data);
        }
        if($request->submit) {
            $rules = array(
                'name'         => 'required|unique:roles',
                'display_name' => 'required',
                'description'  => 'required',
            );

            $attributes = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
                'permission'   => 'Permission'
            );
            $validator = Validator::make($request->all(), $rules,[],$attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); 
            }

            $request['permission'] = is_array($request->permission) ? $request->permission : [];

            $role = new Role;
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();

            $permission = $request->permission;
            $permissions = Permission::whereIn('id',$permission)->get();

            $role->permissions()->sync($permissions);

            flashMessage('success', 'Added Successfully');
        }

        return redirect('admin/roles');
    }

    /**
    * Update Role Details
    *
    * @param array $request    Input values
    * @return redirect     to Roles View
    */
    public function update(Request $request)
    {
        if($request->isMethod("GET")) {
            $data['result'] = Role::find($request->id);
            $data['stored_permissions'] = Role::permission_role($request->id);
            $data['permissions'] = Permission::get();
            return view('admin.roles.edit', $data);
        }
        if($request->submit) {

            $rules = array(
                'name'         => 'required|unique:roles,name,'.$request->id,
                'display_name' => 'required',
                'description'  => 'required',
                // 'permission'   => 'required'
            );

            $attributes = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
                'permission'   => 'Permission'
            );
            $validator = Validator::make($request->all(), $rules,[],$attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $request['permission'] = is_array($request->permission) ? $request->permission : [];

            $role = Role::find($request->id);
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->save();

            $permission = $request->permission;
            $permissions = Permission::whereIn('id',$permission)->get();

            $role->permissions()->sync($permissions);

            flashMessage('success', 'Updated Successfully'); 
        }
        return redirect('admin/roles');
    }

    /**
    * Delete Role
    *
    * @param array $request    Input values
    * @return redirect     to Roles View
    */
    public function delete(Request $request)
    {
        $id = $request->id;
        $role_count = Role::where('id','!=',$id)->count();

        if($role_count == 0) {
            flashMessage('danger','You cannot delete last role');
            return redirect('admin/roles');
        }

        $role = Role::find($id);

        if($role->users()->count()) {
            flashMessage('danger', 'This role used by some admin users. So you can\'t delete it');
            return redirect('admin/roles');
        }

        try {
            $role->users()->sync([]);
            $role->permissions()->sync([]);
            $role->forceDelete();
            flashMessage('success','Deleted Successfully');
        } catch(Exception $e) {
            flashMessage('danger',$e->getMessage());
        }
        return redirect('admin/roles');
    }
}
