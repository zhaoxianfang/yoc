<?php

namespace Modules\Admin\Services;

use Modules\Admin\Models\Admin;
use Modules\Users\Contracts\UserAuthAbstract;

/**
 * 用户登录、退出、获取个人信息等、第三方平台快速登录
 *
 * # 登录
 * 默认模式 手机号+密码 session 登录 admin
 * AdminAuthServices::instance()->auth('admin')->login($request->mobile, $request->password);
 * 设置 手机号+密码 登录 api
 * AdminAuthServices::instance()->auth('api')->byToken()->use('password')->login($request->mobile,$request->password);
 * 设置 邮箱号+密码 登录 web
 * AdminAuthServices::instance()->byToken()->use('email')->login($request->email,$request->password);
 * 设置 id 方式进行 登录 api
 * AdminAuthServices::instance()->auth('api')->byToken()->use('id')->login($request->id);
 * 设置 id + 记住我 方式 登录 api
 * AdminAuthServices::instance()->auth('api')->byToken()->use('id')->needRemember()->login($request->id);
 *
 * # 注册
 * AdminAuthServices::instance()->register();
 *
 * #获取登录用户的用户信息
 * AdminAuthServices::instance()->userInfo();
 *
 * # 发送验证码
 * AdminAuthServices::instance()->sendSms('mobile','sms_code');
 *
 * # 第三方快速登录
 * AdminAuthServices::instance()->fastLogin();
 *
 *  * # 使用自定义字段登录
 * AdminAuthServices::instance()->use(AdminAuthServices::LOGIN_TYPE_CUSTOM)->setCustomField('user_id')->login($user['id']);
 */
class AdminAuthServices extends UserAuthAbstract
{
    public function getModel(): string
    {
        return Admin::class;
    }

    // 注册
    public function register($data): array
    {
        $code = 412;
        $message = '本模块未开此功能!';

        return compact('code', 'message');
    }

    /**
     * 验证 短信验证码是否有效
     */
    public function checkSms(string $mobile = '', string $smsCode = ''): bool
    {
        // TODO
        return false;
    }

    // 发送短信验证码
    public function sendSms(): bool
    {
        return false;
        $accessKeyId = '阿里云或者腾讯云 appid';
        $accessKeySecret = '阿里云或者腾讯云 secret';

        // 可发送多个手机号，变量为数组即可，如：[11111111111, 22222222222]
        $mobile = '18***888';
        $template = '您申请的短信模板';
        $sign = '您申请的短信签名';

        // 短信模板中用到的 参数 模板变量为键值对数组
        $params = [
            'code' => rand(1000, 9999),
            'title' => '您的标题',
            'content' => '您的内容',
        ];

        // 初始化 短信服务（阿里云短信或者腾讯云短信）
        $smsObj = \zxf\Sms\Sms::instance($accessKeyId, $accessKeySecret, 'ali或者tencent');

        // 若使用的是 腾讯云短信 需要 设置 appid 参数; 阿里云则不用
        // $smsObj = $smsObj->setAppid($appid);

        // 发起请求
        // 需要注意，设置配置不分先后顺序，send后也不会清空配置
        $result = $smsObj->setMobile($mobile)->setParams($params)->setTemplate($template)->setSign($sign)->send();
        /**
         * 返回值为bool，你可获得阿里云响应做出你业务内的处理
         *
         * status bool 此变量是此包用来判断是否发送成功
         * code string 阿里云短信响应代码
         * message string 阿里云短信响应信息
         */
        if (! $result) {
            $response = $smsObj->getResponse();
            // 做出处理
        }

        return false;
    }
}
