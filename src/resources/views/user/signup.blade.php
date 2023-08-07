@extends('templatesign')

@section('main')
<div class="cls_signin">
  <div class="container">
    <div class="col-lg-12">
      <h2 class="title">{{trans('messages.home.siginup')}}</h2>
       @if(Auth::user()==null)
      <div class="col-lg-4">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.driver')}}</h4>
          <p >{{trans('messages.profile.track_every')}}</p>
          <a href="{{ url('signup_driver') }}">{{trans('messages.profile.driver_signup')}} <img src="images/new/arrow-right.svg" alt="arrow">
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.rider')}}</h4>
          <p >{{trans('messages.profile.trip_history')}}</p>
          <a href="{{ url('signup_rider') }}">{{trans('messages.profile.rider_signup')}} <img src="images/new/arrow-right.svg" alt="arrow">
          </a>
        </div>
      </div>  
       @endif
        @if(Auth::guard('company')->user()==null)
         <div class="col-lg-4">
            <div class="cls_signintext">
              <h4 >{{trans('messages.home.company')}}</h4>
              <p >{{trans('messages.home.company_history')}}</p>
              <a href="{{ url('signup_company') }}">{{trans('messages.home.company_signup')}} <img src="images/new/arrow-right.svg" alt="arrow">
              </a>
            </div>
          </div>  
       @endif
    </div>
  </div>
</div>

@stop