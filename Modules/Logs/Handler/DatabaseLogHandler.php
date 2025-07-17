<?php

namespace Modules\Logs\Handler;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\System\Constants\ExceptParams;
use Monolog\Handler\AbstractProcessingHandler;
use Throwable;

/**
 * 自定义数据库日志处理器
 *
 * 继承 Monolog 的 AbstractProcessingHandler 抽象类
 * 实现将日志记录写入数据库的功能
 */
class DatabaseLogHandler extends AbstractProcessingHandler
{
    /**
     * @var string 数据库连接名称
     */
    protected string $connection;

    /**
     * @var string 日志表名
     */
    protected $table;

    /**
     * @var int 批量插入的日志条数阈值;每积累 n 条日志触发一次批量写入
     */
    protected mixed $batchSize;

    /**
     * @var int 最大缓冲限制 (防止内存溢出): 当缓冲数据达到约 ?kB 时会自动触发写入
     */
    protected mixed $bufferLimit;

    /**
     * @var array 日志缓冲池
     */
    protected array $buffer = [];

    /**
     * @var int 当前缓冲大小 (字节)
     */
    protected int $bufferSize = 0;

    /**
     * 构造函数
     */
    public function __construct($level, bool $bubble, string $connection, string $table, int $batchSize = 1, int $bufferLimit = 1024 * 1024)
    {
        parent::__construct($level, $bubble);

        $this->connection = $connection;
        $this->table = $table;
        $this->batchSize = max(1, $batchSize);
        $this->bufferLimit = max(1024, $bufferLimit); // 最小1KB

        // 注册析构函数确保缓冲日志被刷新
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * 写入日志记录
     */
    protected function write(\Monolog\LogRecord $record): void
    {
        try {
            // 预处理日志记录
            $processedRecord = $this->processRecordData($record);
            // 是否是 js、css、图片等资源文件
            if (is_resource_file($processedRecord['url'])) {
                return;
            }

            // 序列化记录计算大小
            $recordSize = strlen(serialize($processedRecord));

            // 检查缓冲限制
            if ($this->bufferSize + $recordSize > $this->bufferLimit) {
                $this->flush();
            }

            // 添加到缓冲池
            $this->buffer[] = $processedRecord;
            $this->bufferSize += $recordSize;

            // 检查是否达到批量大小
            if (count($this->buffer) >= $this->batchSize) {
                $this->flush();
            }
        } catch (Throwable $e) {
            // 记录处理器自身的错误
            $this->handleHandlerException($e, $record);
        }
    }

    /**
     * 处理日志记录
     */
    protected function processRecordData(\Monolog\LogRecord $record): array
    {
        $extra = $this->formatContext($record['extra']);

        // 获取 request() 中有价值的header 头信息中几个重要的参数

        // 删除密码等排序字段
        $params = $this->formatContext(collect(request()->except(ExceptParams::$list))->toArray());
        $headers = ! empty($bearerToken = request()->bearerToken()) ? $this->formatContext([
            'Authorization' => $bearerToken,
        ]) : [];

        // 按照字段映射转换
        $data = [
            'user_id' => $extra['user_id'],
            'module_name' => $extra['module_name'],
            'title' => $record['message'],
            'method' => $extra['http_method'],
            'url' => $extra['url'],
            'content' => $this->toString($record['context']),
            'source_ip' => $extra['ip'],
            'is_crawler' => $extra['is_crawler'],
            'user_agent' => $extra['user_agent'],
            'level' => strtolower($record['level_name']),
            'params' => $this->toString(['params' => $params, 'headers' => $headers]),
            'created_at' => $record['datetime']->format('Y-m-d H:i:s'),
            'updated_at' => $record['datetime']->format('Y-m-d H:i:s'),
        ];

        unset(
            $extra['file'],
            $extra['line'],
            $extra['class'],
            $extra['callType'],
            $extra['function'],
            $extra['user_agent'],
        );

        $data['extra'] = $this->toString($extra);

        return $data;
    }

    /**
     * 检查数据是否可以被 json_encode
     */
    public static function isJsonUtf8(mixed $data): bool
    {
        try {
            if (empty($data)) {
                return true;
            }
            if (is_array($data)) {
                $data = json_encode($data);
                // 检查 json_encode 是否返回了 false
                if ($data === false) {
                    return false; // 不能编码为 JSON
                }
            }

            // 检查编码是否为 UTF-8
            return mb_check_encoding($data, 'UTF-8');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 格式化和处理特殊数据
     */
    protected function formatContext(array $data): array
    {
        // 过滤掉不可JSON序列化的资源
        return array_map(function ($item) {
            if (is_resource($item)) {
                return '[resource]';
            }
            if (! self::isJsonUtf8($item)) {
                return '[not UTF-8]';
            }
            if (is_string($item)) {
                return strlen($item) > 200 ? mb_substr($item, 0, 200).'...' : $item;
            }

            return $item;
        }, $data);
    }

    /**
     * 格式化额外数据
     */
    protected function toString(mixed $data): string
    {
        if (! is_object($data) && ! is_array($data)) {
            return $data;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * 刷新缓冲池
     */
    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $attempts = 0;
        $maxAttempts = 3;
        $success = false;

        // 重试机制
        while ($attempts < $maxAttempts && ! $success) {
            try {
                DB::connection($this->connection)
                    ->table($this->table)
                    ->insert($this->buffer);

                $success = true;
            } catch (Throwable $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    $this->handleInsertFailure($e, $this->buffer);
                } else {
                    usleep(100000 * $attempts); // 指数退避
                }
            }
        }

        // 重置缓冲
        $this->buffer = [];
        $this->bufferSize = 0;
    }

    /**
     * 处理插入失败
     */
    protected function handleInsertFailure(Throwable $e, array $failedRecords): void
    {
        // 1. 记录错误到默认日志
        Log::channel('stack')->error('数据库日志写入失败: '.$e->getMessage(), [
            'exception' => $e,
            'failed_count' => count($failedRecords),
        ]);
    }

    /**
     * 处理处理器异常
     */
    protected function handleHandlerException(Throwable $e, array|\Monolog\LogRecord $originalRecord): void
    {
        Log::channel('stack')->error('日志处理器异常: '.$e->getMessage(), [
            // 'original_record' => is_array($originalRecord) ? $originalRecord : $originalRecord->toArray(),
            'message:' => $e->getMessage(),   // 返回用户自定义的异常信息
            'code:' => $e->getCode(),      // 返回用户自定义的异常代码
            'file:' => $e->getFile(),      // 返回发生异常的PHP程序文件名
            'line:' => $e->getLine(),        // 返回发生异常的代码所在行的行号
            // "trace:"     => $err->getTrace(),      //返回发生异常的传递路线
            // "传递路线String" => $err->getTraceAsString(),//返回发生异常的传递路线
        ]);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        try {
            $this->flush();
        } catch (Throwable $e) {
            $this->handleHandlerException($e, []);
        }
    }
}
