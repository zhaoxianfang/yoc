<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-layout="topnav" data-topbar-color="light" data-menu-color="light" data-skin="modern" data-bs-theme="light" data-layout-position="fixed" data-sidenav-size="condensed" data-sidenav-user="true">
<head>

    @include('home::layouts.head')

</head>
<body>
<!-- Begin page -->
<div class="wrapper">

    @include('home::layouts.top_bar')

    <!-- Content -->
    <div class="content-page">
        <div class="container-fluid pt-2">
            {{-- @include('home::layouts.inner-page-title')--}}

            @yield('content')

        </div>

        <!-- Footer Start -->
        <footer class="footer p-1">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <span class="fw-semibold">{{ config('app.name','威四方') }}</span>©2023~<script>document.write(new Date().getFullYear())</script> 版权所有.
                    </div>
                    <div class="col-md-6 g-0">
                        <div class="text-md-end fs-12">
                            <a href="https://beian.mps.gov.cn/#/query/webSearch?code=53010202002026" class="" rel="noreferrer" target="_blank">
                                <img class="" src="/static/images/system/beian.png" alt="BeiAn Logo" style="width: 13px;height: 13px;">
                                滇公网安备53010202002026
                            </a>&nbsp;&nbsp;|&nbsp;&nbsp;
                            <a href="https://beian.miit.gov.cn" target="_blank" class="float-end">滇ICP备<span class="fw-bold">16003347</span>号-2</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

</div>

@section('page_js_before')
    @hasSection('page_js_before')
    @endif
@show

<!-- Vendor js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/vendors.min.js') }}"></script>
<!-- App js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/app.min.js') }}"></script>

@section('page_js')
    @hasSection('page_js')
        <!-- 页面中引入page js -->
    @endif
@show

</body>
</html>
