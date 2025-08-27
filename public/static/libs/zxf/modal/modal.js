class Modal {
    static instances = [];
    static minimizedModals = [];
    static toastContainers = {};
    static zIndex = 10000;
    static bodyScrollEnabled = true;

    constructor(options) {
        this.options = {
            title: '',
            content: '',
            width: 'auto',
            height: 'auto',
            buttons: [],
            position: 'center',
            theme: 'default',
            draggable: true,
            overlay: true,
            overlayClose: true,
            escClose: true,
            autoClose: 0,
            showActionIcons: true, // 是否显示右上角最小化、最大化、关闭按钮 minmax
            transparent: false,
            shake: false,
            buttonsAlign: 'right',
            buttonsVertical: false,
            bodyScroll: false,
            loadingText: '加载中...',
            loadingSpinner: true,
            onOpen: null,
            onClose: null,
            onBeforeClose: null,
            onDrag: null,
            onMinimize: null,
            onMaximize: null,
            onRestore: null,
            onFullscreen: null,
            onIframeLoad: null,
            onIframeError: null,
            opacity: 1,
            offset: null, // 新增offset参数 eg: offset: [100, 200] // [top, left]
            ...options
        };

        this.id = 'zxf-modal-' + Math.random().toString(36).substr(2, 9);
        this.isOpen = false;
        this.isMinimized = false;
        this.isMaximized = false;
        this.isFullscreen = false;
        this.originalWidth = this.options.width;
        this.originalHeight = this.options.height;
        this.originalPosition = { top: 0, left: 0 };
        this.timer = null;
        this.progressInterval = null;
        this.dragState = {
            isDragging: false,
            startX: 0,
            startY: 0,
            startLeft: 0,
            startTop: 0
        };
        // 添加事件监听器存储
        this._eventListeners = {
            iframeLoad: []
        };
        // 添加完成状态标记
        this._isIframeLoaded = false;

        this.init();
        Modal.instances.push(this);
    }

    init() {
        this.createModal();
        this.setupEvents();
    }
    // iframe弹窗完成后的回调事件
    onIframeComplete(callback) {
        if (typeof callback !== 'function') {
            console.warn('onIframeComplete 需要传入函数');
            return this; // 保持链式调用
        }

        // 封装回调以确保传入 Modal 实例
        const wrappedCallback = () => {
            try {
                callback(this); // 将当前 Modal 实例作为参数传递
            } catch (e) {
                console.error('onIframeComplete 回调执行出错:', e);
            }
        };

        if (!this.iframe) {
            console.warn('当前弹窗不包含 iframe');
            setTimeout(wrappedCallback, 0);
            return this;
        }

        if (this._isIframeLoaded) {
            setTimeout(wrappedCallback, 0);
        } else {
            this._eventListeners.iframeLoad.push(wrappedCallback);
        }

        return this; // 支持链式调用
    }
    // 触发iframe加载完成事件
    _triggerIframeLoad() {
        this._isIframeLoaded = true;
        this._eventListeners.iframeLoad.forEach(cb => cb());
        this._eventListeners.iframeLoad = [];
    }

    createModal() {
        // 创建遮罩层
        if (this.options.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'zxf-modal-overlay';
            document.body.appendChild(this.overlay);
        }

        // 创建弹窗容器
        this.modal = document.createElement('div');
        this.modal.className = 'zxf-modal-container';
        this.modal.id = this.id;

        // 设置主题
        if (this.options.theme) {
            this.modal.classList.add(`zxf-modal-theme-${this.options.theme}`);
        }

        // 设置透明度
        if (this.options.transparent) {
            this.modal.classList.add('transparent');
            this.modal.style.opacity = this.options.opacity;
        }

        // 设置宽度和高度
        if (this.options.width !== 'auto') {
            this.modal.style.width = typeof this.options.width === 'number'
                ? `${this.options.width}px`
                : this.options.width;
        }

        if (this.options.height !== 'auto') {
            this.modal.style.height = typeof this.options.height === 'number'
                ? `${this.options.height}px`
                : this.options.height;
        }

        // 创建操作按钮区域
        if (!this.isTextOnlyAutoCloseModal()) {
            // 如果显示最小化、最大化、关闭按钮
            if (this.options.showActionIcons) {
                this.actions = document.createElement('div');
                this.actions.className = 'zxf-modal-actions';

                if ((this.options.title !== false && this.options.title.trim() !== '') ||
                    (this.options.buttons && this.options.buttons.length > 0)) {
                    this.minimizeBtn = document.createElement('button');
                    this.minimizeBtn.className = 'zxf-modal-action-btn';
                    this.minimizeBtn.innerHTML = '&#x2015;';
                    this.minimizeBtn.title = '最小化';
                    this.minimizeBtn.addEventListener('click', () => this.minimize());
                    this.actions.appendChild(this.minimizeBtn);

                    this.maximizeBtn = document.createElement('button');
                    this.maximizeBtn.className = 'zxf-modal-action-btn';
                    this.maximizeBtn.innerHTML = '&#x26F6;';
                    this.maximizeBtn.title = '最大化';
                    this.maximizeBtn.addEventListener('click', () => this.toggleMaximize());
                    this.actions.appendChild(this.maximizeBtn);
                }

                if (this.options.autoClose <= 0) {
                    this.closeBtn = document.createElement('button');
                    this.closeBtn.className = 'zxf-modal-action-btn';
                    this.closeBtn.innerHTML = '&times;';
                    this.closeBtn.title = '关闭';
                    this.closeBtn.addEventListener('click', () => this.close());
                    this.actions.appendChild(this.closeBtn);
                }
                this.modal.appendChild(this.actions);
            }
        }

        // 创建标题区域
        if ((this.options.title !== false && this.options.title.trim() !== '') && !this.isTextOnlyAutoCloseModal()) {
            this.header = document.createElement('div');
            this.header.className = 'zxf-modal-header';

            this.title = document.createElement('h3');
            this.title.className = 'zxf-modal-title';
            this.title.textContent = this.options.title;

            this.header.appendChild(this.title);
            this.modal.appendChild(this.header);
        }

        // 创建内容区域
        this.content = document.createElement('div');
        this.content.className = 'zxf-modal-content';
        this.setContent(this.options.content);
        this.modal.appendChild(this.content);

        // 创建底部按钮区域
        if (this.options.buttons && this.options.buttons.length > 0 && !this.isTextOnlyAutoCloseModal()) {
            this.createFooter();
        }

        // 创建进度条
        if (this.options.autoClose > 0) {
            this.createProgressBar();
        }

        document.body.appendChild(this.modal);
        this.setPosition(this.options.position);

        // 设置 z-index
        Modal.zIndex += 1;
        this.modal.style.zIndex = Modal.zIndex;
        if (this.overlay) {
            this.overlay.style.zIndex = Modal.zIndex - 1;
        }

        if (this.options.shake) {
            this.shake();
        }
    }

    createLoadingIndicator() {
        // 先移除旧的加载指示器（如果存在）
        if (this.loadingIndicator && this.loadingIndicator.parentNode) {
            this.loadingIndicator.parentNode.removeChild(this.loadingIndicator);
        }

        // 创建新的加载指示器
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'zxf-modal-loading-indicator';

        // 添加spinner（如果启用）
        if (this.options.loadingSpinner !== false) {
            const spinner = document.createElement('div');
            spinner.className = 'zxf-modal-loading-spinner';
            this.loadingIndicator.appendChild(spinner);
        }

        // 添加加载文本
        const text = document.createElement('div');
        text.className = 'zxf-modal-loading-text';
        text.textContent = this.options.loadingText || '加载中...';
        this.loadingIndicator.appendChild(text);

        // 添加到内容区域
        this.content.appendChild(this.loadingIndicator);

        // 确保显示
        this.loadingIndicator.style.display = 'flex';
    }

    setupIframeLoadingState() {
        if (!this.iframe) return;

        // 确保加载指示器可见
        this.loadingIndicator.style.display = 'flex';

        // 处理加载完成
        const handleLoad = () => {
            this._triggerIframeLoad();
            if (this.options.onIframeLoad) {
                this.options.onIframeLoad(this); // 同样传递 Modal 实例
            }
            this.hideLoadingIndicator();
        };

        // 处理加载错误
        const handleError = () => {
            this.showLoadingError();
            if (this.options.onIframeError) {
                this.options.onIframeError(this);
            }
        };

        // 添加事件监听
        this.iframe.addEventListener('load', handleLoad);
        this.iframe.addEventListener('error', handleError);

        // 对于已经加载完成的iframe
        if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
            setTimeout(handleLoad, 0);
        }
    }

    hideLoadingIndicator() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }

    showLoadingError() {
        if (this.loadingIndicator) {
            this.loadingIndicator.innerHTML = '';

            const errorIcon = document.createElement('div');
            errorIcon.className = 'zxf-modal-loading-error';
            errorIcon.textContent = '⚠️';
            this.loadingIndicator.appendChild(errorIcon);

            const errorText = document.createElement('div');
            errorText.className = 'zxf-modal-loading-text';
            errorText.textContent = '加载失败';
            this.loadingIndicator.appendChild(errorText);
        }
    }

    isTextOnlyAutoCloseModal() {
        return this.options.autoClose > 0 &&
            (this.options.title === false || this.options.title === '') &&
            (!this.options.buttons || this.options.buttons.length === 0);
    }

    createFooter() {
        this.footer = document.createElement('div');
        this.footer.className = 'zxf-modal-footer';

        if (this.options.buttonsAlign === 'left') {
            this.footer.classList.add('buttons-left');
        } else if (this.options.buttonsAlign === 'center') {
            this.footer.classList.add('buttons-center');
        }

        if (this.options.buttonsVertical) {
            this.footer.classList.add('buttons-vertical');
        }

        this.options.buttons.forEach(btnConfig => {
            const btn = document.createElement(btnConfig.href ? 'a' : 'button');
            btn.className = 'btn';

            if (btnConfig.type) {
                btn.classList.add(`btn-${btnConfig.type}`);
            }

            if (btnConfig.href) {
                btn.href = btnConfig.href;
                if (btnConfig.target) {
                    btn.target = btnConfig.target;
                }
                btn.classList.add('btn-link');
            }

            btn.textContent = btnConfig.text;

            if (btnConfig.class) {
                btn.classList.add(btnConfig.class);
            }

            if (btnConfig.click) {
                btn.addEventListener('click', (e) => {
                    // 如果是链接按钮且没有设置preventDefault，则阻止默认行为
                    if (btnConfig.href && e.preventDefault) {
                        e.preventDefault();
                    }

                    const result = btnConfig.click(e, this);
                    if (result === false) {
                        return;
                    }
                    if (btnConfig.close !== false && result !== 'keep-open') {
                        this.close();
                    }
                });
            } else if (!btnConfig.href) {
                btn.addEventListener('click', () => {
                    if (btnConfig.close !== false) {
                        this.close();
                    }
                });
            }

            this.footer.appendChild(btn);
        });

        this.modal.appendChild(this.footer);
    }

    createProgressBar() {
        this.progress = document.createElement('div');
        this.progress.className = 'zxf-modal-progress';

        this.progressBar = document.createElement('div');
        this.progressBar.className = 'zxf-modal-progress-bar';

        this.progress.appendChild(this.progressBar);
        this.modal.appendChild(this.progress);
    }

    setupEvents() {
        // 拖拽事件 - 更平滑的拖拽处理
        if (this.options.draggable && (this.header || this.modal)) {
            const dragHandle = this.header || this.modal;

            dragHandle.style.cursor = 'move';

            dragHandle.addEventListener('mousedown', (e) => {
                // 忽略按钮点击
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A') {
                    return;
                }

                e.preventDefault();
                this.dragState.isDragging = true;
                this.dragState.startX = e.clientX;
                this.dragState.startY = e.clientY;

                const rect = this.modal.getBoundingClientRect();
                this.dragState.startLeft = rect.left;
                this.dragState.startTop = rect.top;

                // 添加过渡效果使拖动更平滑
                this.modal.style.transition = 'none';

                document.addEventListener('mousemove', this.handleDragMove);
                document.addEventListener('mouseup', this.handleDragEnd);
            });

            this.handleDragMove = (e) => {
                if (!this.dragState.isDragging) return;

                const dx = e.clientX - this.dragState.startX;
                const dy = e.clientY - this.dragState.startY;

                // 限制拖动范围，防止拖出屏幕
                const newLeft = this.dragState.startLeft + dx;
                const newTop = this.dragState.startTop + dy;

                this.modal.style.left = `${newLeft}px`;
                this.modal.style.top = `${newTop}px`;
                this.modal.style.right = 'auto';
                this.modal.style.bottom = 'auto';
                this.modal.style.transform = 'none';

                if (this.options.onDrag) {
                    this.options.onDrag({
                        left: newLeft,
                        top: newTop
                    }, this);
                }
            };

            this.handleDragEnd = () => {
                this.dragState.isDragging = false;
                // 恢复过渡效果
                this.modal.style.transition = '';
                document.removeEventListener('mousemove', this.handleDragMove);
                document.removeEventListener('mouseup', this.handleDragEnd);
            };
        }

        // ESC 关闭
        if (this.options.escClose) {
            this.handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    this.close();
                }
            };
            document.addEventListener('keydown', this.handleKeyDown);
        }

        // 遮罩层点击关闭
        if (this.options.overlay && this.options.overlayClose) {
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) {
                    this.close();
                }
            });
        }
    }

    setupIframeEvents() {
        if (!this.iframe) return;

        // 事件监听处理
        this.messageHandler = (e) => {
            // 忽略非同源消息
            // if (e.source !== this.iframe.contentWindow) return;

            // 处理来自iframe的消息: 关闭弹窗
            if(e.data && e.data.type === 'close'){
                // 关闭当前弹窗
                this.close();
                return ;
            }
            // 处理来自iframe的消息: 模拟点击父页面
            if(e.data && e.data.type === 'triggerClick'){
                document.querySelector(e.data.selector)?.click();
                return ;
            }
            // 处理来自iframe的消息: 关闭当前弹窗 并 模拟点击父页面
            if(e.data && e.data.type === 'closeAndTriggerClick'){
                // 关闭当前弹窗
                this.close();
                // 点击父页面的元素
                document.querySelector(e.data.selector)?.click();
                return ;
            }
            if(typeof receiveModalData === 'function'){
                // 把数据推送到父页面
                receiveModalData(e.data);
            }
        };

        // 监听来自iframe的消息
        window.addEventListener('message', this.messageHandler);

        // 监听 iframe 加载完成
        this.iframe.addEventListener('load', () => {
            try {
                // 尝试直接访问iframe内容（同源情况）
                const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow?.document;
                if (iframeDoc) {
                    this.handleIframeContent(iframeDoc);

                    // 确保 iframe 的 body 已经加载
                    if (iframeDoc.readyState === 'complete') {
                        // 插入脚本
                        const script = iframeDoc.createElement('script');
                        script.type = 'text/javascript';

                        script.text = `
    // 监听来自父页面的消息
    window.addEventListener('message', (e) => {
      console.log(e);
      // 调用父页面方法
      if (e.data.type === 'func') {
        // TODO:
      }
    });
  `;

                        iframeDoc.body.appendChild(script);
                    }
                }
            } catch (e) {
                console.warn('无法直接访问iframe内容:', e.message);
            } finally {
                this.hideLoadingIndicator();
            }
        });

        // 响应式调整 iframe 大小
        const resizeObserver = new ResizeObserver(() => {
            if (this.iframe) {
                this.iframe.style.width = '100%';
                this.iframe.style.height = '100%';
            }
        });

        resizeObserver.observe(this.modal);
    }

    // 调用 iframe 中的函数
    callIframeFunction(funcName, ...args) {
        if (this.iframe) {
            this.iframe.contentWindow[funcName](...args);
        }
    }

    handleIframeContent(iframeDoc) {
        // 创建 MutationObserver 来监听 .layer-bottom-btns 的变化
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                // 检查是否有 .layer-bottom-btns 被添加或修改
                if (mutation.type === 'childList' ||
                    (mutation.type === 'attributes' && mutation.target.classList.contains('layer-bottom-btns'))) {
                    this.syncButtonsFromIframe(iframeDoc);
                }
            });
        });

        // 开始观察整个文档
        observer.observe(iframeDoc, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });

        // 保存 observer 以便后续可以断开连接
        this.iframeObserver = observer;

        // 初始同步
        this.syncButtonsFromIframe(iframeDoc);
    }

    syncButtonsFromIframe(iframeDoc) {
        const bottomBtns = iframeDoc.querySelector('.layer-bottom-btns');
        if (!bottomBtns) return;

        // 1. 检查并注入 form_after 方法
        const iframeWindow = this.iframe.contentWindow;
        if (typeof iframeWindow.form_after === 'undefined') {
            const script = iframeDoc.createElement('script');
            script.textContent = `
            // 提交后回调
            function form_after(resOrErr) {
                // 判断是否存在 code 属性 且code 的值为 200
                if (typeof resOrErr.code !== 'undefined' && resOrErr.code === 200) {
                    Modal.success(resOrErr.message || '操作成功!', {position: 'top-right',timeout: 2500});
                    setTimeout(function () {
                        // 关闭弹窗并触发模拟点击父页面的 .date-table-tools-refresh-btn 按钮
                        parent.postMessage({type: 'closeAndTriggerClick',selector: '.date-table-tools-refresh-btn'}, '*');
                    }, 2600)
                } else {
                    Modal.error(resOrErr.message || '操作失败!', {position: 'top-right',timeout: 3000});
                }
            }
        `;
            iframeDoc.head.appendChild(script);
        }

        // 1. 保留原始按钮在 iframe 中但隐藏它们
        bottomBtns.style.display = 'none';

        // 2. 创建弹窗底部按钮区域（如果不存在）
        if (!this.footer) {
            this.createFooter();
        }

        // 3. 清空现有按钮并重新同步
        this.footer.innerHTML = '';

        // 4. 克隆按钮并保持原始事件绑定
        const buttons = Array.from(bottomBtns.querySelectorAll('button, a'));
        buttons.forEach(originalBtn => {
            // 创建代理按钮
            const proxyBtn = document.createElement(originalBtn.tagName.toLowerCase());

            // 复制所有属性和样式
            Array.from(originalBtn.attributes).forEach(attr => {
                proxyBtn.setAttribute(attr.name, attr.value);
            });
            proxyBtn.className = originalBtn.className;
            proxyBtn.textContent = originalBtn.textContent;
            proxyBtn.style.cssText = originalBtn.style.cssText;

            // 添加点击事件处理程序
            proxyBtn.addEventListener('click', (e) => {
                // 阻止默认行为（如果有）
                if (e.preventDefault) {
                    e.preventDefault();
                }

                // 触发原始按钮的点击事件
                if (originalBtn.click) {
                    originalBtn.click();
                } else {
                    // 创建并触发点击事件
                    const clickEvent = new MouseEvent('click', {
                        view: window,
                        bubbles: true,
                        cancelable: true
                    });
                    originalBtn.dispatchEvent(clickEvent);
                }

                // 特殊处理 postMessage 调用
                if (originalBtn.hasAttribute('onclick') &&
                    originalBtn.getAttribute('onclick').includes('parent.postMessage')) {
                    const originalOnclick = originalBtn.getAttribute('onclick');
                    // 在 iframe 的上下文中执行原始 onclick 代码
                    try {
                        const iframeWindow = this.iframe.contentWindow;
                        iframeWindow.eval(originalOnclick);
                    } catch (error) {
                        console.error('执行 iframe 按钮点击事件出错:', error);
                    }
                }
            });

            // 添加到弹窗底部
            this.footer.appendChild(proxyBtn);
        });
    }

    setPosition(position) {
        if (this.isMaximized || this.isFullscreen) return;

        const rect = this.modal.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        this.modal.style.position = 'fixed';
        this.modal.style.transform = 'none';

        // 如果有offset参数，优先使用offset定位
        if (this.options.offset && Array.isArray(this.options.offset)) {
            const [top, left] = this.options.offset;
            this.modal.style.top = typeof top === 'number' ? `${top}px` : top;
            this.modal.style.left = typeof left === 'number' ? `${left}px` : left;
            this.modal.style.right = 'auto';
            this.modal.style.bottom = 'auto';
            this.modal.style.transform = 'none';
            return;
        }

        switch (position) {
            case 'top-left':
                this.modal.style.left = '15px';
                this.modal.style.top = '15px';
                this.modal.style.right = 'auto';
                this.modal.style.bottom = 'auto';
                break;
            case 'top-right':
                this.modal.style.right = '15px';
                this.modal.style.top = '15px';
                this.modal.style.left = 'auto';
                this.modal.style.bottom = 'auto';
                break;
            case 'bottom-left':
                this.modal.style.left = '15px';
                this.modal.style.bottom = '15px';
                this.modal.style.right = 'auto';
                this.modal.style.top = 'auto';
                break;
            case 'bottom-right':
                this.modal.style.right = '15px';
                this.modal.style.bottom = '15px';
                this.modal.style.left = 'auto';
                this.modal.style.top = 'auto';
                break;
            case 'top':
                this.modal.style.left = '50%';
                this.modal.style.top = '15px';
                this.modal.style.transform = 'translateX(-50%)';
                this.modal.style.right = 'auto';
                this.modal.style.bottom = 'auto';
                break;
            case 'bottom':
                this.modal.style.left = '50%';
                this.modal.style.bottom = '15px';
                this.modal.style.transform = 'translateX(-50%)';
                this.modal.style.right = 'auto';
                this.modal.style.top = 'auto';
                break;
            case 'left':
                this.modal.style.left = '15px';
                this.modal.style.top = '50%';
                this.modal.style.transform = 'translateY(-50%)';
                this.modal.style.right = 'auto';
                this.modal.style.bottom = 'auto';
                break;
            case 'right':
                this.modal.style.right = '15px';
                this.modal.style.top = '50%';
                this.modal.style.transform = 'translateY(-50%)';
                this.modal.style.left = 'auto';
                this.modal.style.bottom = 'auto';
                break;
            case 'center':
            default:
                this.modal.style.left = '50%';
                this.modal.style.top = '50%';
                this.modal.style.transform = 'translate(-50%, -50%)';
                this.modal.style.right = 'auto';
                this.modal.style.bottom = 'auto';
                break;
        }
    }

    open() {
        if (this.isOpen) return;

        // 触发打开前回调
        if (this.options.onOpen) {
            const result = this.options.onOpen(this);
            if (result === false) return;
        }

        this.isOpen = true;

        // 控制页面滚动
        if (!this.options.bodyScroll) {
            document.body.style.overflow = 'hidden';
        }

        // 显示遮罩层
        if (this.overlay) {
            this.overlay.classList.add('active');
        }

        // 显示弹窗
        this.modal.classList.add('active');

        // 自动关闭
        if (this.options.autoClose > 0) {
            this.startAutoCloseTimer();
        }
        return this;
    }

    startAutoCloseTimer() {
        if (this.timer) {
            clearTimeout(this.timer);
        }

        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        const duration = this.options.autoClose;
        let remaining = duration;

        if (this.progressBar) {
            this.progressBar.style.width = '100%';
            this.progressBar.style.transition = `width ${duration}ms linear`;
            setTimeout(() => {
                this.progressBar.style.width = '0%';
            }, 10);
        }

        this.timer = setTimeout(() => {
            this.close();
        }, duration);
    }

    close() {
        // 触发关闭前回调
        if (this.options.onBeforeClose) {
            const result = this.options.onBeforeClose(this);
            if (result === false) return;
        }

        // 断开 iframe 观察者
        if (this.iframeObserver) {
            this.iframeObserver.disconnect();
            this.iframeObserver = null;
        }

        this.isOpen = false;

        // 恢复页面滚动
        if (!this.options.bodyScroll) {
            document.body.style.overflow = '';
        }

        // 移除消息监听器
        if (this.messageHandler) {
            window.removeEventListener('message', this.messageHandler);
        }

        // 清除定时器
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }

        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        // 隐藏弹窗和遮罩层
        this.modal.classList.remove('active');

        if (this.overlay) {
            this.overlay.classList.remove('active');
        }

        // 延迟移除元素
        setTimeout(() => {
            if (this.modal.parentNode) {
                this.modal.parentNode.removeChild(this.modal);
            }

            if (this.overlay && this.overlay.parentNode) {
                this.overlay.parentNode.removeChild(this.overlay);
            }

            // 移除事件监听
            if (this.options.escClose) {
                document.removeEventListener('keydown', this.handleKeyDown);
            }

            if (this.options.draggable) {
                document.removeEventListener('mousemove', this.handleDragMove);
                document.removeEventListener('mouseup', this.handleDragEnd);
            }

            // 触发关闭回调
            if (this.options.onClose) {
                this.options.onClose(this);
            }

            // 从实例数组中移除
            const index = Modal.instances.indexOf(this);
            if (index !== -1) {
                Modal.instances.splice(index, 1);
            }
        }, 300);
        return this;
    }

    minimize() {
        if (this.isMinimized) return;

        // 保存原始位置和尺寸
        this.originalPosition = {
            top: this.modal.style.top,
            left: this.modal.style.left,
            right: this.modal.style.right,
            bottom: this.modal.style.bottom,
            transform: this.modal.style.transform
        };

        this.originalWidth = this.modal.style.width;
        this.originalHeight = this.modal.style.height;

        // 创建最小化元素
        this.minimizedElement = document.createElement('div');
        this.minimizedElement.className = 'zxf-modal-minimized';
        this.minimizedElement.dataset.modalId = this.id;

        const title = document.createElement('span');
        title.className = 'zxf-modal-minimized-title';
        title.textContent = this.options.title || '已最小化';

        const restoreBtn = document.createElement('button');
        restoreBtn.className = 'zxf-modal-minimized-restore';
        restoreBtn.textContent = '恢复';
        restoreBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.restore();
        });

        this.minimizedElement.appendChild(title);
        this.minimizedElement.appendChild(restoreBtn);

        // 添加到最小化容器
        const minimizedContainer = document.getElementById('zxf-modal-minimized-container') ||
            this.createMinimizedContainer();
        minimizedContainer.appendChild(this.minimizedElement);

        // 隐藏原始弹窗
        this.modal.style.display = 'none';
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }

        this.isMinimized = true;

        // 触发最小化回调
        if (this.options.onMinimize) {
            this.options.onMinimize(this);
        }

        // 点击最小化元素恢复
        this.minimizedElement.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                this.restore();
            }
        });

        // 添加到最小化数组
        Modal.minimizedModals.push(this);
    }

    createMinimizedContainer() {
        const container = document.createElement('div');
        container.id = 'zxf-modal-minimized-container';
        container.style.position = 'fixed';
        container.style.bottom = '15px';
        container.style.left = '15px';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        container.style.zIndex = '99999';
        document.body.appendChild(container);
        return container;
    }

    restore() {
        if (!this.isMinimized && !this.isMaximized && !this.isFullscreen) return;

        // 移除全屏/最大化类
        this.modal.classList.remove('fullscreen', 'maximized');

        // 显示原始弹窗
        this.modal.style.display = '';
        if (this.overlay) {
            this.overlay.style.display = '';
        }

        // 恢复原始位置和尺寸
        this.modal.style.top = this.originalPosition.top;
        this.modal.style.left = this.originalPosition.left;
        this.modal.style.right = this.originalPosition.right;
        this.modal.style.bottom = this.originalPosition.bottom;
        this.modal.style.transform = this.originalPosition.transform;
        this.modal.style.width = this.originalWidth;
        this.modal.style.height = this.originalHeight;
        this.modal.style.borderRadius = '';

        // 更新最大化按钮
        if (this.maximizeBtn) {
            this.maximizeBtn.innerHTML = '&#x26F6;';
            this.maximizeBtn.title = '最大化';
        }

        // 移除最小化元素
        if (this.minimizedElement && this.minimizedElement.parentNode) {
            this.minimizedElement.parentNode.removeChild(this.minimizedElement);
        }

        this.isMinimized = false;
        this.isMaximized = false;
        this.isFullscreen = false;

        // 从最小化数组中移除
        const index = Modal.minimizedModals.indexOf(this);
        if (index !== -1) {
            Modal.minimizedModals.splice(index, 1);
        }

        // 如果没有最小化的弹窗了，移除容器
        if (Modal.minimizedModals.length === 0) {
            const container = document.getElementById('zxf-modal-minimized-container');
            if (container) {
                container.parentNode.removeChild(container);
            }
        }

        // 触发恢复回调
        if (this.options.onRestore) {
            this.options.onRestore(this);
        }
    }

    toggleMaximize() {
        if (this.isMaximized) {
            this.restore();
        } else {
            this.maximize();
        }
    }

    maximize() {
        if (this.isMaximized || this.isFullscreen) return;

        // 保存原始尺寸和位置
        this.originalWidth = this.modal.style.width;
        this.originalHeight = this.modal.style.height;
        this.originalPosition = {
            top: this.modal.style.top,
            left: this.modal.style.left,
            right: this.modal.style.right,
            bottom: this.modal.style.bottom,
            transform: this.modal.style.transform
        };
        // 添加最大化类
        this.modal.classList.add('maximized');

        // 最大化 - 基于可视区域
        this.modal.style.width = 'calc(100vw - 0px)';
        this.modal.style.height = 'calc(100vh - 0px)';
        this.modal.style.top = '15px';
        this.modal.style.left = '15px';
        this.modal.style.margin = 'auto'; // 居中
        this.modal.style.transform = 'none';
        this.modal.style.right = 'auto';
        this.modal.style.bottom = 'auto';
        this.modal.style.inset = '0'; // 完全填充

        this.isMaximized = true;

        // 更新最大化按钮
        if (this.maximizeBtn) {
            this.maximizeBtn.innerHTML = '&#10064;';// &#x26F7;
            this.maximizeBtn.title = '恢复';
        }

        // 触发最大化回调
        if (this.options.onMaximize) {
            this.options.onMaximize(this);
        }
    }

    fullscreen() {
        if (this.isFullscreen) return;

        // 保存原始尺寸和位置
        this.originalWidth = this.modal.style.width;
        this.originalHeight = this.modal.style.height;
        this.originalPosition = {
            top: this.modal.style.top,
            left: this.modal.style.left,
            right: this.modal.style.right,
            bottom: this.modal.style.bottom,
            transform: this.modal.style.transform
        };

        // 添加全屏类
        this.modal.classList.add('fullscreen');

        // 全屏 - 基于可视区域
        this.modal.style.width = '100vw';
        this.modal.style.height = '100vh';
        this.modal.style.top = '0';
        this.modal.style.left = '0';
        this.modal.style.transform = 'none';
        this.modal.style.right = 'auto';
        this.modal.style.bottom = 'auto';
        this.modal.style.borderRadius = '0';
        this.modal.style.inset = '0'; // 完全填充
        this.modal.style.margin = '0';

        this.isFullscreen = true;

        // 触发全屏回调
        if (this.options.onFullscreen) {
            this.options.onFullscreen(this);
        }
    }

    setContent(content) {
        this.options.content = content;
        this.content.innerHTML = '';

        if (typeof content === 'string') {
            if (content.startsWith('<iframe ')) {
                // 先创建iframe容器
                const iframeContainer = document.createElement('div');
                iframeContainer.className = 'zxf-modal-iframe-container';

                // 添加iframe到容器
                iframeContainer.innerHTML = content;
                this.content.appendChild(iframeContainer);
                this.iframe = iframeContainer.querySelector('iframe');

                // 立即显示加载指示器
                this.createLoadingIndicator();

                // 设置iframe加载监听
                this.setupIframeLoadingState();
                this.setupIframeEvents();
            } else {
                // HTML 字符串
                this.content.innerHTML = content;
            }
        } else if (content instanceof HTMLElement) {
            // DOM 元素
            this.content.appendChild(content);
        } else if (typeof content === 'function') {
            // 函数
            const contentResult = content();
            if (contentResult instanceof HTMLElement) {
                this.content.appendChild(contentResult);
            } else {
                this.content.innerHTML = contentResult;
            }
        }
        return this;
    }

    setTitle(title) {
        this.options.title = title;
        if (this.title) {
            this.title.textContent = title;
        } else if ((title !== false && title.trim() !== '') && !this.isTextOnlyAutoCloseModal()) {
            this.header = document.createElement('div');
            this.header.className = 'zxf-modal-header';

            this.title = document.createElement('h3');
            this.title.className = 'zxf-modal-title';
            this.title.textContent = title;

            this.header.appendChild(this.title);
            this.modal.insertBefore(this.header, this.content);
        }
        return this;
    }

    shake() {
        // 先确保弹窗居中
        this.setPosition('center');
        // 添加抖动类
        this.modal.classList.add('zxf-modal-shake');
        setTimeout(() => {
            this.modal.classList.remove('zxf-modal-shake');
        }, 400);
    }

    // 静态方法
    static closeAll() {
        Modal.instances.slice().forEach(modal => modal.close());
    }

    static minimizeAll() {
        Modal.instances.forEach(modal => {
            if (!modal.isMinimized) {
                modal.minimize();
            }
        });
    }

    static restoreAll() {
        Modal.minimizedModals.slice().forEach(modal => modal.restore());
    }

    // Toast 通知功能 - 完整实现
    static toast(options) {
        const defaults = {
            message: '',
            type: 'info',
            position: 'top-right',
            timeout: 5000,
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            onClick: null,
            onClose: null
        };

        const config = { ...defaults, ...options };

        // 确保对应位置的容器存在
        if (!Modal.toastContainers[config.position]) {
            const container = document.createElement('div');
            container.className = 'zxf-modal-toast-container';
            container.style.position = 'fixed';

            switch (config.position) {
                case 'top-right':
                    container.style.top = '15px';
                    container.style.right = '15px';
                    container.style.left = 'auto';
                    container.style.bottom = 'auto';
                    container.style.alignItems = 'flex-end';
                    break;
                case 'top-left':
                    container.style.top = '15px';
                    container.style.left = '15px';
                    container.style.right = 'auto';
                    container.style.bottom = 'auto';
                    container.style.alignItems = 'flex-start';
                    break;
                case 'bottom-right':
                    container.style.bottom = '15px';
                    container.style.right = '15px';
                    container.style.top = 'auto';
                    container.style.left = 'auto';
                    container.style.alignItems = 'flex-end';
                    break;
                case 'bottom-left':
                    container.style.bottom = '15px';
                    container.style.left = '15px';
                    container.style.top = 'auto';
                    container.style.right = 'auto';
                    container.style.alignItems = 'flex-start';
                    break;
                case 'top-center':
                    container.style.top = '15px';
                    container.style.left = '50%';
                    container.style.right = 'auto';
                    container.style.bottom = 'auto';
                    container.style.transform = 'translateX(-50%)';
                    container.style.alignItems = 'center';
                    break;
                case 'bottom-center':
                    container.style.bottom = '15px';
                    container.style.left = '50%';
                    container.style.top = 'auto';
                    container.style.right = 'auto';
                    container.style.transform = 'translateX(-50%)';
                    container.style.alignItems = 'center';
                    break;
                case 'center': // 屏幕中心
                    container.style.bottom = '50%';
                    container.style.left = '50%';
                    container.style.top = 'auto';
                    container.style.right = 'auto';
                    container.style.transform = 'translateX(-50%)';
                    container.style.alignItems = 'center';
                    break;
            }

            document.body.appendChild(container);
            Modal.toastContainers[config.position] = container;
        }

        const toast = document.createElement('div');
        toast.className = 'zxf-modal-toast toast-'+config.type+' '+config.position;

        const iconMap = {
            success: '✓',
            error: '✕',
            info: 'ℹ',
            warning: '⚠'
        };

        if (iconMap[config.type]) {
            const icon = document.createElement('span');
            icon.className = 'toast-icon';
            icon.textContent = iconMap[config.type];
            toast.appendChild(icon);
        }

        const message = document.createElement('span');
        message.textContent = config.message;
        toast.appendChild(message);

        if (config.closeButton) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'toast-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                    if (config.onClose) {
                        config.onClose();
                    }
                }, 300);
            });
            toast.appendChild(closeBtn);
        }

        if (config.progressBar && config.timeout > 0) {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'toast-progress';

            const progressBar = document.createElement('div');
            progressBar.className = 'toast-progress-bar';
            progressBar.style.width = '100%';
            progressBar.style.transition = `width ${config.timeout}ms linear`;

            progressContainer.appendChild(progressBar);
            toast.appendChild(progressContainer);

            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 10);
        }

        if (config.onClick) {
            toast.style.cursor = 'pointer';
            toast.addEventListener('click', config.onClick);
        }

        // 添加到容器
        const container = Modal.toastContainers[config.position];
        if (config.newestOnTop) {
            container.insertBefore(toast, container.firstChild);
        } else {
            container.appendChild(toast);
        }

        // 显示 toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // 自动关闭
        if (config.timeout > 0) {
            const timer = setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                    if (config.onClose) {
                        config.onClose();
                    }
                }, 300);
            }, config.timeout);

            // 保存timer以便可以清除
            toast.dataset.timer = timer;
        }

        return {
            element: toast,
            remove: () => {
                clearTimeout(toast.dataset.timer);
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                    if (config.onClose) {
                        config.onClose();
                    }
                }, 300);
            }
        };
    }

    static iframe(title = '信息', url = '', width = 600, height = 400, style = 'width:100%;height:100%;') {
        return new Modal({
            title: title,
            content: `<iframe src="${url || 'about:blank'}" style="${style}" loading="lazy"></iframe>`,
            width: width,
            height: height,
            bodyScroll: false, // 是否允许父页面滚动
            loadingText: '加载中...',
            loadingSpinner: true,
            onBeforeClose: () => {
                // console.log('iframe onBeforeClose')
            },
            onIframeLoad:  (_modal) => {
                // console.log('iframe onIframeLoad')
            },
        }).open();
    }

    static success(message, options = {}) {
        return Modal.toast({ ...options, message, type: 'success' });
    }

    static error(message, options = {}) {
        return Modal.toast({ ...options, message, type: 'error' });
    }

    static info(message, options = {}) {
        return Modal.toast({ ...options, message, type: 'info' });
    }

    static warning(message, options = {}) {
        return Modal.toast({ ...options, message, type: 'warning' });
    }

    static tips(message, options = {}) {
        options.position = 'center'; // 屏幕中心
        return Modal.toast({ ...options, message, type: 'info' });
    }

    static removeAllToasts() {
        Object.values(Modal.toastContainers).forEach(container => {
            container.innerHTML = '';
        });
    }

    /**
     * 创建表单弹窗
     * @param {Object} options 配置选项
     * @param {string} options.title 弹窗标题
     * @param {Array} options.fields 表单字段配置
     * @param {string} [options.labelAlign='left'] 标签对齐方式 (left|top)
     * @param {number|string} [options.labelWidth=150] 左对齐时的标签宽度(px或百分比)
     * @param {boolean} [options.darkTheme=false] 是否使用暗黑主题
     * @param {Array} [options.buttons] 按钮配置
     * @param {boolean} [options.disabled=false] 是否禁用整个表单
     * @param {Function} [options.onSubmit] 表单提交回调
     * @param {Object} [otherOptions] 其他弹窗选项
     * @returns {Modal} 弹窗实例
     */
    static form(options = {}) {
        const {
            title,
            fields = [],
            labelAlign = 'left',
            labelWidth = 150,
            darkTheme = false,
            buttons,
            disabled = false,
            onSubmit,
            ...otherOptions
        } = options;

        // 默认按钮配置
        const defaultButtons = [
            {
                text: '取消',
                type: 'secondary',
                close: true
            },
            {
                text: '确认',
                type: 'primary',
                click: (e, modal) => {
                    const formData = modal.getFormData();
                    const isValid = modal.validateForm();

                    if (isValid && modal.options.onSubmit) {
                        const result = modal.options.onSubmit(formData, modal);
                        // 如果返回false则阻止关闭
                        // return result !== false;
                        // 全部改为由 onSubmit 内部处理是否关闭
                        return false;
                    }

                    return isValid ? true : 'keep-open';
                }
            }
        ];

        // 创建弹窗实例
        const modal = new Modal({
            title: title,
            buttons: buttons || defaultButtons,
            disabled: disabled,
            onSubmit: onSubmit,
            labelAlign: labelAlign,
            labelWidth: labelWidth,
            darkTheme: darkTheme,
            ...otherOptions
        });

        // 添加表单相关方法
        modal.validateForm = function() {
            if (!this.form) return true;

            let isValid = true;

            this.formFields.forEach(field => {
                const { name, required } = field;
                const formItem = this.form.querySelector(`.zxf-modal-form-item[data-name="${name}"]`);

                if (!formItem || !required) return;

                let fieldValid = true;
                const value = this.getFormData()[name];

                if (Array.isArray(value)) {
                    fieldValid = value.length > 0;
                } else {
                    fieldValid = value !== '' && value !== null && value !== undefined;
                }

                if (!fieldValid) {
                    formItem.classList.add('error');
                    isValid = false;
                } else {
                    formItem.classList.remove('error');
                }
            });

            return isValid;
        };

        modal.getFormData = function() {
            const formData = {};

            if (!this.form) return formData;

            this.formFields.forEach(field => {
                const { name, type } = field;

                if (type === 'checkbox' && field.options && field.options.length > 1) {
                    // 处理多选框组
                    const checkboxes = this.form.querySelectorAll(`input[name="${name}[]"]:checked`);
                    formData[name] = Array.from(checkboxes).map(cb => cb.value);
                } else if (type === 'checkbox') {
                    // 处理单个复选框
                    const checkbox = this.form.querySelector(`input[name="${name}"]`);
                    formData[name] = checkbox ? checkbox.checked : false;
                } else if (type === 'radio') {
                    // 处理单选组
                    const radio = this.form.querySelector(`input[name="${name}"]:checked`);
                    formData[name] = radio ? radio.value : null;
                } else if (type === 'select' && field.multiple) {
                    // 处理多选下拉框
                    const select = this.form.querySelector(`select[name="${name}"]`);
                    formData[name] = select ? Array.from(select.selectedOptions).map(opt => opt.value) : [];
                } else {
                    // 处理其他输入类型
                    const input = this.form.querySelector(`[name="${name}"]`);
                    if (input) {
                        formData[name] = input.value;
                    }
                }
            });

            return formData;
        };

        modal.setFormContent = function(fields, labelAlign = 'left', labelWidth = 150, darkTheme = false, disabled = false) {
            // 创建表单容器
            const form = document.createElement('form');
            form.className = `zxf-modal-form zxf-modal-form-label-${labelAlign}`;

            // 添加暗黑主题类
            if (darkTheme) {
                form.classList.add('zxf-modal-form-dark');
                this.modal.classList.add('zxf-modal-theme-dark');
            }

            // 设置标签宽度
            if (labelAlign === 'left') {
                form.style.setProperty('--label-width', typeof labelWidth === 'number' ? `${labelWidth}px` : labelWidth);
            }

            // 添加表单字段
            fields.forEach(fieldConfig => {
                const formItem = this.createFormItem(fieldConfig, disabled);
                if (formItem) {
                    form.appendChild(formItem);
                }
            });

            // 清空内容并添加表单
            this.content.innerHTML = '';
            this.content.appendChild(form);

            // 保存表单引用
            this.form = form;
            this.formFields = fields;
        };

        modal.createFormItem = function(fieldConfig, parentDisabled = false) {
            const {
                type,
                name,
                label,
                placeholder,
                required,
                options,
                default: defaultValue,
                disabled = false,
                multiple = false
            } = fieldConfig;

            // 创建容器
            const formItem = document.createElement('div');
            formItem.className = 'zxf-modal-form-item';
            formItem.dataset.name = name;

            // 设置禁用状态
            if (parentDisabled || disabled) {
                formItem.classList.add('disabled');
            }

            // 创建标签
            if (label) {
                const labelEl = document.createElement('label');
                labelEl.className = 'zxf-modal-form-label';
                labelEl.textContent = label;
                if (required) {
                    labelEl.innerHTML += '<span style="color:#f56c6c"> *</span>';
                }
                formItem.appendChild(labelEl);
            }

            // 创建控件容器
            const controlContainer = document.createElement('div');
            controlContainer.className = 'zxf-modal-form-control';

            // 根据类型创建不同控件
            switch (type) {
                case 'text':
                case 'password':
                case 'email':
                case 'number':
                case 'input':
                    const input = document.createElement('input');
                    input.type = type === 'input' ? 'text' : type;
                    input.name = name;
                    input.placeholder = placeholder || '';
                    input.value = defaultValue !== undefined ? defaultValue : '';
                    if (required) input.required = true;
                    if (parentDisabled || disabled) input.disabled = true;
                    controlContainer.appendChild(input);
                    break;

                case 'textarea':
                    const textarea = document.createElement('textarea');
                    textarea.name = name;
                    textarea.placeholder = placeholder || '';
                    textarea.value = defaultValue !== undefined ? defaultValue : '';
                    if (required) textarea.required = true;
                    if (parentDisabled || disabled) textarea.disabled = true;
                    controlContainer.appendChild(textarea);
                    break;

                case 'select':
                    const select = document.createElement('select');
                    select.name = name;
                    if (multiple) {
                        select.multiple = true;
                        select.size = Math.min(options?.length || 5, 10);
                    }
                    if (parentDisabled || disabled) select.disabled = true;
                    // 设置class属性
                    select.className = 'form-control custom-select';

                    // 添加占位选项
                    if (placeholder && !multiple) {
                        const placeholderOption = document.createElement('option');
                        placeholderOption.value = '';
                        placeholderOption.textContent = placeholder;
                        placeholderOption.disabled = true;
                        placeholderOption.selected = defaultValue === undefined;
                        select.appendChild(placeholderOption);
                    }

                    // 添加选项
                    if (options && Array.isArray(options)) {
                        options.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = opt.value;
                            option.textContent = opt.label || opt.value;

                            // 设置默认选中
                            if (defaultValue !== undefined) {
                                if (multiple && Array.isArray(defaultValue)) {
                                    option.selected = defaultValue.includes(opt.value);
                                } else if (!multiple && defaultValue === opt.value) {
                                    option.selected = true;
                                }
                            }

                            select.appendChild(option);
                        });
                    }

                    controlContainer.appendChild(select);
                    break;

                case 'radio':
                    if (options && Array.isArray(options)) {
                        const radioGroup = document.createElement('div');
                        radioGroup.className = 'zxf-modal-form-radio-group';

                        options.forEach(opt => {
                            const radioItem = document.createElement('div');
                            radioItem.className = 'zxf-modal-form-radio-item';
                            if (parentDisabled || disabled) radioItem.style.pointerEvents = 'none';

                            const radio = document.createElement('input');
                            radio.type = 'radio';
                            radio.name = name;
                            radio.value = opt.value;
                            radio.id = `${name}_${opt.value}`;
                            if (defaultValue === opt.value) radio.checked = true;
                            if (required) radio.required = true;
                            if (parentDisabled || disabled) radio.disabled = true;

                            const radioLabel = document.createElement('label');
                            radioLabel.htmlFor = radio.id;
                            radioLabel.textContent = opt.label || opt.value;

                            radioItem.appendChild(radio);
                            radioItem.appendChild(radioLabel);
                            radioGroup.appendChild(radioItem);
                        });

                        controlContainer.appendChild(radioGroup);
                    }
                    break;

                case 'checkbox':
                    if (options && Array.isArray(options)) {
                        const checkboxGroup = document.createElement('div');
                        checkboxGroup.className = 'zxf-modal-form-checkbox-group';

                        // 多选框组
                        if (options.length > 1) {
                            options.forEach(opt => {
                                const checkboxItem = document.createElement('div');
                                checkboxItem.className = 'zxf-modal-form-checkbox-item';
                                if (parentDisabled || disabled) checkboxItem.style.pointerEvents = 'none';

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.name = name + '[]';
                                checkbox.value = opt.value;
                                checkbox.id = `${name}_${opt.value}`;

                                // 设置默认选中
                                if (defaultValue !== undefined) {
                                    if (Array.isArray(defaultValue)) {
                                        checkbox.checked = defaultValue.includes(opt.value);
                                    } else if (defaultValue === opt.value) {
                                        checkbox.checked = true;
                                    }
                                }

                                if (parentDisabled || disabled) checkbox.disabled = true;

                                const checkboxLabel = document.createElement('label');
                                checkboxLabel.htmlFor = checkbox.id;
                                checkboxLabel.textContent = opt.label || opt.value;

                                checkboxItem.appendChild(checkbox);
                                checkboxItem.appendChild(checkboxLabel);
                                checkboxGroup.appendChild(checkboxItem);
                            });
                        }
                        // 单个复选框
                        else {
                            const checkboxItem = document.createElement('div');
                            checkboxItem.className = 'zxf-modal-form-checkbox-item';
                            if (parentDisabled || disabled) checkboxItem.style.pointerEvents = 'none';

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = name;
                            checkbox.id = name;
                            checkbox.value = '1';

                            // 设置默认选中
                            if (defaultValue !== undefined) {
                                checkbox.checked = Boolean(defaultValue);
                            }

                            if (parentDisabled || disabled) checkbox.disabled = true;

                            const checkboxLabel = document.createElement('label');
                            checkboxLabel.htmlFor = checkbox.id;
                            checkboxLabel.textContent = options[0].label || options[0].value;

                            checkboxItem.appendChild(checkbox);
                            checkboxItem.appendChild(checkboxLabel);
                            checkboxGroup.appendChild(checkboxItem);
                        }

                        controlContainer.appendChild(checkboxGroup);
                    }
                    break;

                default:
                    console.warn(`未知的表单字段类型: ${type}`);
                    return null;
            }

            // 添加错误提示
            const errorEl = document.createElement('div');
            errorEl.className = 'zxf-modal-form-error';
            errorEl.textContent = required ? '此项为必填项' : '';
            controlContainer.appendChild(errorEl);

            formItem.appendChild(controlContainer);
            return formItem;
        };

        // 设置表单内容
        modal.setFormContent(fields, labelAlign, labelWidth, darkTheme, disabled);

        // 初始化表单
        myTools && myTools.init();
        return modal;
    }
}

// 全局快捷方式
window.Modal = Modal;
