@extends('home::layouts.home_layout')
@section('title', "RSA加密解密")

@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">RSA <small>加密解密</small></h5>
                {{-- <h6 class="card-subtitle text-body-secondary">:)</h6>--}}
            </div>

            <div class="card-body">
                <form method="POST">
                    <div class="row g-4">
                        <input type="hidden" id="handle_type" name="handle_type" value="">
                        <div class="form-group col-12 row">
                            <div class="col-4">
                                <label for="key_length" class="form-label">密钥长度 <span class="text-danger">*</span></label>
                                <select class="form-control custom-select" name="key_length" >
                                    <option value="512" >密钥长度:512位</option>
                                    <option value="1024" >密钥长度:1024位</option>
                                    <option value="2048" selected >密钥长度:2048位</option>
                                    <option value="4096" >密钥长度:4096位</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="digest_alg" class="form-label">摘要算法 <span class="text-danger">*</span></label>
                                <select class="form-control custom-select" name="digest_alg" >
                                    <option value="sha512" >摘要算法:sha512</option>
                                    <option value="sha1" >摘要算法:sha1</option>
                                    <option value="md5" >摘要算法:md5</option>
                                    <option value="sha384" >摘要算法:sha384</option>
                                    <option value="sha256" >摘要算法:sha256</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="padding" class="form-label">填充 <span class="text-danger">*</span></label>
                                <select class="form-control custom-select" name="padding" >
                                    <option value="OPENSSL_PKCS1_PADDING" selected >填充:OPENSSL_PKCS1_PADDING</option>
                                    <option value="OPENSSL_NO_PADDING" >填充:OPENSSL_NO_PADDING</option>
                                    <option value="OPENSSL_PKCS1_OAEP_PADDING" >填充:OPENSSL_PKCS1_OAEP_PADDING</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 border-end border-dashed">

                            <div class="mb-3">
                                <label for="public_key" class="form-label">公钥 <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <textarea class="form-control public_key w-100" id="public_key" name="public_key" placeholder="公钥:请先填写公钥或点击最下面的「生成密钥对」自动生成" rows="6" required></textarea>
                                    <small class="form-text text-muted"> 公钥:请先填写您的公钥或点击最下面的「生成密钥对」自动生成 </small>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="before_string" class="form-label">加密前字符串 <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <textarea class="form-control w-100" name="before_string" id="before_string" placeholder="加密前字符串..." rows="10" required></textarea>
                                    <small class="form-text text-muted"> 加密前字符串 </small>
                                </div>
                            </div>

                        </div>

                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="private_key" class="form-label">私钥 <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <textarea class="form-control private_key w-100" id="private_key" name="private_key" placeholder="私钥:请先填写私钥或点击最下面的「生成密钥对」自动生成" rows="6" required></textarea>
                                    <small class="form-text text-muted"> 私钥:请先填写您的私钥 或点击最下面的「生成密钥对」自动生成 </small>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="after_string" class="form-label">加密后的结果 <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <textarea class="form-control w-100" name="after_string" id="after_string" placeholder="加密后的结果..." rows="10"></textarea>
                                    <small class="form-text text-muted"> 加密后的结果 </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-12 row">
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-success submit-btn col-12" data-type="generate_key"><i class="ti ti-circle-key"></i> 生成密钥对</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-purple submit-btn col-12" data-type="encryption"><i class="ti ti-password-user"></i> 公钥加密</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-warning submit-btn col-12" data-type="decrypt"><i class="ti ti-password-fingerprint"></i> 私钥解密</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        function form_before(res) {
            // console.log(res)
            // $('#images_none').show()
            // $('#compressor_res_box').hide()
        }
        function form_after(res) {
            if(res.fn === "generate_key"){
                $('#public_key').val(res.public_key);
                $('#private_key').val(res.private_key);
                // 恢复 #public_key 的必填验证
                $('#public_key').attr('required', 'required');
                $('#private_key').attr('required', 'required');
                $('#before_string').attr('required', 'required');
            }
            if(res.fn === "encryption"){
                $('#after_string').val(res.result);
            }
            if(res.fn === "decrypt"){
                $('#before_string').val(res.result);
            }
        }

        $('.submit-btn').click(function (e) {
            $('#handle_type').val($(this).data('type'));
            if($(this).data('type') == 'generate_key'){
                // 移除 #public_key 的必填验证
                $('#public_key').removeAttr('required');
                $('#private_key').removeAttr('required');
                $('#before_string').removeAttr('required');
            }
            if($(this).data('type') == 'decrypt'){
                // 移除 #decrypt 的必填验证
                $('#before_string').removeAttr('required');
            }
            // $('#form_ele').submit();
            myTools.form.formSubmit(e);
        });
    </script>

@endsection
