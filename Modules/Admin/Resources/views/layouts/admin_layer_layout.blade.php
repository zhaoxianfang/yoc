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

<script src="{{ asset('static/libs/zxf/js/tools.js') }}" type='text/javascript'></script>
<!-- modal 弹出层 -->
<script src="{{ asset('static/libs/zxf/modal/modal.js') }}" charset="utf-8"></script>

@hasSection('use_datatables')
    <script src="{{ asset('static/libs/DataTables/DataTables-2.1.2/datatables.min.js') }}"></script>

    <!-- 时间 -->
    <script src="{{ asset('static/libs/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('static/libs/daterangepicker/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('static/libs/zxf/js/data_tables/data_tables.min.js') }}"></script>
@endif

@section('page_js')
    @hasSection('page_js')
        <!-- 页面中引入page js -->
    @endif
@show

</body>
</html>
