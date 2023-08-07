<?php

namespace App\Http\Controllers\Admin;

use App\Models\Support;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\SupportDataTable;
use Validator;


class SupportController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SupportDataTable $dataTable) {
        return $dataTable->render('admin/support/view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        if($request->isMethod('GET')) {
            return view('admin/support/add');
        }

        if($request->submit) {

            $rules = array(
                'name'  => 'required',
                'link'  => 'required',
                'status'=> 'required',
                'image' => 'required',
            );

             $support_validation=false;

            if (filter_var($request->link, FILTER_VALIDATE_URL)) {
              $support_validation=true;
            } else {                

            $check_spl_char=preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->link);
            $check_number=preg_match("/^\d+$/", $request->link); 

            if($check_spl_char && $check_number)
                $support_validation=true;
            }
            if($support_validation==false)
                $rules['link']='required|integer';
          
            $message=array(
                'link.integer'=>'Please provide a valid link'
            );

            $validator = Validator::make($request->all(), $rules,$message);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
            $target_dir = '/images/support';

            if($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "category-image".time().'.'.$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
            }

            $support        = new Support;
            $support->name  = $request->name;
            $support->link  = $request->link;
            $support->status= $request->status;
            $support->image = $file_name;
            $support->save();
            flashMessage('success', 'Added Successfully');
        }

        return redirect('admin/support');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        if($request->isMethod('GET')) {
            $data['result'] = Support::find($request->id);
            if($data['result']) {
                $data['editable'] = ($request->id =='1' || $request->id =='2') ? 'readonly' : '';
                return view('admin/support/edit', $data);  
            }
            flashMessage('danger', 'Invalid ID');
        }
        if($request->submit) {
            $rules = array(
                'name'  =>'required',
                'link'  => 'required',
                'status'=> 'required',
            );

            $support_validation=false;

            if (filter_var($request->link, FILTER_VALIDATE_URL)) {
              $support_validation=true;
            } else {                

            $check_spl_char=preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->link);
            $check_number=preg_match("/^\d+$/", $request->link); 

            if($check_spl_char && $check_number)
                $support_validation=true;
            }
            if($support_validation==false)
                $rules['link']='required|integer';
          
            $message=array(
                'link.integer'=>'Please provide a valid link'
            );

            $validator = Validator::make($request->all(), $rules,$message);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $support = Support::find($request->id);
            if($request->hasFile('image')) {
                $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
                $target_dir = '/images/support';
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "category-image".time().'.'.$extension;
                $options = compact('target_dir','file_name');
                $upload_result = $image_uploader->upload($image,$options);
                if(!$upload_result['status']){
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
                $support->image = $file_name;
            }

            $support->name  = $request->name;
            $support->link   = $request->link;
            $support->status = $request->status;
            $support->save();
           
            flashMessage('success', 'Updated Successfully');
        }

        return redirect('admin/support');
    }

    public function delete(Request $request)
    {
        if($request->id =='1' || $request->id =='2'){
            flashMessage('danger', "This is required one. So can't delete this"); 
            return redirect('admin/support');
        }

        $support = Support::find($request->id);
        if($support->image){
            $delete_image = resolve('App\Contracts\ImageHandlerInterface');
            $delete_image->delete($support->image,['file_path' => '/images/support/']);
        }
        $support->delete();
        flashMessage('success', 'Deleted Successfully'); 
        return redirect('admin/support');
    }
}
