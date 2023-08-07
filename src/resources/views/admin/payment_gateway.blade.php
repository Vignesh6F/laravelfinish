@extends('admin.template')
@section('main')
<style type="text/css">
	.tooltip-custom {
		display: inline-block;
		position: relative;
		vertical-align: middle;
		line-height: 16px;
		margin: -5px 0 -3px;
		padding: 4px 0;
	}
	.tooltip-custom .icon {
		background: url("../images/seller_settings.png") no-repeat;
		background-size: 120px 200px;
	}
	.tooltip-custom .icon {
		display: block;
		width: 14px;
		height: 14px;
		background-position: -95px -150px !important;
		opacity: 0.4;
	}
	.tooltip-custom em {
		font-weight: normal;
		font-size: 12px;
		padding: 2px 10px 10px;
		width: 200px;
		white-space: normal;
		line-height: 16px;
		top: 22px;
		text-align: left;
		margin-left: -100px !important;
		left: 50%;
		background: #2c3239;
		border-radius: 2px;
		color: #fff;
		font-style: normal;
		position: absolute;
		display: none;
		z-index: 1;
	}
	.tooltip-custom em::after{
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		border-color: #2c3239 transparent transparent;
		border-image: none;
		border-style: solid;
		border-width: 3px;
		top: -6px;
		content: "";
		left: 50%;
		margin-left: -3px;
		position: absolute;
		transform: rotate(180deg);
	}
	.tooltip-custom:hover em {
		display: block !important;
	}
	.box-body
	{
		padding: 0;
	}
</style>
<div class="content-wrapper">
	<section class="content-header">
		<h1> Payment Gateway </h1>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"> <i class="fa fa-dashboard"></i> Home </a>
			</li>
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/payment_gateway') }}"> Payment Gateway </a>
			</li>
			<li class="active"> Edit </li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-info">
					<div class="box-header with-border">
						<h3 class="box-title"> Payment Gateway Form </h3>
					</div>
					{!! Form::open(['url' => 'admin/payment_gateway', 'class' => 'form-horizontal']) !!}
					<div class="box-body">
						<span class="text-danger">(*)Fields are Mandatory</span>
						<!-- Paypal Section Start -->
						<div class="box-body">
							<div class="form-group" ng-init="paypal_enabled={{ old('paypal_enabled',payment_gateway('is_enabled','Paypal')) }}">
								<label for="input_paypal_enabled" class="col-sm-3 control-label">Is Paypal Enabled <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paypal_enabled', array('0' => 'No', '1' => 'Yes'), '', ['class' => 'form-control', 'id' => 'input_paypal_enabled', 'ng-model' => 'paypal_enabled']) !!}
									<span class="text-danger">{{ $errors->first('paypal_enabled') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paypal_mode" class="col-sm-3 control-label">PayPal Mode <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paypal_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('paypal_mode',payment_gateway('mode','Paypal')), ['class' => 'form-control', 'id' => 'input_paypal_mode']) !!}
									<span class="text-danger">{{ $errors->first('paypal_mode') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paypal_id" class="col-sm-3 control-label">PayPal Id <em ng-show="paypal_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_id', old('paypal_id',payment_gateway('paypal_id','Paypal')), ['class' => 'form-control', 'id' => 'input_paypal_id', 'placeholder' => 'PayPal Id']) !!}
									<span class="text-danger">{{ $errors->first('paypal_id') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paypal_client" class="col-sm-3 control-label">PayPal Client ID <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_client', old('paypal_client',payment_gateway('client','Paypal')), ['class' => 'form-control', 'id' => '', 'placeholder' => 'PayPal Client']) !!}
									<span class="text-danger">{{ $errors->first('paypal_client') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paypal_secret" class="col-sm-3 control-label"> PayPal Secret <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_secret', old('paypal_secret',payment_gateway('secret','Paypal')), ['class' => 'form-control', 'id' => '', 'placeholder' => 'PayPal Secret']) !!}
									<span class="text-danger">{{ $errors->first('paypal_secret') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="accessToken" class="col-sm-3 control-label"> PayPal Access Token <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_access_token', old('paypal_access_token',payment_gateway('access_token','Paypal')), ['class' => 'form-control', 'id' => 'accessToken', 'placeholder' => 'PayPal Access Token']) !!}
									<span class="text-danger">{{ $errors->first('paypal_access_token') }}</span>
								</div>
							</div>
						</div>
						<!-- Paypal Section End -->
						<!-- Stripe Section Start -->
						<div class="box-body" ng-init="stripe_enabled={{ old('stripe_enabled',payment_gateway('is_enabled','Stripe')) }}">
							<div class="form-group">
								<label for="input_stripe_enabled" class="col-sm-3 control-label">Is Stripe Enabled <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('stripe_enabled', array('0' => 'No', '1' => 'Yes'), old('stripe_enabled',payment_gateway('is_enabled','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_enabled','ng-model' => 'stripe_enabled']) !!}
									<span class="text-danger">{{ $errors->first('stripe_enabled') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_publish_key" class="col-sm-3 control-label"> Stripe Key <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_publish_key', old('stripe_publish_key',payment_gateway('publish','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_publish_key', 'placeholder' => 'Stripe Key']) !!}
									<span class="text-danger">{{ $errors->first('stripe_publish_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_secret_key" class="col-sm-3 control-label"> Stripe Secret <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_secret_key', old('stripe_secret_key',payment_gateway('secret','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_secret_key', 'placeholder' => 'Stripe Secret']) !!}
									<span class="text-danger">{{ $errors->first('stripe_secret_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_api_version" class="col-sm-3 control-label"> Stripe API Version <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_api_version', old('stripe_api_version',payment_gateway('api_version','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_api_version', 'placeholder' => 'Stripe API Version']) !!}
									<span class="text-danger">{{ $errors->first('stripe_api_version') }}</span>
								</div>
							</div>
						</div>
					</div>
					<!-- Stripe Section End -->
					
					<!-- Braintree Section Start -->
					<div class="box-body" ng-init="bt_enabled={{ old('bt_enabled',payment_gateway('is_enabled','Braintree')) }}">
						<div class="form-group">
								<label for="input_bt_enabled" class="col-sm-3 control-label">Is Braintree Enabled <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('bt_enabled', array('0' => 'No', '1' => 'Yes'), old('bt_enabled',payment_gateway('is_enabled','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_enabled','ng-model' => 'bt_enabled']) !!}
									<span class="text-danger">{{ $errors->first('bt_enabled') }}</span>
								</div>
							</div>
						<div class="form-group">
							<label for="input_mode" class="col-sm-3 control-label"> Payment Mode <em ng-show="bt_enabled == '1'" class="text-danger">*</em> </label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::select('bt_mode', array('sandbox' => 'Sandbox', 'production' => 'Production'), old('bt_mode'
								,payment_gateway('mode','Braintree')), ['class' => 'form-control', 'id' => 'input_mode']) !!}
								<span class="text-danger">{{ $errors->first('mode') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_merchant_id" class="col-sm-3 control-label"> Braintree Merchant ID <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_merchant_id', old('bt_merchant_id',payment_gateway('merchant_id','Braintree')), ['class' => 'form-control', 'id' => 'input_merchant_id', 'placeholder' => 'Merchant ID']) !!}
								<span class="text-danger">{{ $errors->first('bt_merchant_id') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_merchant_id" class="col-sm-3 control-label">
								Braintree Merchant Account ID
								<span class="tooltip-custom"><i class="icon"></i> 
									<em style="margin-left: -108px;">
										Merchant account ID is a unique identifier for a specific merchant account in your gateway, and is used to specify which merchant account to use when creating a transaction.
									</em>
								</span>
							</label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('merchant_account_id', old('merchant_account_id', payment_gateway('merchant_account_id','Braintree')), ['class'=>'form-control', 'id'=>'input_merchant_account_id', 'placeholder'=>'Braintree Merchant Account Id']) !!}
								<small class="description" style="color: #9da1ab;">
					                For default account id, leave it as empty.
					            </small>
							</div>
						</div>

						<div class="form-group">
							<label for="input_bt_public" class="col-sm-3 control-label"> Braintree Public Key <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_public_key', old('bt_public_key',payment_gateway('public_key','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_public', 'placeholder' => 'Public Key']) !!}
								<span class="text-danger">{{ $errors->first('bt_public_key') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_bt_private_key" class="col-sm-3 control-label"> Braintree Private Key <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_private_key', old('bt_private_key',payment_gateway('private_key','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_private_key', 'placeholder' => 'Private Key']) !!}
								<span class="text-danger">{{ $errors->first('bt_private_key') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_tokenization_key" class="col-sm-3 control-label">
								Braintree Tokenization Key
								<span class="tooltip-custom"><i class="icon"></i> 
									<em style="margin-left: -108px;">
										Manage the ways you authorize requests to Braintree for client requests
									</em>
								</span>
								<em class="text-danger">*</em>
							</label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('tokenization_key', old('tokenization_key',payment_gateway('tokenization_key','Braintree')), ['class'=>'form-control', 'id'=>'input_tokenization_key', 'placeholder'=>'Braintree Tokenization Key']) !!}
								<span class="text-danger">{{ $errors->first('tokenization_key') }}</span>
							</div>
						</div>
					</div>
					<!-- Braintree Section End -->

					<div class="form-group">
							<label for="is_web_payment" class="col-sm-3 control-label"> Web payment <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								<input type="checkbox" name="is_web_payment" id="is_web_payment" value="1" {{old('is_web_payment',payment_gateway('is_web_payment','Common')) ==1 ? 'checked':''}} > 
							</div>
						</div>

					<!-- Payout Methods Section Start -->
						<div class="box-body">
							<div class="form-group">
								<label for="input_payout_methods" class="col-sm-3 control-label"> Payout Methods <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									@foreach(PAYOUT_METHODS as $payout_method)
									<div ng-init="payout_method_{{ $payout_method['key'] }}={{ isPayoutEnabled($payout_method['key']) }}">
										<input type="checkbox" name="payout_methods[]" id="payout_method-{{ $payout_method['key'] }}" value="{{ $payout_method['key'] }}" ng-checked="{{ isPayoutEnabled($payout_method['key']) }}"> <label for="payout_method-{{ $payout_method['key'] }}" ng-model="payout_method_{{ $payout_method['key'] }}"> {{ $payout_method["value"] }} </label>
									</div>										
									@endforeach
								</div>
							</div>
					</div>
					<!-- Payout Methods Section End -->
					
					<div class="box-footer text-center">
						<button type="submit" class="btn btn-info" name="submit" value="submit">Submit</button>
						<button type="reset" class="btn btn-default"> Cancel </button>
					</div>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</section>
</div>
@endsection