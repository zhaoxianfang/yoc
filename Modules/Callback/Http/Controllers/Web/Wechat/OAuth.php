<?php

namespace Modules\Callback\Http\Controllers\Web\Wechat;

use Exception;
use Illuminate\Http\Request;
use zxf\Tools\DataArray;
use zxf\WeChat\OfficialAccount\Oauth as WebOAuth;

class OAuth
{
    // 微信配置
    protected array|DataArray $config = [
        'token' => '',
        'appid' => '',
        'secret' => '',
        'aes_key' => '',
        'notify_url' => '',

        // 缓存目录配置（可选，需拥有读写权限）
        'cache_path' => '',
    ];

    public function login(Request $request)
    {
        if ($request->input('code')) {
            return $this->callback($request);
        }

        $oauth = new WebOAuth('default');

        $scope = 'snsapi_userinfo'; // snsapi_userinfo or snsapi_base
        $url = $oauth->getOauthRedirect(config('app.url').'/callback/wechat/login', 'STATE', $scope);

        // 重定向到外部授权地址
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        try {

            $oauth = new WebOAuth('default');
            $token = $oauth->getOauthAccessToken($request->code);

            if (isset($token['errcode'])) {
                // 出错啦！
                return view('errors.tips', [
                    'title' => '网页授权异常',
                    'message' => $token['errmsg'],
                ]);
            }

            // {
            //  "access_token":"ACCESS_TOKEN",
            //  "expires_in":7200,
            //  "refresh_token":"REFRESH_TOKEN",
            //  "openid":"OPENID",
            //  "scope":"SCOPE",
            //  "is_snapshotuser": 1,
            //  "unionid": "UNIONID"
            // }

            $userInfo = $oauth->getUserInfo($token['access_token'], $token['openid']);
            debug_test($userInfo, '微信网页授权 $userInfo');

            return view('errors.tips', [
                'title' => '网页授权成功',
                'message' => $userInfo['nickname'].':'.$userInfo['openid'].':',
                'img' => $userInfo['headimgurl'],
            ]);

        } catch (Exception $e) {
            return view('errors.tips', [
                'message' => $e->getMessage(),
            ]);
        }

    }
}
