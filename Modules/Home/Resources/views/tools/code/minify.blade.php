@extends('home::layouts.home_layout')
@section('title', "JS、CSS代码压缩 Code Minify")

@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">JS、CSS代码压缩 Code Minify</h5>
                <h6 class="card-subtitle text-body-secondary">在线代码压缩工具</h6>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-sm-6 border-end border-dashed">
                        <div class="p-4">
                        {{-- <h4 class="mb-1 fw-bold text-uppercase">JS、CSS代码压缩 Code Minify</h4> --}}
                        {{-- <p class="text-muted mb-4">填写基础信息进行生成</p> --}}

                            <form method="POST">

                                <div class="mb-3">
                                    <label for="type" class="form-label">代码类型 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control custom-select" name="type" id="handle_type">
                                            <option value="js" data-icon="ti ti-file-type-js text-purple fs-18">JS 代码</option>
                                            <option value="css" data-icon="ti ti-file-type-css text-purple fs-18">CSS 代码</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-3">
                                    <label for="provinces" class="form-label">代码片段 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <textarea class="form-control" name="code_string" rows="14" placeholder="请填写相应的 js/css 代码" required></textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <button class="btn btn-primary" type="submit"><strong>代码压缩</strong></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-sm-6 text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-file-type-js fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">处理结果预览</h4>
                        <textarea class="form-control" id="resp_textarea" rows="14" placeholder="处理结果"></textarea>
                        <p class="text-muted mb-1">表单数据处理结果!</p>
                        <p class="text-muted mb-3" id="minify_tips"></p>
                    </div>
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
    </script>

@endsection
