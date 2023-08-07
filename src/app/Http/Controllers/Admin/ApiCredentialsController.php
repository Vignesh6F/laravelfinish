<?php

/**
 * Api Credentials Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Api Credentials
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ApiCredentials;

class ApiCredentialsController extends Controller
{
    /**
     * Load View and Update Api Credentials
     *
     * @return redirect     to api_credentials
     */
    public function index(Request $request)
    {
        if($request->isMethod('GET')) {
            return view('admin.api_credentials');
        }

        // Api Credentials Validation Rules
        $rules = array(
            'google_map_key'        => 'required',
            'google_map_server_key' => 'required',
            'twillo_sid'            => 'required',
            'twillo_service_sid'    => 'required',
            'twillo_token'          => 'required',
            'twillo_from'           => 'required',
            'fcm_server_key'        => 'required',
            'fcm_sender_id'         => 'required',
            'facebook_client_id'    => 'required',
            'facebook_client_secret'=> 'required',
            'google_client'         => 'required',
            'google_client_secret'  => 'required',
            'sinch_key'             => 'required',
            'sinch_secret_key'      => 'required',
            'apple_service_id'      => 'required',
            'apple_team_id'         => 'required',
            'apple_key_id'          => 'required',
            'apple_key_file'        => 'valid_extensions:p8',
            'database_url'          => 'required|url',
            'service_account'       => 'valid_extensions:json',
        );

        if(CheckGetInTuchpopup())
            $rules = array_merge($rules,array('recaptcha_site_key'=>'required','recaptcha_secret_key'=>'required'));

        $messages = [
            'apple_key_file.valid_extensions' => trans('validation.mimes',['values'=>'p8']),
            'service_account.valid_extensions' => trans('validation.mimes',['values'=>'JSON']),
        ];

        $request->validate($rules,$messages);
        
        ApiCredentials::where(['name' => 'key', 'site' => 'GoogleMap'])->update(['value' => $request->google_map_key]);
        ApiCredentials::where(['name' => 'server_key', 'site' => 'GoogleMap'])->update(['value' => $request->google_map_server_key]);

        ApiCredentials::where(['name' => 'server_key', 'site' => 'FCM'])->update(['value' => $request->fcm_server_key]);
        ApiCredentials::where(['name' => 'sender_id', 'site' => 'FCM'])->update(['value' => $request->fcm_sender_id]);

        ApiCredentials::where(['name' => 'sid', 'site' => 'Twillo'])->update(['value' => $request->twillo_sid]);
        ApiCredentials::where(['name' => 'token', 'site' => 'Twillo'])->update(['value' => $request->twillo_token]);
        ApiCredentials::where(['name' => 'from', 'site' => 'Twillo'])->update(['value' => $request->twillo_from]);
        ApiCredentials::where(['name' => 'service_sid', 'site' => 'Twillo'])->update(['value' => $request->twillo_service_sid]);

        ApiCredentials::where(['name' => 'client_id','site' => 'Facebook'])->update(['value' => $request->facebook_client_id]);
        ApiCredentials::where(['name' => 'client_secret','site' => 'Facebook'])->update(['value' => $request->facebook_client_secret]);

        ApiCredentials::where(['name' => 'client_id','site' => 'Google'])->update(['value' => $request->google_client]);
        ApiCredentials::where(['name' => 'client_secret','site' => 'Google'])->update(['value' => $request->google_client_secret]);

        ApiCredentials::where(['name' => 'service_id','site' => 'Apple'])->update(['value' => $request->apple_service_id]); 
        ApiCredentials::where(['name' => 'team_id','site' => 'Apple'])->update(['value' => $request->apple_team_id]);
        ApiCredentials::where(['name' => 'key_id','site' => 'Apple'])->update(['value' => $request->apple_key_id]);
        if(CheckGetInTuchpopup()){
                ApiCredentials::where(['name' => 'site_key', 'site' => 'Recaptcha'])->update(['value' => $request->recaptcha_site_key]);
                ApiCredentials::where(['name' => 'secret_key', 'site' => 'Recaptcha'])->update(['value' => $request->recaptcha_secret_key]);
            }

        $image_uploader = resolve('App\Contracts\ImageHandlerInterface');
		$dir_name = resource_path();
		
        if ($request->hasFile('apple_key_file')) {
            $key_file = $request->file('apple_key_file');
            $dir_name = base_path();
            $target_dir = '/public';
            $file_name = "key.txt";
            $extensions = ['txt','p8'];
            $options = compact('dir_name','target_dir','file_name','extensions');

            $upload_result = $image_uploader->upload($key_file,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }
            $file_name = $dir_name.$target_dir.'/'.$file_name;
            $file_name = str_replace(base_path(),"",$file_name);

            ApiCredentials::where(['name' => 'key_file','site' => 'Apple'])->update(['value' => $file_name]);
        }

        if ($request->hasFile('service_account')) {
            $service_account = $request->file('service_account');
            $target_dir = '/credentials/';
            $file_name = "service_account.json";
            $extensions = ['json'];
            $options = compact('dir_name','target_dir','file_name','extensions');

            $upload_result = $image_uploader->upload($service_account,$options);
            if(!$upload_result['status']) {
                flashMessage('danger', $upload_result['status_message']);
                return back();
            }
            $file_name = $dir_name.$target_dir.'/'.$file_name;
            $file_name = str_replace(base_path(),"",$file_name);

            ApiCredentials::where(['name' => 'service_account','site' => 'Firebase'])->update(['value' => $file_name]);
        }
        ApiCredentials::where(['name' => 'database_url','site' => 'Firebase'])->update(['value' => $request->database_url]);
        
        flashMessage('success', 'Updated Successfully');

        return redirect('admin/api_credentials');
    }
}
