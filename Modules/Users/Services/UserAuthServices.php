<?php

namespace Modules\Users\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Users\Contracts\UserAuthAbstract;
use Modules\Users\Models\User;
use Modules\Users\Models\UserOrigin;

/**
 * 用户登录、退出、获取个人信息等、第三方平台快速登录
 *
 * # 登录
 * 默认模式 手机号+密码 session 登录 web
 * UserAuthServices::instance()->login($request->mobile, $request->password);
 * 设置 手机号+密码 登录 api
 * UserAuthServices::instance()->auth('api')->byToken()->use('password')->login($request->mobile,$request->password);
 * 设置 邮箱号+密码 登录 web
 * UserAuthServices::instance()->byToken()->use('email')->login($request->email,$request->password);
 * 设置 id 方式进行 登录 api
 * UserAuthServices::instance()->auth('api')->byToken()->use('id')->login($request->id);
 * 设置 id + 记住我 方式 登录 api
 * UserAuthServices::instance()->auth('api')->byToken()->use('id')->needRemember()->login($request->id);
 *
 * # 注册
 * UserAuthServices::instance()->register();
 *
 * #获取登录用户的用户信息
 * UserAuthServices::instance()->userInfo();
 *
 * # 发送验证码
 * UserAuthServices::instance()->sendSms('mobile','sms_code');
 *
 * # 第三方快速登录
 * UserAuthServices::instance()->fastLogin();
 *
 * # 使用自定义字段登录
 * UserAuthServices::instance()->use(UserAuthServices::LOGIN_TYPE_CUSTOM)->setCustomField('user_id')->login($user['id']);
 */
class UserAuthServices extends UserAuthAbstract
{
    public function getModel(): string
    {
        return User::class;
    }

    // 注册
    public function register($data): array
    {
        $code = 412;
        $message = '注册失败!';
        $user = '';
        DB::beginTransaction();
        try {
            // 注册包含字段
            $contains = ['username', 'nickname', 'mobile', 'gender', 'email', 'password'];
            if (! Arr::has($data, $contains)) {
                $message = '请求参数错误';
            } else {
                $data = Arr::only($data, $contains);
                $model = $this->getModel();
                $hasErr = false;
                if ($model::query()->where('mobile', $data['mobile'])->exists()) {
                    $message = '该手机号已经被注册过了!';
                    $hasErr = true;
                }
                if (! $hasErr && $model::query()->where('email', $data['email'])->exists()) {
                    $message = '该邮箱号已经被注册过了!';
                    $hasErr = true;
                }

                if ($hasErr) {
                    DB::rollBack();

                    return compact('code', 'message', 'user');
                }

                $user = new $model([
                    'real_name' => $data['username'],
                    'nickname' => $data['nickname'],
                    'mobile' => $data['mobile'],
                    'gender' => $data['gender'], // 1 男 2 女
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                ]);

                if ($user->save()) {
                    // 依赖 mobile 或者 email 唯一 和观察者 模式是否返回false
                    $code = 200;
                    $message = '注册成功!';
                    $user->refresh();
                    // 记录用户来源
                    $user && UserOrigin::record($user, UserOrigin::SOURCE_ACCOUNT_REGISTER);
                }
                DB::commit();
            }
        } catch (Exception $err) {
            DB::rollBack();
            $user = '';
            if ($err->getCode() == 23000) {
                $user = $this->getModel()->where('mobile', $data['mobile'])->first();
                $code = 200;
                $message = '该账号已经注册过了!';
            } else {
                $code = 500;
                $message = '出错啦，请稍后再试!';
            }
        }

        return compact('code', 'message', 'user');
    }

    /**
     * 验证 短信验证码是否有效
     */
    public function checkSms(string $mobile = '', string $smsCode = ''): bool
    {
        // TODO
        return false;
    }

    /**
     * 第三方平台快速登录 「主要用于查找库中的用户信息」
     *
     * @param  string  $loginSource  登录的第三方来源,例如 qq,sina 等
     *
     * @throws Exception
     */
    public function fastLogin(string $loginSource, array $userInfo): array
    {
        DB::beginTransaction();
        try {
            // 记录用户快速 和来源信息验证
            if (empty(UserOrigin::$sourceMaps[$loginSource]) || ! (($userOrigin = UserOrigin::record($userInfo, $loginSource)) instanceof UserOrigin)) {
                throw new Exception('未识别的登录类型！');
            }

            if ($userOrigin->user()->exists()) {
                $user = $userOrigin->user;
            } else {
                $user = new User([
                    'nickname' => $userOrigin['nickname'] ?? '',
                    'gender' => $userOrigin['gender'] ?? 0,
                    'cover' => $userOrigin['cover'],
                    'province' => $userOrigin['province'] ?? '',
                    'city' => $userOrigin['city'] ?? '',
                ]);
                $user->save();
                $user->refresh();
                $userOrigin->user_id = $user->id;
                $userOrigin->save();
                $userOrigin->refresh();
            }
            // 转数组
            $user = collect($user)->toArray();
            // 来源
            $user['origin'] = collect($userOrigin)->except(['all', 'user'])->toArray();
            DB::commit();

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();
            debug_test([$exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine()], $loginSource.':快速登录失败');
            throw new Exception('快速登录失败:'.$exception->getMessage());
        }
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
