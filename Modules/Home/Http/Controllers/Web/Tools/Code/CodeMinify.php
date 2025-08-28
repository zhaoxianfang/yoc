<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Code;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;
use zxf\Min;

/**
 * 代码压缩器
 */
class CodeMinify extends HomeBaseController
{
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home::tools.code.minify', []);
        }

        $codeString = $request->input('code_string');
        $codeType = $request->input('type', 'js');
        if (empty($codeString)) {
            return $this->error([
                'code' => 412,
                'message' => '请填入需要压缩的代码',
            ]);
        }
        if ($codeType == 'js') {
            $minifier = new Min\JS($codeString);
        } elseif ($codeType == 'css') {
            $minifier = new Min\CSS($codeString);
        } else {
            if (
                preg_match('/function|if|else|for|while/', $codeString)
                || preg_match('/\/\//', $codeString)
                || (stripos($codeString, ' var ') !== false
                    || stripos($codeString, ' let ') !== false
                    || stripos($codeString, ' const ') !== false
                )
            ) {
                // 包含 JavaScript 的逻辑和功能 || 的注释（// 或 /*） || 使用 var、let、const定义变量
                $minifier = new Min\JS($codeString);
            } else {
                $minifier = new Min\CSS($codeString);
            }
        }

        $minifiedCode = $minifier->minify();
        $minifiedCode = preg_replace('/[\r\n]+/', '', $minifiedCode); // 去除换行符

        $oldLen = mb_strlen($codeString);
        $newLen = mb_strlen($minifiedCode);
        $minifyRatio = bcmul(bcdiv(bcsub($oldLen, $newLen, 4), $oldLen, 4), 100, 2).'%';

        return $this->success([
            'min_str' => $minifiedCode,
            'old_len' => byteFormat($oldLen),
            'new_len' => byteFormat($newLen),
            'minify_ratio' => $minifyRatio,
            'message' => '转换成功',
        ]);
    }
}
