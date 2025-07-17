<?php

namespace Modules\App\Services;

use InvalidArgumentException;

/**
 * App 端 旋转图片验证码
 */
class AppImageAuthServices
{
    private int $offset = 5; // 允许误差5度

    /**
     * 获取旋转图片验证码的图片
     */
    public function getImg()
    {
        // 生成一个0~360° 范围的随机整数
        $angle = mt_rand(15, 345);

        // 定义图片路径
        $imgPath = public_path('static/images/wsf.jpg');

        // 检查文件是否存在
        if (! file_exists($imgPath)) {
            abort(404, '图片不存在');
        }
        i_session(['app_rotate_verify_value' => $angle]);

        return $this->generateRotateImage($imgPath, $angle);
    }

    /**
     * 验证结果
     *
     *
     * @return bool
     */
    public function check(int $angle = 0, bool $destroySession = false)
    {
        $rotate = i_session('app_rotate_verify_value');

        if (empty($rotate) || empty($angle)) {
            return false;
        }
        if ($destroySession) {
            i_session(['app_rotate_verify_value' => '']);
        }

        // 允许误差$this->offset度
        return abs($angle - $rotate) <= $this->offset;
    }

    /**
     * 生成旋转后的正方形图片并直接输出到浏览器。
     *
     * @param  string  $sourcePath  图片文件路径
     * @param  int  $angle  旋转角度 (0-360)
     *
     * @throws InvalidArgumentException 图片加载或类型不支持时抛出异常
     */
    private function generateRotateImage(string $sourcePath = '', int $angle = 30)
    {
        if ($angle < 0 || $angle >= 360) {
            throw new InvalidArgumentException('旋转角度必须在 0 到 360 之间');
        }

        // 加载图片资源
        $imageInfo = getimagesize($sourcePath);
        if (! $imageInfo) {
            throw new InvalidArgumentException("无法加载图片: $sourcePath");
        }

        // 根据图片类型加载
        $srcImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            default => throw new InvalidArgumentException('不支持的图片类型'),
        };

        if (! $srcImage) {
            throw new InvalidArgumentException('图片加载失败');
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // 获取左上角背景色
        $topLeftColor = imagecolorsforindex($srcImage, imagecolorat($srcImage, 3, 3));
        $bgColor = imagecolorallocatealpha($srcImage, $topLeftColor['red'], $topLeftColor['green'], $topLeftColor['blue'], 127);

        // 旋转图像
        $rotatedImage = imagerotate($srcImage, $angle, $bgColor);
        imagedestroy($srcImage); // 释放原图资源

        // 获取旋转后图像的尺寸
        $rotatedWidth = imagesx($rotatedImage);
        $rotatedHeight = imagesy($rotatedImage);

        // 计算裁剪区域
        $cropX = intval(($rotatedWidth - $originalWidth) / 2);
        $cropY = intval(($rotatedHeight - $originalHeight) / 2);

        // 创建最终图像资源
        $finalImage = imagecreatetruecolor($originalWidth, $originalHeight);
        imagesavealpha($finalImage, true);

        // 填充背景色
        $backgroundColor = imagecolorallocate($finalImage, $topLeftColor['red'], $topLeftColor['green'], $topLeftColor['blue']);
        imagefill($finalImage, 0, 0, $backgroundColor);

        // 将旋转图像裁剪并复制到目标图像
        imagecopy(
            $finalImage,
            $rotatedImage,
            0, 0,                 // 目标起点
            $cropX, $cropY,       // 源起点
            $originalWidth,       // 裁剪宽度
            $originalHeight       // 裁剪高度
        );

        imagedestroy($rotatedImage); // 释放旋转图像资源

        // 输出结果到浏览器
        header('Content-Type: image/png');
        imagepng($finalImage);
        imagedestroy($finalImage); // 释放最终图像资源
        exit();
    }
}
