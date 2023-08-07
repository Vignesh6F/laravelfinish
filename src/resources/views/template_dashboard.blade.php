@include('common.head')
@include('common.dashboard_header')

<main id="main">
@include('common.dashboard_side_menu')
@yield('main')
</main>


@include('common.footer')
@include('common.foot')