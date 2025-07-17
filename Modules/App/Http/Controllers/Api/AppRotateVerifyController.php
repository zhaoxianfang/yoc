<?php

namespace Modules\App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\App\Services\AppImageAuthServices;
use Modules\System\Http\Controllers\Api\ApiBaseController;

/**
 * App端 旋转图片验证码
 */
class AppRotateVerifyController extends ApiBaseController
{
    // 提交日志
    public function getImg(string $random, AppImageAuthServices $services)
    {
        return $services->getImg();
    }

    /**
     * 验证 图片旋转角度
     */
    public function check(Request $request, AppImageAuthServices $services)
    {
        $angle = $request->input('angle', 0);
        if (! $services->check((int) $angle, false)) {
            return response()->json([
                'code' => 400, // 200成功；其他失败
                'msg' => '图片验证码验证失败', // 提示信息
            ]);
        } else {
            return response()->json([
                'code' => 200, // 200成功；其他失败
                'msg' => '验证成功', // 提示信息
            ]);
        }
    }
}
