@extends('demo::layouts.demo_layout')
@section('title', "WMS 仓库管理示例")
@section('page_inner_title', "WMS 仓库管理示例 !")

@section('head_css')
<link rel="stylesheet" href="{{ asset('static/wms/wms.css') }}">
@endsection

@section('content')
{{--    <h1>WMS 仓库管理示例</h1>--}}

    <div class="wms-box">
        <div id="wms-container"></div>

        <div id="wms-header">
            <div class="wms-logo">
                <div class="wms-logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13" /><path d="M13 13h4v8h-10v-6h6" /><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" /></svg>
                </div>
                <div class="wms-logo-text">智能仓储3D系统</div>
            </div>
            <div class="warehouse-tabs" id="warehouse-tabs">
                <!-- 仓库标签将通过JS动态生成 -->
            </div>
        </div>

        <div class="config-panel" id="config-panel">
            <div class="config-title">视图配置</div>
            <div class="config-item">
                <label for="view-mode">查看模式</label>
                <select id="view-mode">
                    <option value="overview">概览模式</option>
                    <option value="shelf">货架视图</option>
                    <option value="slot">库位视图</option>
                </select>
            </div>
            <div class="config-item">
                <label for="label-display">标签显示</label>
                <select id="label-display">
                    <option value="all">显示全部</option>
                    <option value="shelf">仅货架标签</option>
                    <option value="slot">仅库位标签</option>
                    <option value="none">隐藏全部</option>
                </select>
            </div>
            <div class="config-item">
                <label for="slot-label-position">库位标签位置</label>
                <select id="slot-label-position">
                    <option value="front">正面</option>
                    <option value="back">背面</option>
                    <option value="top">顶面</option>
                    <option value="bottom">底面</option>
                </select>
            </div>
            <div class="config-item">
                <label for="vertical-offset">垂直偏移</label>
                <input type="range" id="vertical-offset" min="-50" max="50" value="-15" step="1">
                <span id="vertical-offset-value">-15</span>
            </div>
            <!-- 新增的配置选项 -->
            <div class="config-item">
                <label for="light-intensity">灯光强度</label>
                <input type="range" id="light-intensity" min="0" max="1" value="0.8" step="0.1">
                <span id="light-intensity-value">0.8</span>
            </div>
            <!--    <div class="config-item">-->
            <!--        <label for="animation-speed">动画速度</label>-->
            <!--        <input type="range" id="animation-speed" min="0.5" max="2" value="1" step="0.1">-->
            <!--        <span id="animation-speed-value">1</span>-->
            <!--    </div>-->
            <!--    <div class="config-item">-->
            <!--        <label for="render-quality">渲染质量</label>-->
            <!--        <select id="render-quality">-->
            <!--            <option value="low">低</option>-->
            <!--            <option value="medium" selected>中</option>-->
            <!--            <option value="high">高</option>-->
            <!--        </select>-->
            <!--    </div>-->
        </div>

        <!-- 添加帮助面板 -->
        <div id="help-panel" class="config-panel" style="display: none; left: 20px; right: auto;">
            <div class="wms-panel-header">
                <div class="wms-panel-title">帮助信息</div>
                <div class="wms-close-btn" id="close-help">×</div>
            </div>
            <div class="wms-panel-content">
                <div class="wms-info-section">
                    <div class="wms-info-section-title">快捷键</div>
                    <div class="wms-info-item">
                        <div class="info-label">WASD ↑↓←→ </div>
                        <div class="info-value">方向键:移动视角</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">鼠标拖动</div>
                        <div class="info-value">旋转视角</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">鼠标滚轮 Q/E</div>
                        <div class="info-value">缩放</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">U (up) / L(down)</div>
                        <div class="info-value">垂直 上升/下降</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">R (reset)</div>
                        <div class="info-value">重置视角</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">F (fullscreen)</div>
                        <div class="info-value">打开/关闭 全屏</div>
                    </div>
                    <div class="wms-info-item">
                        <div class="info-label">H (help)</div>
                        <div class="info-value">打开/关闭 查看帮助</div>
                    </div>

                </div>
            </div>
        </div>

        <div id="wms-search-container">
            <!-- <div class="wms-search-title">库位/商品搜索</div>-->
            <div class="wms-search-box">
                <input type="text" id="wms-search-input" placeholder="库位/商品搜索...">
                <button id="wms-search-btn">搜索</button>
            </div>
        </div>

        <div id="wms-info-panel">
            <div class="wms-panel-header">
                <div class="wms-panel-title">库位详细信息</div>
                <div class="wms-close-btn" id="close-info">×</div>
            </div>
            <div class="wms-panel-content" id="info-content">
                <!-- 信息内容将通过JS动态生成 -->
            </div>
            <div class="wms-edit-form" id="wms-edit-form">
                <div class="wms-panel-content">
                    <div class="wms-form-group">
                        <label for="edit-slot-id">库位编号</label>
                        <input type="text" id="edit-slot-id" readonly>
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-slot-status">状态</label>
                        <select id="edit-slot-status">
                            <option value="free">空闲</option>
                            <option value="occupied">占用</option>
                            <option value="reserved">预留</option>
                            <option value="damaged">损坏</option>
                            <option value="locked">锁定</option>
                            <option value="maintenance">维护中</option>
                        </select>
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-product-id">商品ID</label>
                        <input type="text" id="edit-product-id">
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-product-name">商品名称</label>
                        <input type="text" id="edit-product-name">
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-product-category">商品分类</label>
                        <select id="edit-product-category">
                            <option value="hardware">五金</option>
                            <option value="frozen">冻品</option>
                            <option value="dry">干货</option>
                            <option value="aquatic">水产</option>
                            <option value="electronics">电子产品</option>
                            <option value="clothing">服装</option>
                            <option value="food">食品</option>
                            <option value="medicine">药品</option>
                            <option value="other">其他</option>
                        </select>
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-inbound-date">入库时间</label>
                        <input type="date" id="edit-inbound-date">
                    </div>
                    <div class="wms-form-group">
                        <label for="edit-inbound-person">入库人</label>
                        <input type="text" id="edit-inbound-person">
                    </div>
                    <div class="wms-form-actions">
                        <button class="wms-btn wms-btn-default" id="cancel-edit">取消</button>
                        <button class="wms-btn wms-btn-primary" id="save-edit">保存</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="wms-control-panel">
            <div class="wms-joystick">
                <div class="wms-center-btn" id="reset-view" title="重置视图">↺</div>
                <div class="wms-direction-btn up" title="向前">↑</div>
                <div class="wms-direction-btn down" title="向后">↓</div>
                <div class="wms-direction-btn left" title="向左">←</div>
                <div class="wms-direction-btn right" title="向右">→</div>
            </div>
        </div>

        <div id="wms-toolbar" class="collapsed">
            <div class="wms-tool-btn" id="zoom-in" title="放大">+</div>
            <div class="wms-tool-btn" id="zoom-out" title="缩小">-</div>
            <div class="wms-tool-btn" id="toggle-labels" title="切换标签">T</div>
            <div class="wms-tool-btn" id="toggle-stats" title="显示统计">S</div>
            <div class="wms-tool-btn" id="toggle-config" title="视图配置">⚙</div>
            <div class="wms-tool-btn" id="edit-mode" title="编辑模式">✎</div>
            <div class="wms-tool-btn" id="export-data" title="导出数据">⇩</div>
            <div class="wms-tool-btn" id="import-data" title="导入数据">⇧</div>

            <!-- 新增的功能按钮 -->
            <div class="wms-tool-btn" id="screenshot" title="截图">📷</div>
            <div class="wms-tool-btn" id="toggle-fullscreen" title="全屏">⛶</div>
            <div class="wms-tool-btn" id="toggle-help" title="帮助">?</div>

            <div class="wms-tool-btn" id="toggle-toolbar" title="展开工具栏">≡</div>
        </div>

        <div class="stats" id="stats-panel">
            <div>仓库: <span id="stats-warehouse">-</span></div>
            <div>库位总数: <span id="stats-total">0</span></div>
            <div>空闲: <span id="stats-free">0</span></div>
            <div>占用: <span id="stats-occupied">0</span></div>
            <div>预留: <span id="stats-reserved">0</span></div>
            <div>损坏: <span id="stats-damaged">0</span></div>
            <div>锁定: <span id="stats-locked">0</span></div>
            <div>维护: <span id="stats-maintenance">0</span></div>
        </div>

        <div class="loading-overlay" id="loading-overlay">
            <div class="loading-spinner"></div>
        </div>
    </div>

@endsection

@section('page_js')
    <script src="{{ asset('static/libs/three@0.132.2/build/three.min.js') }}" type='text/javascript'></script>
    <script src="{{ asset('static/libs/three@0.132.2/examples/OrbitControls.js') }}" type='text/javascript'></script>

    <script src="{{ asset('static/libs/zxf/js/warehouse3D.js') }}" type='text/javascript'></script>
    <script src="{{ asset('static/wms/wms.js') }}" type='text/javascript'></script>

    <script>

    </script>
@endsection
