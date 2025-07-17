<?php

namespace Modules\Spider\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Spider\Models\SpiderTask;

class SpiderTaskListResource extends JsonResource
{
    /**
     * 将资源转换为数组。
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timer' => $this->timer,
            'name' => $this->name,
            'url' => $this->url,
            'type' => $this->type,
            'url_can_repeated' => $this->url_can_repeated, // 采集url是否可以重复采集;1能，0不能; 例如有些站点主页或文章列表页的url是固定的就可以重复采集,文章详情页地址是唯一的就不能重复采集
            'rules' => $this->rules,
            'next_tasks_id' => $this->next_tasks_id, // 此任务完成后 需要紧密跟随的下一步采集任务,例如采集到文章列表后，需要立即进入到文章正文页面进行内容采集
            'next_tasks' => $this->whenLoaded('nextTasks', function () {
                return new static($this->nextTasks);
            }),
            'sub_tasks' => $this->sub_tasks, // 是否子任务;1是0否;子任务由主任务来调度，一般不直接运行子任务
            'sub_tasks_text' => SpiderTask::$subTasksMaps[$this->sub_tasks], // 是否子任务;1是0否;子任务由主任务来调度，一般不直接运行子任务
            'domain_prefix' => $this->domain_prefix,  // 域名前缀；有些站点url不是完整url,需要拼接上域名前缀路径
            'extend' => $this->extend, // 执行爬虫的扩展
            'before' => $this->before, // 采集前需要做的事
            'after' => $this->after, // 采集后需要做的事
            'fail' => $this->fail, // 采集失败需要做的事
            'success' => $this->success, // 采集成功需要做的事
            'run_status' => $this->run_status,  // 采集状态；0未执行,1成功，2失败
            'status' => $this->status,  // 任务状态；1正常，2关闭
            'status_text' => SpiderTask::$statusMaps[$this->status],
            'run_at' => (string) $this->run_at, // 最近一次采集时间
            // 'created_at' => Carbon::parse($this->created_at ?? '')->format('Y-m-d') ?? '',
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'next_run_date' => empty($this->sub_tasks) ? $this->next_run_date : '', // 下次执行时间,子任务不显示
        ];
    }
}
