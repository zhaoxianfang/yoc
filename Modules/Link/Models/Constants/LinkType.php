<?php

namespace Modules\Link\Models\Constants;

/**
 * 短链接的 类型
 */
enum LinkType: string
{
    case URL = 'url';
    case QR = 'qr';
    case PHONE = 'phone';
    case EMAIL = 'email';
    case ARTICLE = 'article';
    case FILE = 'file';
    case PAY = 'pay';
    case ALIPAY = 'alipay';
    case BAIDU = 'baidu';
    case WECHAT = 'wechat';
    case QQ = 'qq';
    case APP = 'app';
    case OTHER = 'other';

    public function text(): string
    {
        return match ($this) {
            self::URL => '链接',
            self::QR => '二维码',
            self::PHONE => '手机',
            self::EMAIL => '邮箱',
            self::ARTICLE => '文章',
            self::FILE => '文件',
            self::PAY => '支付',
            self::ALIPAY => '支付宝',
            self::BAIDU => '百度',
            self::WECHAT => '微信',
            self::QQ => 'QQ',
            self::APP => 'APP',
            self::OTHER => '其他',
            default => '未知',
        };
    }
}