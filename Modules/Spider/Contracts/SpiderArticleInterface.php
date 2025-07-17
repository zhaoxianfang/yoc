<?php

namespace Modules\Spider\Contracts;

/**
 * 用于 爬虫 SpiderTask 保存的文章模型 应该具备以下方法
 */
interface SpiderArticleInterface
{
    /**
     * 富文本文章类型值 1：富文本，2：Markdown
     */
    public static function getEditorTypeValue(): int;

    /**
     * 爬虫来源类型值 1：用户发布，2:爬虫采集
     */
    public static function getSourceTypeSpiderValue(): int;

    /**
     * 文章状态为正常的值 0：待审，1：正常，2:不公开，3:敏感待审核
     */
    public static function getStatusNormalValue(): int;
}
