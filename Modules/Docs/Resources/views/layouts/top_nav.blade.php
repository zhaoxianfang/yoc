<!-- 顶部导航栏 -->
<header class="header">
    <div class="header-left">
        <div class="mobile-menu-btn" id="sidebarToggle">
                <span class="tooltip">
                    ☰
                    <span class="tooltip-text">切换菜单</span>
                </span>
        </div>
        <div class="logo">
            <a href="/" class="logo-icon"title="返回整站首页" data-tips="right">
                <img src="{{ asset('static/images/logo/logo.png') }}" style="width: 25px;height: 25px;margin-bottom: -4px;" alt="Docs Logo">
            </a>
            <span id="logoText" class="hidden"></span>
            <a href="/docs/" id="logoText-0" title="返回文档首页" data-tips="bottom">{{ $docs_app->app_name ??  config('app.name','威四方') . '在线文档'}}</a>
        </div>
        <ul class="nav-tabs" id="navTabs">
            {{-- .nav-tab-item 触发菜单切换 --}}
            @section('top_nav_tabs')
                @hasSection('top_nav_tabs')
                @endif
                @sectionMissing('top_nav_tabs')
                {{-- <li class="nav-tab nav-tab-item active" data-category="guide">指南</li> --}}
                {{-- <li class="nav-tab nav-tab-item" data-category="api">API</li> --}}
                {{-- <li class="nav-tab nav-tab-item data-category="faq">常见问题</li> --}}
                @endif
            @show

        </ul>
    </div>
    <div class="header-right">
        <div class="search-container" id="searchContainer">
            <input type="text" class="search-input" id="searchInput" autocomplete="off" placeholder="搜索文档...">
            <div class="search-results" id="searchResults"></div>
        </div>
        <div class="dropdown">
            <div class="action-btn" id="themeToggle">
                🌓
            </div>
        </div>
        <div class="dropdown">
            <div class="action-btn tooltip" id="languageToggle">
                🌐
                <span class="tooltip-text">切换语言</span>
                <span class="badge" id="langBadge">CN</span>
            </div>
            <div class="dropdown-menu" id="languageMenu">
                <div class="dropdown-item" data-lang="zh-CN">
                    <span>🇨🇳</span> 简体中文
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item" data-lang="en-US">
                    <span>🇺🇸</span> English
                </div>
            </div>
        </div>
        <img src="{{auth()->guest()?'/static/images/logo/logo.jpg':auth('web')->user()->cover??'/static/images/logo.jpg'}}" alt="用户头像" class="avatar" id="userAvatar">
        <div class="mobile-menu-btn header-right-menu-btn" id="headerMenuToggle">☰</div>
        <div class="user-card" id="userCard">
            <div class="user-card-header">
                <img src="{{auth()->guest()?'/static/images/logo/logo.jpg':auth('web')->user()->cover??'/static/images/logo.jpg'}}" alt="用户头像" class="user-card-avatar">
                <div>
                    <div class="user-card-name">{{ auth('web')->guest()?'未登录':auth('web')->user()->nickname??'无名' }}</div>
                    <div class="user-card-email">wsf@example.com</div>
                </div>
            </div>
            <div class="user-card-body">
                @if (auth('web')->guest())
                    <p><i>👤</i> 登录探索新世界</p>
                @else
                    <p><i>👤</i> 人生在勤，不索何获！</p>
                    {{-- <p><i>🕒</i> 最后登录时间: 2023-05-15</p>--}}
                    <p><i>📅</i> 注册时间: {{ auth('web')->user()->created_at }}</p>
                    {{-- <p><i>🏢</i> 技术部 - 资深专家组</p>--}}
                @endif
            </div>
            <div class="user-card-footer">
                @if (auth('web')->guest())
                    <a href="{{ route('docs.auth.qq_login') }}" class="btn btn-outline-success">
                        <span><i></i> QQ登录</span>
                    </a>
                    <a href="{{ route('docs.auth.weibo_login') }}" class="btn btn-outline-success">
                        <span><i></i> 微博登录</span>
                    </a>
                    <a href="{{ route('docs.auth.login') }}" class="btn btn-outline-success">
                        <span><i></i> 账号登录</span>
                    </a>
                @else
                    <a href="{{ route('docs.auth.logout') }}" class="btn btn-outline-danger">
                        <span><i></i> 退出登录</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
