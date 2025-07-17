@section('head_css')
    @parent
    <style type="text/css" media="screen">
        p{
            margin: unset;
            padding: unset;
        }
    </style>
@endsection

<div class="form-group">
    <div class="col-xs-12 col-sm-12">
        <textarea class="form-control" placeholder="开始创作..."  id="ck_editor"  name="{{ empty($editor_name)?'content':($editor_name) }}" data-rule="required">{{empty($content_value)?"":$content_value}}</textarea>
    </div>
</div>

@section('page_js')
    @parent
    <!-- ckeditor 编辑器 -->
    <script src="{{ asset('static/libs/ckeditor-4.16.1/ckeditor.js') }}"></script>
    <script src="{{ asset('static/libs/ckeditor-4.16.1/config.js') }}"></script>
    <script  type="text/javascript" charset="utf-8" async defer>
        //初始化编辑器
        var editor = CKEDITOR.replace('ck_editor', {
            //配置图片上传地址
            // uploaded:"/files/uploads/editor/img/docs?_token={{ csrf_token() }}",
            // removePlugins:'elementspath,resize',
            // codeSnippet_theme: 'zenburn',
            // height:'400',
            // extraPlugins : 'html5video'
            // readOnly:true
        });

        // $(function () {
        //     //Initialize Select2 Elements
        //     $('.select2').select2();
        // })
    </script>
@endsection
