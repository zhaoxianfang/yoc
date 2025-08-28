@extends('home::layouts.home_layout')
@section('title', "Json 数据格式化")

@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">Json 数据格式化</h5>
                <h6 class="card-subtitle text-body-secondary">轻便、快捷、安全</h6>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-sm-6 border-end border-dashed text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-json fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">格式化【前】的Json数据</h4>
                        <textarea class="form-control" id="json_old_str" rows="22" placeholder="请输入格式化前的Json数据"></textarea>
                    </div>

                    <div class="col-sm-6 text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-json fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">格式化【后】的Json数据</h4>
                        <textarea class="form-control" id="resp_textarea" rows="22" placeholder="请输入格式化后的Json数据"></textarea>
                    </div>
                </div>
                <div class="row g-4 align-items-center text-center">
                    <p class="text-muted mb-1">小提示：在上面的文本框中输入需要格式化的Json数据，就会自动进行处理。</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        function form_after(resp) {
            console.log(resp);
            console.error('操作失败:', resp.message);
            // 处理结果预览
            $('#resp_textarea').val(resp.min_str);
            $('#minify_tips').text('压缩前大小: '+resp.old_len+'; 压缩后大小: '+resp.new_len+'; 压缩比: '+resp.minify_ratio);
        }
        $('#handle_type').on('change', function(){
            var selectedValue = $(this).val();
            if (selectedValue === 'js') {
                $('#cody_type_icon').removeClass().addClass('ti ti-file-type-js fs-32');
            } else if (selectedValue === 'css') {
                $('#cody_type_icon').removeClass().addClass('ti ti-file-type-css fs-32');
            }
        });

        $(function(){
            $('#json_old_str').bind('input propertychange', function(){
                if($(this).val() != ""){
                    var t = myTools.func.formatJson($(this).val());
                    $('#resp_textarea').val(t);
                }else{
                    $('#resp_textarea').val('');
                };
            });
            $('#resp_textarea').bind('input propertychange', function(){
                if($(this).val() != ""){
                    var jsonStr = JSON.stringify($.parseJSON($(this).val()));
                    $('#json_old_str').val(jsonStr);
                }else{
                    $('#json_old_str').val('');
                };
            });
        });
    </script>

@endsection
