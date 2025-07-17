@section('head_css')
    <!-- 引入 Vditor 的 CSS -->
    {{-- <link rel="stylesheet" href="https://unpkg.com/vditor/dist/index.css" />--}}
    <link href="{{ asset('static/libs/vditor/dist/index.css') }}" rel="stylesheet">
@endsection

<!-- 加载编辑器的容器 -->
<div id="vditor_area" style="z-index: 10;">
    <div id="markdown" style="display: none;">{{ (empty($content_value)?"":$content_value) }}</div>
    <div id="vditor">
        @include('system::layouts.loading')
    </div>
    <textarea id="markdown_content" name="{{ empty($editor_name)?'content':$editor_name }}" style="display:none;"></textarea>
    <textarea id="markdown_content_html" name="{{ empty($editor_name)?'content_html':($editor_name.'_html') }}" style="display:none;"></textarea>
</div>

@section('page_js')
    <!-- 引入 Vditor 的 JavaScript -->
    {{--<script src="https://unpkg.com/vditor/dist/index.min.js"></script>--}}
    <script src="{{ asset('static/libs/vditor/dist/index.min.js') }}"></script>
    <script src="{{ asset('static/libs/vditor/vditor_pc.js') }}"></script>

<script type="text/javascript">
    function form_before(ele,obj) {
        // 获取编辑器markdown内容
        $('#markdown_content').val(vditor.getValue())
        // 获取编辑器html内容
        $('#markdown_content_html').val(vditor.getHTML())
    }
    function form_after(res) {
        vditor && vditor.clearStack();
    }
</script>
@endsection
