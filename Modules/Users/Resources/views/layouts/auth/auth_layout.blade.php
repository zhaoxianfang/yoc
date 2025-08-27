<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">

    @hasSection('title')
        <title> @yield('title','') | {{ config('app.name','威四方') }}</title>
    @endif
    @sectionMissing('title')
        <title>{{ config('app.name','威四方') }}</title>
    @endif

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Inspinia is the #1 best-selling admin dashboard template on Wrapmarket. Perfect for building CRM, CMS, project management tools, and custom web apps with clean UI, responsive design, and powerful features.">
    <meta name="keywords" content="Inspinia, admin dashboard, Wrapmarket, Wrapbootstrap, HTML template, Bootstrap admin, CRM template, CMS template, responsive admin, web app UI, admin theme, best admin template">
    <meta name="author" content="weisifang.com,威四方">
    <meta property="og:url" content="https://weisifang.com">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('static/images/favicon.ico') }}">

    @section('head_css_before')
        @hasSection('head_css_before')
        @endif
    @show

    <!-- Theme Config Js -->
    <script src="{{ asset('static/inspinia/v4.0/assets/js/config.js') }}"></script>

    <!-- Vendor css -->
    <link href="{{ asset('static/inspinia/v4.0/assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="{{ asset('static/inspinia/v4.0/assets/css/app.min.css') }}" rel="stylesheet" type="text/css">

    @hasSection('use_TnCode')
        {{-- TnCode 验证码 --}}
        <link href="/tn_code/assets/tn_code.min.css" rel="stylesheet">
    @endif

    @section('head_css')
        @hasSection('head_css')
            <!-- 页面中引入page css -->
        @endif
    @show

</head>

<body>

<div class="auth-box overflow-hidden align-items-center d-flex">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-md-6 col-sm-8">

                <div class="auth-brand text-center mb-4">
                    <a href="javascript:;" class="logo-dark">
                        <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="dark logo" height="32">
                    </a>
                    <a href="javascript:;" class="logo-light">
                        <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="logo" height="32">
                    </a>
                    <h4 class="fw-bold mt-3">后台登录 | {{ config('app.name','威四方') }}!</h4>
                    <p class="text-muted w-lg-75 mx-auto">请输入账号和密码登录系统.</p>
                </div>

                <div class="card p-4 rounded-4">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">用户名 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="username" class="form-control" id="username" placeholder="手机号|邮箱号" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">密码 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" placeholder="••••••••" required>
                            </div>
                        </div>

                        @hasSection('use_TnCode')
                        <div class="mb-3">
                            <a class="btn btn-primary block btn-outline full-width w-100 m-b text-black tncode">去验证</a>
                            <input type="hidden" class="form-control" name="tn_r" value="" id="tn_code_input" autocomplete="off" />
                        </div>
                        @endif

                        <div class="hr-line-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input form-check-input-light fs-14" name="remember" type="checkbox" id="remember">
                                <label class="form-check-label" for="remember">保持登录</label>
                            </div>
                            <a href="{{ route('admin.auth.forget_password') }}" class="text-decoration-underline link-offset-3 text-muted">忘记密码?</a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">登录</button>
                        </div>
                    </form>

                    <p class="text-muted text-center mt-4 mb-0">
                        没有账号? <a href="javascript:;" id="create_access" class="text-decoration-underline link-offset-3 fw-semibold">创建账号</a>
                    </p>
                </div>

                @yield('content')

                <p class="text-center text-muted mt-4 mb-0">
                    © 2023~<script>document.write(new Date().getFullYear())</script> {{ config('app.name','威四方') }} 版权所有.
                </p>
            </div>
        </div>
    </div>
</div>

@section('page_js_before')
    @hasSection('page_js_before')
    @endif
@show

<!-- end auth-fluid-->
<!-- Vendor js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/vendors.min.js') }}"></script>

<!-- App js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/app.min.js') }}"></script>
<script src="{{ asset('static/libs/zxf/js/tools.min.js') }}" type='text/javascript'></script>

@hasSection('use_TnCode')
    <script src="/tn_code/assets/tn_code.min.js"></script>
    <script type="text/javascript">
        $TN.init({
            handleDom: ".tncode", // 触发验证码容器
            getImgUrl: "/tn_code/get_img", // 获取验证码图片地址
            checkUrl: "/tn_code/check" // 验证地址
        }).onSuccess(function () {
            //验证通过
            $("#tn_code_input").val($TN._mark_offset);
            // console.log('验证通过')
        }).onFail(function () {
            //验证失败
            console.log("验证失败");
        });
    </script>
@endif

<script type="text/javascript">
    // 表单提交前的操作
    function form_before() {
        myTools.msg('登录中...');
    }
    // 表单提交后的操作
    function form_after(res){
        if(res.code === 200){
            myTools.msg(res.message || '登录成功!' );
            setTimeout(function(){
                window.location.href = res.url;
            },2000);
        }else{
            myTools.msg(res.message || '登录失败!');
        }
    }
    function createAccess(){
        myTools.msg('请联系管理员进行相关处理!');
    }
    // 快捷方法
    myTools.dom.click('#create_access', function() {
        myTools.msg('暂未开放!');
    });
</script>

@section('page_js')
    @hasSection('page_js')
        <!-- 页面中引入page js -->
    @endif
@show

</body>
</html>
