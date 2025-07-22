<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;

class AdminAuthController extends AdminBaseController
{
    /**
     * Admin 登录页面
     */
    public function login()
    {
        // 判断是否已经登录
        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }
        return view('admin::auth/login');
    }

    // 登录
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|min:11|max:50',
            'password' => 'required|min:6',
            // 'captcha'  => 'required|captcha', //TODO: 图片验证码
             'tn_r' => 'required|TnCode',
        ], [
            'mobile.required' => '请输入手机号',
            'mobile.min' => '手机号格式错误',
            'mobile.max' => '手机号格式错误',
            'password.required' => '请输入密码',
            'password.min' => '密码不能少于6位',
            'captcha.required' => '请输入验证码',
            'captcha.captcha' => '验证码填写错误',
            'tn_r.required' => '滑动验证码验证失败',
        ]);

        $credentials = $request->only(['username', 'password']);
        $remember = false; // 是否记住密码

        // 判断是邮箱还是手机号
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL)? 'email': 'mobile';

        // 重构认证凭据
        $authCredentials = [
            $field => $credentials['username'],
            'password' => $credentials['password']
        ];

        // dd(encrypt('abc'));
        if (! auth('admin')->attempt($authCredentials, false)) {
            return response()->json([
                'code' => 401,
                'message' => '账号或者密码错误'
            ], 401);
        }

        $jump = route('admin.home'); // 成功后跳转到的地址

        return $this->json([
            'code' => 200,
            'message' => '登录成功',
        ], 200, $jump);
    }

    // 登出
    public function logout()
    {
        auth('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        if (request()->ajax()) {
            return $this->json([
                'code' => 200,
                'message' => '退出成功',
                'url'=>route('admin.auth.login')
            ]);
        }
        return to_route('admin.auth.login', [], 302);
    }

    // 忘记密码
    public function forgetPassword(){
        return view('admin::auth/forget-password');
    }

    // 重置密码
    public function retrievePassword(Request $request){
        if (request()->ajax()) {
            return $this->json([
                'code' => 200,
                'message' => '此功能正在开发中...',
                'url'=>route('admin.auth.forget_password')
            ]);
        }
        return to_route('admin.auth.forget_password', [], 302);
    }
}
