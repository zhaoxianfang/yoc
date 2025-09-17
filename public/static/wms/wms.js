
// 初始化仓库3D可视化
document.addEventListener('DOMContentLoaded', () => {
    // 创建Warehouse3D实例
    const warehouse3D = new Warehouse3D('wms-container', {
        slotStatus: [
            { id: 'free', name: '空闲', color: '#52c41a' },
            { id: 'occupied', name: '占用', color: '#faad14' },
            { id: 'reserved', name: '预留', color: '#1890ff' },
            { id: 'damaged', name: '损坏', color: '#f5222d' },
            { id: 'locked', name: '锁定', color: '#722ed1' },
            { id: 'maintenance', name: '维护中', color: '#fa541c' }
        ],
        productCategories: [
            { id: 'hardware', name: '五金', color: '#fa8c16' },
            { id: 'frozen', name: '冻品', color: '#1890ff' },
            { id: 'dry', name: '干货', color: '#722ed1' },
            { id: 'aquatic', name: '水产', color: '#13c2c2' },
            { id: 'electronics', name: '电子产品', color: '#eb2f96' },
            { id: 'clothing', name: '服装', color: '#faad14' },
            { id: 'food', name: '食品', color: '#52c41a' },
            { id: 'medicine', name: '药品', color: '#f5222d' },
            { id: 'other', name: '其他', color: '#d9d9d9' }
        ],
        enableAnimations: true,
        showLabels: true,
        showStats: true,
        slotGapRatio: 0.2, // 库位间距比例
        ambientLightIntensity: 0.8,
        enableShadows: false,
        slotLabelPosition: 'front',
        labelBackgroundOpacity: 0,
        labelBackgroundColor: 'rgba(0, 0, 0, 0)',
        shelfLabelBackgroundColor: 'rgba(0, 0, 0, 0.8)',
        // 库位垂直偏移
        slotVerticalOffset: 0.75,
        // 整个仓库垂直偏移
        wholeOffsetY: -15,
        // 事件回调示例
        onWarehouseChange: (warehouse) => {
            console.log('仓库已切换:', warehouse.name);
        },
        onSlotClick: (slot) => {
            console.log('库位被点击:', slot.id,slot);
        },
        onSlotUpdate: (slot) => {
            console.log('库位已更新:', slot.id,slot);
        },
        onSearchComplete: (slots) => {
            console.log('搜索完成，找到', slots.length, '个库位');
        },
        onEditModeChange: (editMode) => {
            console.log('编辑模式:', editMode ? '开启' : '关闭');
        },
        onConfigChange: (key, value) => {
            console.log('配置已更改:', key, value);
        }
    });

    // 示例：添加一个仓库配置（使用坐标编号方式）
    const warehouseConfig1 = {
        id: 'WH001',
        name: '一号仓库',
        rows: 8,
        columns: 3,
        levels: 6,
        slotsPerLevel: 20, // 每层20个库位
        shelfWidth: 60,   // 货架宽度
        shelfDepth: 3,    // 货架深度
        shelfHeight: 2.5, // 货架高度
        aisleWidth: { row: 8, column: 12 }, // 通道宽度
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
        aisleWidth: { row: 6, column: 8 }, // 通道宽度
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
        aisleWidth: { row: 6, column: 8 }, // 通道宽度
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
