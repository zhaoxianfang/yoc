<meta charset="utf-8">

@hasSection('title')
    <title> @yield('title','') | {{ config('app.name','威四方') }}后台</title>
@endif
@sectionMissing('title')
    <title>{{ config('app.name','威四方') }}</title>
@endif
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Inspinia is the #1 best-selling admin dashboard template on Wrapmarket. Perfect for building CRM, CMS, project management tools, and custom web apps with clean UI, responsive design, and powerful features.">
<meta name="keywords" content="Inspinia, admin dashboard, Wrapmarket, Wrapbootstrap, HTML template, Bootstrap admin, CRM template, CMS template, responsive admin, web app UI, admin theme, best admin template">
<meta name="author" content="weisifang.com,威四方">
<meta property="og:url" content="https://weisifang.com">

<!-- App favicon -->
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

@section('head_css_before')
    @hasSection('head_css_before')
    @endif
@show

<!-- Theme Config Js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/config.js') }}"></script>

<!-- Vendor css -->
<link href="{{ asset('static/inspinia/v4.0/assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">

<!-- App css -->
<link href="{{ asset('static/inspinia/v4.0/assets/css/app.min.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('static/libs/zxf/css/tools.css') }}" rel="stylesheet" type="text/css">

@hasSection('use_datatables')
    <!-- 时间 -->
    <link rel="stylesheet" href="{{ asset('static/libs/daterangepicker/daterangepicker.min.css') }}" />
    <!-- datatables-table -->
    <link rel="stylesheet" href="{{ asset('static/libs/DataTables/DataTables-2.1.2/datatables.min.css') }}" />
@endif

@section('head_css')
    @hasSection('head_css')
        <!-- 页面中引入page css -->
    @endif
@show
