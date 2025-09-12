<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-0">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="/" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('static/images/logo/logo.png') }}" alt="small logo">
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="/" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('static/images/logo/logo_long.png') }}" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('static/images/logo/logo.png') }}" alt="small logo">
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

            <!-- Mega Menu Dropdown -->
            <div class="topbar-item d-sm-flex">
                {!! empty($mega_menu)?'':$mega_menu !!}
            </div>

            <!-- 自定义调整的顶部菜单-->
            <header class="topnav topbar-item flex-horizontal-topnav">
                <nav class="navbar navbar-expand-md">
                    <nav class="container-fluid">
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <ul class="navbar-nav">
                                {!! empty($home_top_nav)?'':$home_top_nav !!}
                            </ul>
                        </div>
                    </nav>
                </nav>
            </header>

            <!-- Search -->
            <div class="app-search d-xl-flex flex-horizontal-top-search z-9999">
                <input type="search" class="form-control topbar-search" name="search" placeholder="搜索...">
                <i data-lucide="search" class="app-search-icon text-muted"></i>
            </div>
        </div>

        <div class="d-flex align-items-center gap-1">
            <!-- Language Dropdown -->
            @include('users::layouts.top_nav.nav_language')

            <!-- Messages Dropdown -->
            @include('users::layouts.top_nav.nav_messages')
            <!-- end topbar item-->

            <!-- Notification Dropdown -->
            @include('users::layouts.top_nav.nav_notification')

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i data-lucide="moon" class="fs-xxl mode-light-moon"></i>
                    <i data-lucide="sun" class="fs-xxl mode-light-sun"></i>
                </button>
            </div>

            @include('home::layouts.user_card')

        </div>
    </div>
</header>
