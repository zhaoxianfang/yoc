@extends('home::layouts.home_layout')
@section('title', "二维码、条形码生成器")

@section('use_datepicker', "true")
@section('use_form', "true")

@section('head_css')
    <link href="{{ asset('static/inspinia/v4.0/assets/plugins/ionRangeSlider/ion.rangeSlider.css') }}" rel="stylesheet">
    <style>
        .mr-5 {
            margin-right: 5px;
        }
        .img-sm{
            width: 25px;
            height: 25px;
        }
        #show_code_img{
            max-width: 80%;
            width: 234px;
        }
    </style>
@endsection

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">二维码、条形码生成器</h5>
                <h6 class="card-subtitle text-body-secondary">易用、便捷的二维码、条形码生成工具！</h6>
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

                        <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a href="#qrcode" data-bs-toggle="tab" id="qrcode_tab" aria-expanded="false" class="nav-link active" aria-selected="true" role="tab">
                                    二维码生成器
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="#barcode" data-bs-toggle="tab" id="barcode_tab" aria-expanded="true" class="nav-link" aria-selected="false" role="tab">
                                    条形码生成器
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane show active" id="qrcode" role="tabpanel">
                                <form method="POST">
                                    <input type="hidden" name="img_type" value="qrcode">
                                    <div class="mb-3">
                                        <label for="content" class="form-label">二维码内容 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <textarea class="form-control" name="content" rows="4" placeholder="请填写相需要生成二维码的内容：例如：文本、网页等" required></textarea>
                                            <small class="form-text text-muted"> 网址、图片地址、数字、字母、中英文字符串等均可 </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="label" class="form-label">Label <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" class="form-control" name="label" placeholder="二维码下方的文字" autocomplete="off" />
                                            <small class="form-text text-muted"> 不填写表示不设置下方的文字信息 </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="font" class="form-label">文字字体</label>
                                        <div class="input-group">
                                            <select class="form-control custom-select" name="font">
                                                <option value="pmzdxx" selected>庞门正道细线体</option>
                                                <option value="pmzdbt">庞门正道标题体</option>
                                                <option value="lishu">隶书</option>
                                                <option value="yishanbei">峄山碑篆体</option>
                                                <option value="xingkai">华文行楷</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="level" class="form-label">容错率 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="w-100">
                                                <div class="form-check fs-lg form-check-inline">
                                                    <input class="form-check-input" type="radio" name="level" id="level_h" value="h" >
                                                    <label class="form-check-label fs-base" for="level_h">高(30%)</label>
                                                </div>
                                                <div class="form-check fs-lg form-check-inline">
                                                    <input class="form-check-input" type="radio" name="level" id="level_q" value="q">
                                                    <label class="form-check-label fs-base" for="level_q">25%</label>
                                                </div>
                                                <div class="form-check fs-lg form-check-inline">
                                                    <input class="form-check-input" type="radio" name="level" id="level_m" value="q" checked>
                                                    <label class="form-check-label fs-base" for="level_m">中等(15%)</label>
                                                </div>
                                                <div class="form-check fs-lg form-check-inline">
                                                    <input class="form-check-input" type="radio" name="level" id="level_l" value="l">
                                                    <label class="form-check-label fs-base" for="level_l">低(7%)</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted"> 选择一个合适的容错率 </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">文字字体大小 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" id="font_size" name="font_size" placeholder="文字字体大小" class="form-control" required="">
                                            <span class="form-text text-muted">文字字体大小</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">像素格大小 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" id="scale" name="scale" placeholder="二维码模块像素格大小" class="form-control" required="">
                                            <span class="form-text text-muted">二维码模块像素格大小</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="logo" class="form-label">设置LOGO</label>
                                        <div class="input-group">
                                            <select class="form-control custom-select" name="logo">
                                                <option value="" >不设置LOGO</option>
                                                <option value="/static/images/logo/logo_mini.jpg" data-img="/static/images/logo/logo_mini.jpg" data-class="img-sm mr-5">本站LOGO</option>
                                                <option value="/static/images/system/default_user.png" data-img="/static/images/system/default_user.png" data-class="img-sm mr-5">默认头像</option>
                                                <option value="/static/images/icon/wechat.png" data-img="/static/images/icon/wechat.png" data-class="img-sm mr-5">微信</option>
                                                <option value="/static/images/icon/qq.png" data-img="/static/images/icon/qq.png" data-class="img-sm mr-5">QQ</option>
                                                <option value="/static/images/icon/sina.png" data-img="/static/images/icon/sina.png" data-class="img-sm mr-5">微博</option>
                                                <option value="/static/images/icon/guoqi.jpg" data-img="/static/images/icon/guoqi.jpg" data-class="img-sm mr-5">国旗</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between">
                                        <button class="btn btn-primary" type="submit"><strong>提交</strong></button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" id="barcode" role="tabpanel">
                                <form method="POST">
                                    <input type="hidden" name="img_type" value="barcode">

                                    <div class="mb-3">
                                        <label for="bar_type" class="form-label">条形码类型</label>
                                        <div class="input-group">
                                            <select class="form-control custom-select" name="bar_type">
                                                <option value="CODABAR">Coda Bar</option>
                                                <option value="EAN13">Ean13(ISBN-13)</option>
                                                <option value="C128" selected>Code128(通用)</option>
                                                <option value="CODE11">Code11</option>
                                                <option value="C39">Code39</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">条形码内容 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" class="form-control" name="content" placeholder="条形码内容" autocomplete="off" />
                                            <small class="form-text text-muted"> 允许数字、字母、和一些常用符号 </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="label" class="form-label">Label <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" class="form-control" name="label" placeholder="二维码下方的文字" autocomplete="off" />
                                            <small class="form-text text-muted"> 不填写表示不设置下方的文字信息 </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="font" class="form-label">文字字体</label>
                                        <div class="input-group">
                                            <select class="form-control custom-select" name="font">
                                                <option value="pmzdxx" selected>庞门正道细线体</option>
                                                <option value="pmzdbt">庞门正道标题体</option>
                                                <option value="lishu">隶书</option>
                                                <option value="yishanbei">峄山碑篆体</option>
                                                <option value="xingkai">华文行楷</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">文字字体大小 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" id="font_size_2" name="font_size" placeholder="文字字体大小" class="form-control" required="">
                                            <span class="form-text text-muted">文字字体大小</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">厚度/高度 <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            <input type="text" id="thickness" name="thickness" placeholder="设置厚度或高度" class="form-control" required="">
                                            <span class="form-text text-muted">设置厚度或高度</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">条码宽度</label>
                                        <div class="col-12">
                                            <input type="text" id="bar_width" name="bar_width" placeholder="条码宽度" class="form-control" required="">
                                            <span class="form-text text-muted">条码宽度</span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between">
                                        <button class="btn btn-primary" type="submit"><strong>提交</strong></button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
                        <img src="" alt="二维码/条形码" id="show_code_img" title="点击图片进行下载" data-tips="track">
                        <p class="text-muted mb-3">表单数据处理结果,点击图片即可下载~</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <!-- IonRangeSlider -->
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/ionRangeSlider/ion.rangeSlider.min.js') }}"></script>

    <script>
        $(function () {
            // 获取url 中的#tab参数的值
            var tab = myTools.func.getAnchorPoint('tab');
            console.log(tab);
            if(tab !=='barcode'){
                $('#qrcode_tab').click();

            }else{
                $('#barcode_tab').click();
            }

            $("#font_size").ionRangeSlider({
                skin: "flat",
                min: 5,
                max: 110,
                step: 1,
                from: 16,
                postfix: " px",
                grid: true,
            });
            $("#font_size_2").ionRangeSlider({
                skin: "flat",
                min: 5,
                max: 110,
                step: 1,
                from: 10,
                postfix: " px",
                grid: true,
            });
            $("#scale").ionRangeSlider({
                skin: "flat",
                min: 1,
                max: 20,
                step: 1,
                from: 3,
                postfix: "px",
                grid: true,
            });
            $("#bar_width").ionRangeSlider({
                skin: "flat",
                min: 1,
                max: 20,
                step: 1,
                from: 1,
                postfix: "px",
                grid: true,
            });
            $("#thickness").ionRangeSlider({
                skin: "flat",
                min: 15,
                max: 245,
                step: 1,
                from: 60,
                postfix: "",
                grid: true,
            });
        });

        $('#show_code_img').hide();
        function form_before() {
            $('#show_code_img').hide();
            $('#resp_textarea').val('处理中...');
        }
        function form_after(resp) {
            if(resp.data.base64){
                $('#show_code_img').show();
                // 处理结果预览
                $('#resp_textarea').val(resp.data.base64);
                $('#show_code_img').attr('src', resp.data.base64);
            }
        }


        $('#show_code_img').on('click', function () {
            if(!$(this).attr('src')){
                return;
            }
            const date_time = new Date().toISOString().replace(/[-:T.Z]/g, "").slice(0, 14);
            myTools.func.downloadBase64($(this).attr('src'), date_time+'.png');
        })

    </script>

@endsection
