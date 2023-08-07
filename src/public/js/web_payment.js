app.controller('checkout', ['$scope', '$http','$timeout', function($scope, $http, $timeout) {
  
setTimeout(function(){


// Create a client.
  braintree.client.create({
    authorization: $scope.tokenization_key
  }, function(clientErr, clientInstance) {
    // Stop if there was a problem creating the client.
    // This could happen if there is a network error or 
    // if the authorization is invalid.
    if(clientErr) {
      console.error('Error creating client:', clientErr);
      return;
    }

    options = {};
    options.client = clientInstance;
    if($scope.merchant_account_id)
      options.merchantAccountId = $scope.merchant_account_id;

    // Create a PayPal Checkout component.
    braintree.paypalCheckout.create(options, function(paypalCheckoutErr, paypalCheckoutInstance) {

      // Stop if there was a problem creating PayPal Checkout.
      // This could happen if there was a network error or 
      // if it's incorrectly configured.
      if(paypalCheckoutErr) {
        swal({
          text: paypalCheckoutErr.message,
          icon: "error",
          button: 'Back',
        }).then((value) => {
          window.location.href = APP_URL+'/api/payment/cancel';
        });
      }

      // Two buttons rendered here
        // Load the PayPal JS SDK (see Load the PayPal JS SDK section)
        paypal.Buttons({
          style: {
            color : 'black',
            label : 'pay',
            height : 45
          },
          fundingSource: paypal.FUNDING.PAYPAL,
          createOrder: function() {
            if($scope.paypal_total==0) {
              $('#payment_type').val('Paypal');
              $('.payment-form').addClass('loader');
              $('#checkout_payment').submit();
            } else {
              return paypalCheckoutInstance.createPayment({
                flow: 'checkout', // Required
                amount: $scope.amount, // Required
                currency: $scope.currency_code, // Required, must match the currency passed in with loadPayPalSDK
                intent: 'sale', // Must match the intent passed in with loadPayPalSDK
              });
            }
          },
          onApprove: function(data,actions) {
            return paypalCheckoutInstance.tokenizePayment(data, function(err,payload) {
              $('#nonce').val(payload.nonce);
              $('#payment_type').val('Paypal');
              $('.payment-form').addClass('loader');
              $('#checkout_payment').submit();
            });
          },
          onCancel: function(data) {
            console.log('PayPal payment cancelled', JSON.stringify(data, 0, 2));
          },
          onError: function(err) {

            title = 'Payment Failed';
            text = 'Currency error';
            button = 'Back';

            swal({
              title: title,
              text: text,
              icon: "error",
              button: button,
            }).then((value) => {
             window.location.href = APP_URL+'/api/payment/cancel';
            });
          }
        }).render('#paypal-buttons');
    });
  });
})

}]);