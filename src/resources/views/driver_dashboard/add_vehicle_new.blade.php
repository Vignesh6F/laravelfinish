<title>Add Vehicle Details</title>
@extends('template_driver_dashboard') @section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding: 0px;" ng-controller="vehicle_details" ng-init="errors = {{json_encode($errors->getMessages())}};">

  {!! Form::open(['url' => 'update_vehicle', 'class' => '','id'=>'vehicle_form','files' => true]) !!}
  <div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0px 0px 15px;">
     <div class="text--left col-lg-12">
          <h1 class="cls_profiletitle"> Add Vehicle Detail </h1>
        </div>
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_make')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::select('vehicle_make_id',$make, '', ['class' => 'form-control', 'id' => 'vehicle_make', 'placeholder' => trans('messages.driver_dashboard.select')]) !!}
      <span class="text-danger">{{ $errors->first('vehicle_make_id') }}</span>
    </div>
    </div>
    <div class="col-lg-12 form-group vehicle_model">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_model')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::select('vehicle_model_id',$model, '', ['class' => 'form-control', 'id' => 'vehicle_model', 'placeholder' => trans('messages.driver_dashboard.select')]) !!}
      <span class="text-danger">{{ $errors->first('vehicle_model_id') }}</span>
    </div>
    </div>
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_number')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::text('vehicle_number','', ['class' => 'form-control', 'id' => 'vehicle_number', 'placeholder' => trans('messages.driver_dashboard.vehicle_number')]) !!}
      <span class="text-danger">{{ $errors->first('vehicle_number') }}</span>
    </div>
    </div>
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_color')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::text('color','', ['class' => 'form-control', 'id' => 'color', 'placeholder' => trans('messages.driver_dashboard.vehicle_color')]) !!}
      <span class="text-danger">{{ $errors->first('color') }}</span>
    </div>
    </div>
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_year')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {!! Form::text('year','', ['class' => 'form-control', 'id' => 'year', 'placeholder' => trans('messages.driver_dashboard.vehicle_year'),'autocomplete'=>'off']) !!}
      <span class="text-danger">{{ $errors->first('year') }}</span>
    </div>
    </div>
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.vehicle_type')}}</label>
     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      <div class="cls_vehicle">
        @foreach($vehicle_type as $type)
        <li class="col-lg-6 col-md-12 col-12">
          <input type="checkbox" name="vehicle_type[]" id="vehicle_type" class="form-check-input vehicle_type" value="{{ $type->id }}"/> {{ $type->car_name }}
        </li>
        @endforeach
      </div>
        <span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
    </div>
    </div>

    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.handicap')}} {{trans('messages.ride.accessibility')}} {{trans('messages.driver_dashboard.available')}}</label>
      <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {{ Form::radio('handicap', '1', false, ['class'=>'form-check-input']) }} {{ trans('messages.driver_dashboard.yes') }}
      {{ Form::radio('handicap', '0', false, ['class'=>'form-check-input']) }} {{ trans('messages.driver_dashboard.no') }}
      </div>
      <div class="text-danger">{{ $errors->first('handicap') }}</div>
    </div>

    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.child_seat')}} {{trans('messages.ride.accessibility')}} {{trans('messages.driver_dashboard.available')}}</label>
      <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {{ Form::radio('child_seat', '1', false, ['class'=>'form-check-input']) }} {{ trans('messages.driver_dashboard.yes') }}
      {{ Form::radio('child_seat', '0', false, ['class'=>'form-check-input']) }} {{ trans('messages.driver_dashboard.no') }}
      </div>
      <div class="text-danger">{{ $errors->first('child_seat') }}</div>
    </div>

    @if($result->gender=='2')
    <div class="col-lg-12 form-group">
     <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{trans('messages.driver_dashboard.request_from')}}</label>
      <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
      {{ Form::radio('request_from', '1', false, ['class'=>'form-check-input']) }} {{ trans('messages.profile.female') }}
      {{ Form::radio('request_from', '0', false, ['class'=>'form-check-input']) }} {{ trans('messages.driver_dashboard.both') }}
      </div>
      <div class="text-danger">{{ $errors->first('request_from') }}</div>
    </div>
    @else
      {{ Form::hidden('request_from', '0') }}
    @endif

    <div class="form-group">
      @foreach($documents as $document)
      <div class="col-lg-12 form-group">
       <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{$document->document_name}}</label>
       <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
        <input type="file" name="{{$document->doc_name}}" class="form-control" />
        <span class="text-danger">
          {{ $errors->first($document->doc_name) }}
        </span>
      
      @if($document->expire_on_date == 'Yes')
      <div class="" style="margin-top: 10px;">
        <input type="date" min="{{ date('Y-m-d') }}" name="expired_date_{{$document->id}}" class="form-control" autocomplete="off" />
        <span class="text-danger">
          {{ $errors->first('expired_date_'.$document->id) }}
        </span>
      </div>
      </div>
    </div>
      @endif @endforeach
    </div>
     <div class="separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12 text--center" style="border-bottom:0px !important; margin-top: 20px;">
        <button style="padding: 0px 60px !important;font-size: 19px !important;" type="submit" class="btn btn--primary btn-blue" id="update_btn">{{trans('messages.driver_dashboard.vehicle_add')}}</button>
    </div>
    {{ Form::close() }}
  </div>
</div>
</div>
</div>
</div>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="{{ url('admin_assets/plugins/datepicker/bootstrap-datepicker3.css') }}">
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
