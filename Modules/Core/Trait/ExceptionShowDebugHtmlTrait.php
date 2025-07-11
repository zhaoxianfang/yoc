<?php

namespace Modules\Core\Trait;

/**
 * 调试输出html异常信息 Trait
 */
trait ExceptionShowDebugHtmlTrait
{
    public function outputDebugHtml($list = [])
    {
        $content = '';
        foreach ($list as $key => $value) {
            if ($key == 'code_string') {
                $content .= '<li class="info-item">
                    <span class="info-label">代码片段：</span>
                    <div class="info-value"><pre><code>'.$value.'</code></pre></div>
                </li>';
            } else {
                $content .= '<li class="info-item">
                    <span class="info-label">'.$key.'：</span>
                    <div class="info-value">'.$value.'</div>
                </li>';
            }
        }
        $sysName = config('app.name', '威四方');
        $copyright = '&copy; '.date('Y').' '.$sysName.' ('.config('app.url', 'https://weisifang.com').') 版权所有.';
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统异常/调试|{$sysName}</title>
    <style>
        /* 基础样式重置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            /*background: #fff;*/
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .info-list {
            list-style: none;
        }

        .info-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            align-items: flex-start;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #3498db;
            min-width: 120px;
            padding-right: 20px;
        }

        .info-value {
            flex: 1;
            word-break: break-word;
            color: #fff;
        }

        .info-value pre {
            background: #f8f8f8;
            color: #000;
            border-radius: 4px;
            padding: 12px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            border-left: 3px solid #3498db;
            margin: 5px 0;
            tab-size: 4;
            background-color: #fff;
        }

        /* 响应式设计 */
        @media (max-width: 600px) {
            .info-item {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 5px;
            }
        }
        /* 页脚样式 */
        footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统错误/调试</h1>

        <ul class="info-list">
            {$content}
        </ul>
    </div>
    <footer>
        {$copyright}
    </footer>
</body>
</html>
HTML;

        return response($html, 500)->header('Content-Type', 'text/html')->send();
    }
}
