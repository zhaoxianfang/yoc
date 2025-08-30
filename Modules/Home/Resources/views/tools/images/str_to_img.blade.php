@extends('home::layouts.home_layout')
@section('title', "文字转换为图片")

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
        #show_text_img{
            max-width: 80%;
        }
    </style>
@endsection

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">文字转换为图片</h5>
                <h6 class="card-subtitle text-body-secondary"></h6>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6 border-end border-dashed">
                        <div class="p-2">
                            {{-- <h4 class="mb-1 fw-bold text-uppercase">身份证号码在线生成器</h4> --}}
                            {{-- <p class="text-muted mb-4">填写基础信息进行生成</p> --}}


                            <p class="text-muted">
                                根据下方填写的配置信息生成目标码图片
                            </p>


                            <form method="POST">
                                <div class="mb-3">
                                    <label for="text" class="form-label">文本内容 <span class="text-danger">*</span></label>
                                    <div class="col-12">
                                        <input type="text" id="text" name="text" placeholder="填入字符串" value="您好!<br>hello!" class="form-control" required="">
                                        <small class="form-text text-muted"> <strong>提示:</strong>填写数字、字母等,不支持<code>#</code>,可以使用<code><<span>br</sapn>></code>字符自定义换行 </small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="label" class="form-label">指定宽高(px) <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="input-group" data-touchspin>
                                                <button type="button" class="btn btn-light floating" data-minus><i class="ti ti-minus"></i></button>
                                                <input type="number" class="form-control form-control-sm border-0" id="width" value="500" min="100"  max="8000">
                                                <button type="button" class="btn btn-light floating" data-plus><i class="ti ti-plus"></i></button>
                                            </div>
                                            <small class="form-text text-muted"> 指定图片的宽度 </small>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group" data-touchspin>
                                                <button type="button" class="btn btn-light floating" data-minus><i class="ti ti-minus"></i></button>
                                                <input type="number" class="form-control form-control-sm border-0" id="height" value="300" min="50" max="8000">
                                                <button type="button" class="btn btn-light floating" data-plus><i class="ti ti-plus"></i></button>
                                            </div>
                                            <small class="form-text text-muted"> 指定图片的高度 </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="label" class="form-label">图片颜色 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" id="fr-color" class="form-control" name="color" value="#FFFFFF">
                                        <input type="color" id="bg-color" class="form-control" name="bg_color" value="#0000FF">
                                    </div>
                                    <small class="form-text text-muted"> 设置图片文字颜色：<code>文字颜色</code>和<code>图片背景色</code></small>
                                </div>

                                <div class="mb-3">
                                    <label for="font" class="form-label">文字字体</label>
                                    <div class="input-group">
                                        <select class="form-control custom-select" name="font" id="font">
                                            <option value="pmzdxx" selected>庞门正道细线体</option>
                                            <option value="pmzdbt">庞门正道标题体</option>
                                            <option value="lishu">隶书</option>
                                            <option value="yishanbei">峄山碑篆体</option>
                                            <option value="xingkai">华文行楷</option>
                                        </select>
                                    </div>
                                    <small class="form-text text-muted"> <strong>提示:</strong>字体由 「猫啃网」(https://www.maoken.com/all-fonts)免费提供</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">文字旋转角度 <span class="text-danger">*</span></label>
                                    <div class="input-group" data-touchspin>
                                        <button type="button" class="btn btn-light floating" data-minus><i class="ti ti-minus"></i></button>
                                        <input type="number" class="form-control form-control-sm border-0" id="rotate" value="0" min="0"  max="360">
                                        <button type="button" class="btn btn-light floating" data-plus><i class="ti ti-plus"></i></button>
                                    </div>
                                    <small class="form-text text-muted"> 文字旋转角度 0~360° </small>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between">
                                    <button class="btn btn-primary" type="submit"><strong>生成图片</strong></button>
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
                        <img src="" alt="二维码/条形码" id="show_text_img" title="生成的目标图片" data-tips="track">
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

        $('#show_text_img').hide();
        function form_before() {
            $('#show_text_img').hide();

            $('#show_text_img').attr("src",'');
            // document.forms["str_to_img"]["fname"].value + "!"
            var text = $('#text').val() || 'hello';
            var width =  $('#width').val() || 500;
            var height = $('#height').val() || 300;
            var color =  ($('#fr-color').val()).substr(1) || '#FFFFFF';
            var bg_color =  ($('#bg-color').val()).substr(1) || '#0000FF';
            var font = $('#font').val() || 'pmzdxx';
            var rotate = $('#rotate').val() || 0;

            let protocol = window.location.protocol, host = window.location.host;
            let url_domain = `${protocol}//${host}`;

            var url = url_domain+'/tools/text2png/'+text+'/'+width+'/'+height+'/'+color+'/'+bg_color+'/'+rotate+'/'+font+'.html';

            $('#show_text_img').attr("src",url);
            $('#show_text_img').show()

            return false
        }
        function form_after(resp) {

        }
    </script>

@endsection
