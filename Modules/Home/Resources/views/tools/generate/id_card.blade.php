@extends('home::layouts.home_layout')
@section('title', "身份证号码在线生成/验证器")

@section('use_datepicker', "true")
@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">身份证号码在线生成与验证器</h5>
                <h6 class="card-subtitle text-body-secondary">娱乐为主，请勿非法使用！</h6>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-sm-6 border-end border-dashed">
                        <div class="p-4">
                        {{-- <h4 class="mb-1 fw-bold text-uppercase">身份证号码在线生成器</h4> --}}
                        {{-- <p class="text-muted mb-4">填写基础信息进行生成</p> --}}

                            <form method="POST">

                                <div class="mb-3">
                                    <label for="type" class="form-label">处理类型 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control custom-select" name="type" id="handle_type">
                                            <option value="generate" data-icon="ti ti-bulb text-purple fs-18">&nbsp;生成器</option>
                                            <option value="validate" data-icon="ti ti-bell-check text-success fs-18" >&nbsp;验证器</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="generate-box">
                                    <div class="mb-3">
                                        <label for="provinces" class="form-label">省份 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control custom-select" name="province">
                                                <option value="" >随机一个省份</option>
                                                @foreach ($provinces as $item)
                                                    <option value="{{$item}}" @if (old('provinces') == $item) selected @endif>{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gender" class="form-label">性别 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control form-select" name="gender">
                                                <option value="" >随机一个性别</option>
                                                <option value="male" @if (old('gender') == 'male') selected @endif>男</option>
                                                <option value="female" @if (old('gender') == 'female') selected @endif>女</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="birthday" class="form-label">出生日期 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control date" name="birthday" placeholder="设置生日" autocomplete="off" data-toggle="date-picker" data-cancel-class="btn-warning">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="multiple" class="form-label">批量生成 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-control form-select" name="multiple">
                                                <option value="1" selected>生成 1 个</option>
                                                <option value="5" >生成 5 个</option>
                                                <option value="10" >生成 10 个</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="validate-box">
                                    <div class="mb-3">
                                        <label for="id_card" class="form-label">身份证号码 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="id_card" class="form-control" placeholder="请填写需要解析的身份证号码" autocomplete="off" data-rule="required" />
                                        </div>
                                    </div>
                                </div>


                                <div class="d-flex flex-wrap justify-content-between">
                                    <button class="btn btn-primary" type="submit"><strong>提交</strong></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-sm-6 text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-user-hexagon fs-32" ></i>
                            </span>
                        </div>
                        <h4 class="mt-3">处理结果预览</h4>
                        <textarea class="form-control" id="resp_textarea" rows="14" placeholder="生成结果 / 解析结果"></textarea>
                        <p class="text-muted mb-3">表单数据处理结果!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        function form_after(resp) {
            // 处理结果预览
            $('#resp_textarea').val(JSON.stringify(resp.data.list, null, 4))
        }

        $('.validate-box').hide();
        $('#handle_type').on('change', function(){
            var selectedValue = $(this).val();
            if (selectedValue === 'generate') {
                // 显示生成身份证的表单
                $('.generate-box').show();
                $('.validate-box').hide();
            } else if (selectedValue === 'validate') {
                // 显示验证身份证的表单
                $('.generate-box').hide();
                $('.validate-box').show();
            }
        });
    </script>

@endsection
