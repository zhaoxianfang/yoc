/**
 * Warehouse3D 类 - 现代化仓库3D可视化看板
 * 封装所有仓库3D可视化功能，提供完整的API接口
 */
class Warehouse3D {
    /**
     * 构造函数
     * @param {string} containerId 容器元素的ID
     * @param {Object} options 全局配置选项
     */
    constructor(containerId, options = {}) {
        // 合并默认配置和用户配置
        this.config = {
            // 默认库位状态配置
            slotStatus: {
                free: { name: '空闲', color: '#52c41a' },
                occupied: { name: '占用', color: '#faad14' },
                reserved: { name: '预留', color: '#1890ff' },
                damaged: { name: '损坏', color: '#f5222d' }
            },
            // 默认商品分类配置
            productCategories: {
                hardware: { name: '五金', color: '#fa8c16' },
                frozen: { name: '冻品', color: '#1890ff' },
                dry: { name: '干货', color: '#722ed1' },
                aquatic: { name: '水产', color: '#13c2c2' },
                other: { name: '其他', color: '#eb2f96' }
            },
            // 默认货架材质颜色
            shelfMaterialColor: 0x8B4513,
            // 默认隔板材质颜色
            plateMaterialColor: 0xD2B48C,
            // 默认地面颜色
            groundColor: 0xaaaaaa,
            // 默认库位尺寸 (长:宽:高 = 5:4:3)
            slotSize: { length: 2.5, width: 2, height: 1.5 },
            // 默认货架尺寸
            shelfSize: { width: 5, depth: 3, height: 2 },
            // 默认通道宽度（货架倍数）
            aisleWidthMultiplier: 2,
            // 行通道宽度（货架倍数）
            rowAisleWidthMultiplier: 2,
            // 列通道宽度（货架倍数）
            columnAisleWidthMultiplier: 2,
            // 默认布局类型
            layoutType: 'STRAIGHT',
            // 是否显示标签
            showLabels: true,
            // 是否显示统计面板
            showStats: true,
            // 是否启用动画
            enableAnimations: true,
            // 是否启用阴影
            enableShadows: true,
            // 初始化相机位置
            cameraPosition: { x: 50, y: 50, z: 50 },
            // 初始化相机目标
            cameraTarget: { x: 0, y: 0, z: 0 },
            // 渲染精度
            pixelRatio: window.devicePixelRatio || 1,
            // 抗锯齿
            antialias: true,
            // 物理校正光照
            physicallyCorrectLights: true,
            // 色调映射
            toneMapping: THREE.ACESFilmicToneMapping,
            // 曝光级别
            exposure: 1.5,
            // 阴影类型
            shadowType: THREE.PCFSoftShadowMap,
            // 环境光强度
            ambientLightIntensity: 0.8,
            // 是否启用控制器阻尼
            enableDamping: true,
            // 控制器阻尼系数
            dampingFactor: 0.05,
            // 是否启用平移
            enablePan: true,
            // 是否启用缩放
            enableZoom: true,
            // 是否启用旋转
            enableRotate: true,
            // 缩放速度
            zoomSpeed: 1.2,
            // 旋转速度
            rotateSpeed: 1.0,
            // 平移速度
            panSpeed: 1.2,
            // 最大极化角
            maxPolarAngle: Math.PI,
            // 最小极化角
            minPolarAngle: 0,
            // 最大距离
            maxDistance: 500,
            // 最小距离
            minDistance: 3,
            // 是否自动旋转
            autoRotate: false,
            // 自动旋转速度
            autoRotateSpeed: 2.0,
            // 库位间距（相对于库位长度的比例）
            slotGapRatio: 0.2,
            // 是否启用编辑模式
            editMode: false,
            // 货架编号规则
            shelfNumberingRule: 'FORWARD_LEFT_TO_RIGHT',
            // 库位标签位置
            slotLabelPosition: 'back',
            // 库位高度偏移
            slotHeightOffset: 0.75,
            // 标签背景颜色
            labelBackgroundColor: 'rgba(0, 0, 0, 0.7)',
            // 地名位置偏移
            groundNameOffset: { x: 0, y: 0, z: 0 },
            // 性能优化选项
            performance: {
                // 是否启用实例化渲染
                useInstancing: true,
                // 每帧最大渲染库位数
                maxSlotsPerFrame: 1000,
                // 是否启用LOD
                useLOD: true,
                // LOD距离阈值
                lodDistanceThreshold: 50
            },
            // 事件回调
            onWarehouseChange: null,
            onSlotClick: null,
            onSlotUpdate: null,
            onSearchComplete: null,
            onEditModeChange: null,
            onViewChange: null,
            ...options
        };

        // 保存容器引用
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`找不到ID为"${containerId}"的容器元素`);
            return;
        }

        // 初始化场景
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x0c1e3e);

        // 初始化相机
        this.camera = new THREE.PerspectiveCamera(
            45,
            window.innerWidth / window.innerHeight,
            0.1,
            1000
        );
        this.camera.position.set(
            this.config.cameraPosition.x,
            this.config.cameraPosition.y,
            this.config.cameraPosition.z
        );

        // 初始化渲染器
        this.renderer = new THREE.WebGLRenderer({
            antialias: this.config.antialias,
            powerPreference: "high-performance"
        });
        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.shadowMap.enabled = this.config.enableShadows;
        this.renderer.shadowMap.type = this.config.shadowType;
        this.renderer.toneMapping = this.config.toneMapping;
        this.renderer.toneMappingExposure = this.config.exposure;
        this.renderer.physicallyCorrectLights = this.config.physicallyCorrectLights;
        this.renderer.setPixelRatio(this.config.pixelRatio);
        this.container.appendChild(this.renderer.domElement);

        // 初始化轨道控制器
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.target.set(
            this.config.cameraTarget.x,
            this.config.cameraTarget.y,
            this.config.cameraTarget.z
        );
        this.controls.enableDamping = this.config.enableDamping;
        this.controls.dampingFactor = this.config.dampingFactor;
        this.controls.enablePan = this.config.enablePan;
        this.controls.enableZoom = this.config.enableZoom;
        this.controls.enableRotate = this.config.enableRotate;
        this.controls.zoomSpeed = this.config.zoomSpeed;
        this.controls.rotateSpeed = this.config.rotateSpeed;
        this.controls.panSpeed = this.config.panSpeed;
        this.controls.maxPolarAngle = this.config.maxPolarAngle;
        this.controls.minPolarAngle = this.config.minPolarAngle;
        this.controls.maxDistance = this.config.maxDistance;
        this.controls.minDistance = this.config.minDistance;
        this.controls.autoRotate = this.config.autoRotate;
        this.controls.autoRotateSpeed = this.config.autoRotateSpeed;

        // 初始化灯光
        this.initLights();

        // 初始化仓库组
        this.warehouseGroup = new THREE.Group();
        this.scene.add(this.warehouseGroup);

        // 初始化变量
        this.warehouses = []; // 仓库配置数组
        this.currentWarehouse = null; // 当前显示的仓库
        this.shelves = []; // 货架数组
        this.slots = []; // 库位数组
        this.highlightedSlots = []; // 高亮显示的库位
        this.animationId = null; // 动画ID
        this.stats = { // 统计信息
            total: 0,
            free: 0,
            occupied: 0,
            reserved: 0,
            damaged: 0
        };
        this.editMode = false; // 编辑模式状态
        this.currentEditingSlot = null; // 当前正在编辑的库位
        this.toolsExpanded = false; // 工具栏展开状态
        this.miniMapEnabled = false; // 小地图启用状态

        // 绑定事件处理
        window.addEventListener('resize', () => this.onWindowResize());
        this.renderer.domElement.addEventListener('click', (event) => this.onCanvasClick(event));
        this.renderer.domElement.addEventListener('mousemove', (event) => this.onCanvasMouseMove(event));

        // 初始化UI事件
        this.initUIEvents();

        // 开始动画循环
        this.animate();
    }

    /**
     * 初始化灯光
     */
    initLights() {
        // 环境光 - 提高亮度
        const ambientLight = new THREE.AmbientLight(0xffffff, this.config.ambientLightIntensity);
        this.scene.add(ambientLight);

        // 添加辅助光
        const fillLight1 = new THREE.DirectionalLight(0x7777ff, 0.4);
        fillLight1.position.set(-50, 50, 50);
        this.scene.add(fillLight1);

        const fillLight2 = new THREE.DirectionalLight(0xff7777, 0.3);
        fillLight2.position.set(50, 50, -50);
        this.scene.add(fillLight2);

        const fillLight3 = new THREE.DirectionalLight(0xffffff, 0.2);
        fillLight3.position.set(0, -50, 0);
        this.scene.add(fillLight3);
    }

    /**
     * 初始化UI事件
     */
    initUIEvents() {
        // 搜索按钮点击事件
        document.getElementById('search-btn').addEventListener('click', () => {
            this.searchSlot();
        });

        // 搜索输入框回车事件
        document.getElementById('search-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.searchSlot();
            }
        });

        // 关闭信息面板
        document.getElementById('close-info').addEventListener('click', () => {
            this.hideInfoPanel();
        });

        // 重置视图
        document.getElementById('reset-view').addEventListener('click', () => {
            this.resetView();
        });

        // 方向控制
        document.querySelector('.direction-btn.up').addEventListener('click', () => {
            this.moveView('forward');
        });

        document.querySelector('.direction-btn.down').addEventListener('click', () => {
            this.moveView('backward');
        });

        document.querySelector('.direction-btn.left').addEventListener('click', () => {
            this.moveView('left');
        });

        document.querySelector('.direction-btn.right').addEventListener('click', () => {
            this.moveView('right');
        });

        // 放大
        document.getElementById('zoom-in').addEventListener('click', () => {
            this.zoomView('in');
        });

        // 缩小
        document.getElementById('zoom-out').addEventListener('click', () => {
            this.zoomView('out');
        });

        // 切换标签显示
        document.getElementById('toggle-labels').addEventListener('click', () => {
            this.toggleLabels();
        });

        // 切换统计面板
        document.getElementById('toggle-stats').addEventListener('click', () => {
            this.toggleStats();
        });

        // 切换配置面板
        document.getElementById('toggle-config').addEventListener('click', () => {
            this.toggleConfigPanel();
        });

        // 编辑模式
        document.getElementById('edit-mode').addEventListener('click', () => {
            this.toggleEditMode();
        });

        // 导出数据
        document.getElementById('export-data').addEventListener('click', () => {
            this.exportData();
        });

        // 切换工具栏
        document.getElementById('toggle-tools').addEventListener('click', () => {
            this.toggleTools();
        });

        // 切换小地图
        document.getElementById('toggle-minimap').addEventListener('click', () => {
            this.toggleMiniMap();
        });

        // 截图
        document.getElementById('screenshot').addEventListener('click', () => {
            this.takeScreenshot();
        });

        // 视图模式切换
        document.getElementById('view-mode').addEventListener('change', (e) => {
            this.changeViewMode(e.target.value);
        });

        // 标签显示设置
        document.getElementById('label-display').addEventListener('change', (e) => {
            this.setLabelDisplay(e.target.value);
        });

        // 标签位置设置
        document.getElementById('label-position').addEventListener('change', (e) => {
            this.setLabelPosition(e.target.value);
        });

        // 库位高度偏移设置
        document.getElementById('slot-offset').addEventListener('input', (e) => {
            this.setSlotHeightOffset(parseFloat(e.target.value));
        });

        // 编辑表单事件
        document.getElementById('cancel-edit').addEventListener('click', () => {
            this.cancelEdit();
        });

        document.getElementById('save-edit').addEventListener('click', () => {
            this.saveEdit();
        });
    }

    /**
     * 窗口大小调整处理
     */
    onWindowResize() {
        this.camera.aspect = window.innerWidth / window.innerHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(window.innerWidth, window.innerHeight);
    }

    /**
     * 画布鼠标移动事件处理
     * @param {Event} event 鼠标移动事件
     */
    onCanvasMouseMove(event) {
        // 计算鼠标在画布上的标准化设备坐标
        const mouse = new THREE.Vector2();
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = - (event.clientY / window.innerHeight) * 2 + 1;

        // 创建射线投射器
        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, this.camera);

        // 检查与库位的交点
        const intersects = raycaster.intersectObjects(this.slots.map(slot => slot.mesh));

        // 显示或隐藏工具提示
        const tooltip = document.getElementById('tooltip');
        if (intersects.length > 0) {
            const hoveredSlot = this.slots.find(slot => slot.mesh === intersects[0].object);
            if (hoveredSlot) {
                tooltip.textContent = hoveredSlot.id;
                tooltip.style.left = (event.clientX + 10) + 'px';
                tooltip.style.top = (event.clientY + 10) + 'px';
                tooltip.style.opacity = '1';
            }
        } else {
            tooltip.style.opacity = '0';
        }
    }

    /**
     * 画布点击事件处理
     * @param {Event} event 点击事件
     */
    onCanvasClick(event) {
        // 计算鼠标在画布上的标准化设备坐标
        const mouse = new THREE.Vector2();
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = - (event.clientY / window.innerHeight) * 2 + 1;

        // 创建射线投射器
        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, this.camera);

        // 检查与库位的交点
        const intersects = raycaster.intersectObjects(this.slots.map(slot => slot.mesh));

        if (intersects.length > 0) {
            const clickedSlot = this.slots.find(slot => slot.mesh === intersects[0].object);
            if (clickedSlot) {
                if (this.editMode) {
                    this.showEditForm(clickedSlot);
                } else {
                    this.showSlotInfo(clickedSlot);
                }

                // 触发点击事件回调
                if (this.config.onSlotClick) {
                    this.config.onSlotClick(clickedSlot);
                }
            }
        }
    }

    /**
     * 显示库位信息
     * @param {Object} slot 库位对象
     */
    showSlotInfo(slot) {
        const infoPanel = document.getElementById('info-panel');
        const infoContent = document.getElementById('info-content');
        const editForm = document.getElementById('edit-form');

        // 显示信息面板，隐藏编辑表单
        infoContent.style.display = 'block';
        editForm.style.display = 'none';

        let content = `
                    <div class="info-section">
                        <div class="info-section-title">基本信息</div>
                        <div class="info-item">
                            <div class="info-label">库位编号</div>
                            <div class="info-value">${slot.id}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">位置</div>
                            <div class="info-value">第${slot.row+1}排, 第${slot.column+1}列, 第${slot.level+1}层, 第${slot.index+1}个</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">状态</div>
                            <div class="info-value"><span class="status-indicator status-${slot.status}"></span>${this.config.slotStatus[slot.status].name}</div>
                        </div>
                    </div>
                `;

        if (slot.product) {
            content += `
                        <div class="info-section">
                            <div class="info-section-title">商品信息</div>
                            <div class="info-item">
                                <div class="info-label">商品ID</div>
                                <div class="info-value">${slot.product.id}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">商品名称</div>
                                <div class="info-value">${slot.product.name}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">商品分类</div>
                                <div class="info-value">${this.config.productCategories[slot.product.category].name}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">入库时间</div>
                                <div class="info-value">${slot.product.inboundDate || '未知'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">入库人</div>
                                <div class="info-value">${slot.product.inboundPerson || '未知'}</div>
                            </div>
                        </div>
                    `;
        } else {
            content += `
                        <div class="info-section">
                            <div class="info-section-title">商品信息</div>
                            <div class="info-item">
                                <div class="info-value">该库位暂无商品</div>
                            </div>
                        </div>
                    `;
        }

        infoContent.innerHTML = content;
        infoPanel.style.display = 'block';

        // 使信息面板可拖动
        this.makePanelDraggable(infoPanel);
    }

    /**
     * 显示编辑表单
     * @param {Object} slot 库位对象
     */
    showEditForm(slot) {
        const infoPanel = document.getElementById('info-panel');
        const infoContent = document.getElementById('info-content');
        const editForm = document.getElementById('edit-form');

        // 显示编辑表单，隐藏信息面板
        infoContent.style.display = 'none';
        editForm.style.display = 'block';

        // 填充表单数据
        document.getElementById('edit-slot-id').value = slot.id;
        document.getElementById('edit-slot-status').value = slot.status;

        if (slot.product) {
            document.getElementById('edit-product-id').value = slot.product.id;
            document.getElementById('edit-product-name').value = slot.product.name;
            document.getElementById('edit-product-category').value = slot.product.category;
            document.getElementById('edit-inbound-date').value = slot.product.inboundDate || '';
            document.getElementById('edit-inbound-person').value = slot.product.inboundPerson || '';
        } else {
            document.getElementById('edit-product-id').value = '';
            document.getElementById('edit-product-name').value = '';
            document.getElementById('edit-product-category').value = 'other';
            document.getElementById('edit-inbound-date').value = '';
            document.getElementById('edit-inbound-person').value = '';
        }

        // 保存当前编辑的库位
        this.currentEditingSlot = slot;

        infoPanel.style.display = 'block';

        // 使信息面板可拖动
        this.makePanelDraggable(infoPanel);
    }

    /**
     * 取消编辑
     */
    cancelEdit() {
        this.hideInfoPanel();
        this.currentEditingSlot = null;
    }

    /**
     * 保存编辑
     */
    saveEdit() {
        if (!this.currentEditingSlot) return;

        const status = document.getElementById('edit-slot-status').value;
        const productId = document.getElementById('edit-product-id').value;
        const productName = document.getElementById('edit-product-name').value;
        const productCategory = document.getElementById('edit-product-category').value;
        const inboundDate = document.getElementById('edit-inbound-date').value;
        const inboundPerson = document.getElementById('edit-inbound-person').value;

        // 更新库位信息
        const updates = {
            status: status,
            product: productId ? {
                id: productId,
                name: productName,
                category: productCategory,
                inboundDate: inboundDate,
                inboundPerson: inboundPerson
            } : null
        };

        this.updateSlot(this.currentEditingSlot.id, updates);

        // 隐藏面板
        this.hideInfoPanel();
        this.currentEditingSlot = null;

        // 触发更新事件回调
        if (this.config.onSlotUpdate) {
            this.config.onSlotUpdate(this.currentEditingSlot);
        }
    }

    /**
     * 隐藏信息面板
     */
    hideInfoPanel() {
        document.getElementById('info-panel').style.display = 'none';
    }

    /**
     * 使面板可拖动
     * @param {HTMLElement} panel 面板元素
     */
    makePanelDraggable(panel) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

        const dragMouseDown = (e) => {
            e = e || window.event;
            e.preventDefault();
            // 获取鼠标位置
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.addEventListener('mouseup', closeDragElement);
            document.addEventListener('mousemove', elementDrag);
        };

        const elementDrag = (e) => {
            e = e || window.event;
            e.preventDefault();
            // 计算新位置
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            // 设置元素的新位置
            panel.style.top = (panel.offsetTop - pos2) + "px";
            panel.style.left = (panel.offsetLeft - pos1) + "px";
        };

        const closeDragElement = () => {
            // 停止移动
            document.removeEventListener('mouseup', closeDragElement);
            document.removeEventListener('mousemove', elementDrag);
        };

        // 只有标题栏可拖动
        const header = panel.querySelector('.panel-header');
        header.onmousedown = dragMouseDown;
    }

    /**
     * 搜索库位
     */
    searchSlot() {
        const query = document.getElementById('search-input').value.trim();
        if (!query) return;

        // 显示加载中
        this.showLoading();

        // 使用setTimeout避免界面卡顿
        setTimeout(() => {
            // 清除之前的高亮
            this.clearHighlights();

            // 查找匹配的库位
            const matchedSlots = this.slots.filter(slot =>
                slot.id.includes(query) ||
                (slot.product && (
                    slot.product.id.includes(query) ||
                    slot.product.name.includes(query)
                ))
            );

            if (matchedSlots.length > 0) {
                // 高亮匹配的库位
                matchedSlots.forEach(slot => {
                    this.highlightSlot(slot);
                });

                // 更新统计信息
                this.updateStats();

                // 将相机对准第一个匹配的库位
                this.focusOnSlot(matchedSlots[0]);

                // 触发搜索完成事件回调
                if (this.config.onSearchComplete) {
                    this.config.onSearchComplete(matchedSlots);
                }
            } else {
                alert('未找到匹配的库位或商品');
            }

            // 隐藏加载中
            this.hideLoading();
        }, 100);
    }

    /**
     * 显示加载中
     */
    showLoading() {
        document.getElementById('loading-overlay').style.display = 'flex';
    }

    /**
     * 隐藏加载中
     */
    hideLoading() {
        document.getElementById('loading-overlay').style.display = 'none';
    }

    /**
     * 高亮显示库位
     * @param {Object} slot 库位对象
     */
    highlightSlot(slot) {
        // 保存原始材质
        const originalMaterial = slot.mesh.material;

        // 创建高亮材质
        const highlightMaterial = new THREE.MeshPhongMaterial({
            color: 0x00ffff,
            emissive: 0x008888,
            transparent: true,
            opacity: 0.8
        });

        // 应用高亮材质
        slot.mesh.material = highlightMaterial;

        // 保存高亮信息以便后续清除
        this.highlightedSlots.push({
            mesh: slot.mesh,
            originalMaterial: originalMaterial
        });

        // 添加闪烁动画
        if (this.config.enableAnimations) {
            let intensity = 0;
            const animateHighlight = () => {
                intensity = (intensity + 0.05) % (Math.PI * 2);
                highlightMaterial.emissiveIntensity = Math.sin(intensity) * 0.5 + 0.5;

                if (this.highlightedSlots.find(s => s.mesh === slot.mesh)) {
                    requestAnimationFrame(animateHighlight);
                }
            };

            animateHighlight();
        }
    }

    /**
     * 清除所有高亮
     */
    clearHighlights() {
        this.highlightedSlots.forEach(item => {
            item.mesh.material = item.originalMaterial;
        });
        this.highlightedSlots = [];
    }

    /**
     * 将相机对准指定库位
     * @param {Object} slot 库位对象
     */
    focusOnSlot(slot) {
        const slotPosition = new THREE.Vector3();
        slot.mesh.getWorldPosition(slotPosition);

        // 移动相机位置
        this.camera.position.set(
            slotPosition.x + 10,
            slotPosition.y + 10,
            slotPosition.z + 10
        );

        // 让相机看向库位
        this.controls.target.copy(slotPosition);
        this.controls.update();

        // 触发视图变化事件回调
        if (this.config.onViewChange) {
            this.config.onViewChange('focus', slotPosition);
        }
    }

    /**
     * 移动视图
     * @param {string} direction 方向 (forward, backward, left, right, up, down)
     */
    moveView(direction) {
        const distance = 15;
        const directionVector = new THREE.Vector3();

        switch (direction) {
            case 'forward':
                directionVector.set(0, 0, -distance);
                break;
            case 'backward':
                directionVector.set(0, 0, distance);
                break;
            case 'left':
                directionVector.set(-distance, 0, 0);
                break;
            case 'right':
                directionVector.set(distance, 0, 0);
                break;
            case 'up':
                directionVector.set(0, distance, 0);
                break;
            case 'down':
                directionVector.set(0, -distance, 0);
                break;
        }

        // 应用相机方向
        directionVector.applyQuaternion(this.camera.quaternion);
        this.camera.position.add(directionVector);
        this.controls.target.add(directionVector);
        this.controls.update();

        // 触发视图变化事件回调
        if (this.config.onViewChange) {
            this.config.onViewChange('move', direction);
        }
    }

    /**
     * 缩放视图
     * @param {string} type 缩放类型 (in, out)
     */
    zoomView(type) {
        const factor = type === 'in' ? 0.8 : 1.2;
        this.camera.fov *= factor;
        this.camera.updateProjectionMatrix();

        // 触发视图变化事件回调
        if (this.config.onViewChange) {
            this.config.onViewChange('zoom', type);
        }
    }

    /**
     * 切换标签显示
     */
    toggleLabels() {
        this.config.showLabels = !this.config.showLabels;

        // 显示或隐藏所有标签
        this.slots.forEach(slot => {
            if (slot.label) {
                slot.label.visible = this.config.showLabels;
            }
            if (slot.productLabel) {
                slot.productLabel.visible = this.config.showLabels;
            }
        });

        // 显示或隐藏货架标签
        this.shelves.forEach(shelf => {
            if (shelf.label) {
                shelf.label.visible = this.config.showLabels;
            }
        });
    }

    /**
     * 设置标签显示模式
     * @param {string} mode 显示模式
     */
    setLabelDisplay(mode) {
        const showShelfLabels = mode === 'all' || mode === 'shelf';
        const showSlotLabels = mode === 'all' || mode === 'slot';

        // 显示或隐藏货架标签
        this.shelves.forEach(shelf => {
            if (shelf.label) {
                shelf.label.visible = showShelfLabels;
            }
        });

        // 显示或隐藏库位标签
        this.slots.forEach(slot => {
            if (slot.label) {
                slot.label.visible = showSlotLabels;
            }
            if (slot.productLabel) {
                slot.productLabel.visible = showSlotLabels;
            }
        });
    }

    /**
     * 设置标签位置
     * @param {string} position 标签位置 (back, top)
     */
    setLabelPosition(position) {
        this.config.slotLabelPosition = position;

        // 重新渲染仓库
        if (this.currentWarehouse) {
            this.renderWarehouse();
        }
    }

    /**
     * 设置库位高度偏移
     * @param {number} offset 偏移量
     */
    setSlotHeightOffset(offset) {
        this.config.slotHeightOffset = offset;

        // 重新渲染仓库
        if (this.currentWarehouse) {
            this.renderWarehouse();
        }
    }

    /**
     * 切换编辑模式
     */
    toggleEditMode() {
        this.editMode = !this.editMode;
        const editBtn = document.getElementById('edit-mode');

        if (this.editMode) {
            editBtn.classList.add('active');
            editBtn.title = '退出编辑模式';
        } else {
            editBtn.classList.remove('active');
            editBtn.title = '编辑模式';
            this.hideInfoPanel();
            this.currentEditingSlot = null;
        }

        // 触发编辑模式变化事件回调
        if (this.config.onEditModeChange) {
            this.config.onEditModeChange(this.editMode);
        }
    }

    /**
     * 切换配置面板
     */
    toggleConfigPanel() {
        const configPanel = document.getElementById('config-panel');
        configPanel.style.display = configPanel.style.display === 'none' ? 'block' : 'none';
    }

    /**
     * 切换工具栏
     */
    toggleTools() {
        this.toolsExpanded = !this.toolsExpanded;
        const toolbar = document.getElementById('toolbar');
        const toggleBtn = document.getElementById('toggle-tools');

        if (this.toolsExpanded) {
            toolbar.style.transform = 'translateX(0)';
            toggleBtn.innerHTML = '✕';
            toggleBtn.title = '收起工具栏';
        } else {
            toolbar.style.transform = 'translateX(80%)';
            toggleBtn.innerHTML = '☰';
            toggleBtn.title = '展开工具栏';
        }
    }

    /**
     * 切换小地图
     */
    toggleMiniMap() {
        this.miniMapEnabled = !this.miniMapEnabled;
        const miniMap = document.getElementById('mini-map');
        miniMap.style.display = this.miniMapEnabled ? 'block' : 'none';

        // 如果启用小地图，更新小地图内容
        if (this.miniMapEnabled) {
            this.updateMiniMap();
        }
    }

    /**
     * 更新小地图
     */
    updateMiniMap() {
        // 简化的实现，实际应用中需要更复杂的小地图渲染逻辑
        const miniMapContent = document.getElementById('mini-map-content');
        miniMapContent.innerHTML = '<div style="padding:10px;text-align:center;">小地图预览</div>';
    }

    /**
     * 截图
     */
    takeScreenshot() {
        this.renderer.render(this.scene, this.camera);
        try {
            const dataURL = this.renderer.domElement.toDataURL('image/png');
            const a = document.createElement('a');
            a.href = dataURL;
            a.download = `warehouse_screenshot_${new Date().getTime()}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        } catch (e) {
            console.error('截图失败:', e);
            alert('截图功能需要安全上下文(HTTPS或localhost)');
        }
    }

    /**
     * 切换视图模式
     * @param {string} mode 视图模式
     */
    changeViewMode(mode) {
        switch (mode) {
            case 'overview':
                this.resetView();
                break;
            case 'shelf':
                // 将视图调整到货架级别
                this.camera.position.set(30, 20, 30);
                this.controls.target.set(0, 5, 0);
                this.controls.update();
                break;
            case 'slot':
                // 将视图调整到库位级别
                this.camera.position.set(10, 5, 10);
                this.controls.target.set(0, 2, 0);
                this.controls.update();
                break;
        }

        // 触发视图变化事件回调
        if (this.config.onViewChange) {
            this.config.onViewChange('mode', mode);
        }
    }

    /**
     * 切换统计面板显示
     */
    toggleStats() {
        this.config.showStats = !this.config.showStats;
        document.getElementById('stats-panel').style.display = this.config.showStats ? 'block' : 'none';
    }

    /**
     * 导出数据
     */
    exportData() {
        const data = this.exportToJSON();
        const blob = new Blob([data], { type: 'application/json' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `warehouse_${this.currentWarehouse.id}_${new Date().toISOString().slice(0, 10)}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * 重置视图
     */
    resetView() {
        this.camera.position.set(
            this.config.cameraPosition.x,
            this.config.cameraPosition.y,
            this.config.cameraPosition.z
        );
        this.controls.target.set(
            this.config.cameraTarget.x,
            this.config.cameraTarget.y,
            this.config.cameraTarget.z
        );
        this.controls.update();

        // 触发视图变化事件回调
        if (this.config.onViewChange) {
            this.config.onViewChange('reset');
        }
    }

    /**
     * 动画循环
     */
    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }

    /**
     * 添加仓库配置
     * @param {Object} config 仓库配置
     */
    addWarehouse(config) {
        // 设置默认值
        const warehouseConfig = {
            id: `WH${this.warehouses.length + 1}`,
            name: `仓库${this.warehouses.length + 1}`,
            rows: 4,
            columns: 6,
            levels: 3,
            slotsPerLevel: 8, // 每层库位数量
            shelfWidth: 20,
            shelfDepth: 4,
            shelfHeight: 2.5,
            aisleWidth: 40, // 通道宽度等于货架宽度的2倍
            rowAisleWidth: 40, // 行通道宽度
            columnAisleWidth: 40, // 列通道宽度
            layout: {
                type: 'STRAIGHT',
                xSpaces: 6,
                ySpaces: 4
            },
            slotLength: 2.5, // 库位长度
            slotWidth: 2,    // 库位宽度
            slotHeight: 1.5, // 库位高度
            numberingRule: {
                shelf: 'FORWARD_LEFT_TO_RIGHT', // 货架编号规则
                slot: 'FULL' // 库位编号规则
            },
            ...config
        };

        this.warehouses.push(warehouseConfig);

        // 更新仓库标签
        this.updateWarehouseTabs();

        // 如果是第一个仓库，设置为当前仓库
        if (this.warehouses.length === 1) {
            this.setCurrentWarehouse(warehouseConfig.id);
        }
    }

    /**
     * 更新仓库标签
     */
    updateWarehouseTabs() {
        const tabsContainer = document.getElementById('warehouse-tabs');
        tabsContainer.innerHTML = '';

        this.warehouses.forEach(warehouse => {
            const tab = document.createElement('div');
            tab.className = `warehouse-tab ${warehouse.id === this.currentWarehouse?.id ? 'active' : ''}`;
            tab.textContent = warehouse.name;
            tab.addEventListener('click', () => {
                this.setCurrentWarehouse(warehouse.id);
            });

            tabsContainer.appendChild(tab);
        });
    }

    /**
     * 设置当前显示的仓库
     * @param {string} warehouseId 仓库ID
     */
    setCurrentWarehouse(warehouseId) {
        const warehouse = this.warehouses.find(w => w.id === warehouseId);
        if (!warehouse) return;

        this.currentWarehouse = warehouse;

        // 重新渲染仓库
        this.renderWarehouse();

        // 更新标签状态
        this.updateWarehouseTabs();

        // 更新统计信息
        this.updateStats();

        // 触发仓库变化事件回调
        if (this.config.onWarehouseChange) {
            this.config.onWarehouseChange(warehouse);
        }
    }

    /**
     * 更新统计信息
     */
    updateStats() {
        // 重置统计
        this.stats = {
            total: this.slots.length,
            free: 0,
            occupied: 0,
            reserved: 0,
            damaged: 0
        };

        // 计算各状态数量
        this.slots.forEach(slot => {
            if (this.stats[slot.status] !== undefined) {
                this.stats[slot.status]++;
            }
        });

        // 更新UI
        document.getElementById('stats-warehouse').textContent = this.currentWarehouse.name;
        document.getElementById('stats-total').textContent = this.stats.total;
        document.getElementById('stats-free').textContent = this.stats.free;
        document.getElementById('stats-occupied').textContent = this.stats.occupied;
        document.getElementById('stats-reserved').textContent = this.stats.reserved;
        document.getElementById('stats-damaged').textContent = this.stats.damaged;
    }

    /**
     * 渲染仓库
     */
    renderWarehouse() {
        // 显示加载中
        this.showLoading();

        // 使用setTimeout避免界面卡顿
        setTimeout(() => {
            // 清除现有仓库内容
            this.warehouseGroup.clear();
            this.shelves = [];
            this.slots = [];
            this.clearHighlights();

            if (!this.currentWarehouse) return;

            const config = this.currentWarehouse;

            // 创建地面
            this.createGround(config);

            // 创建货架
            this.createShelves(config);

            // 添加仓库名称
            this.addWarehouseName(config);

            // 重置视图
            this.resetView();

            // 隐藏加载中
            this.hideLoading();
        }, 100);
    }

    /**
     * 创建地面
     * @param {Object} config 仓库配置
     */
    createGround(config) {
        // 计算地面大小（根据货架布局）
        const groundWidth = config.columns * (config.shelfWidth + config.columnAisleWidth);
        const groundDepth = config.rows * (config.shelfDepth + config.rowAisleWidth);

        // 创建地面网格
        const groundGeometry = new THREE.PlaneGeometry(groundWidth * 1.5, groundDepth * 1.5);
        const groundMaterial = new THREE.MeshPhongMaterial({
            color: this.config.groundColor,
            shininess: 10,
            side: THREE.DoubleSide
        });

        const ground = new THREE.Mesh(groundGeometry, groundMaterial);
        ground.rotation.x = Math.PI / 2;
        ground.position.y = -0.1;
        ground.receiveShadow = true;

        this.warehouseGroup.add(ground);
    }

    /**
     * 添加仓库名称
     * @param {Object} config 仓库配置
     */
    addWarehouseName(config) {
        // 计算地面大小
        const groundWidth = config.columns * (config.shelfWidth + config.columnAisleWidth);
        const groundDepth = config.rows * (config.shelfDepth + config.rowAisleWidth);

        // 创建canvas用于文本渲染
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 128;
        const context = canvas.getContext('2d');

        // 透明背景
        context.clearRect(0, 0, canvas.width, canvas.height);

        // 绘制文本
        context.font = 'bold 48px Microsoft YaHei';
        context.fillStyle = 'white';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(config.name, canvas.width / 2, canvas.height / 2);

        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.DoubleSide
        });

        const geometry = new THREE.PlaneGeometry(30, 7.5);
        const textMesh = new THREE.Mesh(geometry, material);

        // 将仓库名称放在所有货架的最后面，紧贴地面
        textMesh.position.set(
            this.config.groundNameOffset.x,
            0.1 + this.config.groundNameOffset.y,
            groundDepth / 2 + 5 + this.config.groundNameOffset.z
        );
        textMesh.rotation.x = -Math.PI / 2;

        this.warehouseGroup.add(textMesh);
    }

    /**
     * 创建货架
     * @param {Object} config 仓库配置
     */
    createShelves(config) {
        const {
            rows,
            columns,
            levels,
            slotsPerLevel,
            shelfWidth,
            shelfDepth,
            shelfHeight,
            rowAisleWidth,
            columnAisleWidth
        } = config;

        // 根据布局类型创建货架
        switch (config.layout.type) {
            case 'U_SHAPE':
                this.createUShapeLayout(config);
                break;
            case 'L_SHAPE':
                this.createLShapeLayout(config);
                break;
            case 'STRAIGHT':
            default:
                this.createStraightLayout(config);
        }
    }

    /**
     * 创建直线型布局
     * @param {Object} config 仓库配置
     */
    createStraightLayout(config) {
        const {
            rows,
            columns,
            levels,
            slotsPerLevel,
            shelfWidth,
            shelfDepth,
            shelfHeight,
            rowAisleWidth,
            columnAisleWidth
        } = config;

        const totalWidth = columns * shelfWidth + (columns - 1) * columnAisleWidth;
        const totalDepth = rows * shelfDepth + (rows - 1) * rowAisleWidth;

        // 起始位置（使货架居中）
        const startX = -totalWidth / 2 + shelfWidth / 2;
        const startZ = -totalDepth / 2 + shelfDepth / 2;

        // 生成货架编号（从左到右，从后排到前排）
        const shelfNumbers = this.generateShelfNumbers(config);

        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < columns; col++) {
                const x = startX + col * (shelfWidth + columnAisleWidth);
                const z = startZ + row * (shelfDepth + rowAisleWidth);

                // 获取货架编号
                const shelfNumber = shelfNumbers[row][col];

                // 创建货架
                this.createShelf(config, row, col, shelfNumber, x, z);

                // 创建货架上的库位
                for (let level = 0; level < levels; level++) {
                    for (let slotIndex = 0; slotIndex < slotsPerLevel; slotIndex++) {
                        const y = level * shelfHeight + shelfHeight / 2 + this.config.slotHeightOffset;
                        this.createShelfSlot(config, row, level, col, slotIndex, shelfNumber, x, y, z);
                    }
                }
            }
        }
    }

    /**
     * 生成货架编号（从左到右，从后排到前排）
     * @param {Object} config 仓库配置
     * @returns {Array} 货架编号矩阵
     */
    generateShelfNumbers(config) {
        const { rows, columns } = config;
        const numbers = [];
        let counter = 1;

        // 从最后面一排开始，从左到右编号
        for (let row = rows - 1; row >= 0; row--) {
            numbers[row] = [];
            for (let col = 0; col < columns; col++) {
                numbers[row][col] = counter++;
            }
        }

        return numbers;
    }

    /**
     * 创建U型布局
     * @param {Object} config 仓库配置
     */
    createUShapeLayout(config) {
        // U型布局实现
        // 简化处理，实际实现需要根据具体需求
        this.createStraightLayout(config);
    }

    /**
     * 创建L型布局
     * @param {Object} config 仓库配置
     */
    createLShapeLayout(config) {
        // L型布局实现
        // 简化处理，实际实现需要根据具体需求
        this.createStraightLayout(config);
    }

    /**
     * 创建货架
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} col 列
     * @param {number} shelfNumber 货架编号
     * @param {number} x X坐标
     * @param {number} z Z坐标
     */
    createShelf(config, row, col, shelfNumber, x, z) {
        const { shelfWidth, shelfDepth, shelfHeight, levels, slotsPerLevel, slotLength } = config;

        // 计算库位间距
        const gap = slotLength * this.config.slotGapRatio;
        const totalSlotsWidth = slotLength * slotsPerLevel + gap * (slotsPerLevel - 1);

        // 计算立柱位置（根据库位布局动态调整）
        const pillarOffsetX = totalSlotsWidth / 2;
        const pillarOffsetZ = shelfDepth / 2 - 0.2;

        // 创建立柱
        const pillarGeometry = new THREE.BoxGeometry(
            0.2,
            shelfHeight * levels,
            0.2
        );

        const pillarMaterial = new THREE.MeshPhongMaterial({
            color: this.config.shelfMaterialColor
        });

        // 四个角落的立柱
        const pillarPositions = [
            { x: x - pillarOffsetX, y: shelfHeight*levels/2, z: z - pillarOffsetZ },
            { x: x - pillarOffsetX, y: shelfHeight*levels/2, z: z + pillarOffsetZ },
            { x: x + pillarOffsetX, y: shelfHeight*levels/2, z: z - pillarOffsetZ },
            { x: x + pillarOffsetX, y: shelfHeight*levels/2, z: z + pillarOffsetZ }
        ];

        pillarPositions.forEach(pos => {
            const pillar = new THREE.Mesh(pillarGeometry, pillarMaterial);
            pillar.position.set(pos.x, pos.y, pos.z);
            pillar.castShadow = true;
            pillar.receiveShadow = true;
            this.warehouseGroup.add(pillar);
        });

        // 创建隔板（每层）
        for (let level = 0; level < levels; level++) {
            const plateGeometry = new THREE.BoxGeometry(
                totalSlotsWidth + 0.4,
                0.1,
                shelfDepth
            );

            const plateMaterial = new THREE.MeshPhongMaterial({
                color: this.config.plateMaterialColor
            });
            const plate = new THREE.Mesh(plateGeometry, plateMaterial);
            plate.position.set(x, level * shelfHeight + shelfHeight/2, z);
            plate.castShadow = true;
            plate.receiveShadow = true;
            this.warehouseGroup.add(plate);
        }

        // 添加货架编号
        this.addShelfLabel(config, row, col, shelfNumber, x, z, levels * shelfHeight);

        // 保存货架信息
        this.shelves.push({
            id: this.generateShelfId(config, row, col, shelfNumber),
            row: row,
            column: col,
            number: shelfNumber,
            position: { x, z }
        });
    }

    /**
     * 生成货架ID
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} col 列
     * @param {number} shelfNumber 货架编号
     * @returns {string} 货架ID
     */
    generateShelfId(config, row, col, shelfNumber) {
        if (config.numberingRule.shelf === 'ROW_COL') {
            return `R${row+1}C${col+1}`;
        } else {
            return `S${shelfNumber}`;
        }
    }

    /**
     * 添加货架编号标签
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} col 列
     * @param {number} shelfNumber 货架编号
     * @param {number} x X坐标
     * @param {number} z Z坐标
     * @param {number} height 货架高度
     */
    addShelfLabel(config, row, col, shelfNumber, x, z, height) {
        const shelfId = this.generateShelfId(config, row, col, shelfNumber);

        // 创建canvas用于文本渲染
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 128;
        const context = canvas.getContext('2d');

        // 透明背景
        context.clearRect(0, 0, canvas.width, canvas.height);

        // 绘制文本
        context.font = 'bold 24px Microsoft YaHei';
        context.fillStyle = 'white';
        context.textAlign = 'center';
        context.fillText(`货架 ${shelfId}`, canvas.width / 2, 50);

        context.font = '16px Microsoft YaHei';
        context.fillText(`第${row+1}排 第${col+1}列`, canvas.width / 2, 80);

        // 创建纹理
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.DoubleSide
        });

        // 创建平面几何体
        const geometry = new THREE.PlaneGeometry(8, 4);
        const label = new THREE.Mesh(geometry, material);

        // 设置标签位置（在货架前方）
        label.position.set(x, height + 2, z - config.shelfDepth * 0.6);

        // 添加到场景
        this.warehouseGroup.add(label);

        return label;
    }

    /**
     * 创建货架库位
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @param {number} shelfNumber 货架编号
     * @param {number} x X坐标
     * @param {number} y Y坐标
     * @param {number} z Z坐标
     */
    createShelfSlot(config, row, level, col, index, shelfNumber, x, y, z) {
        const { slotLength, slotWidth, slotHeight, slotsPerLevel } = config;

        // 计算库位间距
        const gap = slotLength * this.config.slotGapRatio;
        const totalWidth = slotLength * slotsPerLevel + gap * (slotsPerLevel - 1);

        // 计算库位在货架上的位置
        const slotX = x - totalWidth/2 + slotLength/2 + index * (slotLength + gap);

        // 创建库位几何体 (长:宽:高 = 5:4:3)
        const geometry = new THREE.BoxGeometry(
            slotLength * 0.95,  // 长度方向
            slotHeight * 0.9,   // 高度方向
            slotWidth * 0.95    // 宽度方向
        );

        // 获取库位状态和颜色
        const slotStatus = this.getSlotStatus(config, row, level, col, index);
        const color = new THREE.Color(this.config.slotStatus[slotStatus].color);

        // 创建库位材质
        const material = new THREE.MeshPhongMaterial({
            color: color,
            transparent: true,
            opacity: 0.8
        });

        // 创建库位网格
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(slotX, y, z);
        mesh.castShadow = true;
        mesh.receiveShadow = true;

        // 添加库位到场景
        this.warehouseGroup.add(mesh);

        // 添加库位文本标签
        const label = this.addSlotLabel(config, row, level, col, index, shelfNumber, slotX, y, z, slotStatus);

        // 添加商品分类标签
        const productLabel = this.addProductLabel(config, row, level, col, index, slotX, y, z);

        // 保存库位信息
        const slot = {
            id: this.generateSlotId(config, row, level, col, index, shelfNumber),
            row: row,
            level: level,
            column: col,
            index: index,
            shelfNumber: shelfNumber,
            status: slotStatus,
            product: this.getSlotProduct(config, row, level, col, index),
            mesh: mesh,
            label: label,
            productLabel: productLabel,
            position: { x: slotX, y, z }
        };

        this.slots.push(slot);
    }

    /**
     * 生成库位ID
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @param {number} shelfNumber 货架编号
     * @returns {string} 库位ID
     */
    generateSlotId(config, row, level, col, index, shelfNumber) {
        if (config.numberingRule.slot === 'SHEET_LEVEL_INDEX') {
            const shelfId = this.generateShelfId(config, row, col, shelfNumber);
            return `${config.id}-${shelfId}-${level+1}-${index+1}`;
        } else {
            return `${config.id}-${row+1}-${col+1}-${level+1}-${index+1}`;
        }
    }

    /**
     * 添加库位文本标签
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @param {number} shelfNumber 货架编号
     * @param {number} x X坐标
     * @param {number} y Y坐标
     * @param {number} z Z坐标
     * @param {string} status 库位状态
     * @returns {THREE.Mesh} 标签网格对象
     */
    addSlotLabel(config, row, level, col, index, shelfNumber, x, y, z, status) {
        const slotId = this.generateSlotId(config, row, level, col, index, shelfNumber);

        // 创建canvas用于文本渲染
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 128;
        const context = canvas.getContext('2d');

        // 透明背景
        context.clearRect(0, 0, canvas.width, canvas.height);

        // 绘制文本
        context.font = 'bold 16px Microsoft YaHei';
        context.fillStyle = 'white';
        context.textAlign = 'center';

        // 库位编号
        context.fillText(slotId, canvas.width / 2, 40);

        // 状态
        context.font = '14px Microsoft YaHei';
        context.fillText(this.config.slotStatus[status].name, canvas.width / 2, 70);

        // 位置信息
        context.fillText(`第${index+1}个`, canvas.width / 2, 100);

        // 创建纹理
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.DoubleSide
        });

        // 创建平面几何体
        const geometry = new THREE.PlaneGeometry(4, 2);
        const label = new THREE.Mesh(geometry, material);

        // 设置标签位置（根据配置决定在背面还是顶面）
        if (this.config.slotLabelPosition === 'back') {
            // 在库位背面
            label.position.set(x, y, z + config.slotWidth * 0.5);
        } else {
            // 在库位顶面
            label.position.set(x, y + config.slotHeight * 0.5, z);
            label.rotation.x = -Math.PI / 2;
        }

        // 添加到场景
        this.warehouseGroup.add(label);

        return label;
    }

    /**
     * 添加商品分类标签
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @param {number} x X坐标
     * @param {number} y Y坐标
     * @param {number} z Z坐标
     * @returns {THREE.Mesh|null} 标签网格对象或null
     */
    addProductLabel(config, row, level, col, index, x, y, z) {
        const product = this.getSlotProduct(config, row, level, col, index);
        if (!product) return null;

        // 创建canvas用于文本渲染
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 64;
        const context = canvas.getContext('2d');

        // 透明背景
        context.clearRect(0, 0, canvas.width, canvas.height);

        // 绘制文本
        context.font = 'bold 16px Microsoft YaHei';
        context.fillStyle = this.config.productCategories[product.category].color;
        context.textAlign = 'center';
        context.fillText(this.config.productCategories[product.category].name, canvas.width / 2, 35);

        // 创建纹理
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            side: THREE.DoubleSide
        });

        // 创建平面几何体
        const geometry = new THREE.PlaneGeometry(3, 0.75);
        const label = new THREE.Mesh(geometry, material);

        // 设置标签位置（在库位顶面）
        label.position.set(x, y + config.slotHeight * 0.5, z);
        label.rotation.x = -Math.PI / 2;

        // 添加到场景
        this.warehouseGroup.add(label);

        return label;
    }

    /**
     * 获取库位状态
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @returns {string} 库位状态
     */
    getSlotStatus(config, row, level, col, index) {
        // 简化实现，实际应从配置数据中获取
        const statuses = Object.keys(this.config.slotStatus);
        return statuses[Math.floor(Math.random() * statuses.length)];
    }

    /**
     * 获取库位商品信息
     * @param {Object} config 仓库配置
     * @param {number} row 排
     * @param {number} level 层
     * @param {number} col 列
     * @param {number} index 库位索引
     * @returns {Object|null} 商品信息
     */
    getSlotProduct(config, row, level, col, index) {
        // 简化实现，实际应从配置数据中获取
        if (Math.random() > 0.5) {
            const categories = Object.keys(this.config.productCategories);
            const category = categories[Math.floor(Math.random() * categories.length)];

            return {
                id: `P${Math.floor(1000 + Math.random() * 9000)}`,
                name: `商品${Math.floor(1 + Math.random() * 100)}`,
                category: category,
                inboundDate: new Date().toLocaleDateString(),
                inboundPerson: `操作员${Math.floor(1 + Math.random() * 10)}`
            };
        }
        return null;
    }

    /**
     * 通过JSON数据初始化仓库
     * @param {string|Object} jsonData JSON数据或JSON字符串
     */
    initFromJSON(jsonData) {
        const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;

        // 清除现有仓库
        this.warehouses = [];

        // 添加仓库配置
        if (Array.isArray(data.warehouses)) {
            data.warehouses.forEach(warehouse => {
                this.addWarehouse(warehouse);
            });
        } else if (data.warehouse) {
            this.addWarehouse(data.warehouse);
        }

        // 设置当前仓库
        if (data.currentWarehouseId) {
            this.setCurrentWarehouse(data.currentWarehouseId);
        }
    }

    /**
     * 导出仓库数据为JSON
     * @returns {string} JSON字符串
     */
    exportToJSON() {
        const data = {
            warehouses: this.warehouses,
            currentWarehouseId: this.currentWarehouse?.id,
            slots: this.slots.map(slot => ({
                id: slot.id,
                row: slot.row,
                level: slot.level,
                column: slot.column,
                index: slot.index,
                status: slot.status,
                product: slot.product,
                position: slot.position
            }))
        };

        return JSON.stringify(data, null, 2);
    }

    /**
     * 更新库位信息
     * @param {string} slotId 库位ID
     * @param {Object} updates 更新内容
     */
    updateSlot(slotId, updates) {
        const slot = this.slots.find(s => s.id === slotId);
        if (!slot) return false;

        // 更新库位信息
        if (updates.status !== undefined) {
            slot.status = updates.status;
            slot.mesh.material.color = new THREE.Color(this.config.slotStatus[updates.status].color);

            // 更新标签
            if (slot.label) {
                this.warehouseGroup.remove(slot.label);
                slot.label = this.addSlotLabel(
                    this.currentWarehouse,
                    slot.row,
                    slot.level,
                    slot.column,
                    slot.index,
                    slot.shelfNumber,
                    slot.position.x,
                    slot.position.y,
                    slot.position.z,
                    updates.status
                );
            }
        }

        if (updates.product !== undefined) {
            slot.product = updates.product;

            // 更新商品标签
            if (slot.productLabel) {
                this.warehouseGroup.remove(slot.productLabel);
            }
            slot.productLabel = this.addProductLabel(
                this.currentWarehouse,
                slot.row,
                slot.level,
                slot.column,
                slot.index,
                slot.position.x,
                slot.position.y,
                slot.position.z
            );
        }

        // 更新统计信息
        this.updateStats();

        return true;
    }

    /**
     * 批量更新库位信息
     * @param {Array} updates 更新数组
     */
    batchUpdateSlots(updates) {
        updates.forEach(update => {
            this.updateSlot(update.slotId, update.updates);
        });
    }

    /**
     * 设置库位出库信息
     * @param {string} slotId 库位ID
     * @param {Object} outboundInfo 出库信息
     */
    setOutboundInfo(slotId, outboundInfo) {
        const slot = this.slots.find(s => s.id === slotId);
        if (!slot) return false;

        // 更新库位状态
        slot.status = 'free';
        slot.product = null;
        slot.mesh.material.color = new THREE.Color(this.config.slotStatus.free.color);

        // 更新商品标签
        if (slot.productLabel) {
            this.warehouseGroup.remove(slot.productLabel);
            slot.productLabel = null;
        }

        // 更新标签
        if (slot.label) {
            this.warehouseGroup.remove(slot.label);
            slot.label = this.addSlotLabel(
                this.currentWarehouse,
                slot.row,
                slot.level,
                slot.column,
                slot.index,
                slot.shelfNumber,
                slot.position.x,
                slot.position.y,
                slot.position.z,
                'free'
            );
        }

        // 更新统计信息
        this.updateStats();

        return true;
    }

    /**
     * 设置库位入库信息
     * @param {string} slotId 库位ID
     * @param {Object} inboundInfo 入库信息
     */
    setInboundInfo(slotId, inboundInfo) {
        const slot = this.slots.find(s => s.id === slotId);
        if (!slot) return false;

        // 更新库位状态
        slot.status = 'occupied';
        slot.product = {
            id: inboundInfo.productId,
            name: inboundInfo.productName,
            category: inboundInfo.category || 'other',
            inboundDate: new Date().toLocaleDateString(),
            inboundPerson: inboundInfo.inboundPerson || '未知'
        };
        slot.mesh.material.color = new THREE.Color(this.config.slotStatus.occupied.color);

        // 更新商品标签
        if (slot.productLabel) {
            this.warehouseGroup.remove(slot.productLabel);
        }
        slot.productLabel = this.addProductLabel(
            this.currentWarehouse,
            slot.row,
            slot.level,
            slot.column,
            slot.index,
            slot.position.x,
            slot.position.y,
            slot.position.z
        );

        // 更新标签
        if (slot.label) {
            this.warehouseGroup.remove(slot.label);
            slot.label = this.addSlotLabel(
                this.currentWarehouse,
                slot.row,
                slot.level,
                slot.column,
                slot.index,
                slot.shelfNumber,
                slot.position.x,
                slot.position.y,
                slot.position.z,
                'occupied'
            );
        }

        // 更新统计信息
        this.updateStats();

        return true;
    }

    /**
     * 销毁实例，释放资源
     */
    destroy() {
        // 停止动画循环
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }

        // 移除事件监听
        window.removeEventListener('resize', () => this.onWindowResize());
        this.renderer.domElement.removeEventListener('click', (event) => this.onCanvasClick(event));
        this.renderer.domElement.removeEventListener('mousemove', (event) => this.onCanvasMouseMove(event));

        // 清除渲染器
        this.renderer.dispose();

        // 移除画布
        if (this.container.contains(this.renderer.domElement)) {
            this.container.removeChild(this.renderer.domElement);
        }
    }
}
