@extends('demo::layouts.demo_layout')

@section('title', "Cherry编辑器示例")

@section('use_datatables', "true")

@section('head_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('static/libs/cherry-markdown/dist/cherry-markdown.min.css') }}">
    {{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.12.0/dist/katex.min.css" crossorigin="anonymous">--}}
    <link rel="stylesheet" href="{{ asset('static/libs/KaTeX-0.12.0/dist/katex.min.css') }}" crossorigin="anonymous">

    <style>
        #custom-toc-menu-box{display: none!important;}
        .p-3{padding: 15px 0!important;}
    </style>
@endsection

@section('content')
    <h1>Cherry编辑器示例</h1>

    <form method="POST" id="cherry_form">
        <div id="markdown" ></div>
        <div class="editor_loading">
            @include('system::layouts.loading')
        </div>

        <textarea id="markdown_content" name="content" style="display:none;"></textarea>
        <textarea id="markdown_content_html" name="content_html" style="display:none;"></textarea>

        <div class="d-flex flex-wrap justify-content-between">
            <button class="btn btn-primary" type="submit"><strong>提交</strong></button>
        </div>
    </form>
@endsection

@section('page_js')
    {{--<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@4.6.0/dist/echarts.js"></script>--}}
    {{--引用本地的--}}
    <script type="text/javascript" src="{{ asset('static/libs/echarts/4.6.0/echarts.min.js') }}"></script>

    <!--<script src="https://cdn.jsdelivr.net/npm/katex@0.12.0/dist/katex.min.js" crossorigin="anonymous"></script>-->
    <script src="{{ asset('static/libs/KaTeX-0.12.0/dist/katex.min.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('static/libs/cherry-markdown/dist/cherry-markdown.min.js') }}"></script>
    <script src="{{ asset('static/libs/cherry-markdown/examples/scripts/pinyin/pinyin_dist.js') }}"></script>
    {{-- tex-svg.js 公式渲染 --}}
    <script src="{{ asset('static/libs/cherry-markdown/tex-svg.js') }}" async></script>
    <script src="{{ asset('static/libs/cherry-markdown/edit.pc.min.js') }}"></script>

    <script>

        // 拦截 cherry-markdown 内部触发的的表单提交【例如：插入公式时】
        function form_intercept(ele) {
            if(ele.submitter.className.indexOf('cherry-') > -1){
                return false;
            }
            return true;
        }
        function form_before(e) {
            // 元素赋值
            document.getElementById('markdown_content').value = cherry.getMarkdown();
            // document.getElementById('markdown_content_html').value = cherry.getHtml();
            // cherry.getHtml() 会渲染了一个辅助标签（<mjx-assistive-mml>）,需要去掉
            document.getElementById('markdown_content_html').value = cherry.getHtml().replace(/<mjx-assistive-mml [^>]+>.+?<\/mjx-assistive-mml>/g, '');

            console.log(myTools.form.getFormData('#cherry_form'));
            return false;
        }

        $(function () {

        });
    </script>
@endsection
