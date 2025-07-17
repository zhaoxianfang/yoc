<?php

namespace Modules\Callback\Http\Controllers\Web\Wechat;

use Illuminate\Http\Request;
use Modules\Callback\Http\Controllers\Web\CallbackController;
use zxf\WeChat\OfficialAccount\Qrcode;
use zxf\WeChat\OfficialAccount\Receive;
use zxf\WeChat\OfficialAccount\Template;
use zxf\WeChat\OfficialAccount\User;

/**
 * 微信服务端接口回调
 */
// https://www.kancloud.cn/zoujingli/wechat-developer/519199
class Notify extends CallbackController
{
    private ?array $user = []; // 微信公众号配置

    private ?Receive $receive; // 处理消息的对象Obj

    private array $pushAll = []; // 微信推送过来的所有数据

    private string $openid = ''; // 用户openid

    // private string $configKey = 'wechat_sandbox'; // 微信配置(tools_wechat)的key
    private string $configKey = 'default'; // 微信配置的key

    /**
     * 接收到的原始数据
     *
     * @var mixed|array
     */
    private static mixed $rawInput = null;

    public function __construct(Request $request)
    {
        if (php_sapi_name() !== 'cli') {
            // 非命令行中运行
            $this->receive = new Receive($this->configKey);
        }
    }

    /**
     * 微信服务端回调入口
     *
     * @return string|void|null
     */
    public function index(Request $request)
    {
        try {
            // 获取当前推送的所有数据 ，并把所有键名改为小写
            // $this->pushAll = array_change_key_case($this->receive->getReceive(), CASE_LOWER);
            $this->pushAll = (array) $this->receive->getReceive();

            debug_test($this->pushAll, 'Wechat Callback Notify', get_raw_input());

            // 获取当前推送接口类型 ( text,image,loction,event... )
            $msgType = $this->receive->getMsgType();

            // 获取当前推送来源用户的openid
            $this->openid = $this->receive->getOpenid();

            // '微信服务回调 $msgType', $msgType;
            // '微信服务回调 openid', $this->openid;

            $this->getUserInfo();

            switch ($msgType) {
                case 'text':
                    return $this->textHandle();
                case 'image':
                    return $this->imageHandle();
                case 'voice':
                    return $this->voiceHandle();
                case 'video':
                    return $this->videoHandle();
                case 'shortvideo':
                    return $this->shortVideoHandle();
                case 'link':
                    return $this->linkHandle();
                case 'location':
                    return $this->locationHandle();
                case 'event':
                    return $this->eventHandle();
                default:
                    echo '未定义';
            }
        } catch (\Throwable $e) {
            debug_test($e, 'Wechat Callback Notify:Error');
            throw $e;
        }
    }

    // 获取用户信息
    public function getUserInfo()
    {
        if (empty($this->openid)) {
            return false;
        }
        $this->user = User::instance($this->configKey)->getUserInfo($this->openid);
    }

    // 事件处理
    public function eventHandle()
    {
        switch ($this->pushAll['Event']) {
            case 'subscribe':
                return $this->subscribeHandle();
            case 'unsubscribe':
                return $this->unsubscribeHandle();
            case 'SCAN':
                return $this->scanHandle();
            case 'LOCATION':
                return $this->locationHandle();
            case 'CLICK':
                return $this->clickHandle();
            case 'VIEW':
                return $this->viewHandle();
            case 'MASSSENDJOBFINISH': // 群发消息结束通知
                return $this->massMsgHandle();
            default:
                echo '未定义';
        }
    }

    public function unsubscribeHandle()
    {
        // 取消订阅
        // debug_test($this->user, '取消订阅');

        return true;
    }

    // 订阅事件
    public function subscribeHandle()
    {
        // 扫描带参数二维码 关注订阅 事件
        if (! empty($this->pushAll['EventKey']) && (substr($this->pushAll['EventKey'], 0, 8) == 'qrscene_')) {
            // 扫描带参数二维码事件 用户未关注时，进行关注后的事件推送
            $scene_id = substr($this->pushAll['EventKey'], 8); //  二维码scene_id
            // $this->pushAll['Ticket'] 二维码的ticket，可用来换取二维码图片

            // debug_test($this->user, '扫码关注');

            // 回复文本消息
            return $this->receive->text('扫码关注:'.$scene_id)->reply();
        } else {
            // 普通关注
            // debug_test($this->user, '用户订阅');

            // 回复图文消息（高级图文或普通图文，数组）
            return $this->receive->news([
                [
                    'Title' => '感谢关注',
                    'Description' => 'Description',
                    'PicUrl' => 'https://weisifang.com/static/inspinia/v2.9/img/logo.png',
                    'Url' => 'https://weisifang.com',
                ],
            ])->reply();

        }
    }

    // 扫码事件
    public function scanHandle()
    {
        // 已经关注 扫描带参数二维码事件
        if (! empty($this->pushAll['EventKey'])) {
            // 扫描带参数二维码事件 用户已关注时的事件推送
            // $this->pushAll['EventKey'] 二维码scene_id
            // $this->pushAll['Ticket'] 二维码的ticket，可用来换取二维码图片

            // debug_test($this->user, '扫码');

            // 回复文本消息
            return $this->receive->text('已经关注用户扫码:'.$this->pushAll['EventKey'])->reply();
        }

        return false;
    }

    public function locationHandle()
    {

        // debug_test([
        //     'long' => $this->pushAll['Longitude'] ?? $this->pushAll['Location_Y'], // 地理位置经度
        //     'latitude' => $this->pushAll['Latitude'] ?? $this->pushAll['Location_X'], // 地理位置纬度
        // ], '上报位置');

        if (! empty($this->pushAll['Latitude'])) {
            // 上报地理位置事件 用户同意上报地理位置后，每次进入公众号会话时，都会在进入时上报地理位置，或在进入会话后每5秒上报一次地理位置
            // $this->pushAll['Latitude']; // 地理位置纬度
            // $this->pushAll['Longitude']; // 地理位置经度
            // $this->pushAll['Precision']; // 地理位置精度

            // 回复文本消息
            return $this->receive->text('上报位置: -经度:'.$this->pushAll['Longitude'].' -维度:'.$this->pushAll['Latitude'].' -精度:'.$this->pushAll['Precision'])->reply();
        } else {
            // 普通地理位置消息
            // $this->pushAll['Location_X']; // 地理位置纬度
            // $this->pushAll['Location_Y']; // 地理位置经度
            // $this->pushAll['Scale']; // 地图缩放大小
            // $this->pushAll['Label']; // 地理位置信息
            // $this->pushAll['MsgId']; // 消息id，64位整型
            // $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
            // $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

            // 回复文本消息
            return $this->receive->text('发送位置: -经度:'.$this->pushAll['Location_Y'].' -维度:'.$this->pushAll['Location_X'].' -缩放大小:'.$this->pushAll['Scale'].' -位置信息:'.$this->pushAll['Label'])->reply();
        }
    }

    // 自定义菜单事件
    public function clickHandle()
    {
        // 自定义菜单事件 点击菜单拉取消息时的事件推送
        // $this->pushAll['EventKey']; // 事件 KEY 值，与自定义菜单接口中 KEY 值对应

        return $this->receive->text('点击菜单:'.$this->pushAll['EventKey'])->reply();

    }

    // 自定义菜单事件 点击菜单跳转链接时的事件推送
    public function viewHandle()
    {
        // $this->pushAll['EventKey']; // 跳转地址 www.a.com
        return $this->receive->text('点击菜单跳转:'.$this->pushAll['EventKey'])->reply();
    }

    public function imageHandle()
    {
        // $this->pushAll['PicUrl']; // 图片链接（由系统生成）
        // $this->pushAll['MediaId']; // 图片消息媒体id，可以调用获取临时素材接口拉取数据。
        // $this->pushAll['MsgId']; // 消息id，64位整型
        // $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        // $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

        // debug_test($this->pushAll['PicUrl'], '发送图片');

        // 回复图片消息（需先上传到微信服务器生成 media_id）
        $this->receive->image($this->pushAll['MediaId'])->reply();

        return $this->receive->text('图片地址:'.$this->pushAll['PicUrl'])->reply();
    }

    // 语音消息
    public function voiceHandle()
    {
        // $this->pushAll['MediaId']; // 语音消息媒体id，可以调用获取临时素材接口拉取数据。
        // $this->pushAll['Format']; // 语音格式，如amr，speex等
        // $this->pushAll['MsgId']; // 消息id，64位整型
        // $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        // $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）
        // $this->pushAll['Recognition']; // 语音识别结果，UTF8编码 请注意，「开通语音识别后」，用户每次发送语音给公众号时，微信会在推送的语音消息 XML 数据包中，增加一个 Recognition 字段（注：由于客户端缓存，开发者开启或者关闭语音识别功能，对新关注者立刻生效，对已关注用户需要24小时生效。开发者可以重新关注此帐号进行测试）

        // 回复语音消息（需先上传到微信服务器生成 media_id）
        return $this->receive->voice($this->pushAll['MediaId'])->reply();
    }

    // 视频消息
    public function videoHandle()
    {
        // $this->pushAll['ThumbMediaId']; // 视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
        // $this->pushAll['MediaId']; // 视频消息媒体id，可以调用获取临时素材接口拉取数据。
        // $this->pushAll['MsgId']; // 消息id，64位整型
        // $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        // $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

        // 回复视频消息（需先上传到微信服务器生成 media_id）
        return $this->receive->video($this->pushAll['MediaId'], '视频标题', '视频描述')->reply();
    }

    // 小视频
    public function shortVideoHandle()
    {
        //  $this->pushAll['ThumbMediaId']; // 视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
        //  $this->pushAll['MediaId']; // 视频消息媒体id，可以调用获取临时素材接口拉取数据。
        //  $this->pushAll['MsgId']; // 消息id，64位整型
        //  $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        //  $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

        // 回复视频消息（需先上传到微信服务器生成 media_id）
        return $this->receive->video($this->pushAll['MediaId'], '小视频标题', '小视频描述')->reply();
    }

    public function linkHandle()
    {
        //  $this->pushAll['Title']; // 标题
        //  $this->pushAll['Description']; // 描述
        //  $this->pushAll['Url']; // url
        //  $this->pushAll['MsgId']; // 消息id，64位整型
        //  $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        //  $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

        // debug_test($this->pushAll['Url'], '点击链接');

        return true;
    }

    // 群发消息结束后微信通知
    public function massMsgHandle()
    {
        // 群发的结果，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001):涉嫌广告, err(20001):涉嫌政治, err(20004):涉嫌社会, err(20002):涉嫌色情, err(20006):涉嫌违法犯罪, err(20008):涉嫌欺诈, err(20013):涉嫌版权, err(22000):涉嫌互推(互相宣传), err(21000):涉嫌其他, err(30001):原创校验出现系统错误且用户选择了被判为转载就不群发, err(30002): 原创校验被判定为不能群发, err(30003): 原创校验被判定为转载文且用户选择了被判为转载就不群发, err(40001)：管理员拒绝, err(40002)：管理员30分钟内无响应，超时
        // $this->pushAll['Status']; // 群发的结果
        // $this->pushAll['TotalCount']; // tag_id下粉丝数；或者openid_list中的粉丝数
        // $this->pushAll['FilterCount']; // 过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount 约等于 SentCount + ErrorCount
        // $this->pushAll['SentCount']; // 发送成功的粉丝数
        // $this->pushAll['ErrorCount']; // 发送失败的粉丝数
        // $this->pushAll['ArticleUrl']; // 群发文章的url
        // ... 还有其他参数

        // debug_test($this->pushAll, '群发消息结束后微信通知');

        return true;
    }

    // 文本消息处理
    public function textHandle()
    {
        // $this->pushAll['Content']; // 消息内容
        // $this->pushAll['MsgId']; // 消息id，64位整型
        // $this->pushAll['MsgDataId']; // 消息的数据ID（消息如果来自文章时才有）
        // $this->pushAll['Idx']; // 多图文时第几篇文章，从1开始（消息如果来自文章时才有）

        // debug_test($this->pushAll['Content'], '发送文本');
        //
        $qrcode = Qrcode::instance($this->configKey);
        if (in_array($this->pushAll['Content'], ['qr', 'qrcode', '二维码', '登录码', '登录二维码', '登录'])) {
            // https://weisifang.com/callback/wechat/login
            return $this->receive->text('网页授权登录：'.config('app.url').'/callback/wechat/login')->reply();
            // 有效期一周
            // $res = $qrcode->create('login', 604800);
            // 有效期永久
            $res = $qrcode->create('long_login');

            if (! empty($res['ticket'])) {
                return $this->receive->text('永久登录码：'.$qrcode->url($res['ticket']))->reply();
            } else {
                return $this->receive->text('一周登录码:'.$res['ticket'].' -url:'.$res['url'])->reply();
            }
        }

        if (in_array($this->pushAll['Content'], ['模板', '模板消息', 'template', '威四方', 'wsf'])) {
            // 发送模板消息
            try {
                // 实例接口
                $wechat = new Template($this->configKey);
                // 执行操作
                $wechat->send([
                    'touser' => $this->openid,
                    'template_id' => 'AxHD86UBIAGjJu30Ts8IxHjV7GTo6cSJCU3LV9Qgk4c',
                    'url' => 'https://weisifang.com',
                    'data' => [
                        'content' => [
                            'value' => '威四方',
                        ],
                    ],
                ]);
            } catch (\Exception $e) {
                // 异常处理
                // write_error($e);

                return $this->receive->text('换个词试试~')->reply();
            }
            exit;
        }

        return $this->receive->text('小威正在学习中，还未收录「'.$this->pushAll['Content'].'」')->reply();
    }
}
