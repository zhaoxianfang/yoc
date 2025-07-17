<?php

namespace Modules\Spider\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\AdminBaseController;
use Modules\Blog\Models\ArticleClassifies;
use Modules\Spider\Http\Resources\SpiderTaskListResource;
use Modules\Spider\Models\SpiderTask;
use Modules\Spider\Services\SpiderHandleService;

class SpiderController extends AdminBaseController
{
    /**
     * 爬虫列表
     */
    public function index(Request $request)
    {
        if (! $request->ajax()) {
            return view('spider::admin/index');
        }
        $req = $request->input();

        $query = SpiderTask::query()
            ->with(['nextTasks'])
            ->when(! empty($req['id']), function ($query) use ($req) {
                $query->where('id', $req['id']);
            })
            ->when(! empty($req['name']), function ($query) use ($req) {
                $query->where('name', 'like', '%'.$req['name'].'%');
            })
            ->when(! empty($req['type']), function ($query) use ($req) {
                $query->where('type', $req['type']);
            })
            ->when(isset($req['sub_tasks']), function ($query) use ($req) {
                $query->where('sub_tasks', $req['sub_tasks']);
            })
            ->when(isset($req['status']), function ($query) use ($req) {
                $query->where('status', $req['status']);
            })
            ->when(! empty($req['run_at']), function ($query) use ($req) {
                $run_at = explode('~', $req['run_at']);
                $query->whereBetween('run_at', $run_at);
            })
            ->when(! empty($req['created_at']), function ($query) use ($req) {
                $created_at = explode('~', $req['created_at']);
                $query->whereBetween('created_at', $created_at);
            });
        $count = $query->count();

        $list = $query
            ->offset($req['offset'] ?? 0)
            ->limit($req['limit'] ?? 10)
            ->orderBy($req['sort'] ?? 'id', $req['order'] ?? 'desc')
            ->get();

        // return $this->dataTables($list, $count);

        // $res = SpiderTaskListResource::collection($query->paginate($req['limit'] ?? 10));
        $res = SpiderTaskListResource::collection($list)->toArray($request);

        return $this->dataTables($res, $count);
        //  return $this->json(['rows' => [], 'total' => SpiderTask::count()]);
    }

    public function create()
    {
        $subTaskList = SpiderTask::query()->where(['sub_tasks' => SpiderTask::SUB_TASKS_YES])->get();
        $classify = ArticleClassifies::query()->where('status', ArticleClassifies::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classify)->setWeigh()->setTitle('name')->getTree();

        return view('spider::admin/add', [
            'classify_list' => $classifyList,
            'sub_tasks' => $subTaskList,
        ]);
    }

    public function store(Request $request)
    {
        $this->gate::authorize('create', SpiderTask::class);
        $task = new SpiderTask;

        return $this->saveTask($task, $request);
    }

    public function edit(SpiderTask $task)
    {
        $subTaskList = SpiderTask::query()->where(['sub_tasks' => SpiderTask::SUB_TASKS_YES])->get();
        $classify = ArticleClassifies::query()->where('status', ArticleClassifies::STATUS_NORMAL)->get()->toArray();
        // 转换为Tree
        $classifyList = \zxf\Extend\Menu::instance()->init($classify)->setWeigh()->setTitle('name')->getTree();

        return view('spider::admin/edit', [
            'classify_list' => $classifyList,
            'sub_tasks' => $subTaskList,
            'info' => $task,
        ]);
    }

    public function update(SpiderTask $task, Request $request)
    {
        $this->gate::authorize('update', $task);

        return $this->saveTask($task, $request);
    }

    public function saveTask(SpiderTask $task, Request $request)
    {
        $row = $request->input('row', []);
        $rules = $request->input('rules', []);

        if (empty($row['name']) || empty($rules)) {
            return $this->error('表单未填写完整');
        }
        if (empty($titleList = $rules['title']) || empty($xpathList = $rules['xpath']) || empty($fieldHandle = $rules['field_handle'])) {
            return $this->error('采集规则参数错误');
        }

        if ((empty($row['type']) || empty(SpiderTask::$typeMaps[$row['type']])) || ($row['type'] == SpiderTask::TYPE_LIST && empty($row['url']))) {
            return $this->error('未填写采集url或者未选择采集类型');
        }
        if ($row['type'] == SpiderTask::TYPE_LIST && in_array($row['timer'], ['* * * * *', ''])) {
            return $this->error('此cron时间表达式错误或不能和列表页采集同时使用');
        }

        $ruleList = [];
        $currentHandle = 0; // 当前字段处理方式 0:保留原格式 1:清洗html 2:取出时间字符 3:纯text文字 4:正则原格式 5:正则text
        $currentTitle = '';
        foreach ($titleList as $key => $title) {
            if (empty($xpathList[$key])) {
                return $this->error('xpath|css选择器|正则表达式 不能为空');
            }
            $currentHandle = (empty($title) || $title == $currentTitle) ? $currentHandle : (empty($fieldHandle[$key]) ? 0 : $fieldHandle[$key]);
            $currentTitle = (empty($title) || $title == $currentTitle) ? $currentTitle : $title;
            $ruleList[$currentTitle]['route'][] = $xpathList[$key];
            $ruleList[$currentTitle]['field_handle'] = (int) $currentHandle;

            if (in_array($currentHandle, [4, 5])) {
                if (
                    empty($extend = $rules['extend_rule'])
                    || empty($extend['first'])
                    || empty($regExpOne = $extend['first'][$key])
                ) {
                    return $this->error('正则表达式配置不能为空');
                }
                $ruleList[$currentTitle]['extend']['first'] = $regExpOne;
                $ruleList[$currentTitle]['extend']['href'] = empty($extend['href'][$key]) ? '' : $extend['href'][$key];
            } else {
                $ruleList[$currentTitle]['extend'] = [];
            }
        }

        $row['timer'] = trim(empty($row['timer']) ? '* * * * *' : $row['timer']);
        $row['url'] = trim(empty($row['url']) ? '' : $row['url']); // 采集目标站点url链接
        $row['url_can_repeated'] = empty($row['url_can_repeated']) ? 0 : 1; // 是否可以重复采集
        $row['rules'] = (array) $ruleList; // 采集规则
        $row['next_tasks_id'] = empty($row['next_tasks_id']) ? 0 : (int) $row['next_tasks_id']; // 下一步采集任务id
        $row['sub_tasks'] = empty($row['sub_tasks']) ? 0 : 1; // 是否子任务;1是0否
        $row['domain_prefix'] = empty($row['domain_prefix']) ? '' : ((string) rtrim($row['domain_prefix'], '/').'/'); // 域名前缀；有些站点url不是完整url,需要拼接上域名前缀路径
        $row['extend'] = ! empty($row['extend']) ? $row['extend'] : []; // 执行爬虫的扩展
        $row['before'] = ! empty($row['before']) ? $row['before'] : []; // 采集前需要做的事
        $row['after'] = ! empty($row['after']) ? $row['after'] : []; // 采集后需要做的事
        $row['fail'] = ! empty($row['fail']) ? $row['fail'] : []; // 采集出错时触发的事件
        $row['success'] = ! empty($row['success']) ? $row['success'] : []; // 采集完成后可触发的事件

        if (empty($row['timer']) || (trim($row['timer']) == '* * * * *') || $row['type'] != SpiderTask::TYPE_LIST) {
            $row['sub_tasks'] = SpiderTask::SUB_TASKS_YES;
        }

        $task->fill($row)->save();

        return $this->success([], route('admin.spider.list'));
    }

    public function destroy(SpiderTask $task)
    {
        $this->gate::authorize('destroy', $task);

        $task->delete();

        return $this->success([], route('admin.spider.list'));
    }

    // 爬虫规则测试
    public function ruleTest(Request $request)
    {
        if (! $request->ajax()) {
            // $tasks = SpiderTask::query()->where('status', SpiderTask::STATUS_NORMAL)->get();
            $tasks = SpiderTask::query()->get();

            return view('spider::admin/rule_test', [
                'tasks' => $tasks,
            ]);
        }
        $row = $request->input('row', []);
        $rule = $row['rule'] ?? '';
        $url = $row['url'] ?? '';
        $taskId = $row['task_id'] ?? '';

        $task = '';
        if (! empty($taskId)) {
            $task = SpiderTask::query()->where('id', $taskId)->first();
        }

        if (empty($task)) {
            if (empty($rule) || empty($url)) {
                return $this->error('参数错误');
            }
        } else {
            $rule = '';
            if ($task->type == SpiderTask::TYPE_LIST) {
                $url = '';
            } else {
                if (empty($url) && empty($task->url)) {
                    return $this->error('请填写采集地址URL');
                }
            }
        }
        // 没有设置 或者值为debug，都表示是调试模式
        $isDebug = ! isset($row['is_debug']) || $row['is_debug'] == 'debug'; // 是否为调试模式(true:调试模式，false,补采模式)：调试模式下，不会存储数据，只会返回采集到的数据；补采模式下，会存储数据,补采模式仅在$task为不为空时候有效
        if (empty($task)) {
            $isDebug = true;
        }
        $type = (empty($row['type']) || $row['type'] == '1') ? SpiderTask::TYPE_CONTENT : SpiderTask::TYPE_LIST;

        $service = new SpiderHandleService;
        $resData = (array) $service->test($task, $url, $rule, (bool) $isDebug, $type);
        $resData['message'] = empty($resData['list']) ? '未采集到任何数据' : '采集结束';
        $resData['data']['list'] = $resData['list'] ?? [];
        $resData['data']['list_links'] = $resData['list_links'] ?? [];
        unset($resData['list'], $resData['list_links']);

        return $this->success($resData);
    }
}
