<?php

namespace Modules\Callback\Http\Controllers\Web\Tencent;

use Exception;
use Illuminate\Http\Request;
use Modules\Callback\Http\Controllers\Web\CallbackController;
use Modules\Users\Services\UserAuthServices;
use zxf\Login\Constants\ConstCode;
use zxf\Login\OAuth;

/**
 * QQ 互联登录
 */
class Connect extends CallbackController
{
    /**
     * qq登录
     *
     * 可以在url 中传入 参数 callback_url 用来做通知回调 ； 例如
     * xxx.com/callback/tencent/login?callback_url=http%3A%2F%2Fwww.a.com%2Fa%2Fb%2Fc%3Fd%3D123 callback_url 参数说明
     * 传入前需要做 urlencode($callback_url) 操作 callback_url 回调地址要求允许跨域或者 csrf
     */
    public function login()
    {
        try {
            // 1、 初始化实例类
            // 实例化方式一：
            $oauth = OAuth::Qq();
            // OR 实例化方式二：
            // $oauth = new Qq($config);

            $jump_url = request()->get('callback_url', '');
            $jumpUrl = $jump_url ? urldecode($jump_url) : '';

            // 2、 可选：强制验证回跳地址中的state参数
            // 参数为空时内部会默认一个值
            $oauth->mustCheckState($jumpUrl); // 如需手动验证state,请关闭此行

            // 微博、微信：特别指定用于手机端登录【正常情况下不设置】，则需要设定->setDisplay('mobile')

            // 3、 得到授权跳转地址
            $url = $oauth->getRedirectUrl();

            // 4、重定向到外部授权地址
            return redirect()->away($url);
        } catch (Exception $e) {
            return view('errors.tips', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 回调&通知
     */
    public function callback()
    {
        try {
            /** 1、初始化实例类 */
            $oauth = OAuth::Qq();

            /** 2、可选：手动验证state 并返回跳转前传入的参数 */
            $callbackUrl = $oauth->mustCheckState()->checkState(); // 如需手动验证state,请关闭此行

            /** 3、获取第三方用户信息 */
            $userInfo = $oauth->userInfo(); // 【推荐】处理后的用户信息
            // OR
            // $userInfo = $oauth->getUserInfo(); // 原始用户信息

            /**
             * 如果是App登录
             * $type = "applets";
             * $userInfo = OAuth::$name($this->config)->setType($type)->userInfo();
             */
            /**
             * 如果是App登录
             * $type = "applets";
             * $userInfo = OAuth::$name($this->config)->setType($type)->userInfo();
             */

            // 获取登录类型
            // $userInfo['type'] = ConstCode::getTypeConst($userInfo['channel']);

            // 记录用户信息
            $loginUserInfo = UserAuthServices::instance()->fastLogin('qq', $userInfo);

            if ($callbackUrl) {
                return buildRequestFormAndSend($callbackUrl, $loginUserInfo);
            } else {
                dd($loginUserInfo);
            }
        } catch (Exception $e) {
            return view('errors.tips', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function notify(Request $request)
    {
        return view('errors.tips', [
            'message' => '无通知事件.',
        ]);
    }
}
