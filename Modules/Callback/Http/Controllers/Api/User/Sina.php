<?php

namespace Modules\Callback\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use Modules\Callback\Http\Controllers\Web\CallbackController;
use Modules\Users\Services\UserAuthServices;
use zxf\Login\OAuth;

class Sina extends CallbackController
{
    // app端token换用户信息
    public function tokenToUserInfo(Request $request)
    {
        // $auth = OAuth::Sina(config('tools_oauth.sina.mobile'));
        // OR 调用 tools_oauth 的 mobile 场景
        $auth = OAuth::Sina('mobile');

        $userInfo = $auth->setToken($request->input())->userInfo();

        try {
            // 记录用户信息
            $loginUserInfo = UserAuthServices::instance()->fastLogin('sina', $userInfo);
            // 使用id登录
            $res = UserAuthServices::instance()->auth('api')->byToken(true)->use('id')->login($loginUserInfo['id']);
            $res['data']['user'] = $loginUserInfo;

            return response()->json($res);
        } catch (\Exception $exception) {
            return response()->json([
                'code' => 500,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
