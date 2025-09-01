<?php

namespace Modules\Task\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Task\Http\Resources\TaskCronTabsResource;
use Modules\Task\Models\TaskCronTabs;
use Modules\Task\Services\CronTaskService;

class CronTaskController extends AdminBaseController
{
    /**
     * 任务列表
     */
    public function index(Request $request)
    {
        if (! is_ajax()) {
            return view('task::admin/cron/index');
        }
        $query = TaskCronTabs::query();

        $count = $query->count();

        $list = $query
            ->offset($req['offset'] ?? 0)
            ->limit($req['limit'] ?? 10)
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc')
            ->get();

        $res = TaskCronTabsResource::collection($list)->toArray($request);

        return $this->dataTables($res, $count);
    }

    /**
     * 创建任务
     *
     * @return Renderable
     */
    public function create()
    {
        return view('task::admin/cron/add', []);
    }

    /**
     * 提交创建任务
     *
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request, CronTaskService $service)
    {
        $task = new TaskCronTabs;

        return $this->handleSubmit($request, $task, $service);
    }

    private function handleSubmit(Request $request, TaskCronTabs $task, CronTaskService $service)
    {
        $reqData = $request->validate([
            'row.name' => 'required|min:2|max:50',
            'row.timer' => 'required',
            'row.type' => 'required|in:func,model,curl',

            // func
            'row.execute_class_or_func' => 'required_if:row.type,func',
            // 'row.class_or_func_params' => 'required_if:row.type,func',
            'row.class_or_func_params' => 'sometimes|exclude_unless:row.type,func|nullable|string', // 存在请求字段时才验证[只有row.type 等于 func 时验证]

            // model
            'row.executable_type' => 'required_if:row.type,model',
            'row.executable_id' => 'required_if:row.type,model',

            // curl
            'row.curl_method' => 'required_if:row.type,curl',
            'row.curl_url' => 'required_if:row.type,curl',
            'row.curl_headers' => 'sometimes|exclude_unless:row.type,curl|nullable|string',
            'row.curl_body' => 'sometimes|exclude_unless:row.type,curl|nullable|string',

            'row.status' => 'required',
        ], [
            'row.name.required' => '请输入任务名称',
            'row.name.min' => '任务名称最低长度为2位',
            'row.name.max' => '任务名称最多长度为50位',

            'row.timer.required' => '请按规则选择cron时间',

            'row.type.required' => '请选择执行类型',
            'row.type.in' => '执行类型参数无效',

            // func
            'row.execute_class_or_func.required_if' => '请填写 类/方法 路径',
            // 'row.class_or_func_params.required_if' => '请填写 类/方法的参数',

            // model
            'row.executable_type.required_if' => '请填写数据库模型路径',
            'row.executable_id.required_if' => '请填写数据库模型参数',

            // curl
            'row.curl_method.required_if' => '请选择 请求方法',
            'row.curl_url.required_if' => '请填写 请求地址',
            'row.curl_headers.exclude_unless' => '请填写 请求头',
            'row.curl_body.exclude_unless' => '请填写 请求体',

            'row.status.required' => '请勾选任务状态',
        ]);
        $data = $reqData['row'];

        if (trim($data['timer']) == '* * * * *') {
            return $this->error('cron时间 不能为 * * * * *');
        }

        $checkObject = '';
        $callParams = '';

        // 执行对象是 数据库模型
        if ($data['type'] == TaskCronTabs::TYPE_MODEL) {
            $checkObject = $data['executable_type'];
            $callParams = $data['executable_id'];
        }

        // 执行对象是 类/方法
        if ($data['type'] == TaskCronTabs::TYPE_FUNC) {
            $checkObject = $data['execute_class_or_func'];
            $callParams = $data['class_or_func_params'];
        }

        // 执行对象是 HTTP 请求
        if ($data['type'] == TaskCronTabs::TYPE_CURL) {
            $checkObject = $data['curl_url'];
            try {
                $data['curl_headers'] = ! empty($data['curl_headers']) ? json_encode(json_decode($data['curl_headers'])) : '';
                $data['curl_body'] = ! empty($data['curl_body']) ? json_encode(json_decode($data['curl_body'])) : '';
            } catch (\Exception $e) {
                return $this->error('请求参数错误');
            }

            $callParams = [
                'method' => $data['curl_method'],
                'headers' => $data['curl_headers'],
                'body' => $data['curl_body'],
            ];
            // $data['curl_url']    = $data['curl_url'];
            $data['curl_params'] = $callParams;
            unset($data['curl_headers'], $data['curl_body'], $data['curl_method']);
        }
        // 使用App 验证方法、模型 以及参数 是否存在
        if (in_array($data['type'], [TaskCronTabs::TYPE_FUNC, TaskCronTabs::TYPE_MODEL])) {
            [$isPass, $errInfo] = $service->checkModelOrFunc($data['type'], $checkObject, $callParams);
            if (! $isPass) {
                return $this->error($errInfo);
            }
        }

        $task->fill($data);
        if ($task->save()) {
            return $this->success('操作成功', route('admin.task.cron.list'));
        }

        return $this->error('操作失败');
    }

    /**
     * 查看任务
     *
     *
     * @return Renderable
     */
    public function show(TaskCronTabs $task)
    {
        return view('task::show');
    }

    /**
     * 修改任务
     *
     *
     * @return Renderable
     */
    public function edit(TaskCronTabs $task)
    {
        return view('task::admin/cron/edit', ['task' => $task]);
    }

    /**
     * 提交修改任务
     *
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, TaskCronTabs $task, CronTaskService $service)
    {
        return $this->handleSubmit($request, $task, $service);
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(TaskCronTabs $task)
    {
        $task->delete();

        return $this->success('任务删除成功', route('admin.task.cron.list'));
    }

    // cron 配置帮助
    public function cronHelp()
    {
        return view('task::admin/cron/cron_help');
    }
}
