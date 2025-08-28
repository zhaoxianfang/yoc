<meta charset="UTF-8">
<link rel="icon" href="{{ asset('static/images/favicon.ico') }}" sizes="any">
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<meta property="og:type" content="article">
<meta property="og:title" content="@yield('title','') | {{ config('app.name','威四方') }}">
<meta property="og:description" content="在线文档,知识库,开发文档,模板,企业定制,威四方">

@hasSection('title')
<title> @yield('title','') | {{ config('app.name','威四方') }}</title>
@endif
@sectionMissing('title')
<title>{{ config('app.name','威四方') }}</title>
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="{{ asset('static/libs/zxf/css/bootstrap-col.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('static/docs/css/docs_v2.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('static/libs/zxf/modal/modal.min.css') }}" rel="stylesheet" type="text/css">

@include('system::layouts.css_custom_plugins')

@hasSection('page_has_menu')
{{-- 管理操作组件 --}}
<link href="{{ asset('static/libs/zxf/right_menu/right_menu.min.css') }}" rel="stylesheet" type="text/css">
@endif
