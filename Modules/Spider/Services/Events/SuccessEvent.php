<?php

namespace Modules\Spider\Services\Events;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Modules\Blog\Models\Article;
use Modules\Spider\Contracts\SpiderArticleInterface;
use Modules\Spider\Contracts\SpiderRunEventInterface;
use Modules\Spider\Models\SpiderLink;
use Modules\Spider\Models\SpiderTask;
use Modules\Spider\Models\SpiderTasksLog;

// 爬虫采集成功时的事件
class SuccessEvent implements SpiderRunEventInterface
{
    // 采集成功后保存数据到哪些模型数据库表
    public static function modelMap(): array
    {
        return [
            'default' => [
                'class' => Article::class,  // 类
                'require_field' => [                // 要求必须存在且不为空的字段
                    'title',
                    'content',
                ],
            ],
            'article' => [
                'class' => Article::class,  // 类
                'require_field' => [                // 要求必须存在且不为空的字段
                    'title',
                    'content',
                ],
            ],
        ];
    }

    /**
     * 采集成功时的事件
     *
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function handle(SpiderTask $task, array $article = [], ?string $message = '')
    {
        if (empty($article) || ! self::isStringValueArray($article)) {
            return false;
        }
        $sourceUrl = ! empty($article['spider_url']) ? $article['spider_url'] : ''; // 当前采集的url，文章来源url
        unset($article['spider_url']); // 去除url
        $extend = $task->extend ?? [];
        $success = $task->success ?? [];

        // 设置了save 为空或者  modelMap 不存在的键表示不保存数据
        $saveModelKey = (empty($success) || ! isset($success['save'])) ? 'default' : $success['save'];
        if (empty($saveModelKey) || empty($modelMap = self::modelMap()[$saveModelKey]) || empty($modelClass = $modelMap['class'])) {
            // 未设置保存数据的模型
            self::restoreUrlLink($sourceUrl);

            return false;
        }
        if (empty($modelClass)) {
            self::restoreUrlLink($sourceUrl);

            return false;
        }
        // 判断 $article 是否存在必须字段 require_field
        $requireField = $modelMap['require_field'] ?? [];
        foreach ($requireField as $field) {
            if (empty($article[$field])) {
                self::restoreUrlLink($sourceUrl);

                // SpiderTasksLog::writeLog($task, '文章必须字段[' . $field . ']不存在或为空', $article, $sourceUrl, SpiderTasksLog::STATUS_FAIL);
                return false;
            }
        }

        $model = new $modelClass;
        if (! ($model instanceof Model) || ! ($model instanceof SpiderArticleInterface)) {
            // 未设置保存数据的模型
            self::restoreUrlLink($sourceUrl);
            SpiderTasksLog::writeLog($task, '文章模型类 未正确继承基类 Model 和 SpiderArticleInterface:'.$modelClass, $article, $sourceUrl, SpiderTasksLog::STATUS_FAIL);

            return false;
        }

        try {
            // 爬虫附加数据
            $article['user_id'] = 0;
            $article['type'] = $modelClass::getEditorTypeValue();
            $article['source_type'] = $modelClass::getSourceTypeSpiderValue();
            $article['source_url'] = $sourceUrl;
            $article['status'] = $modelClass::getStatusNormalValue();
            $article['classify_id'] = $extend['classify_id'] ?? 0;

            $model->fill($article);
            $model->save();

            return true;
        } catch (Exception $e) {
            self::restoreUrlLink($sourceUrl);
            SpiderTasksLog::writeLog($task, '文章模型['.$modelClass.']类写入数据失败:'.$e->getMessage(), $article, $sourceUrl, SpiderTasksLog::STATUS_FAIL);

            return false;
        }
    }

    /**
     * 检查是否为['字符串键名'=>'不是数组也不是对象格式类型的值']格式的数组
     *      eg:['name'=>'foo']:true
     *         ['name'=>['foo']]:false
     *         [['name','foo']]:false
     *         ['name'=>new stdClass()]:false
     */
    public static function isStringValueArray(array $array): bool
    {
        return ! array_is_list($array) && array_reduce($array, fn ($carry, $value) => $carry && is_scalar($value), true);
    }

    // 恢复url链接
    // 如果爬虫程序识别为采集成功，但是在处理的时候发现数据不符合要求，可以调用此方法恢复url链接，下次再次采集
    // 删除已经采集成功的url链接
    public static function restoreUrlLink(string $url = '')
    {
        if (! empty($url)) {
            SpiderLink::where('url', $url)->delete();
        }
    }
}
