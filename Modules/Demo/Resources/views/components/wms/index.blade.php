@extends('demo::layouts.demo_layout')
@section('title', "WMS 仓库管理示例")
@section('page_inner_title', "WMS 仓库管理示例 !")

@section('head_css')
<link rel="stylesheet" href="{{ asset('static/wms/wms.css') }}">
@endsection

@section('content')
    <h1>WMS 仓库管理示例</h1>

    <div id="container"></div>

    <div id="header">
        <div class="logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 7H21V21H3V7Z" stroke="white" stroke-width="2"/>
                    <path d="M3 7V4C3 3.44772 3.44772 3 4 3H20C20.5523 3 21 3.44772 21 4V7" stroke="white" stroke-width="2"/>
                    <path d="M12 12H12.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 12H16.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M8 12H8.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="logo-text">智能仓储3D系统</div>
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
            <label for="label-position">库位标签位置</label>
            <select id="label-position">
                <option value="back">背面</option>
                <option value="top">顶面</option>
            </select>
        </div>
        <div class="config-item">
            <label for="slot-offset">库位高度偏移</label>
            <input type="range" id="slot-offset" min="0" max="0.5" step="0.05" value="0.75">
        </div>
    </div>

    <div id="search-container">
        <div class="search-title">库位/商品搜索</div>
        <div class="search-box">
            <input type="text" id="search-input" placeholder="输入库位编号或商品信息">
            <button id="search-btn">搜索</button>
        </div>
    </div>

    <div id="info-panel">
        <div class="panel-header">
            <div class="panel-title">库位详细信息</div>
            <div class="close-btn" id="close-info">×</div>
        </div>
        <div class="panel-content" id="info-content">
            <!-- 信息内容将通过JS动态生成 -->
        </div>
        <div class="edit-form" id="edit-form">
            <div class="panel-content">
                <div class="form-group">
                    <label for="edit-slot-id">库位编号</label>
                    <input type="text" id="edit-slot-id" readonly>
                </div>
                <div class="form-group">
                    <label for="edit-slot-status">状态</label>
                    <select id="edit-slot-status">
                        <option value="free">空闲</option>
                        <option value="occupied">占用</option>
                        <option value="reserved">预留</option>
                        <option value="damaged">损坏</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-product-id">商品ID</label>
                    <input type="text" id="edit-product-id">
                </div>
                <div class="form-group">
                    <label for="edit-product-name">商品名称</label>
                    <input type="text" id="edit-product-name">
                </div>
                <div class="form-group">
                    <label for="edit-product-category">商品分类</label>
                    <select id="edit-product-category">
                        <option value="hardware">五金</option>
                        <option value="frozen">冻品</option>
                        <option value="dry">干货</option>
                        <option value="aquatic">水产</option>
                        <option value="other">其他</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-inbound-date">入库时间</label>
                    <input type="date" id="edit-inbound-date">
                </div>
                <div class="form-group">
                    <label for="edit-inbound-person">入库人</label>
                    <input type="text" id="edit-inbound-person">
                </div>
                <div class="form-actions">
                    <button class="btn btn-default" id="cancel-edit">取消</button>
                    <button class="btn btn-primary" id="save-edit">保存</button>
                </div>
            </div>
        </div>
    </div>

    <div id="control-panel">
        <div class="joystick">
            <div class="center-btn" id="reset-view" title="重置视图">↺</div>
            <div class="direction-btn up" title="向前移动">↑</div>
            <div class="direction-btn down" title="向后移动">↓</div>
            <div class="direction-btn left" title="向左移动">←</div>
            <div class="direction-btn right" title="向右移动">→</div>
        </div>
    </div>

    <div id="toolbar">
        <div class="tool-btn toggle-tools" id="toggle-tools" title="展开/收起工具栏">☰</div>
        <div class="tool-btn" id="zoom-in" title="放大视图">+</div>
        <div class="tool-btn" id="zoom-out" title="缩小视图">-</div>
        <div class="tool-btn" id="toggle-labels" title="切换标签显示">T</div>
        <div class="tool-btn" id="toggle-stats" title="显示/隐藏统计">S</div>
        <div class="tool-btn" id="toggle-config" title="视图配置">⚙️</div>
        <div class="tool-btn" id="edit-mode" title="编辑模式">✎</div>
        <div class="tool-btn" id="export-data" title="导出数据">⇩</div>
        <div class="tool-btn" id="toggle-minimap" title="显示/隐藏小地图">🗺️</div>
        <div class="tool-btn" id="screenshot" title="截图">📷</div>
    </div>

    <div class="stats" id="stats-panel">
        <div>仓库: <span id="stats-warehouse">-</span></div>
        <div>库位总数: <span id="stats-total">0</span></div>
        <div>空闲: <span id="stats-free">0</span></div>
        <div>占用: <span id="stats-occupied">0</span></div>
        <div>预留: <span id="stats-reserved">0</span></div>
        <div>损坏: <span id="stats-damaged">0</span></div>
    </div>

    <div class="mini-map" id="mini-map">
        <div class="mini-map-content" id="mini-map-content"></div>
    </div>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="tooltip" id="tooltip"></div>

@endsection

@section('page_js')
    <script src="{{ asset('static/libs/three@0.132.2/build/three.min.js') }}" type='text/javascript'></script>
    <script src="{{ asset('static/libs/three@0.132.2/examples/OrbitControls.js') }}" type='text/javascript'></script>

    <script src="{{ asset('static/libs/zxf/js/warehouse3D.js') }}" type='text/javascript'></script>
    <script src="{{ asset('static/wms/wms.js') }}" type='text/javascript'></script>

    <script>

    </script>
@endsection
