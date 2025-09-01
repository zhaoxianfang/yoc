<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Admin\Services\AdminAuthServices;

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
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        // 重构认证凭据
        $authCredentials = [
            $field => $credentials['username'],
            'password' => $credentials['password'],
        ];

        // dd(encrypt('abc'));
        if (! auth('admin')->attempt($authCredentials, false)) {
            return response()->json([
                'code' => 401,
                'message' => '账号或者密码错误',
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
        if (is_ajax()) {
            return $this->json([
                'code' => 200,
                'message' => '退出成功',
                'url' => route('admin.auth.login'),
            ]);
        }

        return to_route('admin.auth.login', [], 302);
    }

    // 忘记密码
    public function forgetPassword()
    {
        return view('admin::auth/forget-password');
    }

    // 重置密码
    public function retrievePassword(Request $request)
    {
        if (is_ajax()) {
            return $this->json([
                'code' => 200,
                'message' => '此功能正在开发中...',
                'url' => route('admin.auth.forget_password'),
            ]);
        }

        return to_route('admin.auth.forget_password', [], 302);
    }

    // ======================= 第三方登录 =======================

    public function qqLogin()
    {
        // 判断来源url
        $refererLocal = source_local_website('url');
        $url = url('admin/auth/callback?source_url='.urlencode($refererLocal ?: route('docs.home')));

        return to_route('callback.tencent.login', ['callback_url' => urlencode($url)], 302);
    }

    public function weiboLogin()
    {
        $url = url('admin/auth/callback');

        return to_route('callback.weibo.login', ['callback_url' => urlencode($url)], 302);
    }

    public function wechatLogin()
    {
        return $this->backWithError('暂未开放');
    }

    // 登录回调
    public function callback(Request $request)
    {
        $user = collect($request->input())->toArray();
        $remember = false; // 是否记住密码
        // 进行session 登录
        AdminAuthServices::instance()->auth('admin')->use(AdminAuthServices::LOGIN_TYPE_CUSTOM)->setCustomField('user_id')->needRemember($remember)->login($user['id']);
        if (auth('admin')->guest()) {
            return to_route('admin.auth.login');
        }

        $jump_url = request()->input('source_url', '');
        $to = $jump_url ? urldecode($jump_url) : route('admin.home');

        // return redirect()->away($to); // 可跳转外部地址
        // return to_route('docs.home', [], 302);
        return redirect($to);
    }
}
