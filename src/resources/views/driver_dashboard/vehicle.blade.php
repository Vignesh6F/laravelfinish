<title>{{ trans('messages.driver_dashboard.vehicle_details') }}</title>
@extends('template_driver_dashboard')
@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;">
    
    <div class="page-lead separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12 " style="border-bottom:0px !important; padding: 0">
       <div class="col-lg-6 col-sm-6 col-md-6 col-xs-6">
          <h1 class="cls_profiletitle">{{trans('messages.header.profil')}}</h1>
      </div>
      <div class="col-lg-6 col-sm-6 col-md-6 col-xs-6 text--right">
        <a href="{{ url('add_vehicle') }}" style="padding: 0px 30px !important;
        font-size: 14px !important;" type="submit" class="btn btn--primary btn-blue">
          {{ trans('messages.driver_dashboard.vehicle_details') }}
        </a>
      </div>
    </div>

    <div id="no-more-tables" style="overflow: visible;" class="tr_ico">
        <table class="col-sm-12 table-bordered table-striped table-condensed cf">
            <thead class="cf">
                <tr>
                  <th>{{ trans('messages.user.veh_name') }}</th>
                  <th>{{ trans('messages.driver_dashboard.vehicle_type') }}</th>
                  <th>{{ trans('messages.driver_dashboard.vehicle_number') }}</th>
                  <th>{{ trans('messages.driver_dashboard.status') }}</th>
                  <th></th>
                </tr>
            </thead>                
            <tbody class="driver_trips_details">
              @if($vehicle_documents->count() > 0)
                @foreach($vehicle_documents as $vehicle)
                <tr>
                  <td>{{$vehicle->vehicle_name}}
                    @if($vehicle->default_type == '1')
                      <span class="btn btn--primary">{{ trans('messages.account.default') }}</span>
                    @endif
                  </td>
                  <td>{{$vehicle->vehicle_type}}</td>
                  <td>{{$vehicle->vehicle_number}}</td>
                  <td>{{$vehicle->trans_status}}</td>
                  <td class="payout-options cls_dropoption">
                    <li class="dropdown-trigger list-unstyled">
                      <a href="javascript:void(0);" class="link-reset text-truncate" id="option1">
                        @lang('messages.account.options')
                        <i class="icon icon-caret-down"></i>
                      </a>
                      <ul data-sticky="true" data-trigger="#option1" class="tooltip tooltip-top-left list-unstyled dropdown-menu" aria-hidden="true">
                        <li>
                          <a rel="nofollow" data-method="post" class="link-reset menu-item" href="{{ url('edit_vehicle/'.$vehicle->id) }}">{{ trans('messages.driver_dashboard.edit') }}</a>
                        </li>
                        @if($vehicle->default_type == '0')
                        <li>
                          <a rel="nofollow" data-method="post" class="link-reset menu-item" href="{{ url('default_vehicle/'.$vehicle->id) }}">{{ trans('messages.driver_dashboard.set_as_default') }}</a>
                        </li>
                        @endif
                        <li>
                          <a rel="nofollow" data-method="post" class="link-reset menu-item" href="{{ url('delete_vehicle/'.$vehicle->id) }}">{{ trans('messages.driver_dashboard.delete') }}</a>
                        </li>
                      </ul>
                    </li>
                  </td>
                </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="5" style="height: 46px;text-align: center;">
                      {{trans('messages.dashboard.no_details')}}.
                  </td>
                </tr>
              @endif
            </tbody>
        </table>
      </div>
</div>
</div>
</div>
</div>
</main>
@endsection
