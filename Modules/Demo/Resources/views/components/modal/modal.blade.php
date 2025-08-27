@extends('demo::layouts.demo_layout')
@section('title', !empty($title)?$title:"现代化弹窗类库")
@section('page_inner_title', !empty($title)?$title:"现代化弹窗类库演示")

@section('head_css')
{{--    <link href="{{ asset('static/libs/zxf/modal/modal.min.css') }}" rel="stylesheet" type="text/css">--}}
    <style>
        /* 优化后的样式 */
        /*:root {*/
        /*    --primary-color: #2196F3;*/
        /*    --success-color: #4CAF50;*/
        /*    --warning-color: #FFC107;*/
        /*    --danger-color: #F44336;*/
        /*    --secondary-color: #6c757d;*/
        /*    --dark-color: #343a40;*/
        /*    --light-color: #f8f9fa;*/
        /*}*/

        /** {*/
        /*    box-sizing: border-box;*/
        /*}*/

        /*body {*/
        /*    font-family: 'Microsoft YaHei', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;*/
        /*    line-height: 1.6;*/
        /*    padding: 0;*/
        /*    margin: 0;*/
        /*    background-color: #f5f7fa;*/
        /*    color: #333;*/
        /*}*/

        .page-header {
            background-color: #2c3e50;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-title {
            margin: 0;
            font-size: 2.2rem;
        }

        .page-subtitle {
            margin: 0.5rem 0 0;
            font-weight: normal;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 2rem;
        }

        .demo-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .section-title {
            margin: 0;
            font-size: 1.4rem;
            color: #2c3e50;
        }

        .section-content {
            padding: 1.5rem;
        }

        .section-description {
            margin: 0 0 1.5rem;
            color: #555;
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .btn-success {
            background-color: #4CAF50;
        }

        .btn-warning {
            background-color: #FFC107;
            color: #333;
        }

        .btn-danger {
            background-color: #F44336;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-dark {
            background-color: #343a40;
        }

        .btn-light {
            background-color: #f8f9fa;
            color: #333;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #2196F3;
            color: #2196F3;
        }

        .btn-outline:hover {
            background-color: rgba(33, 150, 243, 0.1);
        }

        .code-block {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            font-family: 'Consolas', 'Monaco', monospace;
            overflow-x: auto;
            margin: 1.5rem 0;
            font-size: 0.9rem;
            border-left: 3px solid #2196F3;
        }

        .options-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.95rem;
        }

        .options-table th, .options-table td {
            border: 1px solid #ddd;
            padding: 0.75rem;
            text-align: left;
        }

        .options-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .options-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .options-table code {
            background-color: #f0f0f0;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.85em;
        }

        .tab-container {
            margin-bottom: 1.5rem;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 0;
        }

        .tab-button {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            color: #555;
            position: relative;
            margin-bottom: -1px;
        }

        .tab-button.active {
            color: #2196F3;
            font-weight: 600;
            border-bottom: 2px solid #2196F3;
        }

        .tab-content {
            display: none;
            padding: 1rem 0;
        }

        .tab-content.active {
            display: block;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .feature-card {
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border-top: 3px solid #2196F3;
        }

        .feature-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .feature-card p {
            color: #555;
            margin-bottom: 0;
        }

        .footer {
            text-align: center;
            padding: 2rem 0;
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            margin-top: 2rem;
        }

        /* 移动端优化 */
        @media (max-width: 768px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .tab-button {
                padding: 0.5rem 1rem;
            }
        }
    </style>
@endsection

@section('content')

<header class="page-header">
    <div class="header-content">
        <h1 class="page-title">现代化弹窗类库</h1>
        <p class="page-subtitle">一个功能强大、样式丰富的现代化弹窗对话框库，支持多种主题、定位选项和交互功能。</p>
    </div>
</header>

<div class="demo-container">
    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">功能介绍</h2>
        </div>
        <div class="section-content">
            <p class="section-description">现代化弹窗类库是一个轻量级、灵活且功能丰富的JavaScript库，用于创建漂亮的模态对话框和Toast通知。它基于纯JavaScript和CSS构建，无任何依赖。</p>

            <div class="feature-grid">
                <div class="feature-card">
                    <h3>多种主题</h3>
                    <p>提供多种内置主题，也可以轻松创建自定义主题。</p>
                </div>
                <div class="feature-card">
                    <h3>丰富功能</h3>
                    <p>支持最小化、最大化、拖动、自动关闭、iframe支持等多种功能。</p>
                </div>
                <div class="feature-card">
                    <h3>完全响应式</h3>
                    <p>在从移动设备到桌面的所有屏幕尺寸上都能完美工作。</p>
                </div>
                <div class="feature-card">
                    <h3>Toast通知</h3>
                    <p>包含完整的Toast通知系统，类似toastr.js。</p>
                </div>
                <div class="feature-card">
                    <h3>易于使用</h3>
                    <p>简单的API和直观的选项和方法。</p>
                </div>
                <div class="feature-card">
                    <h3>轻量级</h3>
                    <p>仅约30KB(JS+CSS压缩后)，无任何依赖。</p>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">快速开始</h2>
        </div>
        <div class="section-content">
            <p class="section-description">只需几个简单步骤即可开始使用现代化弹窗类库。</p>

            <h3>1. 引入文件</h3>
            <div class="code-block">
          <pre>&lt;!-- 在HTML文件中 --&gt;
&lt;link rel="stylesheet" href="modal.css"&gt;
&lt;script src="modal.js"&gt;&lt;/script&gt;</pre>
            </div>

            <h3>2. 创建基本弹窗</h3>
            <div class="code-block">
          <pre>// 带标题和按钮的简单弹窗
new Modal({
  title: '欢迎',
  content: '这是一个简单的弹窗对话框。',
  buttons: [
    { text: '确定', type: 'primary', click: function(e, modal) {
        console.log('点击确定按钮',e, modal)
    }}
  ]
}).open();</pre>
            </div>

            <div class="btn-group">
                <button class="btn btn-primary" onclick="demoQuickStart()">试试看</button>
            </div>

            <h3>3. 创建事件弹窗</h3>
            <div class="code-block">
          <pre>// 带事件/回调的弹窗
var modal_demo =new Modal({
  title: '带事件/回调的弹窗',
  content: '这是一个带事件/回调的弹窗。',
  bodyScroll:true, // 弹窗后是否允许父页面滚动
  position:  'center', // 弹窗位置
  overlay:  true, // 是否显示遮罩层
  overlayClose:  true, // 点击遮罩层是否关闭弹窗
  escClose:  true, // ESC键是否关闭弹窗
  buttons: [ // 操作按钮列表
    { text: '确定', type: 'primary' }
  ],
  onBeforeClose: function() {
    console.log('弹窗关闭前的回调。返回 false 可阻止关闭');
  },
  onClose: function() {
    console.log('弹窗已关闭');
  },
  onOpen: function() {
    console.log('弹窗打开时的回调, 不保证弹窗已经加载完毕');
  },
  onIframeLoad: function() {
    console.log('iframe弹窗已经加载完毕');
  },
  onDrag: function() {
    console.log('弹窗拖动');
  },
  onConfirm: function() {
    console.log('点击了确定按钮');
  },
  onMinimize: function() {
    console.log('点击了最小化按钮');
  },
  onMaximize: function() {
    console.log('点击了最大化按钮');
  },
  onRestore: function() {
      console.log('点击了还原按钮');
  },
  onFullscreen:  function() {
      console.log('点击了全屏按钮');
  },
}).open();

modal_demo.onIframeComplete((modalObj) => {
    console.log('iframe弹窗已经加载完毕 后 通过单独的onIframeComplete来多次回调',modalObj);
})
          </pre>
            </div>

            <h3>4. 显示Toast通知</h3>
            <div class="code-block">
          <pre>// 5秒后自动关闭的成功Toast
Modal.success('操作成功完成!', {
  position: 'top-right',
  timeout: 5000
});</pre>
            </div>

            <div class="btn-group">
                <button class="btn btn-success" onclick="demoQuickStartToast()">试试看</button>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">基础示例</h2>
        </div>
        <div class="section-content">
            <p class="section-description">探索基本弹窗类型和配置。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="basic-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showBasicModal()">基本弹窗</button>
                        <button class="btn" onclick="showNoTitleModal()">无标题</button>
                        <button class="btn" onclick="showNoButtonsModal()">无按钮</button>
                        <button class="btn" onclick="showTextOnlyModal()">纯文本(自动关闭)</button>
                        <button class="btn" onclick="showFormModal()">表单弹窗</button>
                    </div>
                </div>

                <div id="basic-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 基本弹窗
function showBasicModal() {
  new Modal({
    title: '基本弹窗',
    content: '这是一个带标题和默认关闭按钮的基本弹窗对话框。',
    buttons: [
      { text: '关闭', type: 'primary' }
    ]
  }).open();
}

// 无标题弹窗
function showNoTitleModal() {
  new Modal({
    content: '这个弹窗没有标题。',
    buttons: [
      { text: '确定' }
    ]
  }).open();
}

// 无按钮弹窗
function showNoButtonsModal() {
  new Modal({
    title: '无按钮',
    content: '这个弹窗没有按钮。你需要点击右上角的关闭按钮来关闭它。'
  }).open();
}

// 纯文本自动关闭弹窗
function showTextOnlyModal() {
  new Modal({
    content: '这是一个纯文本弹窗，将在3秒后自动关闭。',
    autoClose: 3000
  }).open();
}

// 表单弹窗
function showFormModal() {
  const form = document.createElement('div');
  form.innerHTML = `
    &lt;form&gt;
      &lt;div style="margin-bottom: 15px;"&gt;
        &lt;label style="display: block; margin-bottom: 5px;"&gt;用户名&lt;/label&gt;
                  &lt;input type="text" style="width: 100%; padding: 8px; box-sizing: border-box;"&gt;
      &lt;/div&gt;
      &lt;div style="margin-bottom: 15px;"&gt;
         &lt;label style="display: block; margin-bottom: 5px;"&gt;密码&lt;/label&gt;
                  &lt;input type="password" style="width: 100%; padding: 8px; box-sizing: border-box;"&gt;
      &lt;/div&gt;
    &lt;/form&gt;
  `;

  new Modal({
    title: '登录表单',
    content: form,
    buttons: [
      { text: '取消', type: 'secondary' },
      { text: '登录', type: 'primary' }
    ]
  }).open();
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">按钮变体</h2>
        </div>
        <div class="section-content">
            <p class="section-description">不同的按钮配置和布局。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="buttons-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showSingleButtonModal()">单按钮</button>
                        <button class="btn" onclick="showMultipleButtonsModal()">多按钮</button>
                        <button class="btn" onclick="showVerticalButtonsModal()">垂直按钮</button>
                        <button class="btn" onclick="showLeftAlignedButtonsModal()">左对齐</button>
                        <button class="btn" onclick="showCenterAlignedButtonsModal()">居中对齐</button>
                        <button class="btn" onclick="showLinkButtonsModal()">链接按钮</button>
                    </div>
                </div>

                <div id="buttons-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 单按钮
function showSingleButtonModal() {
  new Modal({
    title: '单按钮',
    content: '这个弹窗只有一个按钮。',
    buttons: [
      { text: '确定', type: 'primary' }
    ]
  }).open();
}

// 多按钮
function showMultipleButtonsModal() {
  new Modal({
    title: '多按钮',
    content: '这个弹窗有多个不同样式的按钮。',
    buttons: [
      { text: '主要', type: 'primary' },
      { text: '成功', type: 'success' },
      { text: '警告', type: 'warning' },
      { text: '危险', type: 'danger' },
      { text: '次要', type: 'secondary' }
    ]
  }).open();
}

// 垂直按钮
function showVerticalButtonsModal() {
  new Modal({
    title: '垂直按钮',
    content: '这个弹窗有垂直堆叠的按钮。',
    buttons: [
      { text: '第一个', type: 'primary' },
      { text: '第二个', type: 'success' },
      { text: '第三个', type: 'warning' }
    ],
    buttonsVertical: true
  }).open();
}

// 左对齐按钮
function showLeftAlignedButtonsModal() {
  new Modal({
    title: '左对齐按钮',
    content: '这个弹窗的按钮是左对齐的。',
    buttons: [
      { text: '取消', type: 'secondary' },
      { text: '保存', type: 'primary' }
    ],
    buttonsAlign: 'left'
  }).open();
}

// 居中对齐按钮
function showCenterAlignedButtonsModal() {
  new Modal({
    title: '居中对齐按钮',
    content: '这个弹窗的按钮是居中对齐的。',
    buttons: [
      { text: '否', type: 'secondary' },
      { text: '是', type: 'primary' }
    ],
    buttonsAlign: 'center'
  }).open();
}

// 链接按钮
function showLinkButtonsModal() {
  new Modal({
    title: '链接按钮',
    content: '这个弹窗有链接样式的按钮。',
    buttons: [
      { text: '跳转', href: 'https://weisifang.com', class: 'a-link', target: '_blank'},
      { text: '删除', type: 'danger' }
    ]
  }).open();
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">定位选项</h2>
        </div>
        <div class="section-content">
            <p class="section-description">将弹窗定位在屏幕上的任何位置。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="position-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showTopLeftModal()">左上角</button>
                        <button class="btn" onclick="showTopRightModal()">右上角</button>
                        <button class="btn" onclick="showBottomLeftModal()">左下角</button>
                        <button class="btn" onclick="showBottomRightModal()">右下角</button>
                        <button class="btn" onclick="showCenterModal()">居中</button>
                        <button class="btn" onclick="showTopCenterModal()">顶部居中</button>
                        <button class="btn" onclick="showBottomCenterModal()">底部居中</button>
                    </div>
                </div>

                <div id="position-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 左上角
function showTopLeftModal() {
  new Modal({
    title: '左上角',
    content: '这个弹窗位于屏幕的左上角。',
    position: 'top-left'
  }).open();
}

// 右上角
function showTopRightModal() {
  new Modal({
    title: '右上角',
    content: '这个弹窗位于屏幕的右上角。',
    position: 'top-right'
  }).open();
}

// 左下角
function showBottomLeftModal() {
  new Modal({
    title: '左下角',
    content: '这个弹窗位于屏幕的左下角。',
    position: 'bottom-left'
  }).open();
}

// 右下角
function showBottomRightModal() {
  new Modal({
    title: '右下角',
    content: '这个弹窗位于屏幕的右下角。',
    position: 'bottom-right'
  }).open();
}

// 居中(默认)
function showCenterModal() {
  new Modal({
    title: '居中',
    content: '这个弹窗位于屏幕中央(默认)。',
    position: 'center'
  }).open();
}

// 顶部居中
function showTopCenterModal() {
  new Modal({
    title: '顶部居中',
    content: '这个弹窗位于屏幕顶部居中。',
    position: 'top'
  }).open();
}

// 底部居中
function showBottomCenterModal() {
  new Modal({
    title: '底部居中',
    content: '这个弹窗位于屏幕底部居中。',
    position: 'bottom'
  }).open();
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">高级功能</h2>
        </div>
        <div class="section-content">
            <p class="section-description">探索弹窗库的高级功能。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="features-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showAutoCloseModal()">自动关闭(3秒)</button>
                        <button class="btn" onclick="showIframeModal()">Iframe弹窗</button>
                        <button class="btn" onclick="showShakeModal()">抖动效果</button>
                        <button class="btn" onclick="showDraggableModal()">可拖动</button>
                        <button class="btn" onclick="showTransparentModal()">透明</button>
                        <button class="btn" onclick="showNoOverlayModal()">无遮罩</button>
                        <button class="btn" onclick="showNoOverlayCloseModal()">遮罩不关闭</button>
                        <button class="btn" onclick="showNoEscCloseModal()">ESC不关闭</button>
                    </div>
                    <p class="section-description">在<code>Iframe</code>弹窗网页内设置<code>.layer-bottom-btns</code> 可让其内部的按钮展示在弹窗底部的操作按钮区。</p>
                </div>

                <div id="features-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 自动关闭
function showAutoCloseModal() {
  new Modal({
    title: '自动关闭',
    content: '这个弹窗将在3秒后自动关闭。',
    autoClose: 3000
  }).open();
}


// Iframe弹窗 调用方式一：
new Modal({
    title: 'Iframe弹窗',
    content: '&lt;iframe src="https://example.com" style="width: 100%; height: 300px;"&gt;&lt;/iframe&gt;',
    width: 600,
    height: 400,
    bodyScroll:false,
    on... // 更多参数 或者监听 on 事件
}).open();

// Iframe弹窗 调用方式二：
Modal.iframe('Iframe弹窗', 'https://example.com', 600, 400);

// Iframe弹窗 并处理iframe 完全加载完成事件：
let eventModal = Modal.iframe('Iframe弹窗', '/demo/modal/iframe-content', 600, 400);

eventModal.onIframeComplete((_modal) => {
    console.log('complete 第一个事件',_modal);
}).onIframeComplete((_modal) => {
    console.log('complete 第二个事件',_modal);
});

// 抖动效果
function showShakeModal() {
  new Modal({
    title: '抖动效果',
    content: '这个弹窗打开时会抖动。',
    shake: true
  }).open();
}

// 可拖动弹窗
function showDraggableModal() {
  new Modal({
    title: '可拖动弹窗',
    content: '尝试通过标题栏拖动这个弹窗。',
    draggable: true
  }).open();
}

// 透明弹窗
function showTransparentModal() {
  new Modal({
    title: '透明弹窗',
    content: '这个弹窗有半透明背景。',
    transparent: true
  }).open();
}

// 无遮罩
function showNoOverlayModal() {
  new Modal({
    title: '无遮罩',
    content: '这个弹窗没有遮罩层。',
    overlay: false
  }).open();
}

// 遮罩不关闭
function showNoOverlayCloseModal() {
  new Modal({
    title: '遮罩不关闭',
    content: '这个弹窗有遮罩层但点击它不会关闭弹窗。',
    overlayClose: false
  }).open();
}

// ESC不关闭
function showNoEscCloseModal() {
  new Modal({
    title: 'ESC不关闭',
    content: '这个弹窗不能用ESC键关闭。',
    escClose: false
  }).open();
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">窗口控制</h2>
        </div>
        <div class="section-content">
            <p class="section-description">使用最小化、最大化和全屏选项控制弹窗窗口。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="controls-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showMinimizableModal()">可最小化</button>
                        <button class="btn" onclick="showMaximizableModal()">可最大化</button>
                        <button class="btn" onclick="showFullscreenModal()">全屏</button>
                        <button class="btn" onclick="showFixedSizeModal()">固定尺寸</button>
                        <button class="btn" onclick="showMultipleModals()">多个弹窗</button>
                        <button class="btn btn-success" onclick="Modal.closeAll()">关闭所有</button>
                        <button class="btn btn-warning" onclick="Modal.minimizeAll()">最小化所有</button>
                        <button class="btn btn-secondary" onclick="Modal.restoreAll()">恢复所有</button>
                    </div>
                </div>

                <div id="controls-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 可最小化弹窗
function showMinimizableModal() {
  new Modal({
    title: '可最小化弹窗',
    content: '这个弹窗可以最小化到左下角。',
    buttons: [
      { text: '最小化', click: function(e, modal) { modal.minimize(); return 'keep-open'; } },
      { text: '关闭' }
    ]
  }).open();
}

// 可最大化弹窗
function showMaximizableModal() {
  new Modal({
    title: '可最大化弹窗',
    content: '这个弹窗可以最大化以占据更多屏幕空间。',
    buttons: [
      { text: '最大化', click: function(e, modal) { modal.maximize(); return 'keep-open'; } },
      { text: '关闭' }
    ]
  }).open();
}

// 全屏弹窗
function showFullscreenModal() {
  new Modal({
    title: '全屏弹窗',
    content: '这个弹窗可以全屏显示。',
    buttons: [
      { text: '全屏', click: function(e, modal) { modal.fullscreen(); return 'keep-open'; } },
      { text: '关闭' }
    ]
  }).open();
}

// 固定尺寸弹窗
function showFixedSizeModal() {
  new Modal({
    title: '固定尺寸弹窗',
    content: '这个弹窗有固定的宽度和高度。',
    width: 400,
    height: 300
  }).open();
}

// 多个弹窗
function showMultipleModals() {
  new Modal({
    title: '第一个弹窗',
    content: '这是第一个弹窗。',
    position: 'top-left',
    width: 300
  }).open();

  new Modal({
    title: '第二个弹窗',
    content: '这是第二个弹窗。',
    position: 'top-right',
    width: 300
  }).open();

  new Modal({
    title: '第三个弹窗',
    content: '这是第三个弹窗。',
    position: 'bottom-left',
    width: 300
  }).open();

  new Modal({
    title: '第四个弹窗',
    content: '这是第四个弹窗。',
    position: 'bottom-right',
    width: 300
  }).open();
}

// 静态方法
Modal.closeAll(); // 关闭所有打开的弹窗
Modal.minimizeAll(); // 最小化所有打开的弹窗
Modal.restoreAll(); // 恢复所有最小化的弹窗</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">主题样式</h2>
        </div>
        <div class="section-content">
            <p class="section-description">从几种内置颜色主题中选择。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="themes-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showDefaultThemeModal()">默认</button>
                        <button class="btn btn-primary" onclick="showPrimaryThemeModal()">主要</button>
                        <button class="btn btn-success" onclick="showSuccessThemeModal()">成功</button>
                        <button class="btn btn-warning" onclick="showWarningThemeModal()">警告</button>
                        <button class="btn btn-danger" onclick="showDangerThemeModal()">危险</button>
                        <button class="btn btn-dark" onclick="showDarkThemeModal()">暗黑</button>
                    </div>
                </div>

                <div id="themes-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 默认主题
function showDefaultThemeModal() {
  new Modal({
    title: '默认主题',
    content: '这个弹窗使用默认主题。',
    theme: 'default'
  }).open();
}

// 主要主题
function showPrimaryThemeModal() {
  new Modal({
    title: '主要主题',
    content: '这个弹窗使用主要主题。',
    theme: 'primary'
  }).open();
}

// 成功主题
function showSuccessThemeModal() {
  new Modal({
    title: '成功主题',
    content: '这个弹窗使用成功主题。',
    theme: 'success',
    buttons: [
      { text: '确定', type: 'success' }
    ]
  }).open();
}

// 警告主题
function showWarningThemeModal() {
  new Modal({
    title: '警告主题',
    content: '这个弹窗使用警告主题。',
    theme: 'warning',
    buttons: [
      { text: '确定', type: 'warning' }
    ]
  }).open();
}

// 危险主题
function showDangerThemeModal() {
  new Modal({
    title: '危险主题',
    content: '这个弹窗使用危险主题。',
    theme: 'danger',
    buttons: [
      { text: '确定', type: 'danger' }
    ]
  }).open();
}

// 暗黑主题
function showDarkThemeModal() {
  new Modal({
    title: '暗黑主题',
    content: '这个弹窗使用暗黑主题。',
    theme: 'dark',
    buttons: [
      { text: '确定', type: 'secondary' }
    ]
  }).open();
}</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">Form弹窗</h2>
        </div>
        <div class="section-content">
            <p class="section-description">内置Form弹窗，可以使用表单进行交互，验证表单、获取表单数据。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="themes-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn" onclick="showFormBaseModal()">基本表单</button>
                        <button class="btn btn-primary" onclick="showFormDarkThemeModal()">多选表单</button>
                        <button class="btn btn-success" onclick="showFormComplexModal()">复杂表单</button>
                    </div>
                </div>

                <div id="themes-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 1. 基本表单示例
function showFormBaseModal() {
    Modal.form({
        title: '用户注册表单',
        width: 400,
        labelAlign: 'top', // 标签左对齐
        labelWidth: 120, // 标签宽度120px
        darkTheme: true, // 使用暗黑主题
        fields: [
            {
                type: 'text',
                name: 'username',
                label: '用户名',
                placeholder: '请输入您的用户名',
                required: true,
                default: '默认用户'
            },
            {
                type: 'password',
                name: 'password',
                label: '密码',
                placeholder: '请输入密码',
                required: true
            },
            {
                type: 'email',
                name: 'email',
                label: '电子邮箱',
                placeholder: '请输入电子邮箱',
                required: true
            },
            {
                type: 'select',
                name: 'gender',
                label: '性别',
                options: [
                    { value: 'male', label: '男' },
                    { value: 'female', label: '女' }
                ],
                default: 'male'
            }
        ],
        onSubmit: (formData, modal) => {
            console.log('表单数据:', formData);
            Modal.success('注册成功！');
            modal.close();
        }
    }).open();
}

// 2. 多选表单示例
function showFormDarkThemeModal() {
    Modal.form({
        title: '多选表单示例',
        width: 600,
        labelAlign: 'left', // 标签顶部对齐
        darkTheme: false, // 使用暗黑主题
        fields: [
            {
                type: 'select',
                name: 'hobbies',
                label: '兴趣爱好',
                multiple: true, // 启用多选
                options: [
                    { value: 'reading', label: '阅读' },
                    { value: 'music', label: '音乐' },
                    { value: 'sports', label: '运动' },
                    { value: 'travel', label: '旅行' },
                    { value: 'games', label: '游戏' }
                ],
                default: ['reading', 'music'] // 默认选中项
            },
            {
                type: 'checkbox',
                name: 'notifications',
                label: '接收通知方式',
                options: [
                    { value: 'email', label: '电子邮件' },
                    { value: 'sms', label: '短信' },
                    { value: 'push', label: '推送通知' }
                ],
                default: ['email', 'push']
            }
        ],
        buttons: [
            {
                text: '取消',
                type: 'secondary',
                close: true
            },
            {
                text: '提交',
                type: 'primary',
                click: (e, modal) => {
                    const formData = modal.getFormData();
                    if (modal.validateForm()) {
                        console.log('提交数据:', formData);
                        Modal.success('提交成功！');
                        modal.close();
                    }
                    return 'keep-open';
                }
            }
        ]
    }).open();
}

// 3. 复杂表单示例
function showFormComplexModal() {
    Modal.form({
        title: '产品信息表单',
        width: 600,
        labelAlign: 'left',
        labelWidth: '20%', // 使用百分比宽度
        fields: [
            {
                type: 'text',
                name: 'product_name',
                label: '产品名称',
                placeholder: '请输入产品名称',
                required: true
            },
            {
                type: 'number',
                name: 'price',
                label: '产品价格',
                placeholder: '请输入价格',
                required: true,
                default: 0
            },
            {
                type: 'select',
                name: 'category',
                label: '产品分类',
                multiple: true,
                options: [
                    { value: 'electronics', label: '电子产品' },
                    { value: 'home', label: '家居用品' },
                    { value: 'clothing', label: '服装' },
                    { value: 'food', label: '食品' }
                ],
                default: ['electronics', 'home']
            },
            {
                type: 'textarea',
                name: 'description',
                label: '产品描述',
                placeholder: '请输入详细的产品描述...',
                required: true
            },
            {
                type: 'radio',
                name: 'status',
                label: '产品状态',
                options: [
                    { value: 'active', label: '上架' },
                    { value: 'inactive', label: '下架' },
                    { value: 'draft', label: '草稿' }
                ],
                default: 'active'
            },
            {
                type: 'checkbox',
                name: 'change',
                label: '我们的特色',
                options: [
                    { value: 'service', label: '服务态度' },
                    { value: 'product_quality', label: '产品质量' },
                    { value: 'after_sales', label: '售后' },
                    { value: 'other', label: '其他' }
                ],
                default: ['service', 'product_quality','after_sales']
            }
        ],
        buttons: [
            {
                text: '重置',
                type: 'danger',
                click: (e, modal) => {
                    modal.form.reset();
                    return 'keep-open';
                }
            },
            {
                text: '保存草稿',
                type: 'default',
                click: (e, modal) => {
                    const formData = modal.getFormData();
                    console.log('保存草稿:', formData);
                    Modal.info('草稿已保存');
                    return 'keep-open';
                }
            },
            {
                text: '提交审核',
                type: 'primary',
                click: (e, modal) => {
                    if (modal.validateForm()) {
                        const formData = modal.getFormData();
                        console.log('提交数据:', formData);
                        Modal.success('提交成功，即将关闭');
                        setTimeout(() => modal.close(), 1500);
                        return true;
                    }
                    return 'keep-open';
                }
            }
        ]
    }).open();
}

</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">Toast通知</h2>
        </div>
        <div class="section-content">
            <p class="section-description">显示具有各种样式和位置的Toast通知。</p>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="openTab(this, event, 'tab-btn')">演示</button>
                    <button class="tab-button" onclick="openTab(this, event, 'tab-code')">代码</button>
                </div>

                <div id="toast-demo" class="tab-content tab-btn active">
                    <div class="btn-group">
                        <button class="btn btn-success" onclick="showSuccessToast()">成功Toast</button>
                        <button class="btn btn-danger" onclick="showErrorToast()">错误Toast</button>
                        <button class="btn btn-primary" onclick="showInfoToast()">信息Toast</button>
                        <button class="btn btn-warning" onclick="showWarningToast()">警告Toast</button>
                        <button class="btn btn-primary" onclick="showTipsToast()">提示Toast</button>
                        <button class="btn" onclick="showCustomToast()">自定义Toast</button>
                        <button class="btn btn-secondary" onclick="Modal.removeAllToasts()">移除所有</button>
                    </div>
                </div>

                <div id="toast-code" class="tab-content tab-code">
                    <div class="code-block">
              <pre>// 成功Toast
function showSuccessToast() {
  Modal.success('操作成功完成!', {
    position: 'top-right',
    timeout: 5000
  });
}

// 错误Toast
function showErrorToast() {
  Modal.error('发生错误!请重试。', {
    position: 'top-right',
    timeout: 5000
  });
}

// 信息Toast
function showInfoToast() {
  Modal.info('有新更新可用。点击了解更多。', {
    position: 'bottom-right',
    onClick: function() {
      alert('这里将显示更新信息。');
    }
  });
}

// 警告Toast
function showWarningToast() {
  Modal.warning('您的会话将在5分钟后过期。', {
    position: 'top-center',
    timeout: 5000
  });
}

// 提示Toast
function showTipsToast() {
  Modal.tips('您的会话将在5分钟后过期。');
}

// 自定义Toast
function showCustomToast() {
  Modal.toast({
    message: '带点击处理程序的自定义Toast',
    type: 'info',
    position: 'bottom-right',
    timeout: 8000,
    onClick: function() {
      alert('Toast被点击了!');
    }
  });
}

// 移除所有Toast
Modal.removeAllToasts();</pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">API参考</h2>
        </div>
        <div class="section-content">
            <p class="section-description">Modal类及其方法的完整文档。</p>

            <h3>Modal选项</h3>
            <table class="options-table">
                <tr>
                    <th>选项</th>
                    <th>类型</th>
                    <th>默认值</th>
                    <th>描述</th>
                </tr>
                <tr>
                    <td><code>title</code></td>
                    <td>String|Boolean</td>
                    <td><code>''</code></td>
                    <td>弹窗标题。设置为<code>false</code>或者<code>''</code>可隐藏标题。</td>
                </tr>
                <tr>
                    <td><code>content</code></td>
                    <td>String|HTMLElement|Function</td>
                    <td><code>''</code></td>
                    <td>弹窗内容。可以是HTML字符串、DOM元素或返回其中之一的函数。</td>
                </tr>
                <tr>
                    <td><code>width</code></td>
                    <td>String|Number</td>
                    <td><code>'auto'</code></td>
                    <td>弹窗宽度。可以是数字(像素)或字符串(如'500px'或'50%')。</td>
                </tr>
                <tr>
                    <td><code>height</code></td>
                    <td>String|Number</td>
                    <td><code>'auto'</code></td>
                    <td>弹窗高度。可以是数字(像素)或字符串(如'500px'或'50%')。</td>
                </tr>
                <tr>
                    <td><code>bodyScroll</code></td>
                    <td>Boolean</td>
                    <td><code>false</code></td>
                    <td>弹窗时，是否允许父页面滚动。</td>
                </tr>
                <tr>
                    <td><code>buttons</code></td>
                    <td>Array</td>
                    <td><code>[]</code></td>
                    <td>按钮配置数组。每个按钮可以有<code>text</code>、<code>type</code>、<code>class</code>、<code>click</code>处理程序和<code>close</code>选项。</td>
                </tr>
                <tr>
                    <td><code>showActionIcons</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>是否显示右上角的<code>最小化</code>、<code>最大化</code>、<code>关闭</code>按钮</td>
                </tr>
                <tr>
                    <td><code>position</code></td>
                    <td>String</td>
                    <td><code>'center'</code></td>
                    <td>弹窗位置。选项：<code>'center'</code>、<code>'top-left'</code>、<code>'top-right'</code>、<code>'bottom-left'</code>、<code>'bottom-right'</code>、<code>'top'</code>、<code>'bottom'</code>、<code>'left'</code>、<code>'right'</code>。</td>
                </tr>
                <tr>
                    <td><code>offset</code></td>
                    <td>Array</td>
                    <td><code>'null'</code></td>
                    <td>弹窗位置(优先级高于<code>position</code>)。eg:<code>offset: [100, 200]</code>,<code>offset: [top, left]</code>。</td>
                </tr>
                <tr>
                    <td><code>theme</code></td>
                    <td>String</td>
                    <td><code>'default'</code></td>
                    <td>弹窗主题。选项：<code>'default'</code>、<code>'primary'</code>、<code>'success'</code>、<code>'warning'</code>、<code>'danger'</code>、<code>'dark'</code>。</td>
                </tr>
                <tr>
                    <td><code>draggable</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>弹窗是否可拖动。</td>
                </tr>
                <tr>
                    <td><code>overlay</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>是否显示遮罩层。</td>
                </tr>
                <tr>
                    <td><code>overlayClose</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>点击遮罩层是否关闭弹窗。</td>
                </tr>
                <tr>
                    <td><code>escClose</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>ESC键是否关闭弹窗。</td>
                </tr>
                <tr>
                    <td><code>autoClose</code></td>
                    <td>Number</td>
                    <td><code>0</code></td>
                    <td>弹窗自动关闭的时间(毫秒)。<code>0</code>表示不自动关闭。</td>
                </tr>
                <tr>
                    <td><code>transparent</code></td>
                    <td>Boolean</td>
                    <td><code>false</code></td>
                    <td>弹窗是否半透明。</td>
                </tr>
                <tr>
                    <td><code>shake</code></td>
                    <td>Boolean</td>
                    <td><code>false</code></td>
                    <td>打开时是否应用抖动动画。</td>
                </tr>
                <tr>
                    <td><code>buttonsAlign</code></td>
                    <td>String</td>
                    <td><code>'right'</code></td>
                    <td>按钮对齐方式。选项：<code>'left'</code>、<code>'center'</code>、<code>'right'</code>。</td>
                </tr>
                <tr>
                    <td><code>buttonsVertical</code></td>
                    <td>Boolean</td>
                    <td><code>false</code></td>
                    <td>按钮是否垂直堆叠。</td>
                </tr>
                <tr>
                    <td><code>onOpen</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗打开时的回调。返回<code>false</code>可阻止打开。</td>
                </tr>
                <tr>
                    <td><code>onIframeLoad</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗加载完毕时的回调。</td>
                </tr>
                <tr>
                    <td><code>onIframeComplete</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗加载完毕时的回调(同onIframeLoad，但是更强大，支持在.open()之后多次链式调用)。</td>
                </tr>

                <tr>
                    <td><code>onClose</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗关闭时的回调。</td>
                </tr>
                <tr>
                    <td><code>onBeforeClose</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗关闭前的回调。返回<code>false</code>可阻止关闭。</td>
                </tr>
                <tr>
                    <td><code>onDrag</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗拖动时的回调。接收位置对象。</td>
                </tr>
                <tr>
                    <td><code>onMinimize</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗最小化时的回调。</td>
                </tr>
                <tr>
                    <td><code>onMaximize</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗最大化时的回调。</td>
                </tr>
                <tr>
                    <td><code>onRestore</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗从最小化/最大化状态恢复时的回调。</td>
                </tr>
                <tr>
                    <td><code>onFullscreen</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>弹窗全屏时的回调。</td>
                </tr>
            </table>

            <h3>实例方法</h3>
            <table class="options-table">
                <tr>
                    <th>方法</th>
                    <th>描述</th>
                </tr>
                <tr>
                    <td><code>open()</code></td>
                    <td>打开弹窗。</td>
                </tr>
                <tr>
                    <td><code>close()</code></td>
                    <td>关闭弹窗。</td>
                </tr>
                <tr>
                    <td><code>minimize()</code></td>
                    <td>将弹窗最小化到左下角。</td>
                </tr>
                <tr>
                    <td><code>restore()</code></td>
                    <td>恢复最小化/最大化的弹窗。</td>
                </tr>
                <tr>
                    <td><code>maximize()</code></td>
                    <td>最大化弹窗。</td>
                </tr>
                <tr>
                    <td><code>fullscreen()</code></td>
                    <td>使弹窗全屏。</td>
                </tr>
                <tr>
                    <td><code>toggleMaximize()</code></td>
                    <td>在最大化和正常状态之间切换。</td>
                </tr>
                <tr>
                    <td><code>setContent(content)</code></td>
                    <td>更新弹窗内容。</td>
                </tr>
                <tr>
                    <td><code>setTitle(title)</code></td>
                    <td>更新弹窗标题。</td>
                </tr>
                <tr>
                    <td><code>shake()</code></td>
                    <td>应用抖动动画。</td>
                </tr>
                <tr>
                    <td><code>callIframeFunction('方法名',  参数1, 参数2, ...)</code></td>
                    <td>调用iframe网页中定义的函数。eg:<code>modal.callIframeFunction('test','参数A')</code></td>
                </tr>
            </table>

            <h3>静态方法</h3>
            <table class="options-table">
                <tr>
                    <th>方法</th>
                    <th>描述</th>
                </tr>
                <tr>
                    <td><code>Modal.closeAll()</code></td>
                    <td>关闭所有打开的弹窗。</td>
                </tr>
                <tr>
                    <td><code>Modal.minimizeAll()</code></td>
                    <td>最小化所有打开的弹窗。</td>
                </tr>
                <tr>
                    <td><code>Modal.restoreAll()</code></td>
                    <td>恢复所有最小化的弹窗。</td>
                </tr>
                <tr>
                    <td><code>Modal.toast(options)</code></td>
                    <td>显示Toast通知。</td>
                </tr>
                <tr>
                    <td><code>Modal.success(message, options)</code></td>
                    <td>显示成功Toast。</td>
                </tr>
                <tr>
                    <td><code>Modal.error(message, options)</code></td>
                    <td>显示错误Toast。</td>
                </tr>
                <tr>
                    <td><code>Modal.info(message, options)</code></td>
                    <td>显示信息Toast。</td>
                </tr>
                <tr>
                    <td><code>Modal.warning(message, options)</code></td>
                    <td>显示警告Toast。</td>
                </tr>
                <tr>
                    <td><code>Modal.removeAllToasts()</code></td>
                    <td>移除所有Toast。</td>
                </tr>
            </table>

            <h3>Toast选项</h3>
            <table class="options-table">
                <tr>
                    <th>选项</th>
                    <th>类型</th>
                    <th>默认值</th>
                    <th>描述</th>
                </tr>
                <tr>
                    <td><code>message</code></td>
                    <td>String</td>
                    <td><code>''</code></td>
                    <td>Toast中显示的消息。</td>
                </tr>
                <tr>
                    <td><code>type</code></td>
                    <td>String</td>
                    <td><code>'info'</code></td>
                    <td>Toast类型。选项：<code>'success'</code>、<code>'error'</code>、<code>'warning'</code>、<code>'primary'</code>、<code>'danger'</code>、<code>'secondary'</code>。</td>
                </tr>
                <tr>
                    <td><code>position</code></td>
                    <td>String</td>
                    <td><code>'top-right'</code></td>
                    <td>Toast位置。选项：<code>'top-right'</code>、<code>'top-left'</code>、<code>'bottom-right'</code>、<code>'bottom-left'</code>、<code>'top-center'</code>、<code>'bottom-center'</code>、<code>'center'</code>。</td>
                </tr>
                <tr>
                    <td><code>timeout</code></td>
                    <td>Number</td>
                    <td><code>5000</code></td>
                    <td>Toast自动关闭前的时间(毫秒)。设置为<code>0</code>可禁用自动关闭。</td>
                </tr>
                <tr>
                    <td><code>closeButton</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>是否显示关闭按钮。</td>
                </tr>
                <tr>
                    <td><code>progressBar</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>是否显示进度条。</td>
                </tr>
                <tr>
                    <td><code>newestOnTop</code></td>
                    <td>Boolean</td>
                    <td><code>true</code></td>
                    <td>新Toast是否显示在旧Toast上方。</td>
                </tr>
                <tr>
                    <td><code>onClick</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>Toast被点击时的回调。</td>
                </tr>
                <tr>
                    <td><code>onClose</code></td>
                    <td>Function</td>
                    <td><code>null</code></td>
                    <td>Toast关闭时的回调。</td>
                </tr>
            </table>

        </div>
    </section>

    <section class="demo-section">
        <div class="section-header">
            <h2 class="section-title">Form弹窗</h2>
        </div>
        <div class="section-content">
            <p class="section-description">封装一套表单弹窗组件，基于Modal封装，可快速实现表单功能。</p>

            <h2>form弹窗参数详细说明</h2>
            <h3>form表单配置参数</h3>
            <div class="markdown-table-wrapper"><table class="options-table"><thead><tr><th>参数名</th><th>类型</th><th>必填</th><th>默认值</th><th>说明</th></tr></thead><tbody><tr><td>title</td><td>string</td><td>是</td><td>无</td><td>弹窗标题</td></tr><tr><td>fields</td><td>Array</td><td>是</td><td>无</td><td>表单字段配置数组</td></tr><tr><td>labelAlign</td><td>string</td><td>否</td><td>'left'</td><td>标签对齐方式，可选: 'left', 'top'</td></tr><tr><td>labelWidth</td><td>number/string</td><td>否</td><td>150</td><td>左对齐时的标签宽度(px或百分比)</td></tr><tr><td>darkTheme</td><td>boolean</td><td>否</td><td>false</td><td>是否使用暗黑主题</td></tr><tr><td>disabled</td><td>boolean</td><td>否</td><td>false</td><td>是否禁用整个表单</td></tr><tr><td>buttons</td><td>Array</td><td>否</td><td>默认按钮</td><td>自定义按钮配置</td></tr><tr><td>onSubmit</td><td>Function</td><td>否</td><td>无</td><td>表单提交回调函数</td></tr></tbody></table></div>


            <h3>form表单字段配置参数</h3>
            <div class="markdown-table-wrapper"><table class="options-table"><thead><tr><th>参数名</th><th>类型</th><th>必填</th><th>默认值</th><th>说明</th></tr></thead><tbody><tr><td>type</td><td>string</td><td>是</td><td>无</td><td>字段类型: 'text', 'password', 'email', 'number', 'select', 'radio', 'checkbox', 'textarea'</td></tr><tr><td>name</td><td>string</td><td>是</td><td>无</td><td>字段名称，用于数据收集</td></tr><tr><td>label</td><td>string</td><td>否</td><td>无</td><td>字段标签文本</td></tr><tr><td>placeholder</td><td>string</td><td>否</td><td>无</td><td>输入框占位文本</td></tr><tr><td>required</td><td>boolean</td><td>否</td><td>false</td><td>是否为必填项</td></tr><tr><td>default</td><td>any</td><td>否</td><td>无</td><td>默认值(根据类型不同可以是字符串、数组、布尔值等)</td></tr><tr><td>disabled</td><td>boolean</td><td>否</td><td>false</td><td>是否禁用此项</td></tr><tr><td>options</td><td>Array</td><td>类型为select/radio/checkbox时必填</td><td>无</td><td>选项配置数组，格式: [{value: '', label: ''}]</td></tr><tr><td>multiple</td><td>boolean</td><td>否</td><td>false</td><td>select类型专用，是否允许多选</td></tr></tbody></table></div>


            <h3>form表单按钮配置参数</h3>
            <div class="markdown-table-wrapper"><table class="options-table"><thead><tr><th>参数名</th><th>类型</th><th>必填</th><th>默认值</th><th>说明</th></tr></thead><tbody><tr><td>text</td><td>string</td><td>是</td><td>无</td><td>按钮文本</td></tr><tr><td>type</td><td>string</td><td>否</td><td>'default'</td><td>按钮类型: 'default', 'primary', 'success', 'warning', 'danger', 'info'</td></tr><tr><td>close</td><td>boolean</td><td>否</td><td>false</td><td>点击后是否关闭弹窗</td></tr><tr><td>click</td><td>Function</td><td>否</td><td>无</td><td>点击回调函数，返回false可阻止关闭</td></tr></tbody></table></div>

        </div>
    </section>

{{--    <footer class="footer">--}}
{{--        <p>现代化弹窗类库 &copy; 2025 | 开源MIT许可证</p>--}}
{{--    </footer>--}}
    @endsection

    @section('page_js')

{{--<script src="{{ asset('static/libs/zxf/modal/modal.min.js') }}" charset="utf-8"></script>--}}
<script>
    // 测试 弹窗页面通过 postMessage 发送的数据通讯
    function receiveModalData(){
        console.log('测试 弹窗页面通过 postMessage 发送的数据通讯')
        console.log('receive data:', arguments)
    }
    // 测试通讯
    function test(){
        Modal.success('this is parent test func', {
            onClick: function() {
                console.log('click parent test')
            }
        });
    }
    // 标签页功能
    function openTab(element, evt, tabName) {
        // 1. 找到当前元素的父元素的父元素下的所有 .tab-content 元素
        const grandParent = element.parentElement.parentElement;
        const tabContents = grandParent.querySelectorAll('.tab-content');

        // 移除查找出来的元素中包含的所有 .active 类
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        // 移除同级元素中包含的所有 .active 类
        element.parentElement.querySelectorAll('.tab-button').forEach(content => {
            content.classList.remove('active');
        });

        // 2. 给当前元素的父元素的父元素下的所有 class 为'tab-btn' 的元素添加 .active 类
        const tabButtons = grandParent.querySelectorAll(`.${tabName}`);
        tabButtons.forEach(button => {
            button.classList.add('active');
        });

        // 3. 给当前点击的按钮添加 active 类
        element.classList.add('active');
    }

    // 快速开始演示
    function demoQuickStart() {
        new Modal({
            title: '欢迎',
            content: '这是快速开始示例中的一个简单弹窗对话框。',
            buttons: [
                { text: '确定', type: 'primary', click: function(e, modal) {
                        console.log('点击确定按钮',e, modal)
                }}
            ]
        }).open();
    }

    function demoQuickStartToast() {
        Modal.success('操作成功完成!', {
            position: 'top-right',
            timeout: 5000
        });
    }

    // 基础示例
    function showBasicModal() {
        new Modal({
            title: '基本弹窗',
            content: '这是一个带标题和默认关闭按钮的基本弹窗对话框。',
            buttons: [
                { text: '关闭', type: 'primary' }
            ]
        }).open();
    }

    function showNoTitleModal() {
        new Modal({
            showActionIcons: false, // 隐藏操作图标
            content: '这个弹窗没有标题。',
            buttons: [
                { text: '确定' }
            ]
        }).open();
    }

    function showNoButtonsModal() {
        new Modal({
            title: '无按钮',
            content: '这个弹窗没有按钮。你需要点击右上角的关闭按钮来关闭它。'
        }).open();
    }

    function showTextOnlyModal() {
        new Modal({
            content: '这是一个纯文本弹窗，将在3秒后自动关闭。',
            transparent:true,
            autoClose: 3000
        }).open();
    }

    function showFormModal() {
        const form = document.createElement('div');
        form.innerHTML = `
        <form>
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">用户名</label>
            <input type="text" style="width: 100%; padding: 8px; box-sizing: border-box;">
          </div>
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">密码</label>
            <input type="password" style="width: 100%; padding: 8px; box-sizing: border-box;">
          </div>
        </form>
      `;

        new Modal({
            title: '登录表单',
            content: form,
            buttons: [
                { text: '取消', type: 'secondary' },
                { text: '登录', type: 'primary' }
            ]
        }).open();
    }

    // 按钮变体
    function showSingleButtonModal() {
        new Modal({
            title: '单按钮',
            content: '这个弹窗只有一个按钮。',
            buttons: [
                { text: '确定', type: 'primary' }
            ]
        }).open();
    }

    function showMultipleButtonsModal() {
        new Modal({
            title: '多按钮',
            content: '这个弹窗有多个不同样式的按钮。',
            buttons: [
                { text: '主要', type: 'primary' },
                { text: '成功', type: 'success' },
                { text: '警告', type: 'warning' },
                { text: '危险', type: 'danger' },
                { text: '次要', type: 'secondary' }
            ]
        }).open();
    }

    function showVerticalButtonsModal() {
        new Modal({
            title: '垂直按钮',
            content: '这个弹窗有垂直堆叠的按钮。',
            buttons: [
                { text: '第一个', type: 'primary' },
                { text: '第二个', type: 'success' },
                { text: '第三个', type: 'warning' }
            ],
            buttonsVertical: true
        }).open();
    }

    function showLeftAlignedButtonsModal() {
        new Modal({
            title: '左对齐按钮',
            content: '这个弹窗的按钮是左对齐的。',
            buttons: [
                { text: '取消', type: 'secondary' },
                { text: '保存', type: 'primary' }
            ],
            buttonsAlign: 'left'
        }).open();
    }

    function showCenterAlignedButtonsModal() {
        new Modal({
            title: '居中对齐按钮',
            content: '这个弹窗的按钮是居中对齐的。',
            buttons: [
                { text: '否', type: 'secondary' },
                { text: '是', type: 'primary' }
            ],
            buttonsAlign: 'center'
        }).open();
    }

    function showLinkButtonsModal() {
        new Modal({
            title: '链接按钮',
            content: '这个弹窗有链接样式的按钮。',
            buttons: [
                { text: '跳转', href: 'https://weisifang.com', class: 'a-link', target: '_blank'},
                { text: '关闭', type: 'danger' }
            ]
        }).open();
    }

    // 定位选项
    function showTopLeftModal() {
        new Modal({
            title: '左上角',
            content: '这个弹窗位于屏幕的左上角。',
            position: 'top-left'
        }).open();
    }

    function showTopRightModal() {
        new Modal({
            title: '右上角',
            content: '这个弹窗位于屏幕的右上角。',
            position: 'top-right'
        }).open();
    }

    function showBottomLeftModal() {
        new Modal({
            title: '左下角',
            content: '这个弹窗位于屏幕的左下角。',
            position: 'bottom-left'
        }).open();
    }

    function showBottomRightModal() {
        new Modal({
            title: '右下角',
            content: '这个弹窗位于屏幕的右下角。',
            position: 'bottom-right'
        }).open();
    }

    function showCenterModal() {
        new Modal({
            title: '居中',
            content: '这个弹窗位于屏幕中央(默认)。',
            position: 'center'
        }).open();
    }

    function showTopCenterModal() {
        new Modal({
            title: '顶部居中',
            content: '这个弹窗位于屏幕顶部居中。',
            position: 'top'
        }).open();
    }

    function showBottomCenterModal() {
        new Modal({
            title: '底部居中',
            content: '这个弹窗位于屏幕底部居中。',
            position: 'bottom'
        }).open();
    }

    // 高级功能
    function showAutoCloseModal() {
        new Modal({
            title: '自动关闭',
            content: '这个弹窗将在3秒后自动关闭。',
            autoClose: 3000
        }).open();
    }

    function showIframeModal() {
        // new Modal({
        //     title: 'Iframe弹窗',
        //     // content: '<iframe src="https://example.com" style="width: 100%; height: 300px;"></iframe>',
        //     content: '<iframe src="/demo/components/iframe-content.html" style="width: 100%; height: 300px;"></iframe>',
        //     width: 600,
        //     height: 400,
        //     bodyScroll:false
        // }).open();
        let testModal = Modal.iframe('Iframe弹窗', '/demo/components/iframe-content', 600, 400);

        testModal.onIframeComplete((_modal) => {
            console.log('complete',_modal);
            // 父页面调用Iframe网页中的test方法并传参
            _modal.callIframeFunction('test', 'test1','test2')
        })
    }

    function showShakeModal() {
        new Modal({
            title: '抖动效果',
            content: '这个弹窗打开时会抖动。',
            shake: true
        }).open();
    }

    function showDraggableModal() {
        new Modal({
            title: '可拖动弹窗',
            content: '尝试通过标题栏拖动这个弹窗。',
            draggable: true
        }).open();
    }

    function showTransparentModal() {
        new Modal({
            title: '透明弹窗',
            content: '这个弹窗有半透明背景。',
            transparent: true,
            buttons: [
                { text: '跳转', href: 'https://weisifang.com', class: 'a-link', target: '_blank'},
                { text: '关闭', type: 'danger' }
            ]
        }).open();
    }

    function showNoOverlayModal() {
        new Modal({
            title: '无遮罩',
            content: '这个弹窗没有遮罩层。',
            overlay: false
        }).open();
    }

    function showNoOverlayCloseModal() {
        new Modal({
            title: '遮罩不关闭',
            content: '这个弹窗有遮罩层但点击它不会关闭弹窗。',
            overlayClose: false
        }).open();
    }

    function showNoEscCloseModal() {
        new Modal({
            title: 'ESC不关闭',
            content: '这个弹窗不能用ESC键关闭。',
            escClose: false
        }).open();
    }

    // 窗口控制
    function showMinimizableModal() {
        new Modal({
            title: '可最小化弹窗',
            content: '这个弹窗可以最小化到左下角。',
            buttons: [
                { text: '最小化', click: function(e, modal) { modal.minimize(); return 'keep-open'; } },
                { text: '关闭' }
            ]
        }).open();
    }

    function showMaximizableModal() {
        new Modal({
            title: '可最大化弹窗',
            content: '这个弹窗可以最大化以占据更多屏幕空间。',
            buttons: [
                { text: '最大化', click: function(e, modal) { modal.maximize(); return 'keep-open'; } },
                { text: '关闭' }
            ]
        }).open();
    }

    function showFullscreenModal() {
        new Modal({
            title: '全屏弹窗',
            content: '这个弹窗可以全屏显示。',
            buttons: [
                { text: '全屏', click: function(e, modal) { modal.fullscreen(); return 'keep-open'; } },
                { text: '关闭' }
            ]
        }).open();
    }

    function showFixedSizeModal() {
        new Modal({
            title: '固定尺寸弹窗',
            content: '这个弹窗有固定的宽度和高度。',
            width: 400,
            height: 300
        }).open();
    }

    function showMultipleModals() {
        new Modal({
            title: '第一个弹窗',
            content: '这是第一个弹窗。',
            position: 'top-left',
            width: 300
        }).open();

        new Modal({
            title: '第二个弹窗',
            content: '这是第二个弹窗。',
            position: 'top-right',
            width: 300
        }).open();

        new Modal({
            title: '第三个弹窗',
            content: '这是第三个弹窗。',
            position: 'bottom-left',
            width: 300
        }).open();

        new Modal({
            title: '第四个弹窗',
            content: '这是第四个弹窗。',
            position: 'bottom-right',
            width: 300
        }).open();
    }

    // 主题样式
    function showDefaultThemeModal() {
        new Modal({
            title: '默认主题',
            content: '这个弹窗使用默认主题。',
            theme: 'default'
        }).open();
    }

    function showPrimaryThemeModal() {
        new Modal({
            title: '主要主题',
            content: '这个弹窗使用主要主题。',
            theme: 'primary'
        }).open();
    }

    function showSuccessThemeModal() {
        new Modal({
            title: '成功主题',
            content: '这个弹窗使用成功主题。',
            theme: 'success',
            buttons: [
                { text: '确定', type: 'success' }
            ]
        }).open();
    }

    function showWarningThemeModal() {
        new Modal({
            title: '警告主题',
            content: '这个弹窗使用警告主题。',
            theme: 'warning',
            buttons: [
                { text: '确定', type: 'warning' }
            ]
        }).open();
    }

    function showDangerThemeModal() {
        new Modal({
            title: '危险主题',
            content: '这个弹窗使用危险主题。',
            theme: 'danger',
            buttons: [
                { text: '确定', type: 'danger' }
            ]
        }).open();
    }

    function showDarkThemeModal() {
        new Modal({
            title: '暗黑主题',
            content: '这个弹窗使用暗黑主题。',
            theme: 'dark',
            buttons: [
                { text: '确定', type: 'success' }
            ]
        }).open();
    }

    // Toast通知
    function showSuccessToast() {
        Modal.success('操作成功完成!', {
            position: 'top-right',
            timeout: 50000
        });
    }

    function showErrorToast() {
        Modal.error('发生错误!请重试。', {
            position: 'top-right',
            timeout: 5000
        });
    }

    function showInfoToast() {
        Modal.info('有新更新可用。点击了解更多。', {
            position: 'bottom-right',
            onClick: function() {
                alert('这里将显示更新信息。');
            }
        });
    }

    function showWarningToast() {
        Modal.warning('您的会话将在5分钟后过期。', {
            position: 'top-center',
            timeout: 5000
        });
    }

    // 提示Toast
    function showTipsToast() {
        Modal.tips('您的会话将在5分钟后过期。');
    }

    function showCustomToast() {
        Modal.toast({
            message: '带点击处理程序的自定义Toast',
            type: 'info',
            position: 'bottom-right',
            timeout: 8000,
            onClick: function() {
                alert('Toast被点击了!');
            }
        });
    }



    // 1. 基本表单示例
    function showFormBaseModal() {
        Modal.form({
            title: '用户注册表单',
            width: 400,
            labelAlign: 'top', // 标签左对齐
            labelWidth: 80, // 标签宽度120px
            darkTheme: true, // 使用暗黑主题
            fields: [
                {
                    type: 'text',
                    name: 'username',
                    label: '用户名',
                    placeholder: '请输入您的用户名',
                    required: true,
                    default: '默认用户'
                },
                {
                    type: 'password',
                    name: 'password',
                    label: '密码',
                    placeholder: '请输入密码',
                    required: true
                },
                {
                    type: 'email',
                    name: 'email',
                    label: '电子邮箱',
                    placeholder: '请输入电子邮箱',
                    required: true
                },
                {
                    type: 'select',
                    name: 'gender',
                    label: '性别',
                    options: [
                        { value: 'male', label: '男' },
                        { value: 'female', label: '女' }
                    ],
                    default: 'male'
                }
            ],
            onSubmit: (formData, modal) => {
                console.log('表单数据:', formData);
                Modal.success('注册成功！');
                modal.close();
            }
        }).open();
    }

    // 2. 多选表单示例
    function showFormDarkThemeModal() {
        Modal.form({
            title: '多选表单示例',
            width: 500,
            labelWidth: 100, // 标签宽度120px
            labelAlign: 'left', // 标签顶部对齐
            darkTheme: false, // 使用暗黑主题
            fields: [
                {
                    type: 'select',
                    name: 'hobbies',
                    label: '兴趣爱好',
                    multiple: true, // 启用多选
                    options: [
                        { value: 'reading', label: '阅读' },
                        { value: 'music', label: '音乐' },
                        { value: 'sports', label: '运动' },
                        { value: 'travel', label: '旅行' },
                        { value: 'games', label: '游戏' }
                    ],
                    default: ['reading', 'music'] // 默认选中项
                },
                {
                    type: 'checkbox',
                    name: 'notifications',
                    label: '接收通知方式',
                    options: [
                        { value: 'email', label: '电子邮件' },
                        { value: 'sms', label: '短信' },
                        { value: 'push', label: '推送通知' }
                    ],
                    default: ['email', 'push']
                }
            ],
            buttons: [
                {
                    text: '取消',
                    type: 'secondary',
                    close: true
                },
                {
                    text: '提交',
                    type: 'primary',
                    click: (e, modal) => {
                        const formData = modal.getFormData();
                        if (modal.validateForm()) {
                            console.log('提交数据:', formData);
                            Modal.success('提交成功！');
                            modal.close();
                        }
                        return 'keep-open';
                    }
                }
            ]
        }).open();
    }

    // 3. 复杂表单示例
    function showFormComplexModal() {
        Modal.form({
            title: '产品信息表单',
            width: 600,
            labelAlign: 'left',
            labelWidth: '20%', // 使用百分比宽度
            fields: [
                {
                    type: 'text',
                    name: 'product_name',
                    label: '产品名称',
                    placeholder: '请输入产品名称',
                    required: true
                },
                {
                    type: 'number',
                    name: 'price',
                    label: '产品价格',
                    placeholder: '请输入价格',
                    required: true,
                    default: 0
                },
                {
                    type: 'select',
                    name: 'category',
                    label: '产品分类',
                    multiple: true,
                    options: [
                        { value: 'electronics', label: '电子产品' },
                        { value: 'home', label: '家居用品' },
                        { value: 'clothing', label: '服装' },
                        { value: 'food', label: '食品' }
                    ],
                    default: ['electronics', 'home']
                },
                {
                    type: 'textarea',
                    name: 'description',
                    label: '产品描述',
                    placeholder: '请输入详细的产品描述...',
                    required: true
                },
                {
                    type: 'radio',
                    name: 'status',
                    label: '产品状态',
                    options: [
                        { value: 'active', label: '上架' },
                        { value: 'inactive', label: '下架' },
                        { value: 'draft', label: '草稿' }
                    ],
                    default: 'active'
                },
                {
                    type: 'checkbox',
                    name: 'change',
                    label: '我们的特色',
                    options: [
                        { value: 'service', label: '服务态度' },
                        { value: 'product_quality', label: '产品质量' },
                        { value: 'after_sales', label: '售后' },
                        { value: 'other', label: '其他' }
                    ],
                    default: ['service', 'product_quality','after_sales']
                }
            ],
            buttons: [
                {
                    text: '重置',
                    type: 'danger',
                    click: (e, modal) => {
                        modal.form.reset();
                        return 'keep-open';
                    }
                },
                {
                    text: '保存草稿',
                    type: 'default',
                    click: (e, modal) => {
                        const formData = modal.getFormData();
                        console.log('保存草稿:', formData);
                        Modal.info('草稿已保存');
                        return 'keep-open';
                    }
                },
                {
                    text: '提交审核',
                    type: 'primary',
                    click: (e, modal) => {
                        if (modal.validateForm()) {
                            const formData = modal.getFormData();
                            console.log('提交数据:', formData);
                            Modal.success('提交成功，即将关闭');
                            setTimeout(() => modal.close(), 1500);
                            return true;
                        }
                        return 'keep-open';
                    }
                }
            ]
        }).open();
    }
</script>
@endsection
