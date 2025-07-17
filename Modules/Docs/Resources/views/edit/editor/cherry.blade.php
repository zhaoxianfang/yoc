@section('head_css')
    @parent
    <link rel="stylesheet" type="text/css" href="{{ asset('static/libs/cherry-markdown/dist/cherry-markdown.min.css') }}">
    {{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.12.0/dist/katex.min.css" crossorigin="anonymous">--}}
    <link rel="stylesheet" href="{{ asset('static/libs/KaTeX-0.12.0/dist/katex.min.css') }}" crossorigin="anonymous">

    <style>
        #custom-toc-menu-box{display: none!important;}
        .p-3{padding: 15px 0!important;}
    </style>
@endsection

<!-- 加载编辑器的容器 -->
<div>
    <div id="dom_mask" style="position: absolute; top: 40px; height: 20px; width: 100%;"></div>
    <div id="markdown" ></div>
    <div class="editor_loading">
        @include('system::layouts.loading')
    </div>

    <textarea id="markdown_content" data-name="{{ empty($editor_name)?'content':$editor_name }}" style="display:none;">{{empty($content_value)?"":$content_value}}</textarea>
    <textarea id="markdown_content_html" name="{{ empty($editor_name)?'content_html':($editor_name.'_html') }}" style="display:none;"></textarea>
</div>

@section('page_js')
    @parent
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

<script type="text/javascript">

    // 拦截 cherry-markdown 内部触发的的表单提交【例如：插入公式时】
    function form_intercept(ele) {
        if(ele.submitter.className.indexOf('cherry-') > -1){
            return false;
        }
        return true;
    }
    function form_before() {
        // 元素赋值
        document.getElementById('markdown_content').value = cherry.getMarkdown();
        // document.getElementById('markdown_content_html').value = cherry.getHtml();
        // cherry.getHtml() 会渲染了一个辅助标签（<mjx-assistive-mml>）,需要去掉
        document.getElementById('markdown_content_html').value = cherry.getHtml().replace(/<mjx-assistive-mml [^>]+>.+?<\/mjx-assistive-mml>/g, '');
    }
</script>
@endsection
