<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Other;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;

class Timezone extends HomeBaseController
{
    /**
     * 时间/时区转换
     */
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home::tools.other.timezone');
        }
        $fromTimeZone = $request->input('form_time_zone', 'Asia/Shanghai');
        $toTimeZone = $request->input('to_time_zone', 'America/New_York');
        $dateTime = $request->input('from_time_zone_date_time', '2024-01-01 12:00:00');

        $converter = \zxf\Tools\TimeZone::instance();
        // 例如：当上海时间是 2024-04-08 12:00:00 时，计算出纽约的当地时间（返回指定时间格式字符串）
        $res = $converter->toTimeZone($dateTime, $fromTimeZone, $toTimeZone, 'Y-m-d H:i:s');

        return $this->success([
            'to_date_time' => $res,
        ]);
    }
}
