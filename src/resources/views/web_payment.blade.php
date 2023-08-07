<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Payment</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    


    <body ng-app="App">
        <div class="flex-center position-ref full-height" ng-controller="checkout"  ng-init="paypal_currency='{{$currency_code}}'; tokenization_key='{{ payment_gateway('tokenization_key','Braintree') }}'; merchant_account_id='{{ payment_gateway('merchant_id','Braintree') }}';">
            <h1> {{$currency_code}} {{$amount}}</h1>
            <form id="checkout_payment">
                
            <div class="content">      
            <input type="" name="nonce" id="nonce">          
                <div id="paypal-buttons"> paypal</div>
            </div>
            </form>
        </div>
    </body>


{!! Html::script('js/jquery-1.11.3.js') !!}
  {!! Html::script('js/jquery-ui.js') !!}

  {!! Html::script('js/angular.js') !!}
  {!! Html::script('js/angular-sanitize.js') !!}
  {!! Html::script('js/messages.js?v=df') !!}

  <script>
    var app = angular.module('App', ['ngSanitize']);
    var APP_URL = {!! json_encode(url('/')) !!};

  </script>
        {!! Html::script('js/sweetalert.min.js') !!}

    <!-- Load the PayPal JS SDK with your PayPal Client ID-->
    <script src="https://www.paypal.com/sdk/js?client-id={{payment_gateway('client','Paypal') }}&currency=USD"></script>
    <!-- Load the Braintree components -->
    <script src="https://js.braintreegateway.com/web/3.66.0/js/client.min.js"></script>
    <script src="https://js.braintreegateway.com/web/3.66.0/js/paypal-checkout.min.js"></script>

    {!! Html::script('js/web_payment.js?v=') !!}

</html>
