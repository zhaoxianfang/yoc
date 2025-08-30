@extends('home::layouts.home_layout')
@section('title', "Unicode转码")

@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">Unicode转换 <small>Unicode encoding conversion</small></h5>
                <h6 class="card-subtitle text-body-secondary">轻便、快捷</h6>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-sm-6 border-end border-dashed text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-code-circle-2 fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">Unicode转中文汉字、ASCII转换Unicode</h4>
                        <textarea class="form-control" id="unicode_str" rows="22" placeholder="此处填入unicode字符串"></textarea>
                    </div>

                    <div class="col-sm-6 text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-typography fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">汉字转Unicode</h4>
                        <textarea class="form-control" id="zhCN_string" rows="22" placeholder="此处填入汉字或其他普通字符串"></textarea>
                    </div>
                </div>
                <div class="row g-4 align-items-center text-center">
                    <p class="text-muted mb-1">使用说明：在左侧输入区填写 Unicode 字符串 或 在右侧填写汉字字符串 都会自动完成转换！</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        // Unicode转中文汉字、ASCII转换Unicode
        function reconvert(str){
            str = str.replace(/(\\u)(\w{1,4})/gi,function($0){
                return (String.fromCharCode(parseInt((escape($0).replace(/(%5Cu)(\w{1,4})/g,"$2")),16)));
            });
            str = str.replace(/(&#x)(\w{1,4});/gi,function($0){
                return String.fromCharCode(parseInt(escape($0).replace(/(%26%23x)(\w{1,4})(%3B)/g,"$2"),16));
            });
            str = str.replace(/(&#)(\d{1,6});/gi,function($0){
                return String.fromCharCode(parseInt(escape($0).replace(/(%26%23)(\d{1,6})(%3B)/g,"$2")));
            });
            return str;
        }
        // unicode 转中文
        function toZhCN(str) {
            var res = [];
            for ( var i=0; i<str.length; i++ ) {
                res[i] = ( "00" + str.charCodeAt(i).toString(16) ).slice(-4);
            }
            return "\\u" + res.join("\\u");
        }

        $(function(){
            $('#unicode_str').bind('input propertychange', function(){
                if($(this).val() != ""){
                    var t = reconvert($(this).val());
                    $('#zhCN_string').val(t);
                }else{
                    $('#zhCN_string').val('');
                };
            });

            $('#zhCN_string').bind('input propertychange', function(){
                if($(this).val() != ""){
                    var t = toZhCN($(this).val());
                    $('#unicode_str').val(t);
                }else{
                    $('#unicode_str').val('');
                };
            });
        });
    </script>

@endsection
