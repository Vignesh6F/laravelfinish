@extends('admin.template')

@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" ng-controller="company_management" ng-init="login_user_type ='{{ LOGIN_USER_TYPE }}'; company_doc=''; errors = {{ json_encode($errors->getMessages()) }};">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Edit Company
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      @if(LOGIN_USER_TYPE == 'admin')
      <li><a href="{{ url(LOGIN_USER_TYPE.'/company') }}">Companies</a></li>
      @endif
      <li class="active">Edit</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <!-- right column -->
      <div class="col-md-12">
        <!-- Horizontal Form -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Edit Company Form</h3>
          </div>

          {!! Form::open(['url'=>LOGIN_USER_TYPE.'/edit_company/'.$result->id, 'class'=>'form-horizontal', 'files'=>true, 'id'=>'company_form', 'novalidate']) !!}
          {{ Form::hidden('user_id', $result->id, array('id'=>'user_id')) }}
          <div class="box-body">
            <span class="text-danger">(*)Fields are Mandatory</span>
            <div class="form-group">
              <label for="input_name" class="col-sm-3 control-label">Name <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('name', $result->name, ['class' => 'form-control', 'id' => 'input_name', 'placeholder' => 'Name']) !!}
                <span class="text-danger">{{ $errors->first('name') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_vat_number" class="col-sm-3 control-label">VAT Number</label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('vat_number', $result->vat_number, ['class' => 'form-control', 'id' => 'input_vat_number', 'placeholder' => 'VAT Number']) !!}
                <span class="text-danger">{{ $errors->first('vat_number') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_email" class="col-sm-3 control-label">Email <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('email', $result->email, ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'Email']) !!}
                <span class="text-danger">{{ $errors->first('email') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_password" class="col-sm-3 control-label">Password</label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('password', '', ['class' => 'form-control', 'id' => 'input_password', 'placeholder' => 'Password']) !!}
                <span class="text-danger">{{ $errors->first('password') }}</span>
              </div>
            </div>

            {!! Form::hidden('user_type','Company', ['class' => 'form-control', 'id' => 'user_type', 'placeholder' => 'Select']) !!}
            <div class="form-group">
              <label for="input_country_code" class="col-sm-3 control-label">Country Code<em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                <select class='form-control' id='input_country_code' name='country_code'>
                  <option value=""> Select </option>
                  @foreach($country_code_option as $country_code)
                  <option value="{{@$country_code->phone_code}}" {{ ($country_code->id == $result->country_id) ? 'Selected' : ''}} data-id="{{ $country_code->id }}">{{$country_code->long_name}}</option>
                  @endforeach
                  {!! Form::hidden('country_id', old('country_id') ? old('country_id'):$result->country_id, array('id'=>'country_id')) !!}
                </select>
                <span class="text-danger">{{ $errors->first('country_code') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">Mobile Number</label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('mobile_number', $result->env_mobile_number, ['class' => 'form-control', 'id' => 'mobile_number', 'placeholder' => 'Mobile Number']) !!}
                <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
              </div>
            </div> 

            @if(LOGIN_USER_TYPE!='company') 
            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">Status <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive', 'Pending' => 'Pending'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
                <span class="text-danger">{{ $errors->first('status') }}</span>
              </div>
            </div>
            @endif

            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">Address Line <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('address_line',$result->address, ['class' => 'form-control', 'id' => 'address_line', 'placeholder' => 'Address Line']) !!}
                <span class="text-danger">{{ $errors->first('address_line') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">City </label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('city',$result->city, ['class' => 'form-control', 'id' => 'city', 'placeholder' => 'City']) !!}
                <span class="text-danger">{{ $errors->first('city') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">State</label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('state',$result->state, ['class' => 'form-control', 'id' => 'state', 'placeholder' => 'State']) !!}
                <span class="text-danger">{{ $errors->first('state') }}</span>
              </div>
            </div> 

            <div class="form-group">
              <label for="input_status" class="col-sm-3 control-label">Postal Code <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::text('postal_code',$result->postal_code, ['class' => 'form-control', 'id' => 'postal_code', 'placeholder' => 'Postal Code']) !!}
                <span class="text-danger">{{ $errors->first('postal_code') }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="input_profile" class="col-sm-3 control-label">Profile </label>
              <div class="col-md-7 col-sm-offset-1">
                {!! Form::file('profile', ['class' => 'form-control', 'id' => 'input_profile', 'accept' => 'image/*']) !!}
                <span class="text-danger">{{ $errors->first('profile') }}</span>
                @if(@$result->profile)
                <a href="{{@$result->profile }}" target="_blank"> <img style="width: auto;height:100px;padding-top: 5px;object-fit: contain;" src="{{@$result->profile }}" id="profile_photo"></a>
                @else
                <div class="license-img">
                  <img style="width: auto;height: 100px; padding-top: 5px;object-fit: contain;" src="{{ url('images/user.jpeg')}}">
                </div>
                @endif
              </div>
            </div>

            <div class="col-sm-12">
              <label class="col-sm-3"></label>
              <div class="loading d-none" id="company_loading"></div>
            </div>

            <div class="form-group" ng-repeat="doc in company_doc" ng-cloak ng-if="company_doc">
              <div class="form-group">
              <label class="col-sm-3 control-label">@{{doc.document_name}} <em class="text-danger">*</em></label>
              <div class="col-md-7 col-sm-offset-1">
                <input type="file" name="file_@{{doc.id}}" class="form-control">
                <span class="text-danger">@{{ errors['file_'+doc.id][0] }}</span>
                <div class="license-img" ng-if=doc.document>
                  <a href="@{{doc.document}}" target="_blank">
                    <img style="padding-top: 5px;width: auto;height: 100px;object-fit: contain;" ng-src="@{{doc.document}}">
                  </a>
                </div>
                <div class="license-img" ng-if=!doc.document>
                  <img style="width: auto;height: 100px; padding-top: 5px;object-fit: contain;" src="{{ url('images/driver_doc.png')}}">
                </div>
              </div>
            </div>
            
           <div class="form-group" ng-if="doc.expiry_required=='1'" style="margin-bottom: 0;">
            <label class="col-sm-3 control-label" ng-if="doc.expiry_required=='1'">Expire Date <em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1" ng-if="doc.expiry_required=='1'">
              <input type="text" min="{{ date('Y-m-d') }}" name="expired_date_@{{doc.id}}" value="@{{doc.expired_date}}" class="form-control document_expired" placeholder="Expire date" autocomplete="off">
              <span class="text-danger">@{{ errors['expired_date_'+doc.id][0] }}</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label"> @{{doc.document_name}} Status<em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
              @if(LOGIN_USER_TYPE != 'company')
                <select class ='form-control' name='@{{doc.doc_name}}_status'>
                  <option value="0" ng-selected="doc.status==0">Pending</option>
                  <option value="1" ng-selected="doc.status==1">Approved</option>
                  <option value="2" ng-selected="doc.status==2">Rejected</option>
                </select>
              @else
                <div ng-if="doc.status==0"> Pending </div>
                <div ng-if="doc.status==1"> Approved </div>
                <div ng-if="doc.status==2"> Rejected </div>
              @endif
          </div>
        </div>

          </div>
         
          <div class="form-group">
          @if($result->id != 1)
          <div class="form-group">
            <label for="input_service_fee" class="col-sm-3 control-label">Company Commission <em class="text-danger">*</em></label>
            <div class="col-md-7 col-sm-offset-1">
              <div class="input-group"> 
                {!! Form::text('company_commission', $result->company_commission, ['class' => 'form-control', 'id' => 'input_service_fee', 'placeholder' => 'Company Commission','readonly'=>LOGIN_USER_TYPE == 'company'?true:false]) !!}
                <div class="input-group-addon" style="background-color:#eee;">%</div>
                <span class="text-danger">{{ $errors->first('company_commission') }}</span>
              </div>
            </div>
          </div>
          @endif
        </div>
           </div>
          <!-- /.box-body -->

          <div class="box-footer text-center">
            <button type="submit" class="btn btn-info" name="submit" value="submit">Submit</button>
            @if(LOGIN_USER_TYPE == 'company')
            <a href="{{url(LOGIN_USER_TYPE.'/edit_company/'.$result->id)}}"><span class="btn btn-default">Cancel</span></a>
            @else
            <a href="{{url(LOGIN_USER_TYPE.'/company')}}"><span class="btn btn-default">Cancel</span></a>
            @endif
          </div>
          <!-- /.box-footer -->
          {!! Form::close() !!}
        </div>
        <!-- /.box -->
      </div>
      <!--/.col (right) -->
    </div>
    <!-- /.row -->
  </div>
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
@endsection

@push('scripts')
<script>
  var datepicker_format = 'dd-mm-yy';
  $('#license_exp_date').datepicker({ 'dateFormat': datepicker_format, maxDate: new Date()});
  $(function () {
    $("#yearDate").datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange: '1950:' + new Date().getFullYear().toString(),
      dateFormat: datepicker_format,
    });
    $('.ui-datepicker').addClass('notranslate');
  });
  $('#insurance_exp_date').datepicker({ 'dateFormat': datepicker_format, maxDate: new Date()});
  $(function () {
    $("#yearDate").datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange: '1950:' + new Date().getFullYear().toString(),
      dateFormat: datepicker_format,
    });
    $('.ui-datepicker').addClass('notranslate');
  });
</script>
@endpush
