<title>Invoice</title>
@extends('template_without_header_footer')
@section('main')
<style>
    h1{
        padding-left    :   50px;
        font-size       :   30px;
        text-align: center;
        font-weight     :   bold;
    }
    table{
        border          :   1px solid black;
        width           :   100%;
        font-size       :   16px;
        margin: 0 auto;
    }
    tr, td{
        border          :   1px solid black;
        padding    :   0 15px;
    }
    th{
        padding    :   0 15px;
    }
    tr{
        border-collapse :   collapse;
    }
    div{
        padding-top     :   20px;
    }
    div{
        padding-top     :   25px;
    }
    img {
        border: 1px solid #c2c2c2;
        border-radius: 100% !important;
        max-width: 100%;
    }
    p{
        line-height: 15px;
    }
    .no-border,.no-border tr,.no-border td {
        border: none;
    }
    .width-60 {
        width: 40%;
        text-align: center;
    }
    .width-40 {
        width: 60%;
    }
</style>

<div style="padding: 10px;width: 100%;margin: 0 auto;display: inline-block;">
    <div ><h1>{{trans('messages.dashboard.trip_invoice')}}</h1>
    </div>
    <div >
        <table>
            <thead >
                <tr>
                    <th>{{trans('messages.dashboard.invoice_no')}}</th>
                    <th >{{trans('messages.dashboard.trip_date')}}</th>
                    <th>{{trans('messages.dashboard.invoice')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr  >
                    <td data-title="Invoice Number"> {{ $trip->id }} </td>
                    <td data-title="Trip date"> {{ date('F d, Y',strtotime($trip->created_at))}} </td>
                    <td data-title="Invoice"> {{ $trip->currency->code }}  {{ $trip->total_fare }} </td>
                </tr>
            </tbody>
        </table>
        <table class="no-border">
            <tr>
                <td class="width-60" style="text-align: center;">
                    <div class="" style="background-image: url({{ $trip->driver_thumb_image }});height: 150px;width: 150px;margin: 0 auto;background-size: cover;background-position: center center;background-repeat: no-repeat;"></div>
                </td>
                <td class="width-40">
                    <div class="col-sm-6">
                        <p>{{trans('messages.dashboard.invoice_issued')}} {{$site_name}}{{trans('messages.dashboard.behalf')}}</p>
                        <p>{{ $trip->driver_name }}</p>
                        <p class="flush">{{ $trip->pickup_time }}</p>
                        <h6 class="color--neutral flush">{{ $trip->pickup_location }}</h6><br>
                        <p class="flush">{{ $trip->drop_time }}</p>
                        <h6 class="color--neutral flush">{{ $trip->drop_location }}</h6><br>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <thead class="cf">
                <tr>
                    <th>{{trans('messages.dashboard.date')}}</th>
                    <th>{{trans('messages.dashboard.desc')}}</th>
                    <th>{{trans('messages.dashboard.net_amt')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice_data as $key => $invoice)
                    <tr class="trip-expand__origin collapsed text-{{ $invoice['colour'] }}">
                        <td> {{ ($key == 0) ? date('F d, Y') : ''}} </td>
                        <td class="text--left "> {{ $invoice['key'] }} </td>
                        <td class="text--right "> {{ $invoice['value'] }} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <ul class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-ul">

    </ul>
</div>
</div>
</div>
</div>
</div>
</main>
<style type="text/css">
</style>
@stop