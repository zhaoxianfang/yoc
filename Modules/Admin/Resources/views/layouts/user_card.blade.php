<!-- Header -->
<div class="dropdown-header noti-title">
    <h6 class="text-overflow m-0">欢迎您的使用!</h6>
</div>

<!-- My Profile -->
<a href="javascript:;" class="dropdown-item">
    <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
    <span class="align-middle">我的资料</span>
</a>

<!-- Notifications -->
<a href="javascript:void(0);" class="dropdown-item">
    <i class="ti ti-bell-ringing me-2 fs-17 align-middle"></i>
    <span class="align-middle">消息通知</span>
</a>

<!-- Settings -->
<a href="javascript:void(0);" class="dropdown-item">
    <i class="ti ti-settings-2 me-2 fs-17 align-middle"></i>
    <span class="align-middle">账号设置</span>
</a>

<!-- Support -->
<a href="javascript:void(0);" class="dropdown-item">
    <i class="ti ti-headset me-2 fs-17 align-middle"></i>
    <span class="align-middle">联系我们</span>
</a>

<!-- Divider -->
<div class="dropdown-divider"></div>

<a href="{{ url("/admin/system/clear/setting") }}" class="dropdown-item ajax_request">
    <i class="ti ti-lock me-2 fs-17 align-middle"></i>
    <span class="align-middle">更新缓存</span>
</a>

<div class="dropdown-divider"></div>
<!-- Lock -->
<a href="javascript:;" class="dropdown-item">
    <i class="ti ti-lock me-2 fs-17 align-middle"></i>
    <span class="align-middle">锁定屏幕</span>
</a>

<!-- Logout -->
<a href="{{ route('admin.auth.logout') }}" class="dropdown-item text-danger fw-semibold">
    <i class="ti ti-logout-2 me-2 fs-17 align-middle"></i>
    <span class="align-middle">退出</span>
</a>
