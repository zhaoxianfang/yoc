<?php

namespace Modules\Task\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TaskCronTabsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'timer' => $this->timer,
            'name' => $this->name,
            'type' => $this->type,
            // modal
            'executable_id' => $this->executable_id,
            'executable_type' => $this->executable_type,
            // func
            'execute_class_or_func' => $this->execute_class_or_func,
            'class_or_func_params' => $this->class_or_func_params,
            // curl
            'curl_url' => $this->curl_url,
            'curl_params' => $this->curl_params,

            'run_status' => $this->run_status,
            'status' => $this->status,
            'cron_next_run_date' => $this->cron_next_run_date,
            'run_at' => Carbon::parse($this->run_at)->toDateTimeString(),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString(),
        ];
    }
}
