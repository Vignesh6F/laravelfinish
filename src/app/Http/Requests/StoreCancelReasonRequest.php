<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreCancelReasonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $rules =  [];
        if($_POST){
            $rules =  [
                'reason'  => 'required|unique:cancel_reasons,reason,'.(isset($request->id)?$request->id:''),
                'status' => 'required',
                'cancelled_by' => 'required'
            ];

            foreach($request->translations ?: array() as $k => $translation)
            {
                $rules['translations.'.$k.'.locale'] = 'required | nullable ';
                $rules['translations.'.$k.'.reason'] = 'required';
            }
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes =  [
            'reason'    => 'Reason',
            'status'  => 'Status',
            'cancelled_by' => 'Cancelled By'
        ];
        foreach(request()->translations ?: array() as $k => $translation)
            {
                $attributes['translations.'.$k.'.locale'] = 'Language';
                $attributes['translations.'.$k.'.reason'] = 'reason';
  
            }
        return $attributes;           
    }

}
