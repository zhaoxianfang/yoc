<?php

return [
    'disable' => env('CAPTCHA_DISABLE', false),
    'characters' => ['2', '3', '4', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'm', 'n', 'p', 'q', 'r', 't', 'u', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'X', 'Y', 'Z'],
    'default' => [
        'length' => 4,   // 验证码的长度
        'width' => 120, // 验证码图片的宽度
        'height' => 36,  // 验证码图片的高度
        'quality' => 90,  // 验证码图片的质量 默认90;数值范围为 0 到 100，数值越高图片越清晰，但体积也会更大。值越低则图像更模糊
        'math' => false, // 启用数学验证码
        'expire' => 120,  // 验证码有效期
        'encrypt' => true, // 启用加密

        'lines' => 3, // 验证码中的干扰线数量；添加干扰线可以防止 OCR（光学字符识别）技术识别，提高验证码的安全性
        'lineColor' => '#1ab394',
        'lineWidth' => 4,
        'angle' => 40, // 是否随机旋转验证码字符；指定角度的范围（例如 45 表示字符会在 -45 到 45 度之间随机旋转），增加识别难
        'contrast' => -5, // 提高字体和背景之间的对比度
        'sharpen' => 5,  // 是否锐化图像
    ],
    // 数学公式
    'math' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => true,
    ],
    // 平面样式的验证码配置（易报错）
    'flat' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'lines' => 4,
        'bgImage' => false,
        'bgColor' => '#ecf2f4',
        'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
        'contrast' => -5, // 提高字体和背景之间的对比度
    ],
    // 迷你
    'mini' => [
        'length' => 3,
        'width' => 60,
        'height' => 32,
    ],
    // 反转颜色
    'inverse' => [
        'length' => 4,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'sensitive' => true,
        'angle' => 12,
        'sharpen' => 10,
        'blur' => 2,
        'invert' => true,
        'contrast' => -5,
    ],
];
