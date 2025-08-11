<?php

namespace Modules\Article\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Article\Models\ArticleClassifies;

class ArticleClassifyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'admin_id' => $this->user_id,
            'admin_name' => $this->whenLoaded('admin', function () {
                return $this->admin->nickname;
            }),
            'pid' => $this->pid,
            'parent_name' => $this->whenLoaded('parent', function () {
                return $this->parent->name;
            }),
            'name' => $this->name,
            'sort' => $this->sort,
            'type' => $this->type,
            'type_text' => ArticleClassifies::$typeMaps[$this->type],
            'show_nav' => $this->show_nav,
            'show_nav_text' => ArticleClassifies::$showNavMaps[$this->show_nav],
            'status' => $this->status,  // 状态；0：待审，1：正常，2:不公开，3:敏感待审核
            'status_text' => ArticleClassifies::$statusMaps[$this->status],
            'created_at' => Carbon::parse($this->pivot->created_at ?? '')->format('Y-m-d H:i:s') ?? '',
            'updated_at' => Carbon::parse($this->pivot->updated_at ?? '')->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
