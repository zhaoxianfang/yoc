@extends('home::layouts.home_layout')
@section('title', "图片压缩与裁剪")

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
                <h5 class="card-title mb-1">图片压缩与裁剪</h5>
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
        $(function () {

        });

        $('#show_ico_img').hide();
        function form_before() {
            $('#show_ico_img').hide();
        }
        function form_after(resp) {
            $('#show_ico_img').show()
            $('#show_ico_img').attr("src",resp.data.base64_str);
        }

        $('#show_ico_img').on('click', function () {
            return;
            const date_time = new Date().toISOString().replace(/[-:T.Z]/g, "").slice(0, 14);
            myTools.func.downloadBase64(base64Img, date_time+'.png');
        })
    </script>

@endsection
