<?php

namespace Modules\System\Http\Controllers\Admin;

use Illuminate\Support\Facades\App;
use Modules\Admin\Http\Controllers\AdminBaseController;

class ClearDataController extends AdminBaseController
{
    /**
     * ==========================================================================
     * 以下为清理各种数据
     * ==========================================================================
     */

    /**
     * 清理系统配置缓存
     */
    public function setting()
    {
        try {
            // 清空配置缓存
            clear_cache();

            // 当前环境是 local 或 testing ...
            // if (App::environment(['local', 'testing'])) {
            // 当前环境不是本地环境
            if (! App::environment('local')) {
                // 重新写入缓存
                open_cache();
            }

            return $this->json([
                'code' => 200,
                'message' => '清理成功',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => '清理失败:'.$e->getMessage(),
            ]);
        }
    }
}
