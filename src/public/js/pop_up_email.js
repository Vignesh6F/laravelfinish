
app.controller('sent_mail_to_user', ['$scope', '$http', function($scope, $http) {
    /*$(document).ready(function(){
           timer();
    })*/;
    var interval;

    var timer = function(){
        interval = setInterval(function(){ 
                        $('.succes_msg').addClass('d-none');
                        if(!$('#popup_mail').val()){
                            $('#cls_loadpop').modal({
                                backdrop: 'static'
                            });
                        }
                    },30000);
    }

    $('#cls_loadpop').on('hide.bs.modal', function () { 
        clearInterval(interval);
        // timer()
        empty_record();
    });
    function empty_record(){
        $('#input_name').val('');
        $('#email_type').val('');
        $('#skipe_type').val('');
        $('#pop_phone_number').val('');
        $('#pop_message').val('');
    }

    $(document).on('click','#submit_forms',function(e){

       if($("#popup_mails").valid()){
                $('#cls_loadpop').find('.modal-content').addClass('loading')
            $http.post(APP_URL+'/pop_up_email',{type:$('input[name=choose_type]:checked', '#popup_mails ').val(),name:$('#input_name').val(),email:$('#email_type').val(),skype:$('#skipe_type').val(),country_code:$('#pop_country_code').val(),phone_number:$('#pop_phone_number').val(),message:$('#pop_message').val()}).then(function(response) {
                $('#cls_loadpop').find('.modal-content').removeClass('loading')
                $('.succes_msg').removeClass('d-none');
                $('#popup_mail').val('success');
                setTimeout(function() {
                    $('#cls_loadpop').modal('hide');
                }, 2000);
            });
       }
    })
    $(".choose_type").click(function(e){
        $('.choose_type').each(function() {
            $('.'+$(this).data('class')).addClass('d-none')
        });
        $('.'+$(this).data('class')).removeClass('d-none')
    });

     var v = $("#popup_mails").validate({
              onkeyup: false,
              onfocusout: false,
              rules: {
                name: { required: true },
                input_email: { required: true,emaill:true },
                input_skipe: { required: true },
                pop_phone_number: { required: true,number:true },
                pop_country_code: { required: true },
                pop_message: { required: true },
              },
              errorElement: "span",
              errorPlacement: function( label, element ) {
                label.insertAfter( element ).addClass('text-danger'); 
              },
            });



      $.validator.addMethod("emaill", function(value, element, param) {
        var hasError = false;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        // var emailblockReg =/^([\w-\.]+@(?!gmail.com)(?!yahoo.com)(?!hotmail.com)([\w-]+\.)+[\w-]{2,4})?$/;
 
        var emailaddressVal = $("#email_type").val();
        console.log(emailaddressVal)
            if(emailaddressVal == '') {
              return false;
            }
         
            else if(!emailReg.test(emailaddressVal)) {
            return false;
            }
         
            return true; 
      },
      $.validator.format("Please enter a valid email address"));

}]);


      