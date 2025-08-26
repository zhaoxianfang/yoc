<div class="scrollbar" data-simplebar>

    <!-- User -->
    <div class="sidenav-user">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="pages-profile.html" class="link-reset">
                    <img src="{{ auth('admin')->user()['user']['cover'] ?? asset('/static/images/system/default_user.png') }}" alt="user-image" class="rounded-circle mb-2 avatar-md">
                    <span class="sidenav-user-name fw-bold">{{ auth('admin')->check() ? auth('admin')->user()['nickname'] : '无名' }}</span>
                    <span class="fs-12 fw-semibold" data-lang="user-role">Art Director</span>
                </a>
            </div>
            <div>
                <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="pages-empty.html#!" aria-haspopup="false" aria-expanded="false">
                    <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                </a>

                <div class="dropdown-menu">
                    <!-- Header -->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome back!</h6>
                    </div>

                    <!-- My Profile -->
                    <a href="pages-profile.html" class="dropdown-item">
                        <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Profile</span>
                    </a>

                    <!-- Notifications -->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-bell-ringing me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Notifications</span>
                    </a>

                    <!-- Wallet -->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-credit-card me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Balance: <span class="fw-semibold">$985.25</span></span>
                    </a>

                    <!-- Settings -->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-settings-2 me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Account Settings</span>
                    </a>

                    <!-- Support -->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="ti ti-headset me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Support Center</span>
                    </a>

                    <!-- Divider -->
                    <div class="dropdown-divider"></div>

                    <a href="{{ url("/admin/system/clear/setting") }}" class="dropdown-item ajax_request">
                        <i class="ti ti-lock me-2 fs-17 align-middle"></i>
                        <span class="align-middle">更新缓存</span>
                    </a>

                    <div class="dropdown-divider"></div>
                    <!-- Lock -->
                    <a href="auth-lock-screen.html" class="dropdown-item">
                        <i class="ti ti-lock me-2 fs-17 align-middle"></i>
                        <span class="align-middle">Lock Screen</span>
                    </a>

                    <!-- Logout -->
                    <a href="{{ route('admin.auth.logout') }}" class="dropdown-item text-danger fw-semibold">
                        <i class="ti ti-logout-2 me-2 fs-17 align-middle"></i>
                        <span class="align-middle">退出</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!--- Sidenav Menu -->
    <ul class="side-nav">
        <li class="side-nav-title" data-lang="menu-title">Menu</li>

        {!! !empty($demo_menu_html)?$demo_menu_html:'<li class="side-nav-item">
            <a href="javascript:;" class="side-nav-link bg-danger text-white disabled">
                <span class="menu-icon"><i class="ti ti-ban"></i></span>
                <span class="menu-text"> 操作异常 </span>
            </a>
        </li>' !!}

    </ul>
</div>
