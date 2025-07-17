<?php

namespace Modules\Task\Services;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Task\Contracts\ExecutableInterface;
use Modules\Task\Jobs\CronTabsJob;
use Modules\Task\Models\TaskCronTabs;
use zxf\Http\Curl;

class CronTaskService
{
    /**
     * 调度自定义定时任务入口[通过 Console/Kernel.php 进行调度]
     *
     *
     * @return void
     */
    public static function handle(Schedule $schedule)
    {
        // 仅在数据库连接可用时执行 Schema::hasTable
        try {
            DB::connection()->getPdo();
            if (Schema::hasTable('task_cron_tabs')) {
                try {
                    $cronTasks = TaskCronTabs::open()->get();
                    foreach ($cronTasks as $task) {
                        $schedule->call(function () use ($task) {
                            try {
                                // 队列调度，避免运行超时1分钟
                                CronTabsJob::dispatch($task)->afterCommit();
                            } catch (Exception $err) {
                                Log::error('[TaskCronTabs]: 定时队列加入失败TaskCronTabs:'.$task->id, (array) $err);
                            }
                        })->cron(trim($task->timer));
                    }
                } catch (Exception $e) {
                    // 套入此层是为了防止 爬虫异常 而影响到其他 命名定时任务
                    Log::error('[TaskCronTabs]: 定时队列执行失败TaskCronTabs:', (array) $e);
                }
            }
        } catch (\Exception $e) {
            // 数据库不可用时跳过
        }
    }

    /**
     * 执行定时任务事件
     *
     *
     * @return false|void
     */
    public function run(TaskCronTabs $task)
    {
        if ($task->status != TaskCronTabs::STATUS_OPEN) {
            return false;
        }
        try {
            // 模型
            if ($task->type == TaskCronTabs::TYPE_MODEL) {
                if ($task->executable && $task->executable instanceof ExecutableInterface) {
                    $task->executable->execute();
                }
                throw new Exception('被执行的模型对象不存在');
            }
            // 方法
            if ($task->type == TaskCronTabs::TYPE_FUNC) {
                $params = $task->class_or_func_params ? json_decode($task->class_or_func_params, true) : [];
                $call_class = $this->getFuncClass($task->execute_class_or_func);
                call_user_func_array($call_class, [$params, $this]);
            }
            // Http 请求
            if ($task->type == TaskCronTabs::TYPE_CURL) {
                $params = $task->curl_params ?? [];
                $url = $task->curl_url;
                $method = $params['method'] ?? 'GET';
                $headers = $params['headers'] ? json_decode($params['headers'], true) : [];
                $body = $params['body'] ? json_decode($params['body'], true) : [];
                $this->callHttpRequest($url, $method, $headers, $body);
            }
            $run_status = TaskCronTabs::RUN_STATUS_SUCCESS;
        } catch (Exception $exception) {
            $run_status = TaskCronTabs::RUN_STATUS_FAIL;
            Log::error('[TaskCronTabs]: 定时队列加入失败TaskCronTabs:'.$task->id, (array) $exception);
        }

        // 更新执行状态
        $task->fill([
            'run_status' => $run_status,
            'run_at' => now()->toDateTimeString(),
        ])->save();
    }

    /**
     * 去除 $string 两边的空格、单引号和双引号
     */
    private function enhancedTrim(string $string): string
    {
        // 去除字符串两边的空格和引号
        $trimmed = trim($string, " \t\n\r\0\x0B'\"");

        // 使用正则表达式移除开头和结尾的多重引号（嵌套引号情况）
        $trimmed = preg_replace('/^(["\']+)(.*?)(\1)+$/', '$2', $trimmed);

        // 再次清理两边空格（确保清理完正则后的残余空格）
        return trim($trimmed);
    }

    /**
     * 获取 func 执行类型 的执行对象类
     */
    private function getFuncClass(string $classOrFunc = ''): array|string
    {
        // 判断 $task->execute_class_or_func 里面是否包含逗号
        // 判断 $task->execute_class_or_func 是否是 [ 开头且 ]结尾
        if (str_contains($classOrFunc, ',') && preg_match('/^\[(.*)\]$/', $classOrFunc, $matches)) {
            $class_or_func = $matches[1];
            // 使用,分割$class_or_func，第一个参数是类名，第二个参数是方法名
            [$class, $method] = explode(',', $class_or_func);
            // 去除 $class 和 $method 两边的空格、单引号和双引号
            $class = $this->enhancedTrim($class);
            $method = $this->enhancedTrim($method);

            return [App::make(trim($class)), trim($method)];
        } else {
            return $this->enhancedTrim($classOrFunc);
        }
    }

    /**
     * 验证执行对象是否正确
     *
     * @param  string  $type  执行类型 func、mo1del
     * @param  string  $modelOrFunc  执行对象
     * @param  string|int|null  $params  调用参数
     * @return array [(bool)是否正确,(string)错误信息]
     */
    public function checkModelOrFunc(string $type, string $modelOrFunc, string|int|null $params = ''): array
    {
        if (empty($modelOrFunc)) {
            return [false, '执行对象不能为空'];
        }
        // 执行对象是 多态模型
        if ($type == TaskCronTabs::TYPE_MODEL) {
            if (empty($params) || ! is_numeric($params)) {
                return [false, '模型参数错误'];
            }
            $model = App::make($modelOrFunc);
            if (! $model) {
                return [false, '调用模型不存在'];
            }
            if (! ($model instanceof ExecutableInterface)) {
                return [false, '调用模型不是「ExecutableInterface」的实现类'];
            }
            // (!method_exists($model, $params))
            if (empty($model::find($params))) {
                return [false, '调用模型参数错误'];
            }

            return [true, ''];
        }

        // 执行对象是 类/方法
        if ($type == TaskCronTabs::TYPE_FUNC) {
            if (! empty($params) && ! is_string($params)) {
                return [false, '调用类/方法参数错误'];
            }
            $call_class = $this->getFuncClass($modelOrFunc);
            if (is_array($call_class) && count($call_class) == 2) {
                // App 判断 $call_class[0] 类中的$call_class[1]方法是否存在
                if (! method_exists($call_class[0], $call_class[1])) {
                    return [false, '调用类/方法不存在'];
                }
            } else {
                if (! function_exists($call_class)) {
                    return [false, '调用函数不存在'];
                }
            }

            return [true, ''];
        }

        return [false, '未定义的执行类型'];
    }

    private function callHttpRequest(string $url, string $method = 'GET', array $headers = [], array $body = []): array
    {
        try {
            $curl = Curl::instance()->setParams($body, 'string');
            if ($headers) {
                $curl->setHeader($headers, false, true);
            }
            $curl->respObj(true);
            $res = match ($method) {
                'POST' => $curl->post($url),
                'PUT' => $curl->put($url),
                'DELETE' => $curl->delete($url),
                'PATCH' => $curl->patch($url),
                default => $curl->get($url),
            };
            $curl->respObj(false);

            return $res->getResp();
        } catch (Exception $exception) {
            // 不做任何事
            return [];
        }
    }
}
