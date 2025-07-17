<?php

namespace Modules\Docs\Http\Controllers\Web;

use Illuminate\Http\Request;
use Modules\Docs\Http\Controllers\DocsBaseController;
use Modules\Docs\Models\DocsApp;
use Modules\Docs\Models\DocsAppUser;
use Modules\Users\Models\User;

/**
 * 文档用户管理
 */
class DocsAppUsersController extends DocsBaseController
{
    // 登录后的回调
    public function loginCallback(Request $request): \Illuminate\Http\Response
    {
        $user = collect(request()->all())->toArray();

        $userId = $user['id'];
        $appId = $request->input('app_id');
        $userFrom = $request->input('from'); // 扫码来源 user->uuid
        $extraNickname = $request->input('extra_nickname', '');

        return $this->writeApply($appId, $userId, $extraNickname, $userFrom);
    }

    private function writeApply($appId, $userId, $extraNickname = '', string $from = ''): \Illuminate\Http\Response
    {
        $app = DocsApp::query()->find($appId);

        // 验证来源于哪个管理员扫码的
        $formUser = User::query()->where('uuid', $from)->first();

        // 有来源人 | 登录用户 | 登录用户是本文档的管理员或创建者
        if (empty($from) || empty($formUser) || $app->appUsers()->where('role', '>=', DocsAppUser::ROLE_MANAGER)->doesntExist()) {
            return $this->tip_error('非法请求！');
        }

        if (empty($app)) {
            return $this->tip_error('文档不存在');
        }
        if ($docsAppUser = DocsAppUser::query()->where('user_id', $userId)->where('doc_app_id', $appId)->first()) {
            if ($docsAppUser->status == DocsAppUser::STATUS_PASS) {
                return $this->tip_info('你已经是该文档的成员了,无需再次申请');
            } else {
                if ($docsAppUser->status > DocsAppUser::STATUS_PASS) {
                    $user = User::query()->find($userId);
                    // 被踢出或驳回的用户 重新申请加入
                    $docsAppUser->status = DocsAppUser::STATUS_WAIT; // 重新申请
                    $docsAppUser->role = DocsAppUser::ROLE_WAIT; // 重新申请
                    $docsAppUser->extra_nickname = ! empty($extraNickname) ? $extraNickname : $user['nickname']; // 重新申请
                    $docsAppUser->save();
                }
            }
        } else {
            $user = User::query()->find($userId);
            $docsAppUser = new DocsAppUser;
            $docsAppUser->fill([
                'user_id' => $userId,
                'doc_app_id' => $appId,
                'audit_id' => 0,
                'extra_nickname' => ! empty($extraNickname) ? $extraNickname : $user['nickname'],
                'role' => DocsAppUser::ROLE_WAIT,
                'status' => DocsAppUser::STATUS_WAIT,
            ]);
            $docsAppUser->save();
        }

        return $this->tip_info('申请已经提交，请耐心待文档管理员审核!');
    }

    /**
     * 同意用户加入文档，并设置成员角色
     */
    public function agreeToJoin(DocsApp $docsApp, User $user, Request $request)
    {
        // 先判断操作者是否是文档创建者 或者管理员
        $this->gate::authorize('update', $docsApp);

        $role = $request->input('role', 3); // 3：参与者/伙伴：5：文档编辑；7：管理员，
        $check = DocsAppUser::query()->where('user_id', $user->id)->where('doc_app_id', $docsApp->id)->first();
        if (empty($check)) {
            return $this->error(['code' => 404, 'message' => '暂无此用户的申请数据']);
        }

        if (empty(DocsAppUser::$rolesMaps[$role])) {
            // 选择了不存在的角色 或者 选择了创建者角色
            return $this->error(['code' => 412, 'message' => '角色选择错误,请重新操作']);
        }

        if (
            $check->update([
                'role' => $role,
                'status' => DocsAppUser::STATUS_PASS,
            ])
        ) {
            return $this->success('操作成功');
        } else {
            return $this->error(['code' => 500, 'message' => '操作失败(不可操作或提交数据异常)']);
        }
    }

    /**
     * 拒绝用户加入文档
     */
    public function refuseToJoin(DocsApp $docsApp, User $user)
    {
        $this->gate::authorize('update', $docsApp);

        $check = DocsAppUser::query()->where('user_id', $user->id)->where('doc_app_id', $docsApp->id)->first();
        if (empty($check)) {
            return $this->error(['code' => 404, 'message' => '暂无此用户的数据']);
        }

        if ($check->role == DocsAppUser::ROLE_FOUNDER) {
            // 操作了创建者角色
            return $this->error(['code' => 412, 'message' => '角色选择错误,请重新操作']);
        }

        if ($check->status == DocsAppUser::STATUS_PASS) {
            return $this->error(['code' => 412, 'message' => '此用户已经是本文档的成员了，无法执行此操作']);
        }
        if (
            $check->update([
                'role' => DocsAppUser::STATUS_WAIT,
                'status' => DocsAppUser::STATUS_REJECT,
            ])
        ) {
            return $this->success('操作成功');
        } else {
            return $this->error(['code' => 500, 'message' => '操作失败(不可操作或提交数据异常)']);
        }
    }

    /**
     * 踢出文档成员
     */
    public function kickOutUser(DocsApp $docsApp, User $user)
    {
        $this->gate::authorize('update', $docsApp);

        $check = DocsAppUser::query()->where('user_id', $user->id)->where('doc_app_id', $docsApp->id)->first();
        if (empty($check)) {
            return $this->error(['code' => 404, 'message' => '暂无此用户的数据']);
        }

        if ($check->role == DocsAppUser::ROLE_FOUNDER) {
            // 操作了创建者角色
            return $this->error(['code' => 412, 'message' => '不能踢出此文档的创建者']);
        }

        if (
            $check->update([
                'role' => DocsAppUser::STATUS_WAIT,
                'status' => DocsAppUser::STATUS_OUT,
            ])
        ) {
            return $this->success('操作成功');
        } else {
            return $this->error(['code' => 500, 'message' => '操作失败(不可操作或提交数据异常)']);
        }
    }
}
