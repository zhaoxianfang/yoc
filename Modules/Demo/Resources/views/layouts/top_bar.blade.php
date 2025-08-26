<!-- Topbar Start -->
<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="index.html" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('static/inspinia/v4.0/assets/images/logo.png') }}" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-sm.png') }}" alt="small logo">
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="index.html" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-black.png') }}" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-sm.png') }}" alt="small logo">
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-primary btn-icon">
                <i class="ti ti-menu-4 fs-22"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ti ti-menu-4 fs-22"></i>
            </button>

            <!-- Search -->
            <div class="app-search d-none d-xl-flex">
                <input type="search" class="form-control topbar-search" name="search" placeholder="搜索...">
                <i data-lucide="search" class="app-search-icon text-muted"></i>
            </div>

            <!-- Mega Menu Dropdown -->
            <div class="topbar-item d-none d-md-flex">
                <div class="dropdown">
                    <button class="topbar-link btn fw-medium btn-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,16" type="button" aria-haspopup="false" aria-expanded="false">
                        Boom Boom! 😍<i class="ti ti-chevron-down ms-1"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-xxl p-0">
                        <div class="h-100" style="max-height: 380px;" data-simplebar>
                            <div class="row g-0">
                                <div class="col-12">
                                    <div class="p-3 text-center bg-light bg-opacity-50">
                                        <h4 class="mb-0 fs-lg fw-semibold">Welcome to <span class="text-primary">INSPINIA+</span> Admin Theme.</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h5 class="mb-2 fw-semibold fs-sm dropdown-header">Dashboard & Analytics</h5>
                                        <ul class="list-unstyled megamenu-list">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Sales Dashboard</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Marketing Dashboard</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Finance Overview</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">User Analytics</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Traffic Insights</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Performance Metrics</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Conversion Tracking</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h5 class="mb-2 fw-semibold fs-sm dropdown-header">Project Management</h5>
                                        <ul class="list-unstyled megamenu-list">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Task Overview</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Kanban Board</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Gantt Chart</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Team Collaboration</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Project Milestones</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Workflow Automation</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Timesheets & Reports</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h5 class="mb-2 fw-semibold fs-sm dropdown-header">User Management</h5>
                                        <ul class="list-unstyled megamenu-list">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">User Profiles</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Access Control</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Role Permissions</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Activity Logs</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Security Settings</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">User Groups</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Authentication & Login</a>
                                            </li>
                                        </ul>
                                    </div> <!-- end dropdown-->
                                </div> <!-- end col-->
                            </div> <!-- end row-->

                        </div> <!-- end .h-100-->
                    </div> <!-- .dropdown-menu-->
                </div> <!-- .dropdown-->
            </div> <!-- end topbar-item -->
        </div> <!-- .d-flex-->

        <div class="d-flex align-items-center gap-2">
            <!-- Language Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link fw-bold" data-bs-toggle="dropdown" data-bs-offset="0,21" type="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/cn.svg') }}" alt="user-image" class="w-100 rounded me-1" height="18" id="selected-language-image">
                        <span id="selected-language-code"> 中文 </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="cn" title="中文">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/cn.svg') }}" alt="中文" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">中文</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="en" title="English">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/us.svg') }}" alt="English" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">English</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="de" title="German">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/de.svg') }}" alt="German" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">Deutsch</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="it" title="Italian">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/it.svg') }}" alt="Italian" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">Italiano</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="es" title="Spanish">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/es.svg') }}" alt="Spanish" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">Español</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="ru" title="Russian">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/ru.svg') }}" alt="Russian" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">Русский</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="hi" title="Hindi">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/in.svg') }}" alt="Hindi" class="me-1 rounded" height="18" data-translator-image>
                            <span class="align-middle">हिन्दी</span>
                        </a>
                    </div> <!-- end dropdown-menu-->
                </div> <!-- end dropdown-->
            </div> <!-- end topbar item-->

            <!-- Messages Dropdown -->

            <!-- Notification Dropdown -->

            <!-- Button Trigger Customizer Offcanvas -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">
                    <i data-lucide="settings" class="fs-xxl"></i>
                </button>
            </div>

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i data-lucide="moon" class="fs-xxl mode-light-moon"></i>
                    <i data-lucide="sun" class="fs-xxl mode-light-sun"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,16" href="javascript:;" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ auth('admin')->user()['user']['cover'] ?? asset('/static/images/system/default_user.png') }}" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                        <div class="d-lg-flex align-items-center gap-1 d-none">
                            <h5 class="my-0">{{ auth('admin')->check() ? auth('admin')->user()['nickname'] : '无名' }}</h5>
                            <i class="ti ti-chevron-down align-middle"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
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
    </div>
</header>
<!-- Topbar End -->
