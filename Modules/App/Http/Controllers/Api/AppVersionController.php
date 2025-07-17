<?php

namespace Modules\App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\App\Models\AppVersion;
use Modules\System\Http\Controllers\Api\ApiBaseController;

class AppVersionController extends ApiBaseController
{
    // 获取最近一次更新的app版本
    public function index(Request $request)
    {
        $version = $request->input('version', '1.0.0'); // 请求者使用的版本号，例如：1.0.0
        // 获取$version的主版本号数字，例如：1.0.0，获取到的是1
        $mainVersionNum = (int) explode('.', $version)[0];
        $versionValue = (int) $request->input('version_value', '100'); // 请求者使用的版本号值，例如：100
        $latestAppPackageInfo = AppVersion::query()->where('platform', $request->platform)->where('status', 1)->orderByDesc('version_num')->first();
        // 检查主版本号是否一致
        if (! empty($latestAppPackageInfo) && ((int) explode('.', $latestAppPackageInfo->version)[0] > $mainVersionNum) && $latestAppPackageInfo->is_wgt) {
            // 需要更新大版本
            $latestAppPackageInfo = AppVersion::query()->where('platform', $request->platform)->where('is_wgt', 0)->where('status', 1)->orderByDesc('version_num')->first();
        }
        if (empty($latestAppPackageInfo) || $versionValue == $latestAppPackageInfo->version_num) {
            // 版本号相同，不需要更新
            return $this->api_json(['latest' => null]);
        }

        return $this->api_json(['latest' => $latestAppPackageInfo]);
    }
}
