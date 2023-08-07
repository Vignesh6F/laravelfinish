
@extends('templatesign')

@section('main')
<div class="" ng-controller="user">
	<div class="topbanner">
		<div class="container">
			<div class="col-lg-12 displayflex">
				<div class="col-lg-6 col-md-6">
					<div class="topbannertxt">
						<h1>{{trans('messages.home.title')}}</h1>

						<p>{{trans('messages.home.desc')}}</p>

						<ul>
							<li>
								@if($app_links[0]->value !="" )
								<a href="{{$app_links[0]->value}}" target="_blank"><img src="images/new/app.png" alt="app"></a>
								@endif
							</li>
							<li>
								@if($app_links[2]->value !="" )
									<a href="{{$app_links[2]->value}}" target="_blank">
										<img src="{{ url('images/new/google.png') }}" alt="Get it on Googleplay" class="CToWUd bot_footimg">
									</a>
								@endif
							</li>
						</ul>
					</div>
				</div>
				<div class="col-lg-6 col-md-6">
					<div class="topbannerimg">
						<img src="images/new/topbanner.png" alt="banner">
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="alllogin">
		<div class="container">
			@if(Auth::user()==null)
			<div class="col-lg-12 alllogintop">
				{{ Form::open(array('url' => 'driver_register','class' => '')) }}
					@if(Auth::user()==null)
						<div class="col-lg-4">
							<div class="allloginone">
								<h3>{{trans('messages.user.ride_with')}} {{$site_name}}</h3>

								<a href="{{ url('signup_rider') }}">{{trans('messages.home.siginup')}} <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="allloginone">
								<h3>{{trans('messages.home.siginup_drive')}}</h3>

								<a href="{{ url('signup_driver') }}">{{trans('messages.home.siginup')}} <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
					@endif
					@if(Auth::guard('company')->user()==null && Auth::user()==null)
					@endif
					@if(Auth::guard('company')->user()==null)
						<div class="col-lg-4">
							<div class="allloginone">
								<h3>{{trans('messages.home.siginup_company')}}</h3>

								<a href="{{ url('signup_company') }}">{{trans('messages.home.siginup')}} <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
					@endif
					
				{{ Form::close() }}
			</div>
			@endif
			<div class="col-lg-12 allloginbottom">
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/easyway.svg" alt="icon">
						<h4>{{trans('messages.home.easy_way')}}</h4>
						<p>{{trans('messages.home.easy_content')}}</p>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/anywhare.svg" alt="icon">
						<h4>{{trans('messages.home.anywhere')}}</h4>
						<p>{{trans('messages.home.anywhere_content')}}</p>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/lowcost.svg" alt="icon">
						<h4>{{trans('messages.home.lowcost')}}</h4>
						<p>{{trans('messages.home.lowcost_content')}}</p>
					</div>
				</div>
			</div>

			<div class="col-lg-12 allloginbottom1">
				<a href="{{ url('ride') }}">{{trans('messages.home.reason')}} <img src="images/new/arrow-right.svg" alt="arrow"></a>
			</div>
		</div>
	</div>

	<div class="cls_sectionone">
		<div class="container">
			<div class="row displayflex">
				<div class="col-lg-6">
					<div class="cls_sectiononeimg">
						<img src="images/new/image3.jpg" alt="banner">
					</div>
				</div>
				<div class="col-lg-6">
					<div class="cls_sectiononetxt">
						<h4 class="text-twotruncate">{{trans('messages.home.drive_you')}} {{trans('messages.home.you_need')}}</h4>
						<p class="text-threetruncate">{{trans('messages.home.drive_with')}} {{$site_name}}{{trans('messages.home.goals')}}</p>
					</div>
				</div>
			</div>
			<div class="row cls_sectionbtm displayflex">
				<div class="col-lg-6">
					<div class="cls_sectiononetxt">
						<h4 class="text-twotruncate">{{trans('messages.home.drive_you1')}}</h4>
						<p class="text-threetruncate">{{trans('messages.home.goals1')}}</p>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="cls_sectiononeimg">
						<img src="images/new/image2.png" alt="banner">
					</div>
					</div>
				
			</div>
		</div>
	</div>

	<div class="cls_arriving">
		<div class="container">
			<div class="title">
				<h5>{{trans('messages.home.now_arrive')}}</h5>
				<h6>{{trans('messages.home.safe')}}</h6>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="cls_arrivingin">
						<img src="images/new/arrive2.svg" alt="banner">

						<h5>{{trans('messages.home.helping')}}</h5>
						<p>{{trans('messages.home.city_with')}} {{$site_name}} {{trans('messages.home.city_with_content')}}</p>
					</div>
				</div>

				<div class="col-lg-6">
					<div class="cls_arrivingin">
						<img src="images/new/arrive1.svg" alt="banner">

						<h5>{{trans('messages.home.safe_ride')}}</h5>
						<p>{{trans('messages.home.backseat')}} {{$site_name}}{{trans('messages.home.designed')}}</p>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>
@stop
