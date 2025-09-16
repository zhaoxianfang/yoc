// 初始化仓库3D可视化
document.addEventListener('DOMContentLoaded', () => {
    // 创建Warehouse3D实例
    const warehouse3D = new Warehouse3D('container', {
        slotStatus: {
            free: { name: '空闲', color: '#52c41a' },
            occupied: { name: '占用', color: '#faad14' },
            reserved: { name: '预留', color: '#1890ff' },
            damaged: { name: '损坏', color: '#f5222d' }
        },
        productCategories: {
            hardware: { name: '五金', color: '#fa8c16' },
            frozen: { name: '冻品', color: '#1890ff' },
            dry: { name: '干货', color: '#722ed1' },
            aquatic: { name: '水产', color: '#13c2c2' },
            other: { name: '其他', color: '#eb2f96' }
        },
        enableAnimations: true,
        showLabels: true,
        showStats: true,
        slotGapRatio: 0.2, // 库位间距比例
        ambientLightIntensity: 0.8,
        exposure: 1.5,
        slotLabelPosition: 'back', // 库位标签显示在背面
        slotHeightOffset: 0.75, // 库位高度偏移
        labelBackgroundColor: 'transparent', // 标签背景透明
        groundNameOffset: { x: 0, y: 0, z: 0 }, // 地名位置偏移
        // 事件回调示例
        onWarehouseChange: (warehouse) => {
            console.log('仓库已切换:', warehouse.name);
        },
        onSlotClick: (slot) => {
            console.log('库位被点击:', slot.id);
        },
        onSlotUpdate: (slot) => {
            console.log('库位已更新:', slot.id);
        },
        onSearchComplete: (slots) => {
            console.log('搜索完成，找到', slots.length, '个库位');
        },
        onEditModeChange: (editMode) => {
            console.log('编辑模式:', editMode ? '开启' : '关闭');
        },
        onViewChange: (type, data) => {
            console.log('视图变化:', type, data);
        }
    });

    // 示例：添加一个仓库配置（使用坐标编号方式）
    const warehouseConfig1 = {
        id: 'WH001',
        name: '一号仓库',
        rows: 4,
        columns: 6,
        levels: 3,
        slotsPerLevel: 8, // 每层8个库位
        shelfWidth: 20,   // 货架宽度
        shelfDepth: 4,    // 货架深度
        shelfHeight: 2.5, // 货架高度
        rowAisleWidth: 4, // 行通道宽度
        columnAisleWidth: 10, // 列通道宽度
        layout: {
            type: 'STRAIGHT',
            xSpaces: 6,
            ySpaces: 4
        },
        slotLength: 2.5,  // 库位长度
        slotWidth: 2,     // 库位宽度
        slotHeight: 1.5,  // 库位高度
        numberingRule: {
            shelf: 'FORWARD_LEFT_TO_RIGHT', // 货架编号规则
            slot: 'FULL' // 使用坐标编号方式
        }
    };

    // 添加仓库
    warehouse3D.addWarehouse(warehouseConfig1);

    // 示例：添加第二个仓库（使用货架编号方式）
    const warehouseConfig2 = {
        id: 'WH002',
        name: '二号仓库',
        rows: 5,
        columns: 8,
        levels: 4,
        slotsPerLevel: 6, // 每层6个库位
        shelfWidth: 18,
        shelfDepth: 3.5,
        shelfHeight: 2,
        rowAisleWidth: 36,
        columnAisleWidth: 36,
        layout: {
            type: 'STRAIGHT',
            xSpaces: 8,
            ySpaces: 5
        },
        slotLength: 2.5,
        slotWidth: 2,
        slotHeight: 1.5,
        numberingRule: {
            shelf: 'FORWARD_LEFT_TO_RIGHT',
            slot: 'SHEET_LEVEL_INDEX' // 使用货架编号方式
        }
    };

    warehouse3D.addWarehouse(warehouseConfig2);

    // 示例：添加第三个仓库（U型布局）
    const warehouseConfig3 = {
        id: 'WH003',
        name: '三号仓库',
        rows: 6,
        columns: 10,
        levels: 5,
        slotsPerLevel: 10, // 每层10个库位
        shelfWidth: 25,
        shelfDepth: 5,
        shelfHeight: 2.2,
        rowAisleWidth: 50,
        columnAisleWidth: 50,
        layout: {
            type: 'U_SHAPE',
            xSpaces: 10,
            ySpaces: 6
        },
        slotLength: 2.5,
        slotWidth: 2,
        slotHeight: 1.5,
        numberingRule: {
            shelf: 'FORWARD_LEFT_TO_RIGHT',
            slot: 'FULL'
        }
    };

    warehouse3D.addWarehouse(warehouseConfig3);

    // 将实例附加到window对象，方便在控制台调试
    window.warehouse3D = warehouse3D;
});
