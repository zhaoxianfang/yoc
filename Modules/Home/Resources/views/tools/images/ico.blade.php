@extends('home::layouts.home_layout')
@section('title', "图片转换为ico")

@section('use_datepicker', "true")
@section('use_form', "true")

@section('head_css')
    <style>
        .mr-5 {
            margin-right: 5px;
        }
        .img-sm{
            width: 25px;
            height: 25px;
        }
        #show_ico_img{
            max-width: 80%;
        }
    </style>
@endsection

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">图片转换为ico</h5>
                <h6 class="card-subtitle text-body-secondary"></h6>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6 border-end border-dashed">
                        <div class="p-2">

                            <p class="text-muted">
                                根据下方填写的配置信息生成目标码图片
                            </p>


                            <form method="POST">
                                <div class="mb-3">
                                    <label for="level" class="form-label">目标尺寸 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="w-100">
                                            <div class="form-check fs-lg form-check-inline">
                                                <input class="form-check-input" type="radio" name="size" id="size_h" value="16">
                                                <label class="form-check-label fs-base" for="size_h">16x16</label>
                                            </div>
                                            <div class="form-check fs-lg form-check-inline">
                                                <input class="form-check-input" type="radio" name="size" id="size_q" value="32" checked>
                                                <label class="form-check-label fs-base" for="size_q">32x32</label>
                                            </div>
                                            <div class="form-check fs-lg form-check-inline">
                                                <input class="form-check-input" type="radio" name="size" id="size_m" value="64">
                                                <label class="form-check-label fs-base" for="size_m">64x64</label>
                                            </div>
                                            <div class="form-check fs-lg form-check-inline">
                                                <input class="form-check-input" type="radio" name="size" id="size_l" value="128">
                                                <label class="form-check-label fs-base" for="size_l">128x128</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted"> 选择一个合适的尺寸大小 </small>
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" >选择图片</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" type="file" name="images" id='images' value="" accept=".jpg, .jpeg, .png">
                                    </div>
                                </div>
                                <div class="hr-line-dashed mb-5"></div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <button class="btn btn-primary" type="submit"><i class="ti ti-favicon"></i> <strong>生成ICO</strong></button>
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
                        <img src="" alt="生成的目标图片" id="show_ico_img" title="点击立即下载" data-tips="track">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')

    <script>
        var base64Img = '';
        $(function () {

        });

        $('#show_ico_img').hide();
        function form_before() {
            base64Img = ''
            $('#show_ico_img').hide();
        }
        function form_after(resp) {
            $('#show_ico_img').show()
            base64Img = resp.data.base64_str;
            $('#show_ico_img').attr("src",base64Img);
        }

        $('#show_ico_img').on('click', function () {
            if(!base64Img){
                return;
            }
            myTools.func.downloadBase64(base64Img, 'favicon.ico');
        })
    </script>

@endsection
