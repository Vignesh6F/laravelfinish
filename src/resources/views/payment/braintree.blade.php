
<div class="cls_paypal" >
  <div class="content">   
    

      <!-- Bootstrap inspired Braintree Hosted Fields example -->
<div class="bootstrap-basic">

   
<div class="wrapper">
        <div class="checkout container">
                <section>
                    <div class="bt-drop-in-wrapper">
                        <div id="bt-dropin"></div>
                    </div>
                </section>
                <p class="error_msg" style="color: red"></p>
                <button type="submit">Pay {{$data['currency_code']}}{{$data['amount']}}</button>
        </div>
    </div>


  </div>
</div>

 @push('scripts')    
 <script src="https://js.braintreegateway.com/web/dropin/1.27.0/js/dropin.min.js"></script>
 <script src='https://js.braintreegateway.com/web/3.76.2/js/three-d-secure.min.js'></script>

  <script>
        var form = document.querySelector('#checkout_payment');
        var client_token = "{{ $data['data']['get_user_token']->bt_clientToken }}";
        let type = '{{ request()->mode }}';
        // console.log(type == '' ? "#222222" : ('light' ? '#222222' : '#fff')) 
        if(type == ''){
          var color = '#222222'
        }
        else 
        {
          var color = type == 'light' ? '#222222' : '#fff';
        }
        var threeDSecureParameters = {
            amount: '{{$data["amount"]}}'
          };


        braintree.dropin.create({
            authorization: client_token,
            selector: '#bt-dropin',
            threeDSecure: true,
             paypal: {
              flow: 'checkout',
              amount: '{{$data["amount"]}}',
              currency: '{{$data["currency_code"]}}'
            },
             card: {
              overrides: {
                styles: {
                  input: {
                    color: 'blue',
                    'font-size': '18px'
                  },
                  '.number': {
                    'color':color
                  },
                  '.expirationDate': {
                    'color':color
                  },
                  '.cvv': {
                    'color':color
                  },
                  '.invalid': {
                    color: 'red'
                  }
                }
              }
            }
        }).then(function (dropinInstance) {
          $('.error_msg').html('');
            form.addEventListener('submit', function (event) {
                    event.preventDefault();
                dropinInstance.requestPaymentMethod({
                  threeDSecure: threeDSecureParameters
                }).then(function (payload) {
                    // Add the nonce to the form and submit
                    document.querySelector('#nonce').value = payload.nonce;
                    $('#payment_type').val('Braintree');
                    $('.payment-form').addClass('loader');
                    form.submit();
                }).catch(function (error) {
                  $('.error_msg').html(error.message)
                });
            });
        }).catch(function (error) {
          $('.error_msg').html(error.message)
        });
    </script>

@endpush
