@extends('admin.template')
@section('main')
<div class="content-wrapper">
	<section class="content-header">
		<h1> Api Credentials </h1>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a>
			</li>
			<li>
				<a href="#">Api Credentials</a>
			</li>
			<li class="active">
				Edit
			</li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-info">
					<div class="box-header with-border">
						<h3 class="box-title">Api Credentials Form</h3>
					</div>
					{!! Form::open(['url' => 'admin/api_credentials', 'class' => 'form-horizontal','files' => true]) !!}
					<div class="box-body">
						<span class="text-danger">(*)Fields are Mandatory</span>
						<div class="form-group">
							<label for="input_google_map_key" class="col-sm-3 control-label">Google Map Key<em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('google_map_key', old('google_map_key',api_credentials('key','GoogleMap')), ['class' => 'form-control', 'id' => 'input_google_map_key', 'placeholder' => 'Google Map KEY']) !!}
								<span class="text-danger">{{ $errors->first('google_map_key') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_google_map_server_key" class="col-sm-3 control-label">Google Map Server Key<em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('google_map_server_key', old('google_map_server_key',api_credentials('server_key','GoogleMap')), ['class' => 'form-control', 'id' => 'input_google_map_server_key', 'placeholder' => 'Google Map Server Key']) !!}
								<span class="text-danger">{{ $errors->first('google_map_server_key') }}</span>
							</div>
						</div>
					</div>
					
					<div class="box-body">
						<div class="form-group">
							<label for="input_twillo_sid" class="col-sm-3 control-label">Twillo SID <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('twillo_sid', api_credentials('sid','Twillo'), ['class' => 'form-control', 'id' => 'input_twillo_sid', 'placeholder' => 'Twillo SID']) !!}
								<span class="text-danger">{{ $errors->first('twillo_sid') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_twillo_token" class="col-sm-3 control-label">Twillo Token <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('twillo_token', api_credentials('token','Twillo'), ['class' => 'form-control', 'id' => 'input_twillo_token', 'placeholder' => 'Twillo Token']) !!}
								<span class="text-danger">{{ $errors->first('twillo_token') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_twillo_from" class="col-sm-3 control-label">Twillo From Number <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('twillo_from', api_credentials('from','Twillo'), ['class' => 'form-control', 'id' => 'input_twillo_from', 'placeholder' => 'Twillo From Number']) !!}
								<span class="text-danger">{{ $errors->first('twillo_from') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_twillo_service_sid" class="col-sm-3 control-label">Twillo Service SID <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('twillo_service_sid', api_credentials('service_sid','Twillo'), ['class' => 'form-control', 'id' => 'input_twillo_service_sid', 'placeholder' => 'Twillo Service SID']) !!}
								<span class="text-danger">{{ $errors->first('twillo_service_sid') }}</span>
							</div>
						</div>
					</div>

					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_server_key" class="col-sm-3 control-label">FCM Server Key <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('fcm_server_key', api_credentials('server_key','FCM'), ['class' => 'form-control', 'id' => 'input_fcm_server_key', 'placeholder' => 'FCM Server Key ']) !!}
								<span class="text-danger">{{ $errors->first('fcm_server_key') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label">FCM Sender Id <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('fcm_sender_id', api_credentials('sender_id','FCM'), ['class' => 'form-control', 'id' => 'input_fcm_sender_id', 'placeholder' => 'FCM Sender Id']) !!}
								<span class="text-danger">{{ $errors->first('fcm_sender_id') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label">Facebook Client ID<em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('facebook_client_id', api_credentials('client_id','Facebook'), ['class' => 'form-control', 'id' => 'input_facebook_client_id', 'placeholder' => 'Facebook Client ID']) !!}
								<span class="text-danger">{{ $errors->first('facebook_client_id') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label">Facebook Client Secret<em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('facebook_client_secret', api_credentials('client_secret','Facebook'), ['class' => 'form-control', 'id' => 'input_facebook_client_secret', 'placeholder' => 'Facebook Client Secret']) !!}
								<span class="text-danger">{{ $errors->first('facebook_client_secret') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label"> Google Client ID <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('google_client', old('google_client',api_credentials('client_id','Google')), ['class' => 'form-control', 'id' => 'input_account_secret', 'placeholder' => 'Google Client Id']) !!}
								<span class="text-danger">{{ $errors->first('google_client') }}</span>
							</div>
						</div>

						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label"> Google Client Secret <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('google_client_secret', old('google_client_secret',api_credentials('client_secret','Google')), ['class' => 'form-control', 'id' => 'input_account_secret', 'placeholder' => 'Google Client Id']) !!}
								<span class="text-danger">{{ $errors->first('google_client_secret') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label"> Sinch Key <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('sinch_key', old('sinch_key',api_credentials('sinch_key','Sinch')), ['class' => 'form-control', 'id' => 'input_account_secret', 'placeholder' => 'Sinch Key']) !!}
								<span class="text-danger">{{ $errors->first('sinch_key') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_fcm_sender_id" class="col-sm-3 control-label"> Sinch Secret Key <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('sinch_secret_key', old('sinch_secret_key',api_credentials('sinch_secret_key','Sinch')), ['class' => 'form-control', 'id' => 'input_account_secret', 'placeholder' => 'Sinch Secret Key']) !!}
								<span class="text-danger">{{ $errors->first('sinch_secret_key') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_apple_service_id" class="col-sm-3 control-label"> Apple Service Id <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('apple_service_id', old('apple_service_id',api_credentials('service_id','Apple')), ['class' => 'form-control', 'id' => 'input_apple_service_id', 'placeholder' => 'Apple Service Id']) !!}
								<span class="text-danger">{{ $errors->first('apple_service_id') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_apple_team_id" class="col-sm-3 control-label"> Apple Team Id <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('apple_team_id', old('apple_team_id',api_credentials('team_id','Apple')), ['class' => 'form-control', 'id' => 'input_apple_team_id', 'placeholder' => 'Apple Team Id']) !!}
								<span class="text-danger">{{ $errors->first('apple_team_id') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_apple_key_id" class="col-sm-3 control-label"> Apple Key Id <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('apple_key_id', old('apple_key_id',api_credentials('key_id','Apple')), ['class' => 'form-control', 'id' => 'input_apple_key_id', 'placeholder' => 'Apple Key Id']) !!}
								<span class="text-danger">{{ $errors->first('apple_key_id') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_apple_key_file" class="col-sm-3 control-label"> Apple Key File <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::file('apple_key_file', ['class' => 'form-control', 'id' => 'input_apple_key_file', 'accept' => 'mimes/txt']) !!}
								<span class="text-danger">{{ $errors->first('apple_key_file') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_database_url" class="col-sm-3 control-label"> Firebase Database URL <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('database_url', old('database_url',api_credentials('database_url','Firebase')), ['class' => 'form-control', 'id' => 'input_database_url', 'placeholder' => 'Firebase Database URL']) !!}
								<span class="text-danger">{{ $errors->first('database_url') }}</span>
							</div>
						</div>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="input_service_account" class="col-sm-3 control-label"> Firebase Service Account File (JSON) <em class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::file('service_account', ['class' => 'form-control', 'id' => 'input_service_account', 'accept' => 'mimes/json']) !!}
								<span class="text-danger">{{ $errors->first('service_account') }}</span>
							</div>
						</div>
					</div>
					@if(CheckGetInTuchpopup())
					<div class="box-body">
			            <div class="form-group">
			              <label for="input_recaptcha_site_key" class="col-sm-3 control-label">Recaptcha site key<em class="text-danger">*</em></label>
				             <div class="col-md-7 col-sm-offset-1">
				                {!! Form::text('recaptcha_site_key',old('recaptcha_site_key',api_credentials('site_key','Recaptcha')), ['class' => 'form-control', 'id' => 'input_recaptcha_site_key', 'placeholder' => 'Recaptcha site key']) !!}
				                <span class="text-danger">{{ $errors->first('recaptcha_site_key') }}</span>
				             </div>
			            </div>
			        </div>
		          	<div class="box-body">
		            	<div class="form-group">
		              		<label for="input_recaptcha_secret_key" class="col-sm-3 control-label">Recaptcha secret key<em class="text-danger">*</em></label>
				            <div class="col-md-7 col-sm-offset-1">
				                {!! Form::text('recaptcha_secret_key', old('recaptcha_secret_key',api_credentials('secret_key','Recaptcha')), ['class' => 'form-control', 'id' => 'input_recaptcha_secret_key', 'placeholder' => 'Recaptcha secret key']) !!}
				                <span class="text-danger">{{ $errors->first('recaptcha_secret_key') }}</span>
				            </div>
		            	</div>
		          	</div>
		          	@endif
		          	
					<div class="box-footer text-center">
						<button type="submit" class="btn btn-info" name="submit" value="submit">Submit</button>
						<button type="reset" class="btn btn-default"> Reset </button>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</section>
</div>
@endsection