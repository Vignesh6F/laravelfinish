@include('common.head')
@include('common.driver_dashboard_header')

<main id="main">
@include('common.driver_dashboard_side_menu')
@yield('main')
</main>


@include('common.footer')
@include('common.foot')