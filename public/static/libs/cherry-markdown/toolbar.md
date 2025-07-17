# 调整工具栏

> https://github.com/Tencent/cherry-markdown/wiki/调整工具栏

工具栏按钮list
cherry自带的工具栏按钮有以下这些：

辅助类
|: 分隔符，单纯的分割工具栏，无任何作用
insert: 插入，单纯的占位，点击没有任何效果，用来配置二级菜单

字体样式类
bold: 加粗
italic: 斜体
underline: 下划线
strikethrough: 删除线
sub: 下标
sup: 上标
ruby: 实现类似给文字加拼音的效果（当然如何把文字转成拼音需要业务方自行实现，也可参考在线demo用的文字转拼音组件）
size: 文字尺寸，自带二级菜单，二级菜单里可选 小、中、大、特大
color: 文字颜色，自带二级菜单，二级菜单里可选 文字颜色、文字背景色

段落属性类
quote: 引用
detail: 手风琴，即可以展开收起内容
h1: 一级标题
h2: 二级标题
h3: 三级标题
header: 标题菜单，自带二级菜单，二级菜单里可以选 1~5级标题
ul: 无序列表
ol: 有序列表
checklist: 任务清单
list: 列表菜单，自带二级菜单，二级菜单里可选 有序列表、无序列表、任务清单
justify: 对齐方式，自带二级菜单，二级菜单里可以选 左对齐、居中、右对齐
panel: 信息面板，自带二级菜单，二级菜单里可以选 tips、info、warning、danger、success

插入类
image: 插入图片
audio: 插入音频
video: 插入视频
pdf: 插入pdf
word: 插入word文档
file: 插入普通文件
link: 插入链接
hr: 插入水平分割线
br: 插入新行
code: 代码块
formula: 插入数学公式
toc: 插入目录
table: 插入表格
drawIo: 插入draw.io画图，点击后会出现draw.io画图面板
graph: 插入画图，自带二级菜单，二级菜单里可选 流程图、时序图、状态图、类图、饼图、甘特图

功能类
undo: 回撤操作
redo: 恢复最近回撤的操作
theme: 切换主题，自带二级菜单，主题可配置也可由业务方自行丰富
codeTheme: 切换代码块的主题，自带二级菜单
mobilePreview: 把预览区域变成h5模式
togglePreview: 打开/关闭预览区(用于左右分栏模式，即左边是编辑区域，右边是预览区域)
switchModel: 切换编辑/预览模式(用于单栏编辑模式，即点一下是编辑模式，再点一下是预览模式，类似github的交互体验)
copy: 复制预览区域的html内容到剪贴板
export: 导出，自带二级菜单，二级菜单里可选 导出PDF、导出长图、导出markdown、导出html
fullScreen: 全屏/取消全屏
settings: 设置，自带二级菜单，二级菜单里可选 常规换行/经典换行切换、关闭/打开预览、隐藏工具栏 （不推荐用了，完全可以自行实现）
