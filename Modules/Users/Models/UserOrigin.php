<?php

namespace Modules\Users\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class UserOrigin extends Model
{
    /**
     * 模型的属性默认值。 自动赋值属性
     *
     * @var array
     */
    protected $attributes = [
    ];

    /**
     * 不能被批量赋值的属性
     * 如果你想让所有属性都可以批量赋值， 你可以将 $guarded 定义成一个空数组。 如果你选择解除你的模型的保护，你应该时刻特别注意传递给 Eloquent 的 fill、create 和 update 方法的数组：
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * 类型转换
     *
     * @var array
     */
    protected $casts = [
        'user_info' => 'array',
        'all' => 'array',
        'authorized_at' => 'datetime:Y-m-d H:i:s',
        'login_at' => 'datetime:Y-m-d H:i:s',
        'verified' => 'boolean',
    ];

    // 用户来源
    const SOURCE_ACCOUNT_REGISTER = 'account_register';

    const SOURCE_SMS = 'sms';

    const SOURCE_QQ = 'qq';

    const SOURCE_SINA = 'sina';

    const SOURCE_WECHAT = 'wechat';

    const SOURCE_MINI_WECHAT = 'mini_wechat';

    const SOURCE_MINI_WECHAT_SHARE = 'mini_wechat_share';

    const SOURCE_USER_SHARE_WEB = 'user_share_web';

    const SOURCE_OTHER = 'other';

    public static array $sourceMaps = [
        self::SOURCE_ACCOUNT_REGISTER => '账号注册',
        self::SOURCE_SMS => '手机验证码',
        self::SOURCE_QQ => 'QQ快速登录',
        self::SOURCE_SINA => '新浪微博',
        self::SOURCE_WECHAT => '微信',
        self::SOURCE_MINI_WECHAT => '微信小程序',
        self::SOURCE_MINI_WECHAT_SHARE => '微信小程序分享页面',
        self::SOURCE_USER_SHARE_WEB => '用户分享web',
        self::SOURCE_OTHER => '暂未接入的来源',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 记录用户来源
     *      提示：可以使用 (UserOrigin::record($data, $source) instanceof UserOrigin) 验证是否记录上来源
     *
     * @param  User|array  $data  用户来源的array数据,如果使用的是 注册或者短信登录来源，$data 的值为 User模型对象，否则为数组
     * @param  string  $source  来源，参见 self::$sourceMaps
     * @return bool|UserOrigin
     *
     * @throws Exception
     */
    public static function record($data, string $source = '')
    {
        if (empty($data) || empty(self::$sourceMaps[$source])) {
            return false;
        }
        // 省市
        $country = $province = $city = $county = $town = $village = $userOrigin = null;

        // 当前用户信息，如果没有登录的返回 null
        $user = get_user_info();

        // 第三方数据来源
        $openId = $data['open_id'] ?? $data['openid'] ?? null;
        $openId = ! empty($openId) ? $openId : (! empty($data['mobile']) ? $data['mobile'] : (! empty($data['email']) ? $data['email'] : null));
        $unionId = $data['union_id'] ?? $data['unionid'] ?? null;

        if (in_array($source, [self::SOURCE_ACCOUNT_REGISTER, self::SOURCE_SMS])) {
            // 注册或者短信登录，一定会有 User::class 数据
            if (! ($data instanceof User)) {
                return false;
            }

            $nickname = $data['nickname'] ?? null;
            $cover = $data['avatar'] ?? null;
            $userOrigin = self::query()->where('user_id', $data['id'])->first();

        } else {
            // 昵称 QQ:nickname  Sina:name|screen_name
            $nickname = $data['nickname'] ?? ($data['name'] ?? null);
            // 本平台性别：0未设置，1男，2女
            // 性别：QQ-gender_type：1女2男  Sina-gender:m男w:女
            $gender = $source == self::SOURCE_QQ ? ($data['gender_type'] == 2 ? 1 : 2) : ($source == self::SOURCE_SINA ? ($data['gender'] == 'm' ? 1 : 2) : 0);
            // 头像 QQ:figureurl_qq Sina:avatar_large
            $cover = $data['figureurl_qq'] ?? ($data['avatar_large'] ?? null);

            if ($source == self::SOURCE_SINA) {
                [$province, $city] = explode(' ', $data['location']);
            }
            if ($source == self::SOURCE_QQ) {
                $province = $data['province'];
                $city = $data['city'];
            }
            if (! empty($unionId)) {
                $userOrigin = self::query()->where('unionid', $unionId)->first();
            }
            if (empty($userOrigin) && $openId) {
                $userOrigin = self::query()->where('open_id', $openId)->first();
            }

            if (empty($userOrigin) && ! empty($cover)) {
                $userOrigin = self::where(['source' => $source, 'cover' => $cover])->first();
            }
        }

        if (! $userOrigin) {
            $userOrigin = new UserOrigin;
        }
        $userId = ! empty($user) ? $user['id'] : (! empty($userOrigin) ? $userOrigin['user_id'] : null);
        $origin = $userOrigin->fill([
            'user_id' => $userId ?? null,
            'open_id' => $openId ?? null,
            'source' => $source,

            'nickname' => $nickname ?? null,
            'gender' => $gender ?? 0, // '性别：0未设置，1男，2女');
            'cover' => $cover ?? null,

            'country' => $country,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'town' => $town,
            'village' => $village,
            'unionid' => $unionId,

            'all' => json_encode($data),
        ]);
        $origin->save();
        $origin->refresh();

        return $origin;
    }
}
