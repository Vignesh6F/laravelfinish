@extends('template_footeronly')
@section('main')

<div class="flash-container">
  @if(Session::has('message'))
  <div class="alert text-center participant-alert " style="    background: #1fbad6 !important;color: #fff !important;margin-bottom: 0;" role="alert">
    <a href="#" class="alert-close text-white" data-dismiss="alert"></a>
    {!! Session::get('message') !!}
  </div>
  @endif
</div>
<div class=" text--left signupdrive" ng-controller="facebook_account_kit">
  @include('user.otp_popup')
  <div class="join-page" >
    <div class="layout--join" >
        <div class="cls_driversignup">
          <div class="container-fluid">
            <div class="col-lg-12 pad-0">
              <div class="col-lg-7 cls_lefttextin">
                <div class="cls_lefttext">
                   <a href="{{ url('/') }}" style="display: block;width: 100%"><img style="width: 109px;margin: 15px 0;height: 50px !important;" src="{{ $logo_url }}"></a>
                  <h1>{{$site_name}} {{trans('messages.user.need_partner')}}</h1>
                  <p>{{trans('messages.user.drive_with_gofer')}} {{$site_name}} {{trans('messages.user.need_partner_content')}}</p>
                </div>
              </div>
              <div class="col-lg-5 pad-0">
                  <div class="driverform">
                    {{ Form::open(array('url' => 'driver_register','class' => 'layout layout--flush driver-signup-form-join-legacy','id'=>'form')) }}
                    {{csrf_field()}}
                    {!! Form::hidden('request_type', '', ['id' => 'request_type' ]) !!}
                    {!! Form::hidden('otp', '', ['id' => 'otp' ]) !!}
                    <div class="cls_createacc">
                      <input type="hidden" name="user_type" value="Driver">
                        <a href="{{ url('signin_driver')}}" class="btn btn--primary">
                         {{trans('messages.ride.already_have_account')}}
                        </a>
                        <h3 class="cls_title">{{trans('messages.user.create_account')}}</h3>
                    </div>
                    <div class="col-lg-12">
                      <div class="col-lg-6 p-0">
                        <div class="forminput">
                         {!! Form::text('first_name', '', ['class' => '_style_3vhmZK','placeholder' => trans('messages.user.firstname'),'id' => 'fname' ]) !!}
                          <span class="text-danger first_name_error">{{ $errors->first('first_name') }}</span>
                        </div>
                      </div>
                      <div class="col-lg-6 p-0">
                        <div class="forminput">
                          {!! Form::text('last_name', '', ['class' => '_style_3vhmZK','placeholder' => trans('messages.user.lastname'),'id' => 'lname' ]) !!}
                          <span class="text-danger last_name_error">{{ $errors->first('last_name') }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="forminput">
                       {!! Form::text('email', '', ['class' => '_style_3vhmZK','placeholder' => trans('messages.user.email') ]) !!}
                        <span class="text-danger email_error">{{ $errors->first('email') }}</span>
                      </div>
                    </div>
                    
                    <div class="col-lg-12">
                      <div class="forminput">
                        <div class="col-lg-3 p-0 mobile-code">
                         <div id="select-title-stage">{{old('country_code')!=null ? '+'.old('country_code') : '+1' }}</div>
                          <input type="hidden" name="country_code" value="{{ old('country_code',(isset($country_code) ? $country_code : '')) }}">
                          <div class="select select--xl" ng-init="old_country_code={{old('country_code')!=null? old('country_code') : '1'}}">
                            <!-- <label for="mobile-country"><div class="flag US"></div></label> -->
                            <select name="country_code" tabindex="-1" id="mobile_country" class="square borderless--right">
                              @foreach($country as $key => $value)
                              <option value="{{$value->phone_code}}" {{ ($value->id == (old('country_id')!=null? old('country_id') : '1')) ? 'selected' : ''  }} data-value="+{{ $value->phone_code}}" data-id="{{ $value->id }}">{{ $value->long_name}}
                              </option>
                              @endforeach
                              {!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
                            </select>
                            <span class="text-danger country_code_error">{{ $errors->first('country_code') }}</span>
                            
                          </div>
                        </div>
                        <div class="col-lg-9 p-0">
                          {!! Form::tel('mobile_number', isset($phone_number)?$phone_number:'', ['class' => '_style_3vhmZK','placeholder' => trans('messages.profile.mobile'),'id' => 'mobile' , 'style'=> 'margin-left:2px']) !!}
                           <span class="text-danger mobile_number_error">{{ $errors->first('mobile_number') }}</span>
                        </div>
                    </div>
                    </div>

                    <div class="col-lg-12">
                      <div class="forminput">
                        <select name="gender" id="gender_options" class="_style_3vhmZK">
                          <option value="" disabled selected hidden>{{ __('messages.driver_dashboard.select').' '.__('messages.profile.gender') }}</option>
                          <option value="1">{{ __('messages.profile.male') }}</option>
                          <option value="2">{{ __('messages.profile.female') }}</option>
                        </select>
                        <span class="text-danger gender_error">{{ $errors->first('gender') }}</span>
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="forminput">
                        {!! Form::password('password', array('class' => '_style_3vhmZK ','placeholder' => trans('messages.user.paswrd'),'id' => 'password') ) !!}
                        <span class="text-danger password_error">{{ $errors->first('password') }}</span>
                      </div>
                    </div>

                    <div class="col-lg-12">
                     <div class="forminput" >
                      <div class="_style_3jmRTe" >
                        <div class="">
                          <div class="autocomplete-input">
                            {!! Form::text('home_address', '', ['class' => '_style_3vhmZK','placeholder' => trans('messages.profile.profile_city'),'id' => 'home_address','autocomplete' => 'false','style' => 'width:100%']) !!}
                          </div>
                          <ul class="autocomplete-results home_address">
                          </ul>
                        </div>
                        
                        
                        <input type="hidden" name="city" id='city' value="">
                        <input type="hidden" name="state" id="state" value="">
                        <input type="hidden" name="country" id="country" value="">
                        <input type="hidden" name="address_line1" id="address_line1" value="">
                        <input type="hidden" name="address_line2" id="address_line2" value="">
                        <input type="hidden" name="postal_code" id="postal_code">
                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                      </div>
                      <span class="text-danger home_address_error">{{ $errors->first('home_address') }}</span>
                      <div style="box-sizing:border-box;border:1px solid #E5E5E4;position:absolute;width:100%;background:#FFFFFF;z-index:1000;visibility:hidden;-moz-box-sizing:border-box;" >
                        <div style="max-height:300px;overflow:auto;" >
                          <div aria-live="assertive" >
                            <div style="font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:400;font-size:14px;line-height:24px;padding:8px 18px;border-bottom:1px solid #E5E5E4;" class="_style_1cBulK" >No results
                            </div>
                          </div>
                        </div>
                        
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="forminput">
                     {!! Form::text('referral_code','', array('class' => '_style_3vhmZK text-uppercase','placeholder' => trans('messages.referrals.referral_code'),'id' => 'referral_code') ) !!}
                     <span class="text-danger referral_code_error">{{ $errors->first('home_address') }}</span>
                   </div>
                  </div>

                  <div class="col-lg-12">
                    <input type="hidden" name="step" value="basics">
                    @php
                    $submit_method = site_settings('otp_verification') ? 'send_otp':'check_otp';
                    @endphp

                    <button name="step" value="basics" class="btn btn--primary" id="submit-btn" ng-click="showPopup('{{$submit_method}}');" type="button" style="width: 100%;" >{{trans('messages.user.submit')}}</button>
                    <p>{{trans('messages.user.proceed')}} {{$site_name}} {{trans('messages.user.contact')}}</p>
                  </div>
                  <input type="hidden" name="code" id="code" />
                  {{ Form::close() }}
                    </div>
              </div>
              </div>
            </div>
      </div>
    </div>

    <div class="cls_arriving text--left" style="margin-top:3em">
      <div class="container">
        <div class="row">
          <div class="col-lg-4">
            <div class="cls_arrivingin">
              <img src="images/new/money.svg" alt="banner">

              <h5>{{trans('messages.user.money_make')}}</h5>
              <p>{{trans('messages.user.money_make_content',['site_name' => $site_name])}}</p>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="cls_arrivingin">
              <img src="images/new/driver.svg" alt="banner">

              <h5>{{trans('messages.user.drive_when_want')}}</h5>
              <p>{{trans('messages.user.drive_when_want_content')}} {{$site_name}}, {{trans('messages.user.imp_moments')}}</p>
            </div>
          </div>

           <div class="col-lg-4">
            <div class="cls_arrivingin">
              <img src="images/new/company.svg" alt="banner">

              <h5>{{trans('messages.user.no_office')}}</h5>
              <p>{{trans('messages.user.no_office_content')}} {{$site_name}} {{trans('messages.user.freedom')}}</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</main>
<style>
.cls_arrivingin img {
    height: 80px;
}
</style>
@stop
