# Warehouse3D 类库使用文档

## 基本用法

```
// 创建Warehouse3D实例
const warehouse3D = new Warehouse3D('container', options);

// 添加仓库配置
warehouse3D.addWarehouse({
    id: 'WH001',
    name: '一号仓库',
    rows: 4,
    columns: 6,
    levels: 3,
    slotsPerLevel: 8,
    shelfWidth: 20,
    shelfDepth: 4,
    shelfHeight: 2.5,
    aisleWidth: { row: 20, column: 30 },
    layout: {
        type: 'STRAIGHT',
        xSpaces: 6,
        ySpaces: 4
    },
    slotLength: 2.5,
    slotWidth: 2,
    slotHeight: 1.5,
    numberingRule: {
        shelf: 'FORWARD_LEFT_TO_RIGHT',
        slot: 'FULL'
    }
});

// 切换当前显示的仓库
warehouse3D.setCurrentWarehouse('WH001');

// 更新库位信息
warehouse3D.updateSlot('WH001-1-1-1-1', {
    status: 'occupied',
    product: {
        id: 'P1001',
        name: '示例商品',
        category: 'hardware',
        inboundDate: '2023-05-15',
        inboundPerson: '张三'
    }
});

// 批量更新库位信息
warehouse3D.batchUpdateSlots([
    {
        slotId: 'WH001-1-1-1-1',
        updates: {
            status: 'occupied',
            product: {
                id: 'P1001',
                name: '示例商品',
                category: 'hardware',
                inboundDate: '2023-05-15',
                inboundPerson: '张三'
            }
        }
    },
    // 更多更新...
]);
```

## 配置参数说明
全局配置选项

| 参数                         | 类型            | 默认值                                 | 说明                |
|----------------------------|---------------|-------------------------------------|-------------------|
| slotStatus                 | 	 Array       | 	[{...}]	                           | 库位状态配置数组          |
| productCategories          | 	Array        | 	[{...}]	                           | 商品分类配置数组          |
| shelfMaterialColor         | 	Number       | 	0x8B4513                           | 	货架材质颜色           |
| plateMaterialColor         | 	Number       | 	0xD2B48C                           | 	隔板材质颜色           |
| groundColor                | 	Number       | 	0xaaaaaa                           | 	地面颜色             |
| slotSize                   | 	Object       | 	{length:2.5, width:2, height:1.5}	 | 库位尺寸 (5:4:3比例)    |
| shelfSize	                 | Object        | 	{width:5, depth:3, height:2}       | 	货架尺寸             |
| aisleWidth	                | Object        | 	{row:20, column:30}	               | 通道宽度配置            |
| layoutType	                | String	       | 'STRAIGHT'                          | 	默认布局类型           |
| showLabels                 | 	Boolean	     | true                                | 	是否显示标签           |
| showStats	                 | Boolean       | 	true                               | 	是否显示统计面板         |
| enableAnimations           | 	Boolean	     | true	                               | 是否启用动画            |
| enableShadows              | 	Boolean      | 	false                              | 	是否启用阴影           |
| cameraPosition	            | Object	       | {x:50, y:50, z:50}	                 | 初始化相机位置           |
| cameraTarget	              | Object        | 	{x:0, y:0, z:0}                    | 	初始化相机目标          |
| pixelRatio                 | 	Number	      | window.devicePixelRatio             | 	渲染精度             |
| antialias	                 | Boolean       | 	true                               | 	抗锯齿              |
| physicallyCorrectLights    | 	Boolean	     | false	                              | 物理校正光照            |
| toneMapping	               | Number        | 	THREE.NoToneMapping	               | 色调映射              |
| exposure	                  | Number	       | 1.0	                                | 曝光级别              |
| shadowType	                | Number        | 	THREE.PCFSoftShadowMap             | 	阴影类型             |
| ambientLightIntensity      | 	Number	      | 0.8	                                | 环境光强度             |
| enableDamping              | 	Boolean	true | 	是否启用控制器阻尼                          |                   |
| dampingFactor	             | Number        | 	0.05	                              | 控制器阻尼系数           |
| enablePan	                 | Boolean       | 	true                               | 	是否启用平移           |
| enableZoom                 | 	Boolean      | 	true                               | 	是否启用缩放           |
| enableRotate               | 	Boolean      | 	true                               | 	是否启用旋转           |
| zoomSpeed	                 | Number	       | 1.0	                                | 缩放速度              |
| rotateSpeed	               | Number        | 	1.0	                               | 旋转速度              |
| panSpeed                   | 	Number       | 	1.0	                               | 平移速度              |
| maxPolarAngle              | 	Number       | 	Math.PI	                           | 最大极化角             |
| minPolarAngle	             | Number        | 	0	                                 | 最小极化角             |
| maxDistance	               | Number        | 	500                                | 	最大距离             |
| minDistance	               | Number        | 	5	                                 | 最小距离              |
| autoRotate	                | Boolean       | 	false                              | 	是否自动旋转           |
| autoRotateSpeed            | 	Number       | 	2.0                                | 	自动旋转速度           |
| slotGapRatio	              | Number        | 	0.2                                | 	库位间距（相对于库位长度的比例） |
| editMode	                  | Boolean	      | false	                              | 是否启用编辑模式          |
| shelfNumberingRule	        | String        | 	'FORWARD_LEFT_TO_RIGHT'            | 	货架编号规则           |
| slotLabelPosition	         | String	       | 'front'	                            | 库位标签位置            |
| labelBackgroundOpacity	    | Number        | 	0	v标签背景透明度                         |                   |
| labelBackgroundColor	      | String        | 	'rgba(0, 0, 0, 0)'                 | 	标签背景颜色           |
| shelfLabelBackgroundColor	 | String	       | 'rgba(0, 0, 0, 0.8)'                | 	货架标签背景颜色         |
| slotVerticalOffset	        | Number	       | 0.75	                               | 库位垂直偏移            |
| wholeOffsetZ	              | Number        | 	0                                  | 	整个仓库垂直偏移         |
| performance                | 	Object       | 	{...}	                             | 性能优化选项            |
| onWarehouseChange          | 	Function	    | null	                               | 仓库切换回调            |
| onSlotClick                | 	Function	    | null                                | 	库位点击回调           |
| onSlotUpdate               | 	Function     | 	null                               | 	库位更新回调           |
| onSearchComplete           | 	Function     | 	null                               | 	搜索完成回调           |
| onEditModeChange	          | Function      | 	null	                              | 编辑模式变化回调          |
| onConfigChange             | 	Function     | null	                               | 配置变化回调            |

## 仓库配置参数
| 参数             | 类型       | 默认值                                          | 说明     |
|----------------|----------|----------------------------------------------|--------|
| id             | 	String  | 	必填	                                         | 仓库唯一标识 |
| name           | 	String  | 	必填	                                         | 仓库名称   |
| rows           | 	Number  | 	4	                                          | 货架行数   |
| columns	       | Number	  | 6	                                           | 货架列数   |
| levels	        | Number	  | 3	                                           | 货架层数   |
| slotsPerLevel	 | Number   | 	8	                                          | 每层库位数量 |
| shelfWidth     | 	Number  | 	20                                          | 	货架宽度  |
| shelfDepth	    | Number   | 	4	                                          | 货架深度   |
| shelfHeight    | 	Number	 | 2.5	                                         | 货架高度   |
| aisleWidth	    | Object   | 	{row:20, column:30}                         | 通道宽度配置 |
| layout	        | Object   | 	{type:'STRAIGHT', xSpaces:6, ySpaces:4}	    | 布局配置   |
| slotLength	    | Number   | 	2.5	                                        | 库位长度   |
| slotWidth	     | Number	  | 2	                                           | 库位宽度   |
| slotHeight	    | Number   | 	1.5                                         | 	库位高度  |
| numberingRule	 | Object	  | {shelf:'FORWARD_LEFT_TO_RIGHT', slot:'FULL'} | 	编号规则  |

### 编号规则说明
#### 货架编号规则：

- FORWARD_LEFT_TO_RIGHT: 从最前面一排开始，从左到右编号
- ROW_COL: 使用行列编号 (R1C2)
- INDEX: 使用索引编号 (S1, S2, S3...)

#### 库位编号规则：

- FULL: 使用完整坐标 (WH001-1-2-3-4)
- SHEET_LEVEL_INDEX: 使用货架编号 (WH001-S12-3-5)

#### 库位标签位置：

- front: 正面
- back: 背面
- top: 顶面
- bottom: 底面

## API 方法

### 仓库管理
- addWarehouse(config) - 添加仓库配置
- setCurrentWarehouse(warehouseId) - 设置当前显示的仓库
- initFromJSON(jsonData) - 通过JSON数据初始化仓库
- exportToJSON() - 导出仓库数据为JSON
- importData() - 导入数据

### 库位操作
- updateSlot(slotId, updates) - 更新库位信息
- batchUpdateSlots(updates) - 批量更新库位信息
- setOutboundInfo(slotId, outboundInfo) - 设置库位出库信息
- setInboundInfo(slotId, inboundInfo) - 设置库位入库信息

### 视图控制
- resetView() - 重置视图
- moveView(direction) - 移动视图
- zoomView(type) - 缩放视图
- focusOnSlot(slot) - 将相机对准指定库位
- searchSlot() - 搜索库位
- toggleLabels() - 切换标签显示
- toggleStats() - 切换统计面板显示
- toggleEditMode() - 切换编辑模式
- toggleConfigPanel() - 切换配置面板
- toggleToolbar() - 切换工具栏
- changeViewMode(mode) - 切换视图模式
- setLabelDisplay(mode) - 设置标签显示模式
- setSlotLabelPosition(position) - 设置库位标签位置
- setVerticalOffset(offset) - 设置垂直偏移

### 资源管理
- destroy() - 销毁实例，释放资源

## 事件说明
Warehouse3D 类库会触发以下自定义事件：

- warehouseChanged - 当切换仓库时触发
- slotClicked - 当点击库位时触发
- slotUpdated - 当库位信息更新时触发
- searchCompleted - 当搜索完成时触发
- editModeChanged - 当编辑模式变化时触发
- configChanged - 当配置变化时触发
