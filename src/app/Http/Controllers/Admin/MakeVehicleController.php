<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\MakeVehicleDataTable;
use App\Models\MakeVehicle;
use Validator;
use App\Http\Start\Helpers;
use App\Models\VehicleModel;

class MakeVehicleController extends Controller
{

    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    //
    public function index(MakeVehicleDataTable $dataTable)
    {
        return $dataTable->render('admin.make_vehicle.view');
    }

    public function add(Request $request)
    {

    	
       if(!$_POST)
        {
            return view('admin.make_vehicle.add');  
        }
        else {
           
            $rules = array(
                'make_vehicle_name' => 'required|min:2|max:20',
                'status'        => 'required',
            );

            
            $attributes = array(
                'make_vehicle_name'      => 'Make Vehicle',
                'status'        => 'Status',
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $MakeVehicle = new MakeVehicle;
            $MakeVehicle->make_vehicle_name = $request->make_vehicle_name;
            $MakeVehicle->status   = $request->status;
            $MakeVehicle->save();
            flashMessage('success', 'Added Successfully'); 
        }

        return redirect('admin/vehicle_make');
    }

    public function update(Request $request){
    	if(!$_POST)
        {
        	$data['result'] = MakeVehicle::find($request->id);
            return view('admin.make_vehicle.edit',$data);  
        }
        else {
            $rules = array(
                'make_vehicle_name' => 'required|min:2|max:20',
                'status'        => 'required',
            );
            $attributes = array(
                'make_vehicle_name'      => 'Make Vehicle',
                'status'        => 'Status',
            );
            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            $MakeVehicle =MakeVehicle::find($request->id);
            $MakeVehicle->make_vehicle_name = $request->make_vehicle_name;
            $MakeVehicle->status   = $request->status;
            $MakeVehicle->save();
            flashMessage('success', 'Updated Successfully'); 
        }
        return redirect('admin/vehicle_make');	
    }

    public function delete(Request $request){
        $model = VehicleModel::where('vehicle_make_id',$request->id)->get()->count();
        if($model > 0)
        {
            flashMessage('error', 'Model using this Make. So Could not Delete this Make');
            return redirect('admin/vehicle_make');
        } 
        MakeVehicle::find($request->id)->delete();
        $this->helper->flash_message('success', 'Deleted Successfully'); 
        // Call flash message function
        return redirect('admin/vehicle_make');
    }
}
