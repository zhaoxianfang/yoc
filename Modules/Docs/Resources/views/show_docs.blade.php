@extends('docs::layouts.layout')
@section('title', ( $docs_doc?->title .(!empty($docs_app)? ' | '.$docs_app?->app_name:'' )))
@section('page_has_menu', "true")

@section('head_css')
@endsection

@section('top_nav_tabs')
    <li class="nav-tab nav-tab-item @if(empty($category) || $category == 'guide') active @endif" data-category="guide">指南</li>
    @if(!empty($docs_has_api_category))
    <li class="nav-tab nav-tab-item @if(!empty($category) && $category == 'api') active @endif" data-category="api">API</li>
    @endif
    <li class="nav-tab nav-tab-item @if(!empty($category) && $category == 'faq') active @endif" data-category="faq">常见问题</li>
@endsection

@section('content')
    {!! $docs_doc?->content_html !!}
@endsection

@section('page_js_before')
    <script>
        // 定义需要激活的菜单大项[ currentCategory ]
        var nav_category = '{!! $category ?? '' !!}';
        // 菜单数据
        const menuData = JSON.parse('{!! addslashes(json_encode($menus, JSON_UNESCAPED_UNICODE)) !!}');
        // console.log(menuData);
    </script>

@endsection

@section('page_js')

@endsection
