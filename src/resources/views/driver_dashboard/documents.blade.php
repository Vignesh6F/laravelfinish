<title>Documents</title>
@extends('template_driver_dashboard') 
@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" ng-controller="payment">
  

    {!! Form::open(['url' => 'driver_document', 'class' => '','id'=>'vehicle_form','files' => true]) !!}
      <div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0 0px 15px;">
        <div class="text--left col-lg-12">
          <h1 class="cls_profiletitle"> @lang('messages.driver_dashboard.driver_documents') </h1>
        </div>
        @foreach($driver_documents as $document)     
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 displayflex" style="align-items: end" >
            <label class="col-lg-4 col-md-4 col-sm-4 col-xs-6" style="padding:6px 0px;">{{$document->document_name}}<em class="text-danger">*</em></label>
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12" style="padding:6px 0px;">
              <input type="file" name="{{$document->doc_name}}" class="form-control">
              <span class="text-danger">
                {{ $errors->first($document->doc_name) }} 
              </span>
              @php $image = ($document->document !='') ? $document->document : url('images/driver_doc.png'); @endphp
              <div class="license-img">
              <a href="{{$image}}" target="_blank">
                <img style="width: 200px;height: 100px;object-fit: cover;" src="{{$image}}">
              </a>
              </div>     
            @if($document->expiry_required == '1')
            <div class="" style="margin-top: 10px;">
              <input type="date" min="{{ date('Y-m-d') }}" name="expired_date_{{$document->id}}" class="form-control" value="{{$document->expired_date}}">
              <span class="text-danger"> 
                {{ $errors->first('expired_date_'.$document->id) }}
              </span>         
            </div>
            @endif
            </div>     
          </div>  
      @endforeach
      <div class="separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12 text--center" style="border-bottom:0px !important; margin-top: 20px;">
        <button style="padding: 0px 60px !important;font-size: 19px !important;" type="submit" class="btn btn--primary btn-blue" id="update_btn">{{trans('messages.user.update')}}</button>
    </div>
  </div>
  {{ Form::close() }}


</div>
</div>
</div>
</div>
</div>
</main>
@stop
<style type="text/css">
    .btn-input:hover, .btn:hover, .file-input:hover, .tooltip:hover, .btn, .btn-input, .file-input, .tooltip {
    background: transparent !important;
    border: none !important;
}
.btn--link .icon_left-arrow {
    -webkit-transition: left .4s ease;
    transition: left .4s ease;
    position: relative;
    left: -2;
    padding-left: 10px;
}
.btn--link:focus .icon_left-arrow, .btn--link:hover .icon_left-arrow {
    left: -6px;
}
@media (max-width: 400px){
    #btn-pad.btn.btn--primary.btn-blue{
      font-size: 11px !important;
      padding:0px 20px !important;
    }
}
</style>
