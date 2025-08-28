<!DOCTYPE html>
<html lang="zh_CN" data-skin="modern">

<head>

    @include('admin::layouts.head')

</head>

<body>
<!-- Begin page -->
<div class="wrapper">

    <!-- Start Main Content -->
    <div class="content-page m-0">

        <div class="container-fluid">

            @yield('content')

        </div>
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

<script src="{{ asset('static/libs/zxf/js/tools.min.js') }}" type='text/javascript'></script>
<!-- modal 弹出层 -->
<script src="{{ asset('static/libs/zxf/modal/modal.min.js') }}" charset="utf-8"></script>

@include('system::layouts.js_custom_plugins')

</body>
</html>
