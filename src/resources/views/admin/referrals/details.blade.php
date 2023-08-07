@extends('admin.template')
@section('main')
<div class="content-wrapper">
	<section class="content-header">
		<h1> View Referral Details </h1>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home </a>
			</li>
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/referrals/'.$user_type) }}"> Referral </a>
			</li>
			<li class="active">
				Details
			</li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box ">
					<div class="box-header with-border">
						<!-- <h3 class="box-title">Referral Details</h3> -->
					</div>
					<div class="box-body">
						<div class="table-responsive">
						<table class="table" id="payout_methods">
							<thead>
								<tr class="text-truncate">
									<th> Referral name </th>
									<th> Trips </th>
									<th> Remaining Trips </th>
									<th> Remaining Days </th>
									<th> Amount </th>
									<th> Status </th>
								</tr>
							</thead>
							<tbody>
								@foreach($referral_details as $referral)
								<tr class="text-truncate">
									<td> {{ $referral->referral_user->full_name }} </td>
									<td> {{ $referral->trips }} </td>
									<td> {{ $referral->remaining_trips }} </td>
									<td> {{ $referral->remaining_days }} </td>
									<td> {{ html_string($referral->currency_symbol) }} {{ $referral->amount }} </td>
									<td> {{ $referral->payment_status }} </td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					
						<div class="box-footer text-center">
							<a class="btn btn-default" style="border: 1px solid #efefef;" href="{{ url(LOGIN_USER_TYPE.'/referrals/'.$user_type) }}">Back</a>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	@endsection