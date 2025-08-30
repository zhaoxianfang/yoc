@extends('demo::layouts.demo_layout')

@section('title', "ckeditor编辑器示例")

@section('use_datatables', "true")

@section('head_css')
    <!-- Summernote Plugin CSS -->
    <link href="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/summernote-bs5.min.css') }}" rel="stylesheet">

    <style>
    </style>
@endsection

@section('content')
    <h1>ckeditor编辑器示例</h1>

    <form method="POST" id="cherry_form">
        {{-- 编辑器容器--}}
        <textarea class="form-control" placeholder="交个朋友"  id="ck_editor"  data-rule="required"></textarea>
    </form>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <h2>
                        ckeditor4
                    </h2>
                    <p>
                        ckeditor4文档地址：https://ckeditor.com/docs/ckeditor4/latest/index.html
                    </p>

                    <div class="alert alert-warning">
                        插件下载地址:
                        <a href="https://ckeditor.com/cke4/addons/plugins/all">https://ckeditor.com/cke4/addons/plugins/all</a>
                    </div>
                    <div class="alert alert-warning">
                        查看插件下载地址:
                        <a href="https://ckeditor.com/cke4/addons/plugins/all">https://ckeditor.com/cke4/addon/xxx插件标识名称</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_js')
    <!-- ckeditor 编辑器 -->
    <script src="{{ asset('static/libs/ckeditor-4.16.1/ckeditor.js') }}"></script>

    <script  type="text/javascript" charset="utf-8" async defer>

        //初始化编辑器
        // 文档地址：https://ckeditor.com/docs/ckeditor4/latest/index.html
        var editor = CKEDITOR.replace('ck_editor', {
            // 自定义配置文件
            customConfig: '/static/libs/ckeditor-4.16.1/config.full.js',
            //配置图片上传地址
            // uploaded:"/upload/img",
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
        function form_before(e) {
            var content = CKEDITOR.instances.ck_editor.getData()
            // $('#ck_submit_content').html(content)
            $('#ck_submit_content').val(content)
            console.log(content)
        }
        function form_after(succ) {
            if(succ.code == 200){
                $('#ck_submit_content').val('')
                $('#ck_editor').val('')
            }
            // $('#ck_submit_content').html(CKEDITOR.instances.ck_editor.getData())

        }
    </script>
@endsection
