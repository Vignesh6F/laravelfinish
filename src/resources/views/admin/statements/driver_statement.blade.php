@extends('admin.template')
@section('main')
<div class="content-wrapper" ng-controller="statements">
	<section class="content @{{ isLoading ? 'loading' : ''}}" ng-cloak>
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-body">
						<br/>
						<input type="hidden" value="{{ $driver_id }}" id="driver_id">
						<div class="box-header" style="height: 54px;">
							<!-- <h3 class="box-title">{{ $count_text }}</h3> -->
						</div>
						<div class="box-header">
							<div class="row">
								<div class="col-lg-3 col-xs-6">
									<div class="small-box">
										<div class="inner">
											<h3>{{ $overall_earning }}</h3>
											<p>{{LOGIN_USER_TYPE=='company'?'Earnings':'Total Amount Received'}}</p>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-xs-6">
									<div class="small-box ">
										<div class="inner">
											<h3>{{ $overall_commission }}</h3>
											<p>{{LOGIN_USER_TYPE=='company'?'Admin Commission':'Total Earnings'}}</p>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-xs-6">
									<div class="small-box">
										<div class="inner">
											<h3>{{ $overall_rides }}</h3>
											<p>Completed Rides</p>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-xs-6">
									<div class="small-box">
										<div class="inner">
											<h3>{{ $cancelled_rides }}</h3>
											<p>Cancelled Rides</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<table id="driver_statement_table" class="table table-condensed">
							<thead>
					            <tr>
					                <th ng-repeat="column in table_columns"> @{{ column.title }} </th>
					            </tr>
					        </thead>
					        <tbody>
					            <tr ng-repeat="statement in statements">
					                <td ng-repeat="column in table_columns">
					                	<p ng-if="column.name != 'action'"> @{{ statement[column.data] }} </p>
					                	<p ng-if="column.name == 'action'"> <a href="@{{ statement[column.data] }}" class="btn btn-xs btn-primary"> View Trip Details </a> </p>
					                </td>
					            </tr>
					        </tbody>
						</table>
						<div class="pull-right">
							<statements-pagination></statements-pagination>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
@endsection
@push('scripts')
<link rel="stylesheet" href="{{ url('css/buttons.dataTables.css') }}">
<script src="{{ url('js/dataTables.buttons.js') }}"></script>
<script src="{{ url('js/buttons.server-side.js') }}"></script>
<script>
	app.controller('statements', function($scope, $http) {
	  	$scope.table_columns = [
			{data: 'id', name: 'id', title: 'Booking ID'},
			{data: 'pickup_location',name: 'pickup_location',title: 'Pickup Location'},
			{data: 'drop_location',name: 'drop_location',title: 'Drop Location'},
			{data: 'action',name: 'action',title: 'Trips Details'},
			{data: 'commission',name: 'commission',title: 'Admin Commission'},
			{data: 'created_at',name: 'created_at',title: 'Dated on'},
			{data: 'status',name: 'status',title: 'Status'},
			{data: 'total_amount',name: 'total_amount',title: 'Earned'}
		];
		$scope.isLoading = true;
		$scope.statements = [];
		$scope.totalPages = 0;
		$scope.currentPage = 1;
		$scope.range = [];
		$scope.offset = 3;

		$scope.generatepageNumber = function(pagination) {
			if (!pagination.to) {
				return [];
			}
			let from = pagination.current_page - $scope.offset;
			if (from < 1) {
				from = 1;
			}
			let to = from + ($scope.offset * 2);
			if (to >= pagination.last_page) {
				to = pagination.last_page;
			}
			let pagesArray = [];
			for (let page = from; page <= to; page++) {
				pagesArray.push(page);
			}
			return pagesArray;
		};

		$scope.getStatmentData = function(pageNumber = 1) {
			$scope.isLoading = true;
			let url = '{{ url(LOGIN_USER_TYPE."/driver_statement")  }}';
			let data = {
				driver : $('#driver_id').val(),
			};

			$http.post(url+'?page='+pageNumber,data).success(function(response) {
				$scope.statements   = response.data;
				$scope.totalPages   = response.last_page;
				$scope.currentPage  = response.current_page;

				$scope.range = $scope.generatepageNumber(response);
				$scope.isLoading = false;
			});
		};
		$(document).on('ready',function() {
			$scope.getStatmentData();
		});
	});
</script>
@endpush