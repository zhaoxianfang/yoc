@section('head_css')
    @parent
<link href="{{ asset('static/libs/editor.md/css/editormd.min.css') }}" rel="stylesheet">
@endsection

<!-- 加载编辑器的容器 -->
<div id="editormd_area" style="z-index: 10;">
    <textarea class="editormd-markdown-textarea" style="display:none;" name="{{ empty($editor_name)?'content':$editor_name }}">{{empty($content_value)?"":$content_value}}</textarea>
    <textarea class="editormd-html-textarea" name="{{ empty($editor_name)?'content_html':($editor_name.'_html') }}" style="display:none;"></textarea>
    @include('system::layouts.loading')
</div>

@section('page_js')
    @parent
<script src="{{ asset('static/libs/editor.md/editormd.min.js') }}"></script>

<script type="text/javascript">
    var mdEditor;
    var browser_height = $(document).height() - 140 ; //浏览器时下窗口文档body的高度

    $(function() {
        mdEditor = editormd("editormd_area", {
            width   : "100%",
            // height  : 640,
            height  : browser_height,
            syncScrolling : "single",
            path    : "{{ asset('static/libs/editor.md') }}/lib/",
            placeholder: "markdown文本編輯器",
            theme : "default",//"dark",
            previewTheme : "default",//"dark",
            editorTheme : "default",//"pastel-on-dark",
            codeFold : true,
            watch : true,                // 关闭实时预览

            // markdown : md_content,
            // saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
            saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea，true表示转化为html格式的内容也同样提交到后台
            searchReplace : true,
            //toolbar  : false,             //关闭工具栏
            //previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
            emoji : true,
            taskList : true,
            tocm     : true,         // Using [TOCM]

            htmlDecode : "style,script,iframe|on*",  // 开启 HTML 标签解析，为了安全性，默认不开启  实现过滤指定标签及属性的解析，提高安全性
            // htmlDecode : "style,script,iframe|*",  // 开启 HTML 标签解析，为了安全性，默认不开启
            // htmlDecode : "style,script,iframe|on*",            // 开启 HTML 实现过滤指定标签及属性的解析，提高安全性
            tex : true,                   // 开启科学公式TeX语言支持，默认关闭
            flowChart : true,             // 开启流程图支持，默认关闭
            sequenceDiagram : true,       // 开启时序/序列图支持，默认关闭,
            //dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
            //dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
            //dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
            //dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
            //dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
            imageUpload : true, // 开启图片上传功能，默认关闭
            // imageName:"image",  //上传图片名称 无效
            // imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
            imageFormats : ["jpg", "jpeg", "gif", "png"],// 上传图片格式
            imageUploadURL : "/files/uploads/editor/img/docs?_token={{ csrf_token() }}",//上传图片路径
            // toolbarIcons : "full", // You can also use editormd.toolbarModes[name] default list, values: full, simple, mini.
            toolbarIcons : function() {
                // Or return editormd.toolbarModes[name]; // full, simple, mini
                // Using "||" set icons align right.
                return [
                    "undo", "redo",
                    "|", "bold","del","italic", "quote","ucwords","uppercase","lowercase",
                    "|", "h1","h2","h3","h4","h5","h6",
                    "|","list-ul","list-ol","hr","link","reference-link",
                    "|","image","code","preformatted-text","code-block","table",
                    // "|","clear",
                    "|","search",  "watch", "fullscreen", "preview",
                    "|","help"
                ]
            },
            onfullscreen : function() {
                $('#editormd_area').addClass('custom-fullscreen-editor');
                this.resize("100%", browser_height);
            },
            onfullscreenExit : function() {
                $('#editormd_area').removeClass('custom-fullscreen-editor');
                this.resize("100%", browser_height);
            },
            onresize : function() {
                // if(!$('#editormd_area').hasClass('editormd-fullscreen') ){
                //     mdEditor.resize("100%",(typeof browser_height!="undefined")?browser_height:"100%");
                // }
            },
            onload : function() {
                // 重要，查找 name 以 "-html-code" 结尾的 textarea，并删除它，否则会多提交一个editormd_area-html-code 的字段
                $('[name$="-html-code"]').remove();

                // 设置快捷键
                var keyMap = {
                    "Ctrl-S": function(cm, icon, cursor, selection) {
                        // 保存
                        $('#editormd_area').parents('form').submit();
                        // submitDoc(cm, icon, cursor, selection)
                    }
                };
                this.addKeyMap(keyMap);
            }
        });
    });
</script>
@endsection
