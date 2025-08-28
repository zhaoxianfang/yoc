<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-skin="modern">

<head>

    @include('demo::layouts.head')

</head>

<body>
<!-- Begin page -->
<div class="wrapper">


    <!-- Sidenav Menu Start -->
    <div class="sidenav-menu">

        <!-- Brand Logo -->
        <a href="#" class="logo">
            <span class="logo logo-light">
                <span class="logo-lg"><img src="{{ asset('static/images/logo/logo_long.png') }}" alt="logo"></span>
                <span class="logo-sm"><img src="{{ asset('static/images/logo/logo.png') }}" alt="small logo"></span>
            </span>

            <span class="logo logo-dark">
                <span class="logo-lg"><img src="{{ asset('static/images/logo/logo_long.png') }}" alt="dark logo"></span>
                <span class="logo-sm"><img src="{{ asset('static/images/logo/logo.png') }}" alt="small logo"></span>
            </span>
        </a>

        <!-- Sidebar Hover Menu Toggle Button -->
        <button class="button-on-hover">
            <i class="ti ti-menu-4 fs-22 align-middle"></i>
        </button>

        <!-- Full Sidebar Menu Close Button -->
        <button class="button-close-offcanvas">
            <i class="ti ti-x align-middle"></i>
        </button>

        @include('demo::layouts.left_menu')
    </div>
    <!-- Sidenav Menu End -->

    @include('demo::layouts.top_bar')

    <!-- Start Main Content -->
    <div class="content-page">

        <div class="container-fluid">
            @include('demo::layouts.inner-page-title')

            @yield('content')

        </div>

        <!-- container -->

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <span class="fw-semibold">{{ config('app.name','威四方') }}</span>©2023~<script>document.write(new Date().getFullYear())</script> 版权所有.
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end d-none d-md-block">
                            10GB of <span class="fw-bold">250GB</span> Free.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

</div>
<!-- END wrapper -->
@include('demo::layouts.theme_setting')

@section('page_js_before')
    @hasSection('page_js_before')
    @endif
@show

<!-- Vendor js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/vendors.min.js') }}"></script>
<!-- App js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/app.min.js') }}"></script>

<script src="{{ asset('static/libs/zxf/js/tools.min.js') }}" type='text/javascript'></script>
<!-- modal 弹出层 -->
<script src="{{ asset('static/libs/zxf/modal/modal.js') }}" charset="utf-8"></script>

@include('system::layouts.js_custom_plugins')


</body>
</html>
