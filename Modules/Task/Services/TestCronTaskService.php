<?php

namespace Modules\Task\Services;

class TestCronTaskService
{
    /**
     * 测试定时任务的「静态方法」调用
     *
     * 调用参数: \Modules\Task\Services\TestCronTaskService::init
     *
     *
     * @return void
     */
    public static function init(array $params = [])
    {
        echo 'static:run cron task init func';
    }

    /**
     * 测试定时任务的「普通方法」调用
     *
     * 调用参数:
     *      ['\Modules\Task\Services\TestCronTaskService','test']
     *      或者
     *      [\Modules\Task\Services\TestCronTaskService,test]
     *
     *
     * @return void
     */
    public function test(array $params = [])
    {
        echo 'run cron task test func';
    }
}
