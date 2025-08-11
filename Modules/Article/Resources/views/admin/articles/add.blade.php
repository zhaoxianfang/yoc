@extends('admin::layouts.admin_layer_layout')

@section('head_css')
    @parent
    <link href="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/summernote-bs5.min.css') }}" rel="stylesheet">
    <style>
        .rule-handle{padding: 0;}
    </style>
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row g-lg-2 g-1">
                <label for="title" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>标题:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="title" name="row[title]" placeholder="" value="" data-rule="required" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="classify_id" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>分类:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[classify_id]" style="border-radius:0px;" >
                        <option value="0">根节点</option>
                        @foreach ($classify_list as $classify)
                            <option value="{{ $classify['id'] }}" >{{ $classify['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="source_url" class="control-label col-xs-12 col-sm-2">来源URL:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="source_url" name="row[source_url]" placeholder="" value="" data-rule="" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="author" class="control-label col-xs-12 col-sm-2">来源/作者:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="author" name="row[author]" placeholder="" value="" data-rule="" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="show_nav" class="control-label col-xs-12 col-sm-12"><font color="#FF0000">*</font>内容:</label>
                <div class="col-xs-12 col-sm-12">
                    {{-- 编辑器容器--}}
                    <div class="summernote form-control col-xs-12 col-sm-12"></div>
                    <input type="hidden" name="row[content]" id="edit_content" value="">
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="status" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control custom-select col-xs-12 col-sm-12" name="row[status]" style="border-radius:0px;" >
                        <option value="0" >待审</option>
                        <option value="1" selected>正常</option>
                        <option value="2" >不公开</option>
                        <option value="3">敏感待审核</option>
                    </select>
                </div>
            </div>

            <div class="form-group row g-lg-2 g-1">
            </div>
            {{-- 操作按钮 使用 .layer-bottom-btns 元素盒子--}}
            <div class="layer-bottom-btns">
                <button class="btn btn-light" onclick="parent.postMessage({type: 'close'}, '*')">取消</button>
                <button class="btn btn-primary" type="submit">提交</button>
            </div>
        </form>
    </div>

@endsection

@section('page_js')
    @parent

    <!-- SUMMERNOTE -->
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
    {{--    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/lang/summernote-zh-CN.min.js') }}"></script>--}}

    <script type="text/javascript">
        var summernoteObj = $('.summernote');
        $(function () {
            // 初始化
            summernoteObj.summernote({
                height: 500,   //set editable area's height
                lang: 'zh-CN', // 设置语言为简体中文
                // 自定义占位符
                placeholder: '开始您的杰作吧~',
                // 大概在源码 9789 行
                toolbar: [
                    ['style', ['undo','redo','style']],
                    ['font', ['bold','italic', 'underline', 'strikethrough', 'hr','superscript','subscript', 'clear']],
                    ['fontname', ['fontname','fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph','height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    // ['view', ['fullscreen', 'codeview', 'help']]
                    ['view', ['fullscreen', 'codeview']]
                ],
                // 自定义行高
                lineHeights: ['0.2', '0.3', '0.4', '0.5', '0.6', '0.8', '1.0', '1.2', '1.4', '1.5', '2.0', '3.0'],
                // 自定义字体名称
                // fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
                // 允许 模态对话框中使用 Summernote
                dialogsInBody: false,
                // 对话框的显示和隐藏没有淡入淡出效果
                dialogsFade: false, // Add fade effect on dialogs
                // 代码中是否开启xss
                // codeviewFilter: false,
                // codeviewIframeFilter: true,

                // 回调
                callbacks: {
                    // 插入图片链接时
                    onImageLinkInsert: function(url) {
                        // url is the image url from the dialog
                        console.log('onImageLinkInsert',url);
                        summernoteObj.summernote('insertImage', url);
                        // 或者
                        // var $img = $('<img>').attr({ src: url })
                        // summernoteObj.summernote('insertNode', $img[0]);
                    },
                    // 上传图片
                    onImageUpload: function(files) {
                        // upload image to server and create imgNode...
                        console.log('onImageUpload',files);

                        for (var i = 0; i < files.length; i++) {
                            my.upload('/files/uploads/summernote/image/img', files[i], function(res){
                                // console.log('success',res)
                                summernoteObj.summernote('insertImage', res.url);
                            },function (err){
                                console.log('error',err)
                            })
                        }

                        // summernoteObj.summernote('insertImage', url);
                        // 或者
                        // var $img = $('<img>').attr({ src: url })
                        // summernoteObj.summernote('insertNode', $img[0]);
                    },
                    // 粘贴
                    onPaste: function(e) {
                        console.log('Called event paste');
                    }
                }
            });
        })
        function form_before(ele,obj) {
            // 内容为空
            if (summernoteObj.summernote('isEmpty')) {
                myTools.msg('内容不能为空');
                return false;
            }
            $('#edit_content').val(summernoteObj.summernote('code'));
        }
    </script>
@endsection
