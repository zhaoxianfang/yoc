<div class="scrollbar" data-simplebar>

    <!-- User -->
    <div class="sidenav-user">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="javascript:;" class="link-reset">
                    <img src="{{ auth('admin')->user()['user']['cover'] ?? asset('/static/images/system/default_user.png') }}" alt="user-image" class="rounded-circle mb-2 avatar-md">
                    <span class="sidenav-user-name fw-bold">{{ auth('admin')->check() ? auth('admin')->user()['nickname'] : '无名' }}</span>
                    <span class="fs-12 fw-semibold" data-lang="user-role">Art Director</span>
                </a>
            </div>
            <div>
                <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="#!" aria-haspopup="false" aria-expanded="false">
                    <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                </a>

                <div class="dropdown-menu">
                    @include('admin::layouts.user_card')
                </div>
            </div>
        </div>
    </div>

    <!--- Sidenav Menu -->
    <ul class="side-nav">
        <li class="side-nav-title" data-lang="menu-title">菜单</li>

        {!! !empty($admin_menu_html)?$admin_menu_html:'<li class="side-nav-item">
            <a href="javascript:;" class="side-nav-link bg-danger text-white disabled">
                <span class="menu-icon"><i class="ti ti-ban"></i></span>
                <span class="menu-text"> 操作异常 </span>
            </a>
        </li>' !!}

    </ul>
</div>
