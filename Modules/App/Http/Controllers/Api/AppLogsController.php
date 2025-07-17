<?php

namespace Modules\App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\System\Http\Controllers\Api\ApiBaseController;

class AppLogsController extends ApiBaseController
{
    // 提交日志
    public function store(Request $request)
    {
        debug_test($request->input(), 'app_logs');

        return response()->json([
            'code' => 200, // 200成功；其他失败
            'msg' => '上传成功', // 提示信息
        ]);
    }
}
