
@hasSection('use_datepicker')
    <!-- Daterangepicker Plugin CSS -->
    <link rel="stylesheet" href="{{ asset('static/inspinia/v4.0/assets/plugins/daterangepicker/daterangepicker.css') }}" type="text/css">
@endif

@hasSection('use_datatables')
    <!-- 时间 -->
    <link rel="stylesheet" href="{{ asset('static/libs/daterangepicker/daterangepicker.min.css') }}" />
    <!-- datatables-table -->
    <link rel="stylesheet" href="{{ asset('static/libs/DataTables/DataTables-2.3.3/datatables.min.css') }}" />
@endif

@hasSection('use_form')
    <link href="{{ asset('static/libs/zxf/modal/modal.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('static/libs/zxf/css/tools.min.css') }}" rel="stylesheet" type="text/css">
@endif



@section('head_css')
    @hasSection('head_css')
        <!-- 页面中引入page css 「保持」放在此文件的最后 -->
    @endif
@show
