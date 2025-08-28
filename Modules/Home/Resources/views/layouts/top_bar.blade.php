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
                                {!! empty($classify_top_nav)?'':$classify_top_nav !!}
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
            </div>

            @include('home::layouts.notice')

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
