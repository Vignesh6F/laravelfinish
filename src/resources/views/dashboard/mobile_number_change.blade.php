@php $otp_verification = site_settings('otp_verification'); @endphp
<div class="modal otp-popup text-left poppayout fade" id="otp_popup" aria-hidden="false" style="" tabindex="-1">
	<div id="modal-add-otp-set-address" class="modal-content">
		<div class="panel-header">
			<button type="button" class="close" data-dismiss="modal"></button>
			<h3>
				@if($otp_verification)
				{{ trans('messages.signup.otp') }} @{{resend_otp}}
				@else
				{{ trans('messages.profile.change') }}
				@endif
			</h3>
		</div>
		<div class="flash-container otp-flash-message alert-success success_msg" id="otp_resended_flash" style="display: none;">
			{{trans('messages.signup.otp_resended')}}
		</div>
		<div class="panel-body">
			<div class="otp-number row">
				<div class="col-xs-4">
					<div class="layout__item country-input" id="country">
						<div id="select-title-stage" class="country-code">
							<!-- {{old('country_code')!=null? '+'.old('country_code') : '+1'}} -->
							+{{ $result->country_code}}
						</div>
						<div class="select">
							<select name="country_code" tabindex="-1" id="mobile_country" class="square borderless--right">
								@foreach($country as $key => $value)
								<option value="{{$value->phone_code}}" {{ $value->id == $result->country_id ? 'selected' : ''}} data-value="+{{ $value->phone_code}}"  data-name="{{ $value->short_name }}">{{ $value->long_name}}
								</option>
								@endforeach
							</select> 
							<span class="text-danger country_code_error">{{ $errors->first('country_code') }}</span>               
						</div>
					</div>
				</div>
				<div class="col-xs-8">
					
					{!! Form::number('mobile', '', ['id' => 'mobile_input','class'=>'mobile-input form-control','placeholder' => trans('messages.profile.mobile')]) !!}

					<span class="text-danger mobile_number_error"></span>
				</div>
			</div>
			@if($otp_verification)
			<div class="otp-field">
				<div class="otp-input">
					{!! Form::number('otp', '', ['id' => 'otp_input','class'=>'form-control','placeholder' => trans('messages.signup.otp')]) !!}
					<span class="text-danger otp_error"></span>
				</div>
			</div>
			@endif
		</div>
		<div class="panel-footer otp_footer">
			@if($otp_verification)
			<input type="button" value="{{ trans('messages.signup.send_otp') }}" class="btn blue-signin-btn" ng-click="changeNumberPopup('send_otp');">
			@endif
			<input type="button" value="{{ trans('messages.user.submit') }}" class="btn blue-signin-btn" ng-click="changeNumberPopup('check_otp');">
		</div>
	</div>
</div>
