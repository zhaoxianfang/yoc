<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppUser;
use Modules\Users\Models\User;
use Modules\Users\Services\UserAuthServices;

/**
 * 文档用户管理
 */
class DocsAppUserApplyController extends DocsBaseController
{
    /**
     * 用户申请加入文档页面，判断是否为QQ和微博
     */
    public function apply(DocsApp $docsApp, Request $request)
    {
        // 验证来源
        $from = $request->input('from', '');
        $formUser = User::query()->where('uuid', $from)->first();

        // 有来源人 | 登录用户 | 登录用户是本文档的管理员或创建者
        if (empty($from) || empty($formUser) || $docsApp->appUsers()->where('role', '>=', DocsAppUser::ROLE_MANAGER)->doesntExist()) {
            return $this->tip_error('非法请求！');
        }

        // 验证是否申请加入过文档
        $this->check($docsApp);

        // type 参数放在最后
        $url = route('docs.apply.entry', ['docsApp' => $docsApp->id, 'from' => $from, 'type' => '']);

        // 验证浏览器
        if (! empty($res = $this->checkBrowser($docsApp, $from))) {
            return $res;
        }

        return view('docs::auth/apply_join_docs', [
            'app' => $docsApp,
            'url' => $url,
            // 显示页面类型
            //      0:让用户选择QQ、微博等登录页面 或 仅提示提示信息；有提示信息时，不显示选择登录方式页面
            //      1:用户重新注册新账号页面；
            //      2:用户填写昵称/备注页面
            'show_page_type' => 0,
            'message' => $tipsInfo ?? '',
            'data' => [],
        ]);

    }

    public function store(DocsApp $docsApp, Request $request)
    {
        // 验证来源
        $from = $request->input('from', '');
        $formUser = User::query()->where('uuid', $from)->first();

        // 有来源人 | 登录用户 | 登录用户是本文档的管理员或创建者
        if (empty($from) || empty($formUser) || $docsApp->appUsers()->where('role', '>=', DocsAppUser::ROLE_MANAGER)->doesntExist()) {
            return $this->tip_error('非法请求！');
        }

        $url = route('docs.apply.entry', ['docsApp' => $docsApp->id, 'type' => '']);

        $authSource = $request->input('auth_source', '');

        if (! empty($authSource)) {
            $user = collect(request()->input())->toArray();
            // 是否记住密码
            if (! auth('web')->loginUsingId($user['id'], false)) {
                return to_route('docs.apply.entry', ['docsApp' => $docsApp->id], 302);
            } else {
                // 填写备注昵称页面
                return view('docs::auth/apply_join_docs', [
                    'app' => $docsApp,
                    'url' => $url,
                    'show_page_type' => 2, // 显示页面类型 0:让用户选择QQ、微博等登录页面 或 仅提示提示信息； 1:用户重新注册新账号页面；2:用户填写昵称/备注页面
                    'message' => '',
                    'data' => [],
                ]);
            }
        }

        $this->check($docsApp);

        // 表单提交上来的数据
        $input = $request->only([
            'extra_nickname', // 备注昵称
        ]);

        // POST 提交数据过来
        $type = $request->input('type', 'login');
        if ($type == 'login') {
            // 选择第三方登录页面
            $data = $request->only(['extra_nickname', 'login_type']); // 备注名称和登录类型

            if ($data['login_type'] == 'qq') {
                $url = route('docs.apply.store', ['from' => $from, 'docsApp' => $docsApp->id, 'auth_source' => 'qq', 'extra_nickname' => $data['extra_nickname'] ?? '', 'type' => '']);

                return to_route('callback.tencent.login', ['callback_url' => urlencode($url)], 302);
            }

            if ($data['login_type'] == 'sina') {
                $url = route('docs.apply.store', ['from' => $from, 'docsApp' => $docsApp->id, 'auth_source' => 'weibo', 'extra_nickname' => $data['extra_nickname'] ?? '', 'type' => '']);

                return to_route('callback.weibo.login', ['callback_url' => urlencode($url)], 302);
            }

            return view('docs::auth/apply_join_docs', [
                'app' => $docsApp,
                'url' => $url,
                'show_page_type' => 0, // 显示页面类型 0:让用户选择QQ、微博等登录页面 或 仅提示提示信息； 1:用户重新注册新账号页面；2:用户填写昵称/备注页面
                'message' => '',
                'data' => $input,
            ]);
        }
        if ($type == 'register') {
            // 提交注册新用户页面
            $data = $request->only(['nickname', 'email', 'mobile', 'password']); // 备注名称和登录类型
            $data['gender'] = 0;
            $data['username'] = '';

            // 进入注册逻辑
            $res = UserAuthServices::instance()->register($data);
            if ($res == 200) {
                if (empty($user = $res['user'])) {
                    $user = User::query()->where('mobile', $data['mobile'])->first();
                }

                $docsAppUser = new DocsAppUser;
                $docsAppUser->fill([
                    'user_id' => $user['id'],
                    'doc_app_id' => $docsApp->id,
                    'audit_id' => 0,
                    'extra_nickname' => $data['nickname'],
                    'role' => DocsAppUser::ROLE_WAIT,
                    'status' => DocsAppUser::STATUS_WAIT,
                ]);
                $docsAppUser->save();

                return $this->tip_success('申请已经提交，请耐心待文档管理员审核');
            } else {
                return $this->tip_error('注册失败:'.$res['message']);
            }
        }
        if ($type == 'remark') {
            // 提交备注、昵称
            $data = $request->only(['extra_nickname', 'docs_app_user_id']);
            DocsAppUser::query()->where('id', $data['docs_app_user_id'])->update([
                'extra_nickname' => $data['extra_nickname'],
            ]);

            return $this->tip_info('申请已经提交，请耐心待文档管理员审核');
        }

        return view('docs::auth/apply_join_docs', [
            'app' => $docsApp,
            'url' => $url,
            'show_page_type' => 0, // 显示页面类型 0:让用户选择QQ、微博等登录页面 或 仅提示提示信息； 1:用户重新注册新账号页面；2:用户填写昵称/备注页面
            'message' => '',
            'data' => $input,
        ]);
    }

    // 验证已经登录的用户
    private function check(DocsApp $docsApp): void
    {
        if (auth('web')->check()) {
            $user = auth()->user();

            if ($docsApp->users()->where('users.id', $user->id)->first()) {
                $this->tip_info('您已经是该文档的成员了,无需再次申请!');
            } elseif ($authInfo = $docsApp->applyUsers()->where('users.id', $user->id)->first()) {
                if ($authInfo['pivot']['role'] == DocsAppUser::ROLE_WAIT) {
                    $this->tip_info('您已经提交过申请,请耐心等待文档管理员审核!');
                }
            } else {
                $docsApp->applyUsers()->attach($user->id, ['role' => DocsAppUser::ROLE_WAIT]);
                $this->tip_info('申请已经提交，请耐心待文档管理员审核!');
            }
        }
    }

    // 验证浏览器
    private function checkBrowser(DocsApp $docsApp, string $from = ''): string|\Illuminate\Http\RedirectResponse
    {
        // 判断是不是微信
        if (is_wechat_browser()) {
            $this->tip_info('暂未开通「微信」扫码申请功能!');
        }
        // 判断是不是支付宝
        if (is_alipay_browser()) {
            $this->tip_info('暂未开通「支付宝」扫码申请功能!');
        }
        // 判断是不是QQ
        if (is_qq_browser()) {
            $url = route('docs.apply.store', ['from' => $from, 'docsApp' => $docsApp->id, 'auth_source' => 'qq', 'type' => '']);

            return to_route('callback.tencent.login', ['callback_url' => urlencode($url)], 302);
        }
        // 判断是否为微博
        if (is_weibo_browser()) {
            $url = route('docs.apply.store', ['from' => $from, 'docsApp' => $docsApp->id, 'auth_source' => 'weibo', 'type' => '']);

            return to_route('callback.weibo.login', ['callback_url' => urlencode($url)], 302);
        }

        return '';
    }
}
