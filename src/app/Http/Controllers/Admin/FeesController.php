<?php

/**
 * Fees Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Fees
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fees;
use Validator;

class FeesController extends Controller
{
    /**
     * Load View and Update Fees Data
     *
     * @return redirect     to fees
     */
    public function index(Request $request)
    {
        if($request->isMethod("GET")) {
            return view('admin.fees');
        }
        if($request->submit) {
            // Fees Validation Rules
            $rules = array(
                'access_fee' => 'numeric',
                'driver_peak_fare' => 'numeric|max:100',
                'driver_service_fee' => 'numeric',
                'additional_rider_fare' => 'required|numeric',
                'additional_fee' => 'required|in:Yes,No'
            );

            // Fees Validation Custom Names
            $attributes = array(
                'access_fee' => 'Rider Service Fee',
                'driver_peak_fare' => 'driver Peak Fare',
                'driver_service_fee' => 'driver Service Fee',
                'additional_fee' => 'Apply Trip Additional Fee',
                'additional_rider_fare' => 'Additional Rider Fare',
            );

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            Fees::where(['name' => 'access_fee'])->update(['value' => $request->access_fee]);
            Fees::where(['name' => 'driver_peak_fare'])->update(['value' => $request->driver_peak_fare]);
            Fees::where(['name' => 'driver_access_fee'])->update(['value' => $request->driver_service_fee]);
            Fees::where(['name' => 'additional_fee'])->update(['value' => $request->additional_fee]);
            Fees::where(['name' => 'additional_rider_fare'])->update(['value' => $request->additional_rider_fare]);

            flashMessage('success', 'Updated Successfully');
        }
        return redirect('admin/fees');
    }
    
}
