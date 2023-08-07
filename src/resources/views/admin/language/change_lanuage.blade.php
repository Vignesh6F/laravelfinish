@extends('admin.template')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
    Manage Language file
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Manage Language file</li>
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
            <h3 class="box-title">Manage Language file Form</h3>
          </div>
          <!-- /.box-header -->
          <!-- form start -->
          {!! Form::open(['url' => route('language.update_locale'), 'class' => '']) !!}
          <div class="box-body">
            <span class="text-danger">(*)Fields are Mandatory</span>
            <div class="form-group" style="margin-top: 20px;">
                  <label for="input_currency_code" class="col-sm-2 text-right control-label" style="font-size: 16px;text-transform: capitalize;">Language</label>
                  <div class="col-sm-10">
                    {!! Form::select('lanuage',$all_lanuage, $select_lang, ['class' => 'form-control', 'id' => 'input_locale_lanuage', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('lanuage') }}</span>
                  </div>
            </div>
            <div class="">

       <div  class="col-sm-12 cls_change1">
        <div class="col-lg-3">
          
          <ul class="nav nav-tabs tabs-left sideways">
            @foreach($language as $key => $value)
             @if(is_array($value))
            <li class="{{$key == 'home' ? 'active' : ''}}"><a href="#{{$key}}" data-toggle="tab">{{$key}}</a></li>
             @else
            <li><a href="#{{$key}}" data-toggle="tab">{{$key}}</a></li>
              @endif
              @endforeach
          </ul>
        </div>

        <div class="col-lg-9">
          
          <div class="tab-content changelang_title">
            @foreach($language as $key => $value)
            @if(is_array($value))
            <div class="tab-pane {{$key == 'home' ? 'active' : ''}}" id="{{$key}}">
               <h1>{{$key}}:</h1>
             @foreach($value as $sub_key => $sub_value)
             
                <div class="form-group col-lg-4">
                    <label style="font-size: 16px;text-transform: capitalize;">{{ $sub_key }} </label>
                      <input class="form-control" type="text" name="data[{{$key}}][{{$sub_key}}]" value="{{$sub_value}}">
                    </div>
                   @endforeach
                   </div>
                @else
            <div class="tab-pane " id="{{$key}}">
                 <div class="form-group col-lg-6"><label>{{ $key }} </label>
                  <input type="text" name="data[{{$key}}]" class="form-control" value="{{$value}}"> 
                  </div>
                </div>
              @endif
              @endforeach
          </div>
        </div>

      </div>
               
           
            </div>
            
          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <button type="submit" class="btn btn-info pull-right" name="submit" value="submit">Submit</button>
            <button type="submit" class="btn btn-default pull-left" name="cancel" value="cancel">Cancel</button>
          </div>
          <!-- /.box-footer -->
          {!! Form::close() !!}
        </div>
        <!-- /.box -->
      </div>
      <!--/.col (right) -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
@stop