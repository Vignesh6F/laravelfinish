@extends('template_driver_dashboard') 
@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;" ng-controller="facebook_account_kit">
  <!-- <div class="page-lead separated--bottom  text--center text--uppercase">
    <h1 class="flush-h1 flush">{{trans('messages.header.profil')}}</h1>
  </div> -->
  <div class="" style="padding:0px 15px;">
    {{ Form::open(array('url' => 'driver_update_profile/'.$result->id,'id'=>'form','class' => 'layout layout--flush','files' => 'true','enctype'=>'multipart/form-data','name'=>'driver_profile')) }}
  </div>
  @include('dashboard.mobile_number_change')
  <input type="hidden" name="user_type" value="{{ $result->user_type }}">
  <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
  <input type="hidden" name="code" id="code" />
  <input type="hidden" id="user_id" name="user_id" value="{{ $result->id }}">
  <input type="hidden" name="id" value="{{ @$result->id}}">
  <div class="page-lead separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_update-loader displayflex" style="border-bottom:0px !important;padding: 0">

    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-6">
      <h1 class="cls_profiletitle">{{trans('messages.header.profil')}}</h1>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text--right">
      <button type="button" class="btn btn--primary btn-blue doc-button" ng-click="selectFile()">
        <span style="padding: 0px 30px !important;font-size: 14px !important;" id="span-cls">{{trans('messages.driver_dashboard.add_photo')}}
        </span>
      </button>
      <input type="file" ng-model="profile_image" style="display:none" accept="image/*"
      id="file" name='profile_image' onchange="angular.element(this).scope().fileNameChanged(this)" />
    </div>
</div>
<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="    padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">
      {{trans('messages.profile.email')}} <em class="text-danger">*</em>
    </label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      <input class="_style_3vhmZK" name="email" value="{{ @$result->email}}" placeholder="{{trans('messages.profile.email')}}">
      <span class="text-danger"> {{ $errors->first('email') }} </span>
    </div>
  </div>
</div>
<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding:6px 0px;">{{trans('messages.profile.phone')}}<em class="text-danger">*</em></label>
    <div class="col-lg-1 col-md-1 col-sm-2 col-xs-2" style="padding:6px 0px;">
      <input class="_style_3vhmZK" type="text" name="phone_code" value="+{{ @$result->country_code}}" readonly="">
      <input type="hidden" id="mobile_country" name="mobile_country" value="{{ @$result->country_code}}">
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-9" style="padding:6px 0px;">
      <input class="_style_3vhmZK" id="mobile" name="mobile_number" value="{{ @$result->mobile_number}}" placeholder="{{trans('messages.profile.mobile')}}" readonly="" style=" margin-left: 2px;">
      <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12" style="padding:6px 0px;">
      <input type="button" class="_style_3vhmZK" name="change_number" value="{{ trans('messages.profile.change') }}" id="submit-btn" ng-click="changeNumberPopup('show_popup')" style=" margin-left: 4px;">
    </div>
  </div>
</div>

<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-12" style="padding:6px 0px;">{{trans('messages.profile.gender')}}<em class="text-danger">*</em></label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-9" style="padding:6px 0px;">
      {!! Form::text('gender', $result->gender_text, ['class'=>'_style_3vhmZK phone-no', 'disabled'=>'true']) !!}
    </div>
  </div>
</div>


<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.profile.addr')}}</label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">


      <div class="">
        <div class="autocomplete-input">
         {!! Form::text('address_line1', @$result->driver_address->address_line1.@$result->driver_address->address_line2, ['class' => '_style_3vhmZK','placeholder' => trans('messages.profile.addr'),'id' => 'home_address','autocomplete' => 'false']) !!}  
       </div>
       <ul class="autocomplete-results home_address">
       </ul>
     </div>

   </div>
   <span class="text-danger">{{ $errors->first('address_line1') }}</span>
 </div>
</div>
<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="    padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;"> 
      {{trans('messages.profile.profile_city')}}
    </label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::text('city', @$result->driver_address->city, ['class' => '_style_3vhmZK','placeholder' => trans('messages.profile.profile_city'),'id' => 'city']) !!}
      {{-- <input type="hidden" name="state" id="state" value="{{ @$result->driver_address->state }}"> --}}
      <input type="hidden" name="country" id="country" value="">
      <input type="hidden" name="address_line1" id="address_line1" value="{{ @$result->driver_address->address_line1 }}">
      <input type="hidden" name="address_line2" id="address_line2" value="{{ @$result->driver_address->address_line2 }}">
      <input type="hidden" name="postal_code" id="postal_code" value="{{ @$result->driver_address->postal_code }}">
      <input type="hidden" name="latitude" id="latitude" value="">
      <input type="hidden" name="longitude" id="longitude" value="">
      <span class="text-danger">{{ $errors->first('city') }}</span>
    </div>
  </div>
</div>
<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;"> 
      {{trans('messages.profile.state')}}
    </label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::text('state', @$result->driver_address->state, ['class' => '_style_3vhmZK','placeholder' => trans('messages.profile.state'),'id' => 'state']) !!}
    </div>
    <span class="text-danger">{{ $errors->first('state') }}</span>
  </div>
</div>
<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 5px 0px;">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex">
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;"> {{trans('messages.profile.country')}} </label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      <select class=" _style_3vhmZK " name="country_code" tabindex="-1" title="" disabled="">
        @foreach($country as $key => $value)
        <option value="{{$value->phone_code}}" {{$value->id == @$result->country_id ? 'selected' : ''}} data-value="+{{ $value->phone_code}}"> {{ $value->long_name}} </option>
        @endforeach
      </select>
    </div>
  </div>
  <span class="text-danger">{{ $errors->first('country_code') }}</span>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex" >
    <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;"> {{trans('messages.profile.profile_postal_code')}}</label>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      <input class="_style_3vhmZK" name="postal_code" value="{{ @$result->driver_address->postal_code}}" placeholder="{{trans('messages.profile.profile_postal_code')}}">
    </div>
    <span class="text-danger">{{ $errors->first('postal_code') }}</span>

  </div>
</div>
<div class="separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="border-bottom:0px !important; margin-top: 20px; text-align: center;">
  <button style="padding: 0px 60px !important;font-size: 19px !important;" type="submit" class="btn btn--primary btn-blue" id="update_btn">{{trans('messages.user.update')}}</button>
</div>
{{ Form::close() }}
</div>
</div>
</div>
</div>
</div>
</main>
@stop
