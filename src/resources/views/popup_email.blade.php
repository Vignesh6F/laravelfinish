<input type="hidden" value="{{Session::get('pop_email')}}" id="popup_mail">
<div class="modal fade cls_loadpop" id="cls_loadpop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" ng-controller="sent_mail_to_user">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header border-0">
        
        <h5 class="modal-title" id="exampleModalLabel">Get In Touch With Us For Gofer Details!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body">
        <h1 class="succes_msg d-none alert alert-success"> Mail Successfully Sent! </h1>
        <form id="popup_mails">
      <div class="form-group row">
        <label for="input_name" class="col-sm-3 col-form-label">Name<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="name" id="input_name" placeholder="Name">
        </div>
      </div>
      <div class="form-group row align-items-center cls_cusradio">
        <label class="col-sm-3 col-form-label" for="formGroupExampleInput2">Choose type<em class="text-danger">*</em></label>
        <div class="col-sm-9 p-sm-0">
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="email_type" value="email" type="radio" id="customRadioInline1" name="choose_type" class="custom-control-input choose_type" checked>
          <label class="custom-control-label" for="customRadioInline1">Email</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="skipe_type" value="skype"  type="radio" id="customRadioInline2" name="choose_type" class="custom-control-input choose_type">
          <label class="custom-control-label" for="customRadioInline2">Skype</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="whatsapp_type" value="whatsapp"  type="radio" id="customRadioInline3" name="choose_type" class="custom-control-input choose_type">
          <label class="custom-control-label" for="customRadioInline3">Whatsapp</label>
        </div>
      </div>
      </div>
      <div class="form-group row email_type">
        <label for="email_type" class="col-sm-3 col-form-label">Email<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" name="input_email" class="form-control" id="email_type" placeholder="Email">
        </div>
      </div>
      <div class="form-group row skipe_type d-none">
        <label for="skipe_id" class="col-sm-3 col-form-label">Skype ID<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" name="input_skipe" class="form-control" id="skipe_type" placeholder="Skype Id">
        </div>
      </div>
      <div class="form-group row whatsapp_type d-none">
        <label for="phoe_number" class="col-sm-3 col-form-label">Whatsapp<em class="text-danger">*</em></label>
        <div class="col-sm-3 mb-2" style="padding-right: 0px;">
          {!! Form::select('pop_country_code', $country_lists, @$default_country_phone_code, ['class' => 'form-control', 'id' => 'pop_country_code', 'placeholder' => 'Country' , 'style' => '-webkit-appearance: auto;']) !!}
        </div>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="pop_phone_number" id="pop_phone_number" placeholder="Phone Number">
        </div>
      </div>
      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">Message<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <textarea name="pop_message" id="pop_message" class="form-control" rows="6" style="resize: none;"></textarea>
          <small style="display: block;" id="passwordHelpInline" class="text-muted">
         For more assistance, our specialist will get back to you.
        </small>
        </div>
      </div>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
        <div class="col-sm-9">
          <div id="recaptcha_id"></div>
          @if ($errors->has('g-recaptcha-response'))  
           <span class="text-danger">
             {{ $errors->first('g-recaptcha-response') }}
           </span> 
          @endif
        </div>
      </div>
    </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" id="submit_forms" class="btn btn--primary" disabled="disabled">Submit</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var verifyCallback = function(response) {
    $('#submit_forms').removeAttr('disabled');
  };

  var onloadCallback = function() {
    grecaptcha.render('recaptcha_id', {
      'sitekey' : "{{api_credentials('site_key','Recaptcha')}}",
      'callback' : verifyCallback,
      'theme' : 'light'
    });
  };

</script>
