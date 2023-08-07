<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Http\Start\Helpers;
use App\Models\VehicleModel;
use App\DataTables\VehicleModelDataTable;
use App\Models\MakeVehicle;
use App\Models\Vehicle;


class VehicleModelController extends Controller
{
    //
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    //
    public function index(VehicleModelDataTable $dataTable)
    {
        
        return $dataTable->render('admin.vehicle_model.view');
    }

    public function add(Request $request)
    {
       if(!$_POST)
        {
            $data['make']=MakeVehicle::Active()->pluck('make_vehicle_name','id')->toArray();
            return view('admin.vehicle_model.add',$data);  
        }
        else if($request->submit) { 
            $rules = array(
                'vehicle_make_id' => 'required',
                'model_name'   =>'required|max:20',
                'status'        => 'required',
            );

            $attributes = array(
                'vehicle_make_id'       => 'Make',
                'model_name'            => 'Model',
                'status'                => 'Status',
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $VehicleModel = new VehicleModel;

            $VehicleModel->vehicle_make_id = $request->vehicle_make_id;
            $VehicleModel->model_name = $request->model_name;
            $VehicleModel->status   = $request->status;
            $VehicleModel->save();
            flashMessage('success', 'Added Successfully'); 
        }
        return redirect('admin/vehicle_model');
    }

    public function update(Request $request)
    {
       if(!$_POST)
        {
            $data['make'] =MakeVehicle::Active()->pluck('make_vehicle_name','id')->toArray();
            $data['result'] = VehicleModel::find($request->id);
            return view('admin.vehicle_model.edit',$data);  
        }
        else if($request->submit) {
            $rules = array(
                'vehicle_make_id' => 'required',
                'model_name'     =>'required',
                'status'        => 'required',
            );

            $attributes = array(
                'vehicle_make_id'       => 'Make',
                'model_name'            => 'Model',
                'status'                => 'Status',
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            $validator->setAttributeNames($attributes); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); 
                // Form calling with Errors and Input values
            }

            $VehicleModel = VehicleModel::find($request->id);
            $VehicleModel->vehicle_make_id = $request->vehicle_make_id;
            $VehicleModel->model_name = $request->model_name;
            $VehicleModel->status   = $request->status;
            $VehicleModel->save();
            flashMessage('success', 'Updated Successfully'); 
        }

        return redirect('admin/vehicle_model');
    }

    public function delete(Request $request){
        $count = Vehicle::where('vehicle_model_id',$request->id)->count();
        if($count > 0){
            $this->helper->flash_message('danger', "The vehicle model used by some users. So can't delete this vehicle model");
            return redirect('admin/vehicle_model');
        }
        VehicleModel::find($request->id)->delete();
        $this->helper->flash_message('success', 'Deleted Successfully'); 
        // Call flash message function
        return redirect('admin/vehicle_model');
    }

    public function makeListValue(Request $request){

        $model = VehicleModel::Active()->where('vehicle_make_id',$request->make_id)->pluck('model_name','id')->toArray();

        return response()->json($model);
    }


}
