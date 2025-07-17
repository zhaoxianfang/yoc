<?php

namespace Modules\Task\Contracts;

/**
 * 定时任务执行器接口
 */
interface ExecutableInterface
{
    /**
     * 执行任务的方法
     *
     * @return mixed
     */
    public function execute();
}
