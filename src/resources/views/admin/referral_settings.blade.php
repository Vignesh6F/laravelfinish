@extends('admin.template')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" ng-controller="referal_fare">
  <!-- Content Header (Page header) -->
  <section class="content-header" ng-init="driver_trips={{old('driver_trips')??$driver_result['number_of_trips']}};driver_days={{old('driver_days')??$driver_result['number_of_days']}};driver_amount={{old('driver_amount')??$driver_result['referral_amount']}};rider_trips={{old('rider_trips')??$rider_result['number_of_trips']}};rider_days={{old('rider_days')??$rider_result['number_of_days']}};rider_amount={{old('rider_amount')??$rider_result['referral_amount']}};">
    <h1> {{ $main_title }} </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="{{ url('admin/referral_settings') }}"> {{ $main_title }} </a></li>
      <li class="active"> Edit </li>
    </ol>
  </section>
  <!-- Main content -->
  <div class="row">
    <!-- right column -->
    <div class="col-md-12">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"> Manage Driver Referral Settings Form </h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        {!! Form::open(['url' => $update_url, 'class' => 'form-horizontal', 'method'=> 'POST']) !!}
        <div class="box-body">
          <div class="form-group">
            <label for="input_driver_trips" class="col-sm-3 control-label">Number Of Trips<em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
              {!! Form::hidden('user_type','driver') !!}
              <input type="text" name="driver_trips"  class="form-control" id="input_driver_trips" placeholder="Number Of Trips" ng-model="driver_trips" numbers-only>
              {{-- {!! Form::text('driver_trips', old('driver_trips',$driver_result['number_of_trips']), ['class' => 'form-control', 'id' => 'input_driver_trips', 'placeholder' => 'Number Of Trips']) !!} --}}
              <span class="text-danger">{{ $errors->first('driver_trips') }}</span>
            </div>
          </div>
          <div class="form-group">
            <label for="input_driver_days" class="col-sm-3 control-label">Time Frame (In days)<em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
              <input type="text" name="driver_days" class="form-control" id="input_driver_days" placeholder="Time Frame" ng-model="driver_days" numbers-only>
           {{--    {!! Form::text('driver_days', old('driver_days',$driver_result['number_of_days']), ['class' => 'form-control', 'id' => 'input_driver_days', 'placeholder' => 'Time Frame']) !!} --}}
              <span class="text-danger">{{ $errors->first('driver_days') }}</span>
            </div>
          </div>          
          <div class="form-group">
            <label for="input_driver_currency" class="col-sm-3 control-label">Currency Code</label>
            <div class="col-md-7 col-sm-offset-1">
              {!! Form::select('driver_currency', $currency, old('driver_currency',$driver_result['currency_code']), ['class' => 'form-control', 'id' => 'input_driver_currency']) !!}
              <span class="text-danger">{{ $errors->first('driver_currency') }}</span>
            </div>
          </div>
          <div class="form-group">
            <label for="input_driver_amount" class="col-sm-3 control-label">
              Amount for Trips<em class="text-danger">*</em>
            </label>
            <div class="col-md-7 col-sm-offset-1">
               <input type="text" name="driver_amount"  class="form-control" id="input_driver_amount" placeholder="Amount for Trips" ng-model="driver_amount" numbers-only>
              {{-- {!! Form::text('driver_amount', old('driver_amount',$driver_result['referral_amount']), ['class' => 'form-control', 'id' => 'input_driver_amount', 'placeholder' => 'Amount for Trips']) !!} --}}
              <span class="text-danger">{{ $errors->first('driver_amount') }}</span>
            </div>
          </div>
          
        </div>
        <!-- /.box-body -->
        <div class="box-footer text-center">
          <button type="submit" class="btn btn-info" name="submit" value="submit">Submit</button>
        </div>
        <!-- /.box-footer -->
        {!! Form::close() !!}
      </div>
      <!-- /.box -->
    </div>
    <!--/.col (right) -->
  </div>

  <div class="row">
    <!-- right column -->
    <div class="col-md-12">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"> Manage Rider Referral Settings Form </h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        {!! Form::open(['url' => $update_url, 'class' => 'form-horizontal', 'method'=> 'POST']) !!}
        <div class="box-body">
          <div class="form-group">
            <label for="input_rider_trips" class="col-sm-3 control-label">Number Of Trips<em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
              {!! Form::hidden('user_type','rider') !!}
               <input type="text" name="rider_trips"  class="form-control" id="input_rider_trips" placeholder="Number Of Trips" ng-model="rider_trips" numbers-only>            
              <span class="text-danger">{{ $errors->first('rider_trips') }}</span>
            </div>
          </div>
          <div class="form-group">
            <label for="input_rider_days" class="col-sm-3 control-label">Time Frame (In days)<em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
               <input type="text" name="rider_days"  class="form-control" id="input_rider_days" placeholder="Time Frame" ng-model="rider_days" numbers-only>
             
              <span class="text-danger">{{ $errors->first('rider_days') }}</span>
            </div>
          </div>          
          <div class="form-group">
            <label for="input_rider_currency" class="col-sm-3 control-label">Currency Code</label>
            <div class="col-md-7 col-sm-offset-1">
              {!! Form::select('rider_currency', $currency, old('rider_currency',$rider_result['currency_code']), ['class' => 'form-control', 'id' => 'input_rider_currency']) !!}
              <span class="text-danger">{{ $errors->first('rider_currency') }}</span>
            </div>
          </div>
          <div class="form-group">
            <label for="input_rider_amount" class="col-sm-3 control-label">
              Amount for Trips<em class="text-danger">*</em>
            </label>
            <div class="col-md-7 col-sm-offset-1">
                <input type="text" name="rider_amount"  class="form-control" id="input_rider_amount" placeholder="Amount for Trips" ng-model="rider_amount" numbers-only>
              
              <span class="text-danger">{{ $errors->first('rider_amount') }}</span>
            </div>
          </div>
          
        </div>
        <!-- /.box-body -->
        <div class="box-footer text-center">
          <button type="submit" class="btn btn-info" name="submit" value="submit">Submit</button>
        </div>
        <!-- /.box-footer -->
        {!! Form::close() !!}
      </div>
      <!-- /.box -->
    </div>
    <!--/.col (right) -->
  </div>
  <!-- /.row -->
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
@stop