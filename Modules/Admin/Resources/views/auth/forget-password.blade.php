@extends('admin::layouts.auth.auth_layout')
@section('title', '忘记密码')

@section('content')

    <div class="auth-brand text-center mb-4">
        <a href="javascript:;" class="logo-dark">
            <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="dark logo" height="32">
        </a>
        <a href="javascript:;" class="logo-light">
            <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="logo" height="32">
        </a>
        <h4 class="fw-bold mt-3">找回密码 |  {{ config('app.name','威四方') }}!</h4>
        <p class="text-muted w-lg-75 mx-auto">请输入必要的信息进行找回密码.</p>
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input form-check-input-light fs-14" name="remember" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">保持登录</label>
                </div>
                <a href="{{ route('admin.auth.forget_password') }}" class="text-decoration-underline link-offset-3 text-muted">忘记密码?</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-semibold py-2">提交</button>
            </div>
        </form>

        <p class="text-muted text-center mt-4 mb-0">
            已有账号? <a href="{{ route('admin.auth.login') }}" class="text-decoration-underline link-offset-3 fw-semibold">返回登录</a>
        </p>
    </div>
@endsection

@section('page_js')
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
    </script>
@endsection
