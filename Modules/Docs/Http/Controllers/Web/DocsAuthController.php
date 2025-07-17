<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Docs\Http\Controllers\DocsBaseController;

/**
 * App 文档 登录授权页
 */
class DocsAuthController extends DocsBaseController
{
    public function login(Request $request)
    {
        // 生成密码  \Illuminate\Support\Facades\Hash::make('明文密码') 或者 bcrypt('明文密码')
        // 测试密码  \Illuminate\Support\Facades\Hash::check('明文密码', 'hash密码')
        if (! $request->isMethod('post')) {
            if (! auth('web')->guest()) {
                return redirect()->route('docs.home')->with('success', '登录成功'); // 成功后跳转到文档设置页面地址
            }

            return view('docs::auth/login', []);
        }
        $validator = Validator::make($request->all(), [
            'account' => 'required|min:2',
            'password' => 'required|min:2',
            'captcha' => 'required|captcha',
        ], [
            'account.required' => '用户名不能为空',
            'account.min' => '用户名不能少于2个字符',
            'password.required' => '密码不能为空',
            'password.min' => '密码不能少于2个字符',
            'captcha.required' => '请输入验证码',
            'captcha.captcha' => '验证码填写错误',
        ]);
        if ($validator->fails()) {
            return $this->backWithError($validator);
        }
        // $credentials = $request->only(['account', 'password']);
        $remember = true; // 是否记住密码
        if (
            ! auth('web')->attempt(['mobile' => $request->account, 'password' => $request->password], $remember)
            && ! auth('web')->attempt(['email' => $request->account, 'password' => $request->password], $remember)
        ) {
            return $this->backWithError('账号或者密码错误');
        }

        $jump = url()->previous(); // 成功后跳转到上一个页面地址

        return redirect($jump)->with('success', '登录成功'); // 成功后跳转到文档设置页面地址

    }

    public function register(Request $request)
    {
        if (! $request->isMethod('post')) {
            return view('docs::auth/register', []);
        }
        //        $request->validate([
        //            'captcha' => 'required|captcha',
        //        ], [
        //            'captcha.required' => '请输入验证码',
        //            'captcha.captcha'  => '验证码填写错误',
        //        ]);

        $validator = Validator::make($request->all(), [
            'captcha' => 'required|captcha',
        ], [
            'captcha.required' => '请输入验证码',
            'captcha.captcha' => '验证码填写错误',
        ]);
        if ($validator->fails()) {
            return $this->backWithError($validator);
        }

        return $this->backWithError('暂未开放注册功能');
    }

    public function logout(Request $request)
    {
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($request->ajax()) {
            return $this->success('退出成功');
        }

        return to_route('docs.home', [], 302);
    }

    public function qq()
    {
        // 判断来源url
        $refererLocal = source_local_website('url');
        $url = route('docs.auth.callback', ['source_url' => urlencode($refererLocal ?: route('docs.home'))]);

        return to_route('callback.tencent.login', ['callback_url' => urlencode($url)], 302);
    }

    public function weibo()
    {
        // 判断来源url
        $refererLocal = source_local_website('url');
        $url = route('docs.auth.callback', ['source_url' => urlencode($refererLocal ?: route('docs.home'))]);

        return to_route('callback.weibo.login', ['callback_url' => urlencode($url)], 302);
    }

    public function callback(Request $request)
    {
        $user = collect($request->input())->toArray();
        $remember = true; // 是否记住密码

        if (! auth('web')->loginUsingId($user['id'], $remember)) {
            return $this->tip_error('登录失败');
        }

        $jump_url = $request->input('source_url', '');
        $to = $jump_url ? urldecode($jump_url) : route('docs.home');

        // return redirect()->away($to); // 可跳转外部地址
        // return to_route('docs.home', [], 302);
        return redirect($to);
    }
}
