@extends('demo::layouts.demo_layout')
@section('title', !empty($title)?$title:"鼠标右键组件RightMenu")
@section('page_inner_title', !empty($title)?$title:"鼠标右键组件RightMenu库演示")

@section('head_css')

    <link rel="stylesheet" href="{{ asset('static/libs/zxf/right_menu/right_menu.min.css') }}">
    <style>
        :root {
            /* 主题色 */
            --demo-primary-color: #1677ff;
            --demo-primary-hover: #4096ff;
            --demo-primary-active: #0958d9;
            --demo-success-color: #52c41a;
            --demo-warning-color: #faad14;
            --demo-error-color: #ff4d4f;

            /* 文本色 */
            --demo-text-color: rgba(0, 0, 0, 0.88);
            --demo-text-secondary: rgba(0, 0, 0, 0.45);
            --demo-text-light: #fff;

            /* 边框和背景 */
            --demo-border-color: #d9d9d9;
            --demo-border-light: rgba(0, 0, 0, 0.06);
            --demo-bg-color: #f5f5f5;
            --demo-bg-light: #fff;
            --demo-bg-hover: rgba(0, 0, 0, 0.02);

            /* 头部 */
            --demo-header-bg: #001529;
            --demo-header-text: #fff;

            /* 代码块 */
            --demo-code-bg: rgba(0, 0, 0, 0.02);
            --demo-code-border: rgba(0, 0, 0, 0.06);

            /* 圆角 */
            --demo-radius-sm: 4px;
            --demo-radius-md: 6px;
            --demo-radius-lg: 8px;
            --demo-radius-xl: 12px;

            /* 阴影 */
            --demo-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            --demo-shadow-md: 0 4px 12px 0 rgba(0, 0, 0, 0.08);
            --demo-shadow-lg: 0 8px 24px 0 rgba(0, 0, 0, 0.12);

            /* 间距 */
            --demo-space-xs: 4px;
            --demo-space-sm: 8px;
            --demo-space-md: 16px;
            --demo-space-lg: 24px;
            --demo-space-xl: 32px;
        }

        /** {*/
        /*    box-sizing: border-box;*/
        /*    margin: 0;*/
        /*    padding: 0;*/
        /*}*/

        /*body {*/
        /*    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;*/
        /*    line-height: 1.5715;*/
        /*    color: var(--demo-text-color);*/
        /*    background-color: var(--demo-bg-light);*/
        /*    -webkit-font-smoothing: antialiased;*/
        /*}*/

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--demo-space-lg);
        }

        code{
            white-space:pre!important;
        }

        /* 头部样式 */
        header {
            background-color: var(--demo-header-bg);
            color: var(--demo-header-text);
            padding: var(--demo-space-lg) 0;
            margin-bottom: var(--demo-space-xl);
            box-shadow: var(--demo-shadow-md);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container .header-title {
            font-size: 32px;
            font-weight: 600;
            color: #fff;
            margin-bottom: var(--demo-space-sm);
        }

        .container .header-subtitle {
            font-size: 16px;
            opacity: 0.8;
        }

        /* 标题样式 */
        .container h1, .container h2, .container h3, .container h4 {
            color: var(--demo-text-color);
            font-weight: 500;
        }

        .container h1 {
            font-size: 28px;
            margin-bottom: var(--demo-space-md);
        }

        .container h2 {
            font-size: 24px;
            margin: var(--demo-space-xl) 0 var(--demo-space-md);
            padding-bottom: var(--demo-space-sm);
            border-bottom: 1px solid var(--demo-border-color);
        }

        .container h3 {
            font-size: 20px;
            margin: var(--demo-space-lg) 0 var(--demo-space-md);
        }

        .container h4 {
            font-size: 16px;
            margin: var(--demo-space-md) 0 var(--demo-space-sm);
        }

        /* 演示区域 */
        .container .demo-area {
            margin: var(--demo-space-xl) 0;
            padding: var(--demo-space-lg);
            border: 1px solid var(--demo-border-color);
            border-radius: var(--demo-radius-lg);
            background-color: var(--demo-bg-light);
            box-shadow: var(--demo-shadow-sm);
            overflow: auto;
        }

        /* Tab切换 */
        .container .demo-tabs {
            margin-top: var(--demo-space-lg);
        }

        .container .tab-header {
            display: flex;
            border-bottom: 1px solid var(--demo-border-color);
            margin-bottom: -1px;
        }

        .container .tab-btn {
            padding: var(--demo-space-sm) var(--demo-space-md);
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-size: 14px;
            color: var(--demo-text-secondary);
            transition: all 0.2s;
        }

        .container .tab-btn.active {
            color: var(--demo-primary-color);
            border-bottom-color: var(--demo-primary-color);
        }

        .container .tab-btn:hover:not(.active) {
            color: var(--demo-text-color);
        }

        .container .tab-content {
            display: none;
            padding: var(--demo-space-md) 0;
        }

        .container .tab-content.active {
            display: block;
        }

        /* 演示盒子 */
        .container .demo-box {
            padding: var(--demo-space-md);
            margin: var(--demo-space-md) 0;
            background-color: var(--demo-bg-color);
            border-radius: var(--demo-radius-md);
            cursor: context-menu;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .container .demo-box:hover {
            background-color: var(--demo-bg-hover);
            border-color: var(--demo-border-light);
        }

        .container .demo-box.special {
            background-color: #e6f4ff;
            border: 1px dashed #91caff;
        }

        .container .demo-box.special:hover {
            background-color: #d0e6ff;
        }

        /* 代码块 */
        .container .code-block {
            position: relative;
            background-color: var(--demo-code-bg);
            border-radius: var(--demo-radius-md);
            border: 1px solid var(--demo-code-border);
            overflow: hidden;
            margin: var(--demo-space-md) 0;
        }

        .container .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--demo-space-sm) var(--demo-space-md);
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid var(--demo-code-border);
            font-size: 14px;
        }

        .container .copy-btn {
            background: none;
            border: none;
            color: var(--demo-text-secondary);
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: var(--demo-space-xs);
        }

        .container .copy-btn:hover {
            color: var(--demo-primary-color);
        }

        .container pre {
            padding: var(--demo-space-md);
            overflow-x: auto;
            font-size: 14px;
            line-height: 1.5715;
            margin: 0;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        }

        /* 按钮组 */
        .container .btn-group {
            display: flex;
            margin: var(--demo-space-md) 0;
            gap: var(--demo-space-md);
        }

        .container .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--demo-space-sm) var(--demo-space-md);
            background-color: var(--demo-primary-color);
            color: var(--demo-text-light);
            border: none;
            border-radius: var(--demo-radius-md);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            height: 32px;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.02);
        }

        .container .btn:hover {
            background-color: var(--demo-primary-hover);
            transform: translateY(-1px);
        }

        .container .btn:active {
            background-color: var(--demo-primary-active);
        }

        .container .btn-outline {
            background-color: transparent;
            border: 1px solid var(--demo-primary-color);
            color: var(--demo-primary-color);
        }

        .container .btn-outline:hover {
            background-color: rgba(22, 119, 255, 0.1);
        }

        /* 特性网格 */
        .container .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--demo-space-md);
            margin: var(--demo-space-lg) 0;
        }

        .container .feature-card {
            padding: var(--demo-space-lg);
            border-radius: var(--demo-radius-md);
            background-color: var(--demo-bg-light);
            border: 1px solid var(--demo-border-color);
            transition: all 0.2s;
        }

        .container .feature-card:hover {
            box-shadow: var(--demo-shadow-md);
            transform: translateY(-8px);
            border-color: var(--demo-primary-color);
        }

        .container .feature-card h4 {
            margin-top: 0;
            color: var(--demo-primary-color);
            display: flex;
            align-items: center;
        }

        .container .feature-card h4::before {
            content: "✓";
            margin-right: var(--demo-space-sm);
            color: var(--demo-success-color);
        }

        /* 移动端提示 */
        .container .mobile-tip {
            display: none;
            padding: var(--demo-space-md);
            background-color: #e6f7ff;
            border-radius: var(--demo-radius-md);
            border-left: 4px solid var(--demo-primary-color);
            margin: var(--demo-space-md) 0;
        }

        /* 表格 */
        .container .api-table {
            width: 100%;
            border-collapse: collapse;
            margin: var(--demo-space-lg) 0;
            font-size: 14px;
        }

        .container .api-table th, .container .api-table td {
            padding: var(--demo-space-md);
            text-align: left;
            border-bottom: 1px solid var(--demo-border-color);
        }

        .container .api-table th {
            background-color: var(--demo-bg-color);
            font-weight: 500;
            white-space: nowrap;
        }

        .container .api-table tr:hover td {
            background-color: var(--demo-bg-hover);
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .container {
                padding: 0 var(--demo-space-md);
            }

            header {
                padding: var(--demo-space-md) 0;
            }

            .container .header-title {
                font-size: 24px;
            }

            .container h2 {
                font-size: 20px;
            }

            .container .demo-area {
                padding: var(--demo-space-md);
            }

            .container .mobile-tip {
                display: block;
            }

            .container .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        /* 代码高亮 */
        .token.comment,
        .token.prolog,
        .token.doctype,
        .token.cdata {
            color: #6a9955;
        }

        .token.punctuation {
            color: #d4d4d4;
        }

        .token.property,
        .token.tag,
        .token.boolean,
        .token.number,
        .token.constant,
        .token.symbol,
        .token.deleted {
            color: #b5cea8;
        }

        .token.selector,
        .token.attr-name,
        .token.string,
        .token.char,
        .token.builtin,
        .token.inserted {
            color: #ce9178;
        }

        .token.operator,
        .token.entity,
        .token.url,
        .language-css .token.string,
        .style .token.string {
            color: #d4d4d4;
        }

        .token.atrule,
        .token.attr-value,
        .token.keyword {
            color: #569cd6;
        }

        .token.function,
        .token.class-name {
            color: #dcdcaa;
        }

        .token.regex,
        .token.important,
        .token.variable {
            color: #d16969;
        }
    </style>
@endsection

@section('content')
<header>
    <div class="container header-content">
        <div>
            <h1 class="header-title">RightMenu 组件</h1>
            <p class="header-subtitle">鼠标右键/长按菜单解决方案</p>
        </div>
    </div>
</header>

<main class="container">
    <section>
        <h2>核心特性</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h4>跨平台支持</h4>
                <p>PC端使用右键触发，移动端使用长按触发，完美适配不同设备</p>
            </div>
            <div class="feature-card">
                <h4>多级菜单</h4>
                <p>支持无限级子菜单，智能定位，自动判断显示位置</p>
            </div>
            <div class="feature-card">
                <h4>精致动画</h4>
                <p>菜单弹出带有平滑动画效果，提升用户体验</p>
            </div>
            <div class="feature-card">
                <h4>灵活配置</h4>
                <p>支持为不同元素配置不同菜单，支持动态更新菜单</p>
            </div>
            <div class="feature-card">
                <h4>完整回调</h4>
                <p>回调函数提供触发元素和菜单项完整信息</p>
            </div>
            <div class="feature-card">
                <h4>移动优化</h4>
                <p>专为移动端优化的交互体验和视觉效果</p>
            </div>
        </div>
    </section>

    <section class="demo-area">
        <h2>基本用法</h2>
        <p>右键点击下方元素或长按(移动端)查看效果：</p>

        <div class="demo-box" id="demo1">右键点击我或长按我(移动端)</div>

        <div class="mobile-tip">
            在移动设备上，请长按上方元素1.4秒触发菜单
        </div>

        <div class="demo-tabs">
            <div class="tab-header">
                <button class="tab-btn active" data-tab="demo1-code">代码</button>
                <button class="tab-btn" data-tab="demo1-desc">说明</button>
            </div>
            <div class="tab-content active" id="demo1-code">
                <div class="code-block">
                    <div class="code-header">
                        <span>JavaScript</span>
                        <button class="copy-btn" data-target="demo1-code-content">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            复制
                        </button>
                    </div>
                    <pre id="demo1-code-content"><code class="language-javascript">// 初始化 RightMenu
const rightMenu = new RightMenu({
  // 指定作用元素选择器
  selector: '#demo1',

  // 设置菜单项宽度
  itemWidth: 160,

  // 菜单配置数组
  menus: [
    {
      text: '刷新',
      icon: '&#x21bb;',
      callback: (el, menu, target) => {
        console.log('触发元素:', el);
        console.log('菜单项:', menu);
        console.log('触发元素 dom', target);
        alert(`刷新操作 - 元素ID: ${target.id}`);
      }
    },
    {
      text: '编辑',
      icon: '&#x270E;',
      children: [
        {
          text: '复制',
          icon: '&#x1F4CB;',
          callback: (el, menu, target) => {
            console.log('元素数据:', target.dataset);
            alert('复制操作');
          }
        },
        {
          text: '粘贴',
          icon: '&#x1F4CE;',
          callback: (el, menu, target) => alert('粘贴操作')
        }
      ]
    },
    'divider', // 分割线
    {
      text: '删除',
      icon: '&#x1F5D1;',
      callback: (el, menu, target) => alert('删除操作')
    }
  ]
});</code></pre>
                </div>
            </div>
            <div class="tab-content" id="demo1-desc">
                <h4>功能说明</h4>
                <ul style="margin-left: 20px; margin-bottom: 16px;">
                    <li>这是一个基本用法示例，展示如何为一个元素配置右键/长按菜单</li>
                    <li>菜单包含多级结构，支持图标显示和分割线</li>
                    <li>回调函数可以获取触发元素和菜单项的完整信息</li>
                    <li>在移动设备上会自动切换为长按触发方式</li>
                </ul>

                <h4>参数说明</h4>
                <ul style="margin-left: 20px;">
                    <li><strong>selector</strong>: 指定要绑定菜单的元素选择器</li>
                    <li><strong>itemWidth</strong>: 设置菜单项的宽度(px)</li>
                    <li><strong>menus</strong>: 菜单配置数组，可以包含子菜单和分割线</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="demo-area">
        <h2>多元素不同菜单</h2>
        <p>为不同元素配置不同的菜单：</p>

        <div class="demo-box" id="demo2" data-id="item-1" data-name="项目一">项目 1：三级菜单 (右键或长按)</div>
        <div class="demo-box special" data-id="item-2" data-name="项目二">项目 2 (特殊菜单)</div>

        <div class="demo-tabs">
            <div class="tab-header">
                <button class="tab-btn active" data-tab="multi-code">代码</button>
                <button class="tab-btn" data-tab="multi-desc">说明</button>
            </div>
            <div class="tab-content active" id="multi-code">
                <div class="code-block">
                    <div class="code-header">
                        <span>JavaScript</span>
                        <button class="copy-btn" data-target="multi-code-content">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            复制
                        </button>
                    </div>
                    <pre id="multi-code-content"><code class="language-javascript">// 为普通元素创建菜单
const multiMenu = new RightMenu({
      selector: '#demo2',
      menus: [
        {
          text: '基础操作',
          children: [
            {
              text: '查看详情',
              children: [
                {
                  text: '分享',
                  callback: (el, menu, target) => alert(`分享 ${target.dataset.name}`)
                },
                {
                  text: '导出',
                  callback: (el, menu, target) => alert(`导出 ${target.dataset.name}`)
                }
              ]
            },
            {
              text: '编辑内容',
              callback: (el, menu, target) => alert(`编辑 ${target.dataset.name}`)
            }
          ]
        },
        'divider', // 分割线
        {
          text: '更多操作',
          children: [
            {
              text: '分享',
              callback: (el, menu, target) => alert(`分享 ${target.dataset.name}`)
            },
            {
              text: '导出',
              callback: (el, menu, target) => alert(`导出 ${target.dataset.name}`)
            }
          ]
        }
      ]
    });

// 为特殊元素创建独立菜单
const specialMenu = new RightMenu({
  selector: '.demo-box.special',
  menus: [
    {
      text: '特殊操作',
      icon: '&#x2728;',
      children: [
        {
          text: '高级设置',
          callback: (el, menu, target) => alert(`高级设置 ${target.dataset.name}`)
        },
        {
          text: '数据分析',
          callback: (el, menu, target) => alert(`分析 ${target.dataset.name}`)
        }
      ]
    },
    'divider',
    {
      text: '删除项目',
      icon: '&#x1F5D1;',
      callback: (el, menu, target) => alert(`删除 ${target.dataset.name}`)
    }
  ]
});</code></pre>
                </div>
            </div>
            <div class="tab-content" id="multi-desc">
                <h4>功能说明</h4>
                <ul style="margin-left: 20px; margin-bottom: 16px;">
                    <li>展示如何为不同元素配置不同的菜单</li>
                    <li>普通元素和特殊元素有各自的菜单配置</li>
                    <li>通过dataset可以访问元素的自定义属性</li>
                    <li>菜单项支持图标和分割线</li>
                </ul>

                <h4>实现要点</h4>
                <ul style="margin-left: 20px;">
                    <li>使用不同的选择器为不同元素创建独立的RightMenu实例</li>
                    <li>通过<code>:not()</code>选择器排除特殊元素</li>
                    <li>回调函数中可以访问元素的dataset获取自定义数据</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="demo-area">
        <h2>动态更新菜单</h2>
        <p>点击下方按钮更新菜单配置：</p>

        <div class="demo-box" id="dynamic-demo" data-role="demo">右键点击我查看动态菜单</div>
        <div class="btn-group">
            <button id="update-menu-btn" class="btn">更新菜单</button>
            <button id="reset-menu-btn" class="btn btn-outline">重置菜单</button>
        </div>

        <div class="demo-tabs">
            <div class="tab-header">
                <button class="tab-btn active" data-tab="dynamic-code">代码</button>
                <button class="tab-btn" data-tab="dynamic-desc">说明</button>
            </div>
            <div class="tab-content active" id="dynamic-code">
                <div class="code-block">
                    <div class="code-header">
                        <span>JavaScript</span>
                        <button class="copy-btn" data-target="dynamic-code-content">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            复制
                        </button>
                    </div>
                    <pre id="dynamic-code-content"><code class="language-javascript">// 初始化动态菜单
const dynamicMenu = new RightMenu({
  selector: '#dynamic-demo',
  menus: [
    {
      text: '初始菜单',
      callback: (el, menu, target) => alert(`初始菜单 - 角色: ${target.dataset.role}`)
    }
  ]
});

// 更新菜单
document.getElementById('update-menu-btn').addEventListener('click', () => {
  dynamicMenu.updateMenus([
    {
      text: '更新后的菜单',
      icon: '&#x1F504;',
      children: [
        {
          text: '新功能一',
          callback: (el, menu, target) => alert(`新功能一 - ${target.dataset.role}`)
        },
        {
          text: '新功能二',
          callback: (el, menu, target) => alert(`新功能二 - ${target.dataset.role}`)
        }
      ]
    },
    {
      text: '设置',
      icon: '&#x2699;',
      callback: (el, menu, target) => alert(`设置 - ${target.dataset.role}`)
    }
  ]);

  alert('菜单已更新，请右键点击测试元素查看新菜单');
});

// 重置菜单
document.getElementById('reset-menu-btn').addEventListener('click', () => {
  dynamicMenu.updateMenus([
    {
      text: '重置后的菜单',
      callback: (el, menu, target) => alert(`菜单已重置 - ${target.dataset.role}`)
    }
  ]);
});</code></pre>
                </div>
            </div>
            <div class="tab-content" id="dynamic-desc">
                <h4>功能说明</h4>
                <ul style="margin-left: 20px; margin-bottom: 16px;">
                    <li>展示如何动态更新菜单配置</li>
                    <li>点击"更新菜单"按钮会加载新的菜单配置</li>
                    <li>点击"重置菜单"按钮会恢复初始菜单</li>
                    <li>菜单更新后会立即生效</li>
                </ul>

                <h4>实现要点</h4>
                <ul style="margin-left: 20px;">
                    <li>使用<code>updateMenus()</code>方法动态更新菜单配置</li>
                    <li>可以随时切换不同的菜单配置</li>
                    <li>适合需要根据应用状态动态改变菜单的场景</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="demo-area">
        <h2>组件API</h2>

        <h3>配置选项</h3>
        <table class="api-table">
            <thead>
            <tr>
                <th>参数</th>
                <th>说明</th>
                <th>类型</th>
                <th>默认值</th>
                <th>必填</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><span class="highlight">selector</span></td>
                <td>作用区元素选择器</td>
                <td>string</td>
                <td>-</td>
                <td>是</td>
            </tr>
            <tr>
                <td><span class="highlight">itemWidth</span></td>
                <td>菜单项宽度(px)</td>
                <td>number</td>
                <td>160</td>
                <td>否</td>
            </tr>
            <tr>
                <td><span class="highlight">longPressTime</span></td>
                <td>移动端长按触发时间(ms)</td>
                <td>number</td>
                <td>1400</td>
                <td>否</td>
            </tr>
            <tr>
                <td><span class="highlight">zIndex</span></td>
                <td>菜单的z-index值</td>
                <td>number</td>
                <td>9999</td>
                <td>否</td>
            </tr>
            <tr>
                <td><span class="highlight">animationDuration</span></td>
                <td>动画持续时间(ms)</td>
                <td>number</td>
                <td>200</td>
                <td>否</td>
            </tr>
            <tr>
                <td><span class="highlight">menus</span></td>
                <td>菜单配置数组</td>
                <td>Array</td>
                <td>-</td>
                <td>是</td>
            </tr>
            </tbody>
        </table>

        <h3>菜单项配置</h3>
        <table class="api-table">
            <thead>
            <tr>
                <th>参数</th>
                <th>说明</th>
                <th>类型</th>
                <th>示例</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><span class="highlight">text</span></td>
                <td>菜单文本</td>
                <td>string</td>
                <td>'刷新'</td>
            </tr>
            <tr>
                <td><span class="highlight">icon</span></td>
                <td>菜单图标(unicode字符)</td>
                <td>string</td>
                <td>'&#x21bb;'</td>
            </tr>
            <tr>
                <td><span class="highlight">children</span></td>
                <td>子菜单数组</td>
                <td>Array</td>
                <td>[{ text: '子菜单' }]</td>
            </tr>
            <tr>
                <td><span class="highlight">callback</span></td>
                <td>点击回调函数</td>
                <td>Function</td>
                <td>(el, menu, target) => {}</td>
            </tr>
            </tbody>
        </table>

        <h4>回调函数参数说明</h4>
        <ul style="margin-left: 20px; margin-bottom: 16px;">
            <li><span class="highlight">el</span>: 被右键点击或长按的DOM元素，可以直接访问其属性如target.id、target.dataset等</li>
            <li><span class="highlight">menu</span>: 触发回调的菜单项对象，包含text、icon等配置信息</li>
        </ul>

        <h3>公共方法</h3>
        <table class="api-table">
            <thead>
            <tr>
                <th>方法名</th>
                <th>说明</th>
                <th>参数</th>
                <th>示例</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><span class="highlight">updateMenus(menus)</span></td>
                <td>更新菜单配置</td>
                <td>menus: Array</td>
                <td>updateMenus([{ text: '新菜单' }])</td>
            </tr>
            <tr>
                <td><span class="highlight">destroy()</span></td>
                <td>销毁组件</td>
                <td>无</td>
                <td>destroy()</td>
            </tr>
            </tbody>
        </table>
    </section>
</main>

@endsection

@section('page_js')

<script src="{{ asset('static/libs/zxf/right_menu/right_menu.min.js') }}" charset="utf-8"></script>

<script>
    // Tab切换功能
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            const tabContainer = btn.closest('.demo-tabs');

            // 更新按钮状态
            tabContainer.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // 更新内容状态
            tabContainer.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        });
    });

    // 复制代码功能
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const codeContent = document.getElementById(targetId).textContent;

            if (navigator.clipboard) {
                // 使用 Clipboard API
                navigator.clipboard.writeText(codeContent)
                    .then(() => {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> 已复制';

                        setTimeout(() => {
                            btn.innerHTML = originalText;
                        }, 2000);
                    })
                    .catch((err) => {
                        console.error("无法复制文本: ", err);
                    });
            } else {
                // 降级到 execCommand 方法
                const textarea = document.createElement("textarea");
                textarea.value = codeContent;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand("copy");

                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> 已复制';

                    setTimeout(() => {
                        btn.innerHTML = originalText;
                    }, 2000);
                } catch (err) {
                    console.error("无法复制文本: ", err);
                }
                document.body.removeChild(textarea);
            }
        });
    });

    // 基本用法
    const rightMenu = new RightMenu({
        selector: '#demo1',
        itemWidth: 160,
        menus: [
            {
                text: '刷新',
                icon: '&#x21bb;',
                callback: (el, menu, target) => {
                    console.log('触发元素:', el);
                    console.log('菜单项:', menu);
                    console.log('触发元素 dom', target);
                    alert(`刷新操作 - 元素ID: ${target.id}`);
                }
            },
            {
                text: '编辑',
                icon: '&#x270E;',
                children: [
                    {
                        text: '复制',
                        icon: '&#x1F4CB;',
                        callback: (el, menu, target) => {
                            console.log('元素数据:', target.dataset);
                            alert('复制操作');
                        }
                    },
                    {
                        text: '粘贴',
                        icon: '&#x1F4CE;',
                        callback: (el, menu, target) => alert('粘贴操作')
                    }
                ]
            },
            'divider',
            {
                text: '删除',
                icon: '&#x1F5D1;',
                callback: (el, menu, target) => alert('删除操作')
            }
        ]
    });

    // 多元素不同菜单
    const multiMenu = new RightMenu({
        selector: '#demo2',
        itemWidth: 120,
        menus: [
            {
                text: '基础操作',
                children: [
                    {
                        text: '查看详情',
                        children: [
                            {
                                text: '分享',
                                callback: (el, menu, target) => alert(`分享 ${target.dataset.name}`)
                            },
                            {
                                text: '导出',
                                callback: (el, menu, target) => alert(`导出 ${target.dataset.name}`)
                            }
                        ]
                    },
                    {
                        text: '编辑内容',
                        callback: (el, menu, target) => alert(`编辑 ${target.dataset.name}`)
                    }
                ]
            },
            'divider', // 分割线
            {
                text: '更多操作',
                children: [
                    {
                        text: '分享',
                        callback: (el, menu, target) => alert(`分享 ${target.dataset.name}`)
                    },
                    {
                        text: '导出',
                        callback: (el, menu, target) => alert(`导出 ${target.dataset.name}`)
                    }
                ]
            }
        ]
    });

    const specialMenu = new RightMenu({
        selector: '.demo-box.special',
        menus: [
            {
                text: '特殊操作',
                icon: '&#x2728;',
                children: [
                    {
                        text: '高级设置',
                        callback: (el, menu, target) => alert(`高级设置 ${target.dataset.name}`)
                    },
                    {
                        text: '数据分析',
                        callback: (el, menu, target) => alert(`分析 ${target.dataset.name}`)
                    }
                ]
            },
            'divider',
            {
                text: '删除项目',
                icon: '&#x1F5D1;',
                callback: (el, menu, target) => alert(`删除 ${target.dataset.name}`)
            }
        ]
    });

    // 动态菜单
    const dynamicMenu = new RightMenu({
        selector: '#dynamic-demo',
        menus: [
            {
                text: '初始菜单',
                callback: (el, menu, target) => alert(`初始菜单 - 角色: ${target.dataset.role}`)
            }
        ]
    });

    document.getElementById('update-menu-btn').addEventListener('click', () => {
        dynamicMenu.updateMenus([
            {
                text: '更新后的菜单',
                icon: '&#x1F504;',
                children: [
                    {
                        text: '新功能一',
                        callback: (el, menu, target) => alert(`新功能一 - ${target.dataset.role}`)
                    },
                    {
                        text: '新功能二',
                        callback: (el, menu, target) => alert(`新功能二 - ${target.dataset.role}`)
                    }
                ]
            },
            {
                text: '设置',
                icon: '&#x2699;',
                callback: (el, menu, target) => alert(`设置 - ${target.dataset.role}`)
            }
        ]);

        alert('菜单已更新，请右键点击测试元素查看新菜单');
    });

    document.getElementById('reset-menu-btn').addEventListener('click', () => {
        dynamicMenu.updateMenus([
            {
                text: '重置后的菜单',
                callback: (el, menu, target) => alert(`菜单已重置 - ${target.dataset.role}`)
            }
        ]);
    });
</script>
@endsection
