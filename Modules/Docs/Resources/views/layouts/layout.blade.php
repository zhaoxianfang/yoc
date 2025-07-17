<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
@include('docs::layouts.head')

@section('head_css')
    @hasSection('head_css')
    @endif
@show

</head>
<body>
<div class="app-container">
    <!-- 顶部导航栏 -->
    @include('docs::layouts.top_nav')

    @include('docs::layouts.page_error_tips')

    @hasSection('page_has_menu')
    <!-- 左侧菜单栏 -->
    <aside class="sidebar" id="sidebar">
        <ul class="menu" id="menuContainer">
            <!-- 菜单内容将通过JavaScript动态生成 -->
        </ul>
    </aside>
    @endif

    <!-- 内容区域 -->
    <main class="content" id="content">
        {{--<div class="breadcrumb" id="breadcrumb">--}}
        {{--    <a href="#">首页</a>--}}
        {{--    <span class="breadcrumb-separator">/</span>--}}
        {{--    <a href="#" id="currentCategoryBreadcrumb">指南</a>--}}
        {{--    <span class="breadcrumb-separator">/</span>--}}
        {{--    <span id="currentPageBreadcrumb">欢迎</span>--}}
        {{--</div>--}}

        <article class="article" id="articleContent">
            @yield('content')
        </article>
    </main>

    @hasSection('page_has_menu')
    <!-- 右侧目录 -->
    <aside class="toc" id="toc">
        <div class="toc-title">
            <span id="tocTitleText">目录</span>
            <span class="toc-toggle" id="tocToggle">收起</span>
        </div>
        <ul class="toc-list" id="tocList">
            <!-- 目录内容将通过JavaScript动态生成 -->
        </ul>
    </aside>

    <!-- 右侧目录展开按钮 -->
    <div class="toc-collapse-handle" id="tocCollapseHandle">◀</div>
    @endif

    <!-- 返回顶部按钮 -->
    <div class="back-to-top" id="backToTop">⇧</div>

    <!-- 页脚 -->
    @include('docs::layouts.footer')
</div>
<script>
    const base_url = '{{ !empty($base_url) ? $base_url : '' }}'
    const app_id = '{{ !empty($docs_app) ? $docs_app->id : '' }}'
    const current_doc_id = '{{ !empty($docs_doc) ? (!empty($docs_doc->_id)?$docs_doc->_id:$docs_doc->id) : '' }}'

    {{-- const _docs_doc = JSON.parse('{!! addslashes(json_encode($docs_doc, JSON_UNESCAPED_UNICODE)) !!}'); --}}
    {{-- console.log(_docs_doc); --}}
</script>

@section('page_js_before')
    @hasSection('page_js_before')
    @endif
@show

<script src="{{ asset('static/libs/zxf/modal/modal.min.js') }}" charset="utf-8"></script>
@hasSection('page_has_menu')
  @if (!empty($docs_app) && $docs_app->isEditor())
{{-- 管理操作组件 --}}
<script src="{{ asset('static/libs/zxf/right_menu/right_menu.min.js') }}" charset="utf-8"></script>
<script src="{{ asset('static/docs/js/docs_doc_v2.js') }}" charset="utf-8"></script>
  @endif
@endif

@sectionMissing('page_has_menu')

@endif

<script src="{{ asset('static/libs/zxf/js/my_console.min.js') }}"></script>
<script> my_console.version(); </script>

<script src="{{ asset('static/docs/js/docs_v2.min.js') }}" charset="utf-8"></script>

@section('page_js')
    @hasSection('page_js')
    @endif
@show

@hasSection('page_has_menu')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 有 菜单目录
        initDocsAll();
    });
</script>
@endif
@sectionMissing('page_has_menu')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 没有 菜单目录
        initDocHead()
    });
</script>
@endif
{{-- 给 uniapp 使用 --}}
<script type="text/javascript" src="{{ asset('static/libs/zxf/js/uniapp/webview.min.js') }}"></script>
</body>
</html>
