<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Generate;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;
use zxf\BarCode\BarCodeGenerate;
use zxf\QrCode\Common\EccLevel;
use zxf\QrCode\QRCodeGenerate;

/**
 * 条形码、二维码
 */
class Qrcode extends HomeBaseController
{
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home::tools.generate.qrcode', []);
        }
        $imgType = $request->post('img_type', 'qrcode');
        if ($imgType == 'qrcode') {
            return $this->qrcodeGenerate($request);
        }

        return $this->barcodeGenerate($request);
    }

    public function qrcodeGenerate(Request $request)
    {
        $content = $request->post('content', url()->full()); // 二维码内容
        $label = $request->post('label', ''); // 二维码下方文字
        $level = $request->post('level', 'q'); // 容错级别
        $fontSize = $request->post('font_size', '12'); // 文字大小
        $scale = $request->post('scale', '2'); // 像素格大小
        $logo = $request->post('logo', ''); // 设置LOGO
        $font = $request->post('font', 'lishu'); // 文字字体

        $levelMap = [
            'h' => EccLevel::H,
            'q' => EccLevel::Q,
            'm' => EccLevel::M,
            'l' => EccLevel::L,
        ];
        $qrcode = new QRCodeGenerate([
            // 'version'  => $level == 'high' ? min(max(strlen($text) / 10, 10), 35) : 2,
            // 'version'  => \zxf\QrCode\Common\Version::AUTO,
            'eccLevel' => ! empty($logo) ? EccLevel::H : $levelMap[$level],
            'scale' => (int) $scale, // 每个模块的像素大小
        ]);

        $data64 = $qrcode
            ->content($content)
            ->withText($label ?? '', $font, $fontSize) // 可选
            ->withLogo(! empty($logo) ? public_path($logo) : '') // 可选
            ->toBase64();

        return $this->success([
            'base64' => $data64,
        ]);
    }

    public function barcodeGenerate(Request $request)
    {
        $codeType = $request->post('bar_type', 'C128'); // 条形码类型
        $content = $request->post('content', url()->full()); // 条码内容
        $label = $request->post('label', ''); // 条码下方文字
        $fontSize = $request->post('font_size', '12'); // 文字大小
        $thickness = $request->post('thickness', '60'); // 条码厚度/高度
        $barWidth = $request->post('bar_width', '1'); // 条码宽度
        $font = $request->post('font', 'lishu'); // 文字字体

        $data64 = (new BarCodeGenerate)
            ->width((int) $barWidth) // 条码宽度，单位为像素
            ->height((int) $thickness) // 条码高度，单位为像素
            ->padding(8) // 条码安全区，单位为像素
            ->text($label, (int) $fontSize, $font) // 设置底部文本
            ->content($content) // 设置条码内容
            ->format($codeType) // 设置条码格式
            ->toBase64();

        return $this->success([
            'base64' => $data64,
        ]);
    }
}
