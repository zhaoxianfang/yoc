<?php

namespace Modules\Article\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Article\Models\Article;

class ArticleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'classify_id' => $this->classify_id,
            'classify' => $this->whenLoaded('classify', function () {
                return new ArticleClassifyResource($this->classify);
            }),
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'author' => $this->author,
            'publish_time' => $this->publish_time, // 发布时间 该字段主要给「爬虫」使用
            'sort' => $this->sort,
            'type' => $this->type, // 文章内容类型；1：富文本，2：Markdown
            'type_text' => Article::$typeMaps[$this->type],
            'read' => $this->read,
            'like' => $this->like,
            'spider' => $this->spider,
            'source_type' => $this->source_type,
            'source_type_text' => Article::$sourceTypeMaps[$this->source_type],
            'source_url' => $this->source_url,
            'status' => $this->status,  // 状态；0：待审，1：正常，2:不公开，3:敏感待审核
            'status_text' => Article::$statusMaps[$this->status] ?? '未定义',
            'created_at' => Carbon::parse($this->created_at ?? '')->format('Y-m-d H:i:s') ?? '',
            'updated_at' => Carbon::parse($this->updated_at ?? '')->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
