/**
 * 工业级右键菜单组件 - 最终优化版
 * @class RightMenu
 * @description 支持PC端右键和移动端长按触发的多级菜单组件
 */
class RightMenu {
    /**
     * 构造函数
     * @param {Object} options 配置选项
     * @param {string} options.selector 作用区元素选择器（必填）
     * @param {number} [options.itemWidth=160] 菜单项宽度(px)
     * @param {number} [options.longPressTime=1400] 移动端长按触发时间(ms)
     * @param {number} [options.zIndex=9999] 菜单的z-index值
     * @param {number} [options.animationDuration=200] 动画持续时间(ms)
     * @param {Array} options.menus 菜单配置数组
     */
    constructor(options = {}) {
        // 参数校验
        if (!options.selector) {
            throw new Error('selector参数是必须的');
        }
        if (!options.menus || !Array.isArray(options.menus)) {
            throw new Error('menus参数必须是数组');
        }

        // 默认配置
        this.config = {
            selector: options.selector,
            itemWidth: options.itemWidth || 160,
            longPressTime: options.longPressTime || 1400,
            zIndex: options.zIndex || 9999,
            animationDuration: options.animationDuration || 200,
            menuClass: 'right-menu-' + Math.random().toString(36).substr(2, 9) // 生成唯一类名
        };

        // 菜单配置
        this.menus = options.menus;

        // 状态变量
        this.isMobile = this._checkMobile();
        this.activeElement = null; // 当前激活的元素
        this.currentMenu = null; // 当前显示的菜单
        this.timer = null; // 长按计时器
        this.longPressing = false; // 是否正在长按
        this.lastTouchPos = { x: 0, y: 0 }; // 最后触摸位置

        // 初始化
        this._init();
    }

    /**
     * 检测是否为移动端
     * @private
     * @return {boolean} 是否是移动端
     */
    _checkMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * 初始化组件
     * @private
     */
    _init() {
        // 创建菜单容器
        this._createMenuContainer();

        // 绑定事件
        this._bindEvents();
    }

    /**
     * 创建菜单容器
     * @private
     */
    _createMenuContainer() {
        // 创建主容器
        this.menuContainer = document.createElement('div');
        this.menuContainer.className = 'right-menu-container ' + this.config.menuClass;
        this.menuContainer.style.display = 'none';
        this.menuContainer.style.zIndex = this.config.zIndex;
        document.body.appendChild(this.menuContainer);
    }

    /**
     * 绑定事件
     * @private
     */
    _bindEvents() {
        const elements = document.querySelectorAll(this.config.selector);

        elements.forEach(el => {
            // PC端事件 - 右键菜单
            el.addEventListener('contextmenu', this._handleContextMenu.bind(this));

            // 移动端事件 - 长按菜单
            if (this.isMobile) {
                el.addEventListener('touchstart', this._handleTouchStart.bind(this), { passive: false });
                el.addEventListener('touchend', this._handleTouchEnd.bind(this));
                el.addEventListener('touchmove', this._handleTouchMove.bind(this));
                el.addEventListener('touchcancel', this._handleTouchEnd.bind(this));
            }
        });

        // 点击其他地方关闭菜单
        document.addEventListener('click', this._handleDocumentClick.bind(this), true);
        document.addEventListener('scroll', this._handleDocumentScroll.bind(this), true);
    }

    /**
     * 处理右键菜单事件
     * @private
     * @param {Event} e 事件对象
     */
    _handleContextMenu(e) {
        // 阻止默认右键菜单
        e.preventDefault();
        e.stopPropagation();

        // 显示菜单
        this._showMenu(e, e.clientX, e.clientY);
    }

    /**
     * 处理移动端触摸开始事件
     * @private
     * @param {Event} e 事件对象
     */
    _handleTouchStart(e) {
        // 阻止默认行为（防止触发系统菜单）
        e.preventDefault();
        e.stopPropagation();

        // 记录触摸位置
        const touch = e.touches[0] || e.changedTouches[0];
        this.lastTouchPos = { x: touch.clientX, y: touch.clientY };

        // 重置状态
        this.longPressing = false;

        // 设置长按计时器
        this.timer = setTimeout(() => {
            this.longPressing = true;
            this._showMenu(e, this.lastTouchPos.x, this.lastTouchPos.y);
        }, this.config.longPressTime);
    }

    /**
     * 处理移动端触摸结束事件
     * @private
     * @param {Event} e 事件对象
     */
    _handleTouchEnd(e) {
        // 清除计时器
        clearTimeout(this.timer);

        // 如果不是长按触发，恢复默认点击行为
        if (!this.longPressing) {
            const clickEvent = new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            });
            e.target.dispatchEvent(clickEvent);
        }

        this.longPressing = false;
    }

    /**
     * 处理移动端触摸移动事件
     * @private
     * @param {Event} e 事件对象
     */
    _handleTouchMove(e) {
        // 如果移动距离过大，取消长按
        const touch = e.touches[0] || e.changedTouches[0];
        const dx = touch.clientX - this.lastTouchPos.x;
        const dy = touch.clientY - this.lastTouchPos.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 10) {
            clearTimeout(this.timer);
        }
    }

    /**
     * 处理文档点击事件
     * @private
     * @param {Event} e 事件对象
     */
    _handleDocumentClick(e) {
        // 如果点击的不是菜单容器或其子元素，则隐藏菜单
        if (!this.menuContainer.contains(e.target)) {
            this._hideMenu();
        }
    }

    /**
     * 处理文档滚动事件
     * @private
     */
    _handleDocumentScroll() {
        this._hideMenu();
    }

    /**
     * 显示菜单
     * @private
     * @param {HTMLElement} target 目标对象
     * @param {number} x 显示位置的x坐标
     * @param {number} y 显示位置的y坐标
     */
    _showMenu(dom, x, y) {
        // 隐藏现有菜单
        this._hideMenu();

        // 保存激活元素
        // this.activeElement = dom.target;
        this.activeElement = dom;

        // 创建一级菜单
        this.currentMenu = this._createMenu(this.menus, 1);
        this.menuContainer.innerHTML = '';
        this.menuContainer.appendChild(this.currentMenu);

        // 设置菜单位置
        this._positionMenu(this.currentMenu, x, y);

        // 显示菜单容器
        this.menuContainer.style.display = 'block';

        // 阻止滚动
        document.body.style.overflow = 'hidden';
    }

    /**
     * 隐藏菜单
     * @private
     */
    _hideMenu() {
        if (this.menuContainer) {
            this.menuContainer.style.display = 'none';
            this.menuContainer.innerHTML = '';
            this.currentMenu = null;
            this.activeElement = null;

            // 恢复滚动
            document.body.style.overflow = '';
        }
    }

    /**
     * 创建菜单
     * @private
     * @param {Array} items 菜单项数组
     * @param {number} level 菜单层级
     * @return {HTMLElement} 创建的菜单元素
     */
    _createMenu(items, level) {
        const menu = document.createElement('ul');
        menu.className = `right-menu level-${level}`;
        menu.style.width = `${this.config.itemWidth}px`;
        menu.style.transition = `opacity ${this.config.animationDuration}ms ease, transform ${this.config.animationDuration}ms ease`;

        // 添加动画初始状态
        if (level > 1) {
            menu.style.opacity = '0';
            menu.style.transform = 'scale(0.95)';
        }

        items.forEach(item => {
            if (item === 'divider') {
                // 分割线
                const divider = document.createElement('li');
                divider.className = 'right-menu-divider';
                menu.appendChild(divider);
            } else {
                // 菜单项
                const menuItem = document.createElement('li');
                menuItem.className = 'right-menu-item';

                // 菜单内容
                const content = document.createElement('div');
                content.className = 'right-menu-content';

                // 图标
                if (item.icon) {
                    const icon = document.createElement('span');
                    icon.className = 'right-menu-icon';
                    icon.innerHTML = item.icon;
                    content.appendChild(icon);
                }

                // 文本
                const text = document.createElement('span');
                text.className = 'right-menu-text';
                text.textContent = item.text;
                content.appendChild(text);

                // 子菜单指示器
                if (item.children && item.children.length > 0) {
                    const arrow = document.createElement('span');
                    arrow.className = 'right-menu-arrow';
                    arrow.innerHTML = '&#9654;';
                    content.appendChild(arrow);
                }

                menuItem.appendChild(content);
                menu.appendChild(menuItem);

                // 绑定点击事件
                this._bindMenuItemEvent(menuItem, item, level);
            }
        });

        return menu;
    }

    /**
     * 绑定菜单项事件
     * @private
     * @param {HTMLElement} menuItem 菜单项元素
     * @param {Object} item 菜单项配置
     * @param {number} level 菜单层级
     */
    _bindMenuItemEvent(menuItem, item, level) {
        menuItem.addEventListener('click', e => {
            e.stopPropagation();

            if (item.children && item.children.length > 0) {
                // 显示子菜单
                this._showSubMenu(menuItem, item.children, level + 1);
            } else if (item.callback) {
                // 触发回调，传入当前激活的元素和菜单项
                item.callback(this.activeElement, item,this.activeElement.target);
                this._hideMenu();
            }
        });
    }

    /**
     * 显示子菜单
     * @private
     * @param {HTMLElement} parentItem 父菜单项
     * @param {Array} items 子菜单项数组
     * @param {number} level 菜单层级
     */
    _showSubMenu(parentItem, items, level) {
        // 移除现有的同级菜单
        const parentMenu = parentItem.parentElement;
        const existingSubMenu = parentMenu.querySelector(`.level-${level}`);
        if (existingSubMenu) {
            existingSubMenu.remove();
        }

        // 创建子菜单
        const subMenu = this._createMenu(items, level);

        // 计算位置
        const rect = parentItem.getBoundingClientRect();
        let left = rect.right;
        let top = rect.top;

        // 检查右侧是否有足够空间
        const viewportWidth = window.innerWidth;
        if (left + this.config.itemWidth > viewportWidth) {
            // 尝试左侧
            if (rect.left - this.config.itemWidth > 0) {
                left = rect.left - this.config.itemWidth;
            } else {
                // 左右都没有空间，改为垂直模式
                subMenu.classList.add('vertical');
                left = rect.left + 10; // 缩进
            }
        }

        // 检查底部是否有足够空间
        const viewportHeight = window.innerHeight;
        const menuHeight = items.length * 40; // 估算高度
        if (top + menuHeight > viewportHeight) {
            top = Math.max(0, viewportHeight - menuHeight - 10);
        }

        // 设置位置
        subMenu.style.left = `${left}px`;
        subMenu.style.top = `${top}px`;

        // 添加到容器
        this.menuContainer.appendChild(subMenu);

        // 添加动画效果
        setTimeout(() => {
            subMenu.style.opacity = '1';
            subMenu.style.transform = 'scale(1)';
        }, 10);
    }

    /**
     * 定位菜单
     * @private
     * @param {HTMLElement} menu 菜单元素
     * @param {number} x 目标x坐标
     * @param {number} y 目标y坐标
     */
    _positionMenu(menu, x, y) {
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const menuWidth = this.config.itemWidth;
        const menuHeight = menu.children.length * 40; // 估算高度

        // 调整X坐标
        if (x + menuWidth > viewportWidth) {
            x = viewportWidth - menuWidth - 10;
        }
        x = Math.max(0, x);

        // 调整Y坐标
        if (y + menuHeight > viewportHeight) {
            y = viewportHeight - menuHeight - 10;
        }
        y = Math.max(0, y);

        // 设置位置
        menu.style.left = `${x}px`;
        menu.style.top = `${y}px`;
    }

    /**
     * 更新菜单配置
     * @public
     * @param {Array} menus 新的菜单配置数组
     */
    updateMenus(menus) {
        if (!Array.isArray(menus)) {
            throw new Error('menus参数必须是数组');
        }
        this.menus = menus;
    }

    /**
     * 销毁组件
     * @public
     */
    destroy() {
        // 移除事件监听
        const elements = document.querySelectorAll(this.config.selector);

        elements.forEach(el => {
            el.removeEventListener('contextmenu', this._handleContextMenu);

            if (this.isMobile) {
                el.removeEventListener('touchstart', this._handleTouchStart);
                el.removeEventListener('touchend', this._handleTouchEnd);
                el.removeEventListener('touchmove', this._handleTouchMove);
                el.removeEventListener('touchcancel', this._handleTouchEnd);
            }
        });

        document.removeEventListener('click', this._handleDocumentClick);
        document.removeEventListener('scroll', this._handleDocumentScroll);

        // 移除菜单容器
        if (this.menuContainer && this.menuContainer.parentNode) {
            this.menuContainer.parentNode.removeChild(this.menuContainer);
        }

        // 清除状态
        this.activeElement = null;
        this.currentMenu = null;
        clearTimeout(this.timer);
    }
}
