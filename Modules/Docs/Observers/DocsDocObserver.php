<?php

namespace Modules\Docs\Observers;

use Exception;
use Modules\Docs\Models\DocsDoc;

/**
 * 在观察者中 返回 false ,那么操作就无法完成
 * Class DocsDocObserver
 */
class DocsDocObserver
{
    /**
     * 监听数据即将创建的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function creating(DocsDoc $doc)
    {
        $doc->title = mb_substr($doc->title, 0, 190, 'UTF-8');          // 限制标题长度
        $doc->content = mb_substr($doc->content, 0, 4294967000, 'UTF-8'); // 限制长度
        $doc->content_html = mb_substr($doc->content_html, 0, 4294967000, 'UTF-8'); // 限制长度
    }

    /**
     * 处理 User「created」事件
     *
     *
     * @return void
     */
    public function created(DocsDoc $doc) {}

    /**
     * 监听数据即将更新的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updating(DocsDoc $doc)
    {
        $doc->title = mb_substr($doc->title, 0, 190, 'UTF-8');                  // 限制标题长度
        $doc->content = mb_substr($doc->content, 0, 4294967000, 'UTF-8'); // 限制长度
        $doc->content_html = mb_substr($doc->content_html, 0, 4294967000, 'UTF-8'); // 限制长度
    }

    /**
     * 监听数据更新后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function updated(DocsDoc $doc) {}

    /**
     * 监听数据即将保存的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function saving(DocsDoc $doc)
    {
        $doc->title = mb_substr($doc->title, 0, 190, 'UTF-8');             // 限制标题长度
        $doc->content = mb_substr($doc->content, 0, 4294967000, 'UTF-8'); // 限制长度
        $doc->content_html = mb_substr($doc->content_html, 0, 4294967000, 'UTF-8'); // 限制长度
    }

    /**
     * 监听数据保存后的事件。
     *
     *
     * @return void
     *
     * @throws Exception
     */
    public function saved(DocsDoc $doc) {}

    /**
     * 监听数据即将删除的事件。
     *
     *
     * @return void
     */
    public function deleting(DocsDoc $doc)
    {
        // return false;
    }

    /**
     * 监听数据删除后的事件。
     *
     *
     * @return void
     */
    public function deleted(DocsDoc $doc)
    {
        // 删除和用户相关的数据和表
    }
}
