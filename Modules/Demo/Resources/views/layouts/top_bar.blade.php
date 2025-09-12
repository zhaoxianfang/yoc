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
            @include('admin::layouts.mega_menu')
            <!-- end topbar-item -->
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
            @include('users::layouts.top_nav.nav_messages')
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
                        @include('demo::layouts.user_card')
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->
