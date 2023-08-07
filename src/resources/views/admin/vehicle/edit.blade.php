@extends('admin.template')
@section('main')
<div class="content-wrapper" ng-controller="vehicle_management">
	<section class="content-header">
		<h1>
		Edit Vehicles
		</h1>
		<ol class="breadcrumb">
			<li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home </a></li>
			<li><a href="{{ url(LOGIN_USER_TYPE.'/vehicle') }}"> Vehicles </a></li>
			<li class="active"> Edit </li>
		</ol>
	</section>
	<section class="content" ng-init='vehicle_id="{{$result->id}}"'>
		<div class="row">
			<div class="col-md-12">
				<div class="box box-info">
					<div class="box-header with-border">
						<h3 class="box-title">Edit Vehicles Form</h3>
					</div>
					{!! Form::open(['url' => LOGIN_USER_TYPE.'/edit_vehicle/'.$result->id, 'class' => 'form-horizontal vehicle_form','files' => true,'id'=>'vehicle_form']) !!}
					{!! Form::hidden('user_country_code', @$result->user->country->phone_code, ['id' => 'user_country_code']) !!}
					{!! Form::hidden('user_gender', $result->user->gender, ['id' => 'user_gender']) !!}
					<div class="box-body">
						<span class="text-danger">(*)Fields are Mandatory</span>
						@if (LOGIN_USER_TYPE!='company')
							<div class="form-group" ng-init='company_name = "{{$result->company_id}}"'>
								<label for="input_company" class="col-sm-3 control-label">Company Name <em class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1" ng-init='get_driver()'>
									{!! Form::text('company_name', $company->name, ['class'=>'form-control', 'id'=>'input_company_name', 'readonly'=>'true']) !!}
								</div>
							</div>
						@else
							<span ng-init='company_name="{{Auth::guard("company")->user()->id}}";get_driver()'></span>
						@endif
						<div class="form-group">
							<label for="input_company" class="col-sm-3 control-label">Driver Name <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1" ng-init='driver_name = "{{$result->user_id}}";selectedDriver={{ $result->user_id }}'>
								{!! Form::hidden('driver_name', $result->user_id) !!}
								{!! Form::text('driver', $result->user->first_name.' '.$result->user->last_name.' - '.$result->user->id, ['class'=>'form-control', 'id'=>'driver', 'readonly'=>'true']) !!}
							</div>
						</div>
						<div class="form-group">
							<label for="input_status" class="col-sm-3 control-label">Status <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
								<span class="text-danger">{{ $errors->first('status') }}</span>
							</div>
						</div> 
						<div class="form-group">
			              <label for="input_status" class="col-sm-3 control-label">Make <em class="text-danger">*</em></label>
			              <div class="col-md-7 col-sm-offset-1">
			                {!! Form::select('vehicle_make_id',$make, $result->vehicle_make_id, ['class' => 'form-control', 'id' => 'vehicle_make', 'placeholder' => 'Select']) !!}
			                <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
			              </div>
			            </div>
			            <div class="form-group">
			              <label for="input_status" class="col-sm-3 control-label">Model <em class="text-danger">*</em></label>
			              <div class="col-md-7 col-sm-offset-1" ng-init='vehicle_model_id="{{ $result->vehicle_model_id }}";'>
			              	<span class="loading form-control" id="model_loading" style="display: none;"></span>
			                {!! Form::select('vehicle_model_id', array(), '', ['class'=>'form-control', 'id'=>'vehicle_model', 'placeholder'=>'Select']) !!}
			                <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
			              </div>
			            </div>
						<div class="form-group cls_vehicle">
							<label for="vehicle_type" class="col-sm-3 control-label">Vehicle Type <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1 form-check">
								@php $vehicle_types = explode(',', $result->vehicle_id); @endphp
								@foreach($car_type as $type)
								<li class="col-lg-6">
									<input type="checkbox" name="vehicle_type[]" id="vehicle_type" class="form-check-input vehicle_type" value="{{ $type->id }}" data-error-placement="container" data-error-container="#vehicle_type_error" {{ in_array($type->id,$vehicle_types) ? 'checked' : '' }}/> <span> {{ $type->car_name }}</span>
									</li>
								@endforeach
								</br></br>
								<div class="text-danger" id="vehicle_type_error"></div>
								<span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="default" class="col-sm-3 control-label">Default <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1 form-check" style="padding-top: 6px;">
								{{ Form::radio('default', '1', $result->default_type=='1' ? true:false, ['class' => 'form-check-input default', 'id' => 'default_yes', 'data-error-placement'=>'container', 'data-error-container'=>'#default_error']) }} <span style="margin-right: 15px;">Yes</span>
								{{ Form::radio('default', '0', $result->default_type=='0' ? true:false, ['class' => 'form-check-input default', 'id' => 'default_no', 'data-error-placement'=>'container', 'data-error-container'=>'#default_error']) }} No
								</br>
								<div class="text-danger" id="default_error"></div>
								<span style="color:gray;font-style: italic;"> * If the driver has only one vehicle with active status, it will be automatically selected as default.</span>
							</div>
						</div>
						<div class="form-group">
							<label for="handicap" class="col-sm-3 control-label">Handicap Accessibility Available <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1 form-check" style="padding-top: 6px;">
								{{ Form::radio('handicap', '1', in_array('2',$options) ? true:false, ['class' => 'form-check-input handicap', 'id' => 'handicap_yes', 'data-error-placement'=>'container', 'data-error-container'=>'#handicap_error']) }} <span style="margin-right: 15px;">Yes</span>
								{{ Form::radio('handicap', '0', !in_array('2',$options) ? true:false, ['class' => 'form-check-input handicap', 'id' => 'handicap_no', 'data-error-placement'=>'container', 'data-error-container'=>'#handicap_error']) }} No
								</br>
								<div class="text-danger" id="handicap_error"></div>
							</div>
						</div>
						<div class="form-group">
							<label for="child_seat" class="col-sm-3 control-label">Child Seat Accessibility Available <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1 form-check" style="padding-top: 6px;">
								{{ Form::radio('child_seat', '1', in_array('3',$options) ? true:false, ['class' => 'form-check-input child_seat', 'id' => 'child_seat_yes', 'data-error-placement'=>'container', 'data-error-container'=>'#child_seat_error']) }} <span style="margin-right: 15px;">Yes</span>
								{{ Form::radio('child_seat', '0', !in_array('3',$options) ? true:false, ['class' => 'form-check-input child_seat', 'id' => 'child_seat_no', 'data-error-placement'=>'container', 'data-error-container'=>'#child_seat_error']) }} No
								</br>
								<div class="text-danger" id="child_seat_error"></div>
							</div>
						</div>
						<div class="form-group">
							<label for="request_from" class="col-sm-3 control-label">Request From <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1 form-check" style="padding-top: 6px;">
								{{ Form::radio('request_from', '1', in_array('1',$options) ? true:false, ['class' => 'form-check-input request_from', 'id' => 'request_from_female', 'data-error-placement'=>'container', 'data-error-container'=>'#request_from_error']) }}  <span style="margin-right: 15px;">Female</span>
								{{ Form::radio('request_from', '0', !in_array('1',$options) ? true:false, ['class' => 'form-check-input request_from', 'id' => 'request_from_both', 'data-error-placement'=>'container', 'data-error-container'=>'#request_from_error']) }} Both
								</br>
								<div class="text-danger" id="request_from_error"></div>
								<span style="color:gray;font-style: italic;"> * If the driver is male, it will be automatically selected as both.</span>
							</div>
						</div>
						<div class="form-group">
							<label for="vehicle_number" class="col-sm-3 control-label">Vehicle Number <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('vehicle_number',@$result->vehicle_number, ['class' => 'form-control', 'id' => 'vehicle_number', 'placeholder' => 'Vehicle Number']) !!}
								<span class="text-danger">{{ $errors->first('vehicle_number') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="color" class="col-sm-3 control-label">Color <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('color',@$result->color, ['class' => 'form-control', 'id' => 'color', 'placeholder' => 'Color']) !!}
								<span class="text-danger">{{ $errors->first('color') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="year" class="col-sm-3 control-label">Year <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('year',@$result->year, ['class' => 'form-control', 'id' => 'year', 'placeholder' => 'Year']) !!}
								<span class="text-danger">{{ $errors->first('year') }}</span>
							</div>
						</div>
						<input type="hidden" id="vehicle_id" value="{{$result->id}}">
						<p ng-init='vehicle_doc="";errors={{json_encode($errors->getMessages())}};'></p>

						<div class="col-sm-12">
							<label class="col-sm-3"></label>
							<div class="loading d-none" id="vehicle_loading"></div>
						</div>

						<div class="form-group" ng-repeat="doc in vehicle_doc" ng-cloak ng-if="vehicle_doc">
							<div class="form-group">
							<label class="col-sm-3 control-label"> @{{doc.document_name}} <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								<input type="file" name="file_@{{doc.id}}" class="form-control">
								<span class="text-danger">@{{ errors['file_'+doc.id][0] }}</span>								
								<div class="license-img" ng-if="doc.document">
									<a href="@{{doc.document}}" target="_blank">
										<img style="width: auto;height: 100px;object-fit: contain;padding-top: 5px" ng-src="@{{doc.document}}">
									</a>
								</div>
								<div class="license-img" ng-if="!doc.document">
									<img style="width: auto;height: 100px;object-fit: contain;padding-top: 5px" src="{{ url('images/driver_doc.png')}}">
								</div>
							</div>
							</div>
							<div class="form-group">
							<label class="col-sm-3 control-label" ng-if="doc.expiry_required=='1'">Expire Date <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1" ng-if="doc.expiry_required=='1'">
								<input type="text" name="expired_date_@{{doc.id}}" min="{{ date('Y-m-d') }}" value="@{{doc.expired_date}}" class="form-control document_expired" placeholder="Expire date" autocomplete="off">
								<span class="text-danger">@{{ errors['expired_date_'+doc.id][0] }}</span>
							</div>
							</div>
							<div class="form-group">
							<label class="col-sm-3 control-label">@{{doc.document_name}} Status <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								<select class ='form-control' name='@{{doc.doc_name}}_status'>
									<option value="0" ng-selected="doc.status==0">Pending</option>
									<option value="1" ng-selected="doc.status==1">Approved</option>
									<option value="2" ng-selected="doc.status==2">Rejected</option>
								</select>
							</div>
						</div>
						</div>
					</div>
					<div class="box-footer text-center">
						<button type="submit" class="btn btn-info" name="submit" value="submit"> Submit </button>
						<a href="{{url(LOGIN_USER_TYPE.'/vehicle')}}"><span class="btn btn-default">Cancel</span></a>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</section>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" rel="stylesheet"/>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
<script>
$("#year").datepicker({
    format: "yyyy",
    viewMode: "years", 
    minViewMode: "years",
    autoclose : true,
    startDate: '1950',
    endDate: '<?php echo date('Y'); ?>'
});
</script>
@endsection
