@extends('docs::layouts.layout')
@section('title', '广场')

@section('top_nav_tabs')
    <a href="{{ url('/docs') }}">
        <li class="nav-tab @if(empty($nav_type) || $nav_type== 'home') active @endif">广场</li>
    </a>
    @if (auth()->check())
    <a href="{{ url('/docs/my') }}">
        <li class="nav-tab @if($nav_type== 'mine') active @endif">我的</li>
    </a>
    <a href="{{ url('/docs/create') }}">
        <li class="nav-tab @if($nav_type== 'create') active @endif">新建</li>
    </a>
    @endif
@endsection

@section('head_css')
    <link href="{{ asset('static/libs/zxf/css/bootstrap-form.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div style="width: 300px; margin: 0 auto;transform: translate(0, 50%);">
        <div class="row bg-white">
            <div class="col-12">
                <div class="">
                    <div>
                        <h2 class="text-center">登录</h2>
                    </div>

                    <div class="">
                        <form method="post" class="p-3 unbind-form">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-12"><input type="text" class="form-control" placeholder="手机/邮箱号" name="account" value="{{ old("account") }}" data-rule="required" /></div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12"><input type="password" class="form-control" placeholder="密码" name="password" value="{{ old("password") }}" data-rule="required" /></div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 input-group">
                                    <input id="captcha" type="text" class="form-control" name="captcha" placeholder="验证码" autocomplete="off" required>
                                    <img src="{{ captcha_src() }}" alt="captcha flat" title="点击刷新" style="cursor: pointer;margin: 0;padding: 0;" class="input-group-append" id="captcha_img">
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <span class="text-right col-sm-12" style="margin: -20px 0 10px 0;">没有账号？<a href="{{ route('docs.auth.register') }}">去注册</a> </span>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-sm btn-w-m btn-primary width-full">登录</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_js')
    <script>
        // 使用 DOMContentLoaded 确保 DOM 完全加载
        document.addEventListener('DOMContentLoaded', function() {
            // 绑定验证码图片点击事件（使用事件委托，防止动态加载的元素无法触发）
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'captcha_img') {
                    refresh_captcha();
                }
            });
        });

        // 刷新验证码（与原 jQuery 逻辑一致）
        function refresh_captcha() {
            var captchaImg = document.getElementById('captcha_img');
            if (!captchaImg) return;

            // 随机选择验证码类型：default、math、inverse
            var captchaTypes = ["default", "math", "inverse"];
            var captchaType = captchaTypes[Math.floor(Math.random() * captchaTypes.length)];

            // 生成新的验证码 URL（防止缓存）
            var captchaUrl = '{{ captcha_src('_captcha_type_') }}?' + Math.random();
            captchaImg.src = captchaUrl.replace('_captcha_type_', captchaType);
        }

        // 表单提交后自动刷新验证码（与原 jQuery 逻辑一致）
        function form_after(res) {
            refresh_captcha();

            var captchaInput = document.getElementById('captcha');
            if (captchaInput) {
                captchaInput.value = ''; // 清空验证码输入框
            }
        }
    </script>
@endsection
