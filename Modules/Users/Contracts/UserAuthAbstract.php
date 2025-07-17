<?php

namespace Modules\Users\Contracts;

use Exception;
use Illuminate\Support\Facades\Hash;
use Modules\System\Services\BaseService;
use Modules\Users\Models\UserOrigin;

abstract class UserAuthAbstract extends BaseService
{
    /**
     * auth 授权的名称
     *
     * @var string 参照 auth.php 配置里面的 guards 例如 web|admin|docs|api|...
     */
    private $authName = 'web';

    /**
     * 是否使用apiToken 方式登录授权
     *
     * @var bool 默认false [true:使用token(jwt|passport|...)方式,false:使用session方式]
     */
    private $replyToken = false;

    /**
     * 登录方式
     *
     * @var string 参见
     */
    private $loginType = 'mobile';

    /**
     * 自定义登录字段
     *
     * @var string
     */
    private $customFiled = '';

    /**
     * 是否记住登录状态
     *
     * @var bool
     */
    private $remember = false;

    /**
     * 用户登录成功后的返回信息是否携带用户信息
     *
     * @var bool
     */
    private $withUserInfo = false;

    // 登录方式
    const LOGIN_TYPE_MOBILE = 'mobile';

    const LOGIN_TYPE_EMAIL = 'email';

    const LOGIN_TYPE_SMS = 'sms';

    const LOGIN_TYPE_USER_ID = 'id';

    const LOGIN_TYPE_CUSTOM = 'custom';

    public static $loginTypeMap = [
        self::LOGIN_TYPE_MOBILE => '手机号+密码',
        self::LOGIN_TYPE_EMAIL => '邮箱号+密码',
        self::LOGIN_TYPE_SMS => '短信验证码',
        self::LOGIN_TYPE_USER_ID => '用户id',
        self::LOGIN_TYPE_CUSTOM => '自定义字段登录',
    ];

    // 获取操作模型类
    abstract public function getModel(): string;

    // 注册
    abstract public function register($data): array;

    /**
     * 验证 短信验证码是否有效
     */
    abstract public function checkSms(string $mobile = '', string $smsCode = ''): bool;

    // 发送短信
    abstract public function sendSms(): bool;

    /**
     * 选择一个授权方式/模块 参照 auth.php 配置里面的 guards
     *
     * @param  string  $name  例如 web|admin|docs|api|...
     * @return $this
     */
    public function auth(string $name = 'web')
    {
        $this->authName = $name;

        return $this;
    }

    /**
     * 设置授权方式 支持session或token两种方式
     *
     * @param  bool  $replyToken  [true:使用token(jwt|passport|...)方式,false:使用session方式]
     * @return $this
     */
    public function byToken(bool $replyToken = true)
    {
        $this->replyToken = $replyToken;

        return $this;
    }

    /**
     * 选择登录方式
     *
     * @param  string  $type  参见 self::$loginTypeMap
     * @return $this
     *
     * @throws Exception
     */
    public function use(string $type = self::LOGIN_TYPE_MOBILE)
    {
        if (empty(self::$loginTypeMap[$type])) {
            throw new Exception('不支持的登录方式');
        }
        $this->loginType = $type;

        return $this;
    }

    /**
     * 设置自定义登录字段
     *
     *
     * @return $this
     */
    public function setCustomField(string $filed = '')
    {
        $this->customFiled = $filed;

        return $this;
    }

    /**
     * 获取用户信息
     *
     * @return array
     */
    public function userInfo()
    {
        if (auth($this->authName)->check()) {
            return [
                'code' => 200,
                'message' => '获取成功',
                'data' => auth($this->authName)->user(),
            ];
        } else {
            return [
                'code' => 403,
                'message' => '暂未登录，无法获取用户信息',
            ];
        }
    }

    /**
     * 登录结束后是否需要携带返回用户信息
     *
     *
     * @return $this
     */
    public function carryUserInfo(bool $bool = true)
    {
        $this->withUserInfo = $bool;

        return $this;
    }

    /**
     * 是否记住登录状态
     *
     *
     * @return $this
     */
    public function needRemember(bool $remember = true)
    {
        $this->remember = $remember;

        return $this;
    }

    /**
     * 使用账号 和密码进行登录
     *
     * @param  string  $account  账号(手机号、邮箱号、用户id)
     * @param  string|null  $password  密码或短信验证码（账号为用户id时候为空）
     * @return array
     *
     * @throws Exception
     */
    public function login(string $account, ?string $password = '')
    {
        $data = [];
        $isLogin = false;
        $user = '';

        $model = $this->getModel();
        // 使用 session 登录
        if (! $this->replyToken) {
            if ($this->loginType == self::LOGIN_TYPE_USER_ID) {
                $isLogin = auth($this->authName)->loginUsingId($account, $this->remember);
            }
            if ($this->loginType == self::LOGIN_TYPE_MOBILE) {
                $isLogin = auth($this->authName)->attempt(['mobile' => $account, 'password' => $password], $this->remember);
            }
            if ($this->loginType == self::LOGIN_TYPE_EMAIL) {
                $isLogin = auth($this->authName)->attempt(['email' => $account, 'password' => $password], $this->remember);
            }
            if ($this->loginType == self::LOGIN_TYPE_SMS) {
                if ($this->checkSms($account, $password)) {
                    $isLogin = auth($this->authName)->attempt(['mobile' => $account], $this->remember);
                }
            }
            if ($this->loginType == self::LOGIN_TYPE_CUSTOM) {
                $user = $model::where($this->customFiled, $account)->first();
                $isLogin = auth($this->authName)->loginUsingId($account, $this->remember);
            }
        } else {
            // 使用 token 登录
            if ($this->loginType == self::LOGIN_TYPE_CUSTOM) {
                $user = $model::where($this->customFiled, $account)->first();
            } else {
                // 使用 api token 登录
                $accountFieldName = $this->loginType == self::LOGIN_TYPE_SMS ? self::LOGIN_TYPE_MOBILE : $this->loginType;
                $user = $model::where($accountFieldName, $account)->first();
            }

            if ($user) {
                if ($this->loginType == self::LOGIN_TYPE_SMS) {
                    $isLogin = $this->checkSms($account, $password);
                } elseif ($this->loginType == self::LOGIN_TYPE_USER_ID || Hash::check($password, $user->password)) {
                    $isLogin = true;
                }
                if ($isLogin) {
                    $authInfo = $user->createToken($this->authName);
                    $token = $authInfo->accessToken;
                    if ($token) {
                        // 手动修改 header 头 让 auth() 可以获取当前用户信息
                        // request()->headers->set('Authorization', 'Bearer '.$token);
                        $data = [
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                            // 'client_id'  => $authInfo->client_id
                        ];
                    } else {
                        $isLogin = false;
                    }
                }
            }
        }

        // if ($isLogin && auth($this->authName)->check()) {
        if ($isLogin) {
            $code = 200;
            $message = '登录成功';
            // $user = auth($this->authName)->user();
            if ($user->status !== $model::STATUS_NORMAL) {
                $isLogin = false;
                $this->logout();
                $code = 403;
                $message = $user->status == $model::STATUS_FREEZE ? '此账号已冻结,无法登录' : '此账号未激活,请先联系管理员再试';
            }
        } else {
            $code = 403;
            $message = $this->loginType == self::LOGIN_TYPE_SMS ? '账号或者验证码错误' : '账号或者密码错误~';
            $this->logout();
        }

        if ($isLogin && $this->loginType == self::LOGIN_TYPE_SMS) {
            // 记录用户来源
            UserOrigin::record($user, UserOrigin::SOURCE_SMS);
        }

        if ($isLogin && $this->withUserInfo) {
            // 返回数据中需要携带上用户信息
            $data['user'] = $user;
        }

        // 检查关联企业

        return $isLogin ? compact('code', 'message', 'data') : compact('code', 'message');
    }

    // 用户退出
    public function logout(): array
    {
        try {
            auth($this->authName)->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        } catch (Exception $e) {
        }

        return [
            'code' => 200,
            'message' => '退出成功',
        ];
    }
}
