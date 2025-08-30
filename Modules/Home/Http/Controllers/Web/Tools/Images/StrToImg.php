<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Images;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;
use zxf\Tools\TextToImg;

class StrToImg extends HomeBaseController
{
    /**
     * 文字生成图片
     */
    public function index(Request $request)
    {
        return view('home::tools.images.str_to_img', []);
    }

    // demo : /tools/text2png/ApiDoc2.0上线啦/1000/100/FFFFFF/7B00FF/0/lishu.html
    // 字符串生成图片
    public function create(string $text = '文字生成图片ABCdef.', ?string $width = '400', ?string $height = '300', ?string $color = 'FFFFFF', ?string $bgcolor = '0000FF', ?string $rotate = '0', ?string $font = 'lishu')
    {
        //        $paramStr = $text ? $text : '文字生成图片ABC/400/300/FFFFFF/0000FF/0/lishu/1';
        //        // 把 .html 替换为空
        //        $paramStr = str_replace('.html', '', $paramStr);
        //        $param = explode('/', $paramStr);
        //
        //        // 文字，宽度，高度，颜色，背景色，文字旋转角度
        //        $text = ! empty($param['0']) ? $param['0'] : 'hello';
        //        $width = ! empty($param['1']) ? $param['1'] : '500';
        //        $height = ! empty($param['2']) ? $param['2'] : '350';
        //        $color = ! empty($param['3']) ? $param['3'] : 'FFFFFF';
        //        $bgcolor = ! empty($param['4']) ? $param['4'] : '0000FF';
        //        $rotate = ! empty($param['5']) ? $param['5'] : '0';
        //        $font = ! empty($param['6']) ? $param['6'] : 'lishu';

        // 隶书字体 lishu
        $color = strtr($color, '#', '');
        $bgcolor = strtr($bgcolor, '#', '');

        $font = str_replace('.html', '', $font);

        TextToImg::instance($width, $height)->setFontStyle($font)->setText($text)->setColor($color)->setBgColor($bgcolor)->setAngle($rotate)->render();
        exit;
    }
}
