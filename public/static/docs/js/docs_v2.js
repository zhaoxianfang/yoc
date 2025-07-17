// 多语言数据
const i18nData = {
    "zh-CN": {
        "searchPlaceholder": "搜索...",
        "noResults": "没有找到匹配的结果",
        "tocTitle": "目录",
        "tocToggle": "收起",
        "footerText": "威四方-在线文档. 保留所有权利.",
        "about": "关于我们",
        "help": "帮助中心",
        "privacy": "隐私政策",
        "home": "首页",
        "guide": "指南",
        "api": "API",
        "component": "组件",
        "faq": "常见问题",
        "welcome": "欢迎",
        "logoText": "文档系统",
        "toggleMenu": "切换菜单",
        "toggleTheme": "切换主题",
        "toggleLanguage": "切换语言",
        "moreOptions": "更多选项"
    },
    "en-US": {
        "searchPlaceholder": "Search...",
        "noResults": "No matching results found",
        "tocTitle": "Table of Contents",
        "tocToggle": "Collapse",
        "footerText": "WeiSiFang Docs. All rights reserved.",
        "about": "About Us",
        "help": "Help Center",
        "privacy": "Privacy Policy",
        "home": "Home",
        "guide": "Guide",
        "api": "API",
        "component": "Components",
        "faq": "FAQ",
        "welcome": "Welcome",
        "logoText": "DocSystem",
        "toggleMenu": "Toggle Menu",
        "toggleTheme": "Toggle Theme",
        "toggleLanguage": "Toggle Language",
        "moreOptions": "More Options"
    }
};

// 语言代码映射
const langCodeMap = {
    "zh-CN": "CN",
    "en-US": "EN",
};

// DOM元素
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const menuContainer = document.getElementById('menuContainer');
const content = document.getElementById('content');
const navTabs = document.getElementById('navTabs');
const navTabItems = document.querySelectorAll('.nav-tab');
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const searchContainer = document.getElementById('searchContainer');
const themeToggle = document.getElementById('themeToggle');
const languageToggle = document.getElementById('languageToggle');
const languageMenu = document.getElementById('languageMenu');
const langBadge = document.getElementById('langBadge');
const userAvatar = document.getElementById('userAvatar');
const userCard = document.getElementById('userCard');
const headerMenuToggle = document.getElementById('headerMenuToggle');
const toc = document.getElementById('toc');
const tocList = document.getElementById('tocList');
const tocTitle = document.querySelector('.toc-title');
const tocToggle = document.getElementById('tocToggle');
const tocCollapseHandle = document.getElementById('tocCollapseHandle');
const articleContent = document.getElementById('articleContent');
const footer = document.querySelector('.footer');
const footerText = document.getElementById('footerText');
const logoText = document.getElementById('logoText');
const backToTop = document.getElementById('backToTop');
const tocTitleText = document.getElementById('tocTitleText');

// 当前状态
let currentCategory = 'guide';
let defaultCategory = 'guide';
let sidebarOpen = false;
let headerMenuOpen = false;
let searchOpen = false;
let currentLanguage = 'zh-CN';
let tocCollapsed = false;

// 初始化
function initDocsAll() {
    // 如果定义了nav_category就直接使用 ，否则获取menuData的第一个键
    currentCategory = (typeof nav_category === 'undefined' || isEmpty(nav_category)) ? Object.keys(menuData)[0]: nav_category;
    initTheme(); // 初始化主题
    renderMenu(currentCategory); //  渲染菜单
    setupEventListeners(); // 设置整个页面事件监听器
    setupScrollSpy(); // 设置滚动监听
    updateUIForLanguage(); // 更新UI语言
    setupBackToTop(); // 设置返回顶部按钮
    updateTOCVisibility(); // 更新TOC可见性

    initDocContent();

}
// 只初始化文档内容部分（适用于页面内容局部加载）
function initDocContent() {
    generateTOC(); // 生成目录
    setupCodeBlocks(); // 添加代码块高亮
    //
    setTimeout(() => {
        typeof init_right_menu == 'function' && init_right_menu();
    }, 100);
    init_tips();
}

// 只初始化文档页面头部（适用于页面没有菜单）
function initDocHead() {
    initTheme(); // 初始化主题
    pageHeadEventListeners();// 初始化页面头事件
    setupScrollSpy(); // 设置滚动监听
    updateUIForLanguage(); // 更新UI语言
    setupBackToTop(); // 设置返回顶部按钮
}

// 初始化主题
function initTheme(theme = '') {
    // 判断 theme 是否为空
    const currentTheme = theme || localStorage.getItem('docs_theme') || document.documentElement.getAttribute('data-theme') || '';
    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme || '');
        // 存储用户选择的主题 eg: dark
        localStorage.setItem('docs_theme', currentTheme);
    } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('docs_theme', '');
    }
    if(typeof cherry !== 'undefined'){
        cherry.setTheme(currentTheme || 'light');
    }else{
        setTimeout(function () {
            typeof cherry !== 'undefined' && cherry.setTheme(currentTheme || 'light');
        },2000)
    }
}

// 获取当前主题
function getTheme(){
    return localStorage.getItem('docs_theme') || document.documentElement.getAttribute('data-theme') || 'light';
}
// 获取CSRF令牌
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}
function findFirstDocItem(items) {
    for (const item of items) {
        if (item.menu_type === "doc" || item.menu_type === 'app_manage_doc') {
            return item; // 找到第一个 type 为 "doc" 的 item
        }
        if (item.children && item.children.length > 0) {
            const found = findFirstDocItem(item.children); // 递归查找子级
            if (found) return found;
        }
    }
    return null; // 没找到
}
// 渲染菜单
function renderMenu(category,doc_id = null) {
    // 判断 menuData 是否存在或者是否为空
    if (typeof menuData === 'undefined' || isEmpty(menuData)) {
        return;
    }
    const menu_list = menuData[category] || [];
    const items = Object.assign([], menu_list);
    let buttons = [];
    // 取出 items 的最后一个元素，判断最后一个元素是否存在 键名为 buttons 的元素
    if (items[items.length - 1] && items[items.length - 1].buttons) {
        // 从items 中删除 buttons
        buttons = items.splice(items.length - 1, 1)[0].buttons;
    }

    menuContainer.innerHTML = generateMenuHTML(items);

    if (buttons.length > 0) {
        menuContainer.insertAdjacentHTML('beforeend', generateButtonsHTML(buttons));
    }

    let url = window.location.pathname;

    doc_id = !isEmpty(doc_id)?doc_id: (typeof current_doc_id !== 'undefined'?  current_doc_id: ''); // 需要激活的菜单项的id和它的所有parents菜单也都展开和激活
    if(isEmpty(doc_id)) {
        // 使用正则匹配 /数字/ 加上 help或者edit或者users 的结构
        let match = url.match(/\/(\d+)\/(help|edit|users)/);
        if ( !match || !match[2]) {
            // 使用正则匹配 /appId_docId 的结构
            match = url.match(/\/(\d+)_(\d+)/);
        }

        if (match && match[2]) {
            doc_id = match[2];
        } else {
            // 查找包含无限级children结构的 items里面 menu_type 为 doc 的第一个数据
            // 打开第一篇文章
            let find = findFirstDocItem(items);
            if (find) {
                doc_id = find.id;
            }
        }
    }
    if(doc_id){
        let menuItem = menuContainer.querySelector(`.menu-item[data-id="${doc_id}"]`);
        if (menuItem) {
            menuItem.classList.add('open');
            menuItem.classList.add('active');
        }

        while (menuItem) {
            menuItem = menuItem.parentElement.closest('.menu-item');
            if (menuItem) {
                menuItem.classList.add('open');
            }
        }
    }
}

// 生成菜单HTML
function generateMenuHTML(items, level = 0) {
    return items.map(item => {
        const hasChildren = item.children && item.children.length > 0;
        const indentClass = `level-${level}`;
        let hasUrl = ( !isEmpty(item.url) && item.url !== '#' && item.url !== 'javascript:;');
        let datasetStr= hasUrl?` data-id="${item.id}" `:generateDatasetStr(item);
        return `<li class="menu-item ${indentClass} ${(hasChildren || item.menu_type === 'dir') ? 'has-children' : ''}" ${datasetStr}>
                        <a href="${hasUrl ? item.url : '#'}" class="menu-link" data-has_url="${hasUrl ? 1 : ''}" ${datasetStr}>
                            ${item.icon ? `<span class="menu-icon">${item.icon}</span>` : ''}
                            <span class="menu-text">${item.title}</span>
                            ${item.badge ? `<span class="menu-badge">${item.badge}</span>` : ''}
                        </a>
                        ${hasChildren ? `
                            <ul class="submenu">
                                ${generateMenuHTML(item.children, level + 1)}
                            </ul>
                        ` : ''}
                    </li>
                `;
    }).join('');
}

// 生成底部按钮HTML
function generateButtonsHTML(buttons) {
    return buttons.map(btn => {
        return `<div class="m-1 text-center">
            <button type="button" class="docs-app-menu-handle-btn ${btn.class??''}" data-app_id="${btn.app_id??''}" data-type="${btn.type??''}">
                ${btn.icon ? `<i class="wsf-icon icon-plus">${btn.icon}</i>` : ''}
                ${btn.title}
            </button>
        </div>`;
    }).join('');
}

// 生成dataset 字符串
function generateDatasetStr(obj) {
    if ( !obj) {
        return "";
    }
    let datasetStr = "";
    // 判断obj 是否为 json对象，遍历 obj的每个键值，如果值是字符串或者数字类型；值不为空且字符串类型值的长度小于10则设置dataset
    for (const key in obj) {
        if (obj.hasOwnProperty(key) && !['children', 'title'].includes(key)) {
            let value = obj[key];
            if (typeof value === 'string' || typeof value === 'number') {
                if(value.toString() !== '' && value.toString().length < 30){
                    datasetStr += ' data-'+key+'="'+value+'" ';
                }else if(value.toString() === ''){
                    datasetStr += ' data-'+key+' ';
                }
            }
        }
    }

    return datasetStr;
}

// 设置代码块 - 新增
function setupCodeBlocks() {
    const preElements = document.querySelectorAll('pre');

    preElements.forEach(pre => {
        // 检查是否已经添加了复制按钮
        if (pre.querySelector('.copy-code-btn')) {
            return;
        }
        // 添加复制按钮
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-code-btn';
        copyBtn.textContent = '复制';
        copyBtn.title = '复制代码';
        pre.appendChild(copyBtn);

        // 复制功能
        copyBtn.addEventListener('click', () => {
            const code = pre.querySelector('code').textContent;
            // 剪贴板复制
            copyToClipboard(code,copyBtn);
        });

        // 检测是否需要行号
        const code = pre.querySelector('code');
        // if (code && code.innerHTML.includes('\n')) {
        if (code && code.innerHTML) {
            addLineNumbers(pre, code);
        }
    });
    // 查找没有pre 标签包围的code 标签
    const codeElements = document.querySelectorAll('code:not(pre code)');
    codeElements.forEach(codeElement => {
        // 点击 code 标签时复制
        codeElement.addEventListener('click', () => {
            copyToClipboard(codeElement.textContent,'');
        })
    })
}
// 调用 剪贴板复制 功能
function copyToClipboard(text,copyBtn) {
    if (navigator.clipboard) {
        // 使用 Clipboard API
        navigator.clipboard.writeText(text)
            .then(() => {
                // console.log("文本已成功复制到剪贴板！");
                if(!isEmpty(copyBtn)){
                    copyBtn.textContent = '已复制!';
                    copyBtn.classList.add('copied');
                    setTimeout(() => {
                        copyBtn.textContent = '复制';
                        copyBtn.classList.remove('copied');
                    }, 2500);
                }else{
                    typeof show_tips === 'function' && show_tips('内容已复制',1000);
                }
            })
            .catch((err) => {
                console.error("无法复制文本: ", err);
            });
    } else {
        // 降级到 execCommand 方法
        const textarea = document.createElement("textarea");
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand("copy");
            // console.log("文本已成功复制到剪贴板...！");
            if(!isEmpty(copyBtn)) {
                copyBtn.textContent = '已复制!';
                copyBtn.classList.add('copied');
                setTimeout(() => {
                    copyBtn.textContent = '复制';
                    copyBtn.classList.remove('copied');
                }, 2500);
            }else {
                typeof show_tips === 'function' && show_tips('内容已复制',1000);
            }
        } catch (err) {
            console.error("无法复制文本: ", err);
        }
        document.body.removeChild(textarea);
    }
}


// 添加行号 - 新增
function addLineNumbers(pre, code) {
    pre.classList.add('with-line-numbers');

    // 分割代码行为数组
    const lines = code.innerHTML.split('\n');

    // 重新构建带行号的代码
    let numberedCode = '';
    lines.forEach((line, index) => {
        if (line.trim() === '' && index === lines.length - 1) return; // 忽略最后一行空行
        numberedCode += `<span class="line-number"></span>${line}\n`;
    });

    code.innerHTML = numberedCode;
}

// 设置事件监听
function setupEventListeners() {
    menuHeadEventListeners();
    pageHeadEventListeners();
}

// 页面头部事件监听
function pageHeadEventListeners() {
    // 顶部导航标签切换
    navTabs.addEventListener('click', (e) => {
        if (e.target.classList.contains('nav-tab-item')) {
            const category = e.target.dataset.category;
            switchCategory(category);
        }
    });

    // 搜索功能：输入
    searchInput.addEventListener('input', debounce((e) => {
        const query = e.target.value.trim();
        // console.log('搜索',  query);
        if (query.length > 0) {
            docs_query(query);
        } else {
            searchResults.style.display = 'none';
        }
    }, 1200));
    // 搜索功能：回车
    searchInput.addEventListener('keydown', function(event) {
        // 检查按下的键是否是Enter（键码13）
        if (event.key === 'Enter' || event.keyCode === 13) {
            // 执行回车后的操作
            // console.log('输入的值是:', this.value);
            // 可以在这里提交表单或执行其他逻辑
            const query = this.value.trim();
            // console.log('搜索',  query);
            if (query.length > 0) {
                docs_query(query);
            } else {
                searchResults.style.display = 'none';
            }
        }
    });

    // 点击搜索区域外部关闭搜索结果
    document.addEventListener('click', (e) => {
        if (!searchContainer.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // 主题切换
    themeToggle.addEventListener('click', toggleTheme);

    // 语言切换
    languageToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        languageMenu.classList.toggle('show');
    });

    // 语言选择
    languageMenu.addEventListener('click', (e) => {
        if (e.target.classList.contains('dropdown-item')) {
            const lang = e.target.dataset.lang;
            switchLanguage(lang);
        }
    });

    // 点击页面其他区域关闭语言菜单
    document.addEventListener('click', (e) => {
        if (!languageToggle.contains(e.target)) {
            languageMenu.classList.remove('show');
        }
    });

    // 用户头像点击
    userAvatar.addEventListener('click', (e) => {
        e.stopPropagation();
        userCard.classList.toggle('show');
    });

    // 点击页面其他区域关闭用户卡片
    document.addEventListener('click', (e) => {
        if (!userAvatar.contains(e.target)) {
            userCard.classList.remove('show');
        }
    });

    // 顶部右侧菜单切换
    headerMenuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleHeaderMenu();
    });

    // 在小屏幕下点击内容区域关闭菜单
    content.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
            closeMobileMenus();
        }
    });

    // 窗口大小变化时调整布局
    window.addEventListener('resize', handleResize);
}

function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 900;
}

// 页面菜单等页面内容事件监听
function menuHeadEventListeners() {
    // 左侧菜单切换按钮
    sidebarToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleSidebar();
    });

    // 菜单项点击
    menuContainer.addEventListener('click', (e) => {
        const menuLink = e.target.closest('.menu-link');
        if (!menuLink) return;

        e.preventDefault();
        const menuItem = menuLink.parentElement;
        const hasChildren = menuItem.classList.contains('has-children');
        const id = menuLink.dataset.id;
        const title = menuLink.querySelector('.menu-text').textContent.trim();

        if (hasChildren) {
            // 切换子菜单展开/收起
            menuItem.classList.toggle('open');
        } else {
            // 没有子菜单或者挂载文章的纯菜单
            if(menuLink.dataset.menu_type === 'dir'){
                return;
            }
            const href =  menuLink.getAttribute('href');
            // 有url 的直接刷新页面
            if(menuLink.dataset.has_url){
                window.location.href = href;
                return;
            }
            // 没有url的设置活跃菜单项
            setActiveMenuItem(menuItem);
            loadContent(id, title, menuLink.dataset);
        }
    });
    // 目录切换
    tocToggle.addEventListener('click', () => {
        tocCollapsed = true;
        updateTOCVisibility();
    });

    // 目录展开按钮点击
    tocCollapseHandle.addEventListener('click', () => {
        tocCollapsed = false;
        updateTOCVisibility();
    });
}

function docs_query(query){
    if(typeof custom_search_page == 'function'){
        try {
            const results = custom_search_page(query);
            if(results){
                if (typeof results?.then === 'function') {
                    // 处理异步情况
                    results.then(data => {
                        renderSearchResults(data);
                        searchResults.style.display = 'block';
                    }).catch(err => {});
                } else {
                    // 处理同步情况
                    renderSearchResults(results);
                    searchResults.style.display = 'block';
                }
            }
        } catch (err) {
            // 捕获同步错误（如函数未定义）
            searchResults.style.display = 'none';
        }
    }
}

// 更新TOC可见性
function updateTOCVisibility() {
    if(!tocCollapseHandle){
        return;
    }
    if (window.innerWidth <= 900) {
        tocCollapseHandle.style.display = 'none';
        return;
    }

    if (tocCollapsed) {
        toc.classList.add('toc-collapsed');
        tocCollapseHandle.style.display = 'flex';
    } else {
        toc.classList.remove('toc-collapsed');
        tocCollapseHandle.style.display = 'none';
    }
}

// 切换显示TOC
function showDocsToc(hidden = false) {
    if (hidden) {
        toc.classList.add('toc-collapsed');
        toc.style.display = 'none';
    } else {
        toc.classList.remove('toc-collapsed');
        toc.style.display = 'block';
    }
}

// 切换侧边栏
function toggleSidebar() {
    sidebarOpen = !sidebarOpen;
    sidebar.classList.toggle('open', sidebarOpen);
}

function isEmpty(value) {
    if (value === null || value === undefined) return true;
    if (typeof value === 'string' && (value.trim() === '' || value === 'undefined' ) ) return true;
    if (Array.isArray(value) && value.length === 0) return true;
    if (typeof value === 'object' && Object.keys(value).length === 0) return true;
    return false;
}
// 切换分类
function switchCategory(category) {
    // 判断 category 是否为 undefined
    category = !isEmpty(category) ? category: defaultCategory;
    currentCategory = category;

    // 更新活跃标签
    navTabItems.forEach(tab => tab.classList.remove('active'));
    document.querySelector(`.nav-tab[data-category="${category}"]`)?.classList.add('active');

    // 渲染对应菜单
    renderMenu(category);

    // 在小屏幕下关闭菜单
    if (window.innerWidth <= 900) {
        closeMobileMenus();
    }
    setTimeout(() => {
        typeof init_right_menu == 'function' && init_right_menu();
    }, 100);
}

// 设置活跃菜单项
function setActiveMenuItem(menuItem) {
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    menuItem.classList.add('active');
}

// 加载内容
function loadContent(id, title, dataset) {
    // 这里可以添加实际的内容加载逻辑
    if(typeof custom_load_page == 'function'){
        try {
            const result = custom_load_page(id, title, dataset);
            if(result){
                if (result instanceof Promise) {
                    // 处理异步情况
                    result.then(data => {
                        renderMenu(currentCategory,id);
                    }).catch(err => {});
                } else {
                    // 处理同步情况
                    renderMenu(currentCategory,id);
                }
            }
        } catch (syncErr) {
            // 捕获同步错误（如函数未定义）
            console.error('同步调用错误:', syncErr);
        }
    }else{
        // console.log(`加载内容: ${id} - ${title}`);
        renderMenu(currentCategory,id);
    }

    // 模拟内容加载
    document.getElementById('content').scrollTo(0, 0);
}

// json 转 formData数据
function jsonToFormData(json) {
    const formData = new FormData();
    for (const key in json) {
        if (json.hasOwnProperty(key)) {
            formData.append(key, json[key]);
        }
    }
    return formData;
}

// 进行pjax 请求
function pjax_request(url, data, success, error, method='GET') {
    const config = {
        method,
        headers: {
            'X-PJAX': 'true', // 关键 PJAX 标识头
            'X-Requested-With': 'XMLHttpRequest', // 可选，模拟 AJAX
            'X-CSRF-TOKEN': getCsrfToken() // 从 meta 获取并发送
        },
        mode: 'cors', // 确保服务器允许跨域
    };

    if (method.toUpperCase() === 'POST') {
        config.body = jsonToFormData(data);
    }

    // 2、使用 fetch API
    return fetch(url, config).then(async response => {
        const data = await response.json().catch(() => ({}));
        return response.ok ? data : Promise.reject({
            ...data,
            status: response.status,
            statusText: response.statusText
        });
    }).then(data => {
        if(data){
            if(typeof success == 'function'){
                let res = success(data);
                initDocContent();
                return res;
            }
        }
    }).catch(err => {
        error && error(err);
        throw err;
    });
}

// 进行http 请求
function http_request(url, data, success, error, method='GET') {
    const config = {
        method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest', // 可选，模拟 AJAX
            // 'Content-Type': 'application/x-www-form-urlencoded', // 注意: 不要手动设置 Content-Type，浏览器会自动处理
            'X-CSRF-TOKEN': getCsrfToken() // 从 meta 获取并发送
        },
        mode: 'cors', // 确保服务器允许跨域
    };

    if (method.toUpperCase() === 'POST') {
        config.body = jsonToFormData(data);
    }

    // 2、使用 fetch API
    return fetch(url, config).then(async response => {
        const data = await response.json().catch(() => ({}));
        return response.ok ? data : Promise.reject({
            ...data,
            status: response.status,
            statusText: response.statusText
        });
    }).then(data => {
        if(data){
            if(typeof success == 'function'){
                return success(data);
            }
        }
    }).catch(err => {
        error && error(err);
        typeof show_tips === 'function' && show_tips(err.message || err.msg || '请求失败',3200);
        // throw err;
    });
}

// 切换主题
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    initTheme(currentTheme === 'dark' ? 'light' : 'dark');
}

// 切换语言
function switchLanguage(lang) {
    console.log(`切换语言: ${lang}`);
    currentLanguage = lang;
    updateUIForLanguage();
    languageMenu.classList.remove('show');
    langBadge.textContent = langCodeMap[lang] || lang.substring(0, 2).toUpperCase();
}

// 切换顶部菜单
function toggleHeaderMenu() {
    headerMenuOpen = !headerMenuOpen;
    navTabs.classList.toggle('open', headerMenuOpen);
    searchContainer.classList.toggle('open', headerMenuOpen);
}

// 关闭移动端菜单
function closeMobileMenus() {
    // 左侧菜单
    sidebarOpen = false;
    sidebar?.classList.remove('open');

    // 顶部菜单
    headerMenuOpen = false;
    navTabs.classList.remove('open');
    searchContainer.classList.remove('open');
}

// 处理窗口大小变化
function handleResize() {
    if (window.innerWidth > 900) {
        closeMobileMenus();
    }
    updateTOCVisibility();

    // 判断屏幕是否为移动端
    if(isMobile()){
        (typeof cherry !== 'undefined') && setTimeout(function(){
            cherry.switchModel('editOnly');
        },400);
    }else{
        (typeof cherry !== 'undefined') && setTimeout(function(){
            cherry.switchModel('edit&preview');
        },400);
    }
}

// 防抖函数
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}

// 渲染搜索结果
function renderSearchResults(results) {
    if (results.length > 0) {

        searchResults.innerHTML = results.map(result => {
            result.category = !isEmpty(result.category)?  result.category : defaultCategory;
            let in_docs_app_inner_search = true; // 是否在某一个文档内搜索？
            if(result.app && result.app.app_name){
                // 在所有docs 中搜索时会有 app.app_name
                result.category =result.app.app_name;
                in_docs_app_inner_search = false;
            }

            let hasUrl = ( !isEmpty(result.url) && result.url !== '#' && result.url !== 'javascript:;');
            let datasetStr= hasUrl?` data-id="${result.id}" `:generateDatasetStr(result);
            return ` <div class="search-result-item" data-has_url="${hasUrl ? 1 : ''}" ${datasetStr} data-in_app_inner="${in_docs_app_inner_search?'1':''}">
                        <div class="result-title">
                            ${result.icon || ''} ${result.title}
                        </div>
                        <div class="result-content">${result.content.substring(0, 60)}...</div>
                        <div class="result-category">${i18nData[currentLanguage][result.category] || result.category}</div>
                    </div>
                `}).join('');

        // 搜索结果项点击
        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                const category = item.dataset.category;
                const id = item.dataset.id;
                const title = item.querySelector('.result-title').textContent.trim();

                currentCategory = category;
                // 切换到对应分类
                switchCategory(category);

                // 没有子菜单或者挂载文章的纯菜单
                if(item.dataset.menu_type === 'dir'){
                    return;
                }

                // 有url 的直接刷新页面
                if(item.dataset.has_url){
                    window.location.href = item.getAttribute('href');
                    return;
                }

                // 这里可以加载对应的文档内容
                loadContent(id, title, item.dataset);

                // 关闭搜索结果
                searchResults.style.display = 'none';
                searchInput.value = '';
            });
        });
    } else {
        searchResults.innerHTML = `<div class="search-result-item">${i18nData[currentLanguage].noResults}</div>`;
    }
}

// 生成目录
function generateTOC() {
    // 判断 tocList 的值是否为 null
    if (tocList == null) {
        return;
    }

    const headings = articleContent.querySelectorAll('h1, h2, h3, h4, h5');
    let tocHTML = '';

    headings.forEach(heading => {
        const id = heading.id || heading.textContent.toLowerCase().replace(/\s+/g, '-');
        heading.id = id;

        const level = parseInt(heading.tagName.substring(1));
        const className = `toc-item-h${level}`;

        tocHTML += `<li class="toc-item ${className}">
                        <a href="#${id}" class="toc-link">${heading.textContent}</a>
                    </li>`;
    });

    tocList.innerHTML = tocHTML;

    // 目录项点击
    tocList.querySelectorAll('.toc-link')?.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// 设置滚动监听
function setupScrollSpy() {
    const headings = articleContent?.querySelectorAll('h1, h2, h3, h4, h5');
    const tocLinks = tocList?.querySelectorAll('.toc-link');

    window.addEventListener('scroll', () => {
        let currentActive = null;
        const scrollPosition = window.scrollY + 100;

        headings?.forEach(heading => {
            if (heading.offsetTop <= scrollPosition) {
                currentActive = heading.id;
            }
        });

        tocLinks?.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentActive}`) {
                link.classList.add('active');
            }
        });
    });
}

// 设置返回顶部按钮
function setupBackToTop() {
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// 更新UI语言
function updateUIForLanguage() {
    const langData = i18nData[currentLanguage] || i18nData['zh-CN'];

    // 更新搜索框占位符
    searchInput.placeholder = langData.searchPlaceholder;

    // 判断tocTitleText 是否存在
    if (tocTitleText) {
        // 更新目录标题
        tocTitleText.textContent = langData.tocTitle;
        tocToggle.textContent = langData.tocToggle;
    }

    // 更新页脚
    footerText.textContent = langData.footerText;
    document.querySelectorAll('.footer a')[0].textContent = langData.about;
    document.querySelectorAll('.footer a')[1].textContent = langData.help;
    document.querySelectorAll('.footer a')[2].textContent = langData.privacy;

    // 更新logo文本
    logoText && (logoText.textContent = langData.logoText);

    // 更新工具提示
    document.querySelector('.tooltip-text', sidebarToggle).textContent = langData.toggleMenu;
    document.querySelector('.tooltip-text', themeToggle).textContent = langData.toggleTheme;
    document.querySelector('.tooltip-text', languageToggle).textContent = langData.toggleLanguage;
    document.querySelector('.tooltip-text', headerMenuToggle).textContent = langData.moreOptions;

    // 重新渲染搜索结果（如果有）
    if (searchResults.style.display === 'block') {
        // const query = searchInput.value.trim();
        // if (query.length > 0) {
        //     docs_query(query);
        // }
    }
}

/**
 * 自定义 Tabs 组件 开始
 */
// document.addEventListener('DOMContentLoaded', function() {
class CustomTabs {
    constructor(tabsContainer) {
        this.tabsContainer = tabsContainer || document;
        this.tabLinks = this.tabsContainer.querySelectorAll('.custom-tabs a');
        this.tabContents = this.tabsContainer.querySelectorAll('.tab-content');
        this.html = document.documentElement;

        const _this = this;
        // 事件监听
        this.tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.id !== 'current') {
                    _this.switchTab(this);
                }
            });
        });

        this.initTabs();
    }
    initTabs() {
        if (this.tabLinks.length > 0 && this.tabContents.length > 0) {
            this.tabLinks[0].id = 'current';
            this.tabContents[0].classList.add('active');
        }
        this.handleHash();
    }
    switchTab(clickedLink) {
        // 移除所有active状态
        this.tabLinks.forEach(link => link.id = '');
        this.tabContents.forEach(content => content.classList.remove('active'));

        // 添加当前active状态
        clickedLink.id = 'current';
        const target = document.querySelector(clickedLink.getAttribute('name'));
        if (target) target.classList.add('active');

        // 更新URL
        const tabName = clickedLink.getAttribute('name');
        if (tabName) {
            history.replaceState(null, null, tabName);
        }
    }
    handleHash() {
        const hash = window.location.hash;
        if (hash) {
            const matchingLink = Array.from(this.tabLinks).find(link =>
                link.getAttribute('name') === hash
            );
            if (matchingLink) this.switchTab(matchingLink);
        }
    }
}
window.CustomTabs = CustomTabs;
// 初始化 Tabs
new CustomTabs();

/**
 * 自定义 Tabs 组件 结束
 */

/**
 * 模拟jquery 的元素选择和赋值 开始
 */
/**
 * 监听文件选择变化并返回文件URL
 * @param {string|HTMLElement} inputFileEle - 选择器字符串 (#id 或 .class) 或 DOM 元素
 * @param {function} successFun - 成功回调函数
 * @return {function} 取消监听的函数
 */
function listenFileChangeURL(inputFileEle, successFun) {
    // 获取DOM元素
    const element = typeof inputFileEle === 'string'
        ? document.querySelector(inputFileEle)
        : inputFileEle;

    if (!element) {
        console.error('Element not found:', inputFileEle);
        return function() {};
    }

    // 上次处理的文件，用于避免重复触发
    let lastFile = null;

    // 事件处理函数
    const handleFileChange = function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        const file = files[0];
        // 检查是否是同一个文件，避免重复处理
        if (lastFile && lastFile.name === file.name && lastFile.size === file.size && lastFile.lastModified === file.lastModified) {
            return;
        }
        lastFile = file;

        const url = window.URL || window.webkitURL || window.mozURL;

        let file_src;
        try {
            file_src = url && url.createObjectURL
                ? url.createObjectURL(file)
                : element.value || (e.target.result || '');
        } catch (error) {
            console.error('Error creating object URL:', error);
            file_src = element.value || '';
        }

        if (successFun && typeof successFun === 'function') {
            successFun(file_src, e.target);
        }
    };

    // 绑定事件
    element.addEventListener('change', handleFileChange);

    // 返回一个取消监听的方法
    return function() {
        element.removeEventListener('change', handleFileChange);
        lastFile = null; // 清除引用
    };
}

/**
 * 监听 .alert 组件 开始
 */
function init_alert(){
    // 获取alert容器
    const alertContainer = document.querySelector('.alert-fixed-container');

    if (!alertContainer) return;

    // 监听alert容器内的点击事件（事件委托）
    alertContainer.addEventListener('click', function(e) {
        // 检查点击的是否是.alert-close元素
        if (e.target.matches('.alert-close') || e.target.closest('.alert-close')) {
            const closeBtn = e.target.matches('.alert-close') ? e.target : e.target.closest('.alert-close');
            const alertElement = closeBtn.closest('.alert');

            if (alertElement) {
                // 移除对应的alert元素
                alertElement.remove();
            }
        }
    });

    // 自动销毁alert的函数
    function setupAutoDismiss(alertElement) {
        // 设置8.5秒后自动移除
        const timer = setTimeout(() => {
            alertElement.remove();
        }, 8500);

        // 当鼠标悬停时暂停自动移除
        alertElement.addEventListener('mouseenter', () => {
            clearTimeout(timer);
        });

        // 当鼠标离开时重新设置自动移除
        alertElement.addEventListener('mouseleave', () => {
            setTimeout(() => {
                alertElement.remove();
            }, 8500);
        });
    }

    // 初始化已有的alert元素
    const existingAlerts = alertContainer.querySelectorAll('.alert');
    existingAlerts.forEach(alert => {
        setupAutoDismiss(alert);
    });

    // 使用MutationObserver监听新添加的alert元素
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                // 检查添加的节点是否是.alert元素
                if (node.nodeType === 1 && node.matches('.alert')) {
                    setupAutoDismiss(node);
                }

                // 检查添加的节点内部是否包含.alert元素
                if (node.nodeType === 1 && node.querySelector('.alert')) {
                    node.querySelectorAll('.alert').forEach(alert => {
                        setupAutoDismiss(alert);
                    });
                }
            });
        });
    });

    // 开始观察alert容器
    observer.observe(alertContainer, {
        childList: true,
        subtree: true
    });
}
document.addEventListener('DOMContentLoaded', function() {
    init_alert();
});

/**
 * 监听 .alert 组件 结束
 */

/**
 * 监听 tips 组件 结束
 */
/**
 * 设置 title 的 tips 气泡
 * init_tips()
 * 使用 : 在需要的地方设置 data-tips 属性 支持 top|上, bottom|下, left|左, right|右,track|跟随 默认为跟随
 * demo: data-tips | data-tips="top" |  data-tips="bottom" | data-tips="left" | data-tips="right" | data-tips="track"
 */
function init_tips() {

    /**
     * 极致优化版 Tips 气泡提示
     * 功能特点：
     * 1. 极简API：仅使用 data-tips 控制位置，title 控制内容
     * 2. 智能内容识别：自动识别普通文本、HTML内容和DOM元素
     * 3. 五种定位方式：data-tips 支持 top, bottom, left, right, track(跟随鼠标)
     * 3. 气泡内容：title 支持普通文本/HTML/DOM(#id,.class)引用三种内容类型
     * 4. 自动边界检测：防止提示框超出视口
     * 5. 平滑动画效果：CSS3过渡动画
     * 6. 性能优化：事件委托+防抖处理
     * 7. 动态元素支持：自动处理新增DOM元素
     *
     * 使用示例：
     * <!-- 普通文本提示 -->
     * <button title="这是提示内容" data-tips="top">上侧提示</button>
     *
     * <!-- HTML内容提示 -->
     * <button title="<strong>加粗提示</strong>" data-tips="bottom">下侧提示</button>
     *
     * <!-- DOM元素内容查找获取并提示 -->
     * <button title="#contentId" data-tips="left">左侧提示</button>
     *
     * <!-- 跟随鼠标提示 -->
     * <button title="跟随提示" data-tips="track">跟随提示</button>
     */
    var Tips = {
        // 配置参数
        config: {
            className: 'tips-container',      // 气泡类名
            arrowClassName: 'tips-arrow',     // 箭头类名
            allowTypes: ['top', 'bottom', 'left', 'right', 'track'], // 允许的定位类型
            defaultType: 'track',            // 默认定位类型
            showDelay: 60,                   // 显示延迟(ms)
            hideDelay: 150,                  // 隐藏延迟(ms)
            offset: 12,                      // 静态定位偏移量
            trackOffset: { x: 0, y: 15 },    // 跟随定位偏移量
            maxWidth: 300,                   // 最大宽度
            arrowSize: 8,                    // 箭头大小
            checkViewport: true              // 是否检查视口边界
        },

        // 状态变量
        tipElement: null,        // 气泡元素
        tipArrow: null,         // 箭头元素
        currentTarget: null,     // 当前触发元素
        showTimeout: null,      // 显示定时器
        hideTimeout: null,      // 隐藏定时器
        lastPosition: null,     // 最后鼠标位置
        scrollListener: null,   // 滚动监听器
        resizeListener: null,   // 窗口大小变化监听器

        /**
         * 初始化方法
         * options：支持修改的配置参数
         */
        init: function(options) {
            // 合并配置
            if (options) {
                for (var key in options) {
                    if (this.config.hasOwnProperty(key)) {
                        this.config[key] = options[key];
                    }
                }
            }

            // 初始化监听器
            this.setupListeners();
            // 绑定事件
            this.setupEventListeners();
        },

        /**
         * 设置全局监听器
         */
        setupListeners: function() {
            // 移除旧的监听器
            if (this.scrollListener) {
                window.removeEventListener('scroll', this.scrollListener);
            }
            if (this.resizeListener) {
                window.removeEventListener('resize', this.resizeListener);
            }

            // 创建新的监听器
            this.scrollListener = this.handleScroll.bind(this);
            this.resizeListener = this.handleResize.bind(this);

            window.addEventListener('scroll', this.scrollListener, { passive: true });
            window.addEventListener('resize', this.resizeListener, { passive: true });
        },

        /**
         * 滚动事件处理
         */
        handleScroll: function() {
            // 如果当前有显示的气泡，则更新位置
            if (this.tipElement && this.tipElement.style.display === 'block') {
                var tipType = this.getCurrentTipType();
                var target = tipType === 'track' ? this.lastPosition : this.currentTarget;
                this.positionTip(target, tipType);
            }
        },

        /**
         * 窗口大小变化处理
         */
        handleResize: function() {
            this.handleScroll();
        },

        /**
         * 获取当前气泡类型
         */
        getCurrentTipType: function() {
            if (!this.tipElement) return this.config.defaultType;

            for (var i = 0; i < this.config.allowTypes.length; i++) {
                if (this.tipElement.classList.contains(this.config.allowTypes[i])) {
                    return this.config.allowTypes[i];
                }
            }

            return this.config.defaultType;
        },

        /**
         * 设置事件监听
         */
        setupEventListeners: function() {
            // 移除旧监听
            this.removeEventListeners();

            // 获取所有提示元素
            var elements = document.querySelectorAll('[data-tips]');

            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];

                // 确保只初始化一次
                if (!el.dataset.tipsInitialized) {
                    // 保存原始title并清空
                    el.dataset.originalTitle = el.title;
                    el.title = '';
                    el.dataset.tipsInitialized = 'true';

                    // 绑定事件
                    el.addEventListener('mouseenter', this.handleMouseEnter.bind(this));
                    el.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
                    el.addEventListener('mousemove', this.handleMouseMove.bind(this));
                }
            }
        },

        /**
         * 移除事件监听
         */
        removeEventListeners: function() {
            var elements = document.querySelectorAll('[data-tips][data-tips-initialized="true"]');
            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];
                el.removeEventListener('mouseenter', this.handleMouseEnter);
                el.removeEventListener('mouseleave', this.handleMouseLeave);
                el.removeEventListener('mousemove', this.handleMouseMove);
            }
        },

        /**
         * 鼠标移入处理
         */
        handleMouseEnter: function(e) {
            this.currentTarget = e.currentTarget;
            clearTimeout(this.hideTimeout);

            // 立即记录鼠标位置
            this.updateLastPosition(e);

            // 延迟显示(防抖)
            this.showTimeout = setTimeout(function() {
                this.showTip(this.currentTarget);
            }.bind(this), this.config.showDelay);
        },

        /**
         * 更新最后鼠标位置
         */
        updateLastPosition: function(e) {
            this.lastPosition = {
                x: e.clientX,
                y: e.clientY,
                scrollX: window.scrollX || document.documentElement.scrollLeft,
                scrollY: window.scrollY || document.documentElement.scrollTop,
                target: e.currentTarget
            };
        },

        /**
         * 鼠标移出处理
         */
        handleMouseLeave: function() {
            clearTimeout(this.showTimeout);
            this.hideTimeout = setTimeout(function() {
                this.hideTip();
            }.bind(this), this.config.hideDelay);
        },

        /**
         * 鼠标移动处理
         */
        handleMouseMove: function(e) {
            // 更新鼠标位置
            this.updateLastPosition(e);

            // 如果是track模式且气泡可见，则更新位置
            if (this.tipElement && this.tipElement.classList.contains('track')) {
                if (this.tipElement.style.display === 'block') {
                    this.positionTip(this.lastPosition, 'track');
                }
            }
        },

        /**
         * 创建气泡元素
         */
        createTipElement: function() {
            // 如果不存在或已被移除则创建
            if (!this.tipElement || !document.body.contains(this.tipElement)) {
                this.tipElement = document.createElement('div');
                this.tipElement.className = this.config.className;

                // 创建箭头元素
                this.tipArrow = document.createElement('div');
                this.tipArrow.className = this.config.arrowClassName;
                this.tipElement.appendChild(this.tipArrow);

                // 设置基础样式
                Object.assign(this.tipElement.style, {
                    position: 'absolute',
                    maxWidth: this.config.maxWidth + 'px',
                    display: 'none',
                    zIndex: '9999',
                    pointerEvents: 'none'
                });

                document.body.appendChild(this.tipElement);
            }
            return this.tipElement;
        },

        /**
         * 显示气泡
         */
        showTip: function(target) {
            var content = target.dataset.originalTitle || '';
            if (!content) return;

            var tipElement = this.createTipElement();
            this.setContent(tipElement, content);

            var tipType = this.getTipType(target);

            // 显示前强制重排确保尺寸正确
            this.forceReflow(tipElement);

            // 定位气泡
            this.positionTip(tipType === 'track' ? this.lastPosition : target, tipType);

            // 显示气泡
            tipElement.classList.add(tipType, 'visible');
            tipElement.style.display = 'block';
        },

        /**
         * 强制重排以获取准确尺寸
         */
        forceReflow: function(element) {
            return element.offsetHeight;
        },

        /**
         * 隐藏气泡
         */
        hideTip: function() {
            if (this.tipElement) {
                this.tipElement.classList.remove('visible');
                // 动画结束后隐藏
                setTimeout(function() {
                    if (this.tipElement && !this.tipElement.classList.contains('visible')) {
                        this.tipElement.style.display = 'none';
                    }
                }.bind(this), this.config.hideDelay);
            }
        },

        /**
         * 设置气泡内容
         */
        setContent: function(element, content) {
            // 保留箭头，移除其他内容
            while (element.childNodes.length > 1) {
                element.removeChild(element.lastChild);
            }

            // 创建内容容器
            var contentWrapper = document.createElement('div');
            contentWrapper.style.margin = '0';
            contentWrapper.style.padding = '0';

            // DOM选择器
            if (/^[.#]/.test(content)) {
                var targetEl = document.querySelector(content);
                if (targetEl) {
                    // 克隆并显示内容
                    var clone = targetEl.cloneNode(true);
                    clone.style.display = 'block';
                    clone.style.visibility = 'visible';
                    contentWrapper.appendChild(clone);
                }
            }
            // HTML内容
            else if (/^</.test(content)) {
                contentWrapper.innerHTML = content;
            }
            // 普通文本
            else {
                // 处理换行符
                var textContent = content.replace(/\n/g, ' ').replace(/\\n/g, '\n');
                var lines = textContent.split('\n');

                for (var i = 0; i < lines.length; i++) {
                    if (i > 0) contentWrapper.appendChild(document.createElement('br'));
                    contentWrapper.appendChild(document.createTextNode(lines[i]));
                }
            }

            element.appendChild(contentWrapper);
        },

        /**
         * 获取定位类型
         */
        getTipType: function(element) {
            var type = (element.dataset.tips || '').toLowerCase();
            return this.config.allowTypes.includes(type) ? type : this.config.defaultType;
        },

        /**
         * 定位气泡(核心方法)
         */
        positionTip: function(target, type) {
            if (!this.tipElement) return;

            // 重置位置类
            this.config.allowTypes.forEach(function(t) {
                this.tipElement.classList.remove(t);
            }, this);

            this.tipElement.classList.add(type);

            // 跟随鼠标定位
            if (type === 'track') {
                this.positionTrackTip(target);
            }
            // 静态元素定位
            else {
                this.positionStaticTip(target, type);
            }
        },

        /**
         * 静态元素定位
         */
        positionStaticTip: function(element, type) {
            // 获取精确尺寸
            var tipRect = this.tipElement.getBoundingClientRect();
            var targetRect = element.getBoundingClientRect();
            var scrollX = window.scrollX || document.documentElement.scrollLeft;
            var scrollY = window.scrollY || document.documentElement.scrollTop;

            var top = 0, left = 0;

            // 计算基础位置
            switch (type) {
                case 'top':
                    top = targetRect.top + scrollY - tipRect.height - this.config.offset;
                    left = targetRect.left + scrollX + targetRect.width / 2 - tipRect.width / 2;
                    break;
                case 'bottom':
                    top = targetRect.top + scrollY + targetRect.height + this.config.offset;
                    left = targetRect.left + scrollX + targetRect.width / 2 - tipRect.width / 2;
                    break;
                case 'left':
                    top = targetRect.top + scrollY + targetRect.height / 2 - tipRect.height / 2;
                    left = targetRect.left + scrollX - tipRect.width - this.config.offset;
                    break;
                case 'right':
                    top = targetRect.top + scrollY + targetRect.height / 2 - tipRect.height / 2;
                    left = targetRect.left + scrollX + targetRect.width + this.config.offset;
                    break;
            }

            // 视口边界检查
            if (this.config.checkViewport) {
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;

                // 水平边界
                left = Math.max(0, Math.min(left, viewportWidth + scrollX - tipRect.width));
                // 垂直边界
                top = Math.max(0, Math.min(top, viewportHeight + scrollY - tipRect.height));
            }

            // 应用最终位置
            this.tipElement.style.left = Math.round(left) + 'px';
            this.tipElement.style.top = Math.round(top) + 'px';
        },

        /**
         * 跟随鼠标定位
         */
        positionTrackTip: function(position) {
            // 确保气泡已渲染并获取准确尺寸
            this.tipElement.style.display = 'block';
            var tipRect = this.tipElement.getBoundingClientRect();

            // 计算位置(考虑滚动和偏移)
            var left = position.x + this.config.trackOffset.x;
            var top = position.y + this.config.trackOffset.y;

            // 视口边界检查
            if (this.config.checkViewport) {
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;

                // 水平边界
                left = Math.max(0, Math.min(
                    left,
                    viewportWidth - tipRect.width
                ));

                // 垂直边界
                top = Math.max(0, Math.min(
                    top,
                    viewportHeight - tipRect.height
                ));
            }

            // 应用位置(添加滚动偏移)
            this.tipElement.style.left = Math.round(left + (position.scrollX || 0)) + 'px';
            this.tipElement.style.top = Math.round(top + (position.scrollY || 0)) + 'px';
        },

        /**
         * 更新提示(用于动态内容)
         */
        update: function() {
            this.setupEventListeners();
        },

        /**
         * 销毁实例
         */
        destroy: function() {
            // 移除事件监听
            this.removeEventListeners();
            if (this.scrollListener) {
                window.removeEventListener('scroll', this.scrollListener);
            }
            if (this.resizeListener) {
                window.removeEventListener('resize', this.resizeListener);
            }

            // 移除气泡
            if (this.tipElement && document.body.contains(this.tipElement)) {
                document.body.removeChild(this.tipElement);
            }

            // 恢复原始title
            var elements = document.querySelectorAll('[data-tips]');
            for (var i = 0; i < elements.length; i++) {
                var el = elements[i];
                if (el.dataset.originalTitle) {
                    el.title = el.dataset.originalTitle;
                    delete el.dataset.originalTitle;
                    delete el.dataset.tipsInitialized;
                }
            }

            // 重置状态
            this.tipElement = null;
            this.tipArrow = null;
            this.currentTarget = null;
            this.lastPosition = null;
        }
    };
    Tips.init();
}

/**
 * 监听 tips 组件 结束
 */
// // 初始化整个应用页面
// initDocsAll();
// // 只初始化文档内容部分
// initDocContent();
// // 只初始化文档页面头部（适用于页面没有菜单）
// initDocHead();


function page_loading(message='加载中...') {
    new Modal({ content: `${message}`, theme: getTheme()==='dark'? 'dark' : 'default', // default or dark
    }).open();
}
// 显示自动关闭的提示
function show_tips(message='提示',time=3500) {
    new Modal({ content: `${message}`,width:100,autoClose: time, theme: getTheme()==='dark'? 'dark' : 'default', // default or dark
    }).open();
}

// 自定义接管点击菜单时加载文章页面
function custom_load_page(id,title,dataset){
    // console.log(`加载内容: ${id} - ${title}`,dataset);

    let docUrl, pageUrl;
    let appId = dataset.app_id || dataset.doc_app_id || app_id;
    if(dataset.menu_type === 'app_manage_doc'){
        // 此处 的id 表示 help | users 等 静态页面标识
        // 要访问的页面是文档管理页面；例如 /docs/1(appId)/users
        docUrl = '/docs/'+appId+'/'+id;
        pageUrl = docUrl; // 页面url
    }else{
        // 查看某篇文章
        docUrl = '/docs/doc/doc_'+id; // 仅加载文章的url
        pageUrl = '/docs/doc/'+app_id+'_'+id; // 页面url
    }

    // 判断 dataset 里面是否存在名为 in_app_inner 的键
    if (dataset.hasOwnProperty('in_app_inner')) {
        // 在某个文档之外进行搜索时，直接使用跳转的方式处理
        if( isEmpty(dataset.in_app_inner)){
            docUrl = '/docs/doc/'+appId+'_'+id;
            pageUrl = docUrl;
            // 2、刷新页面
            window.location.href = pageUrl;
            return false;
        }
    }

    page_loading(`正在加载[${title}]...`);
    return pjax_request(docUrl,{},function (data) {
        // 替换当前历史记录中的 URL（不刷新页面）
        window.history.pushState(null, '', pageUrl);

        setArticleHTML(data.content_html || '');

        Modal.closeAll();
    },function (err){
        Modal.closeAll();
        const errorMsg = `${err.status}: 加载失败`;
        Modal.error(errorMsg, { position: 'top-center', timeout: 3000 });
    },"GET");
}

function setArticleHTML(html) {
    // 1. 清除当前可能存在的临时 init_page
    if (window._temp_init_page) {
        delete window._temp_init_page;
    }

    // 2. 替换 HTML
    document.getElementById('articleContent').innerHTML = html;

    // 3. 提取并执行当前 HTML 的 init_page（但不污染全局）
    const scripts = document.getElementById('articleContent').querySelectorAll('script');
    scripts.forEach(script => {
        if (script.textContent.includes(' init_page(')) {
            try {
                // 使用 `new Function` 避免直接执行 script 里的其他代码
                const fn = new Function(`
                    ${script.textContent}
                    return typeof init_page === 'function' ? init_page : null;
                `);
                const currentInitPage = fn();

                if (currentInitPage) {
                    // 存储到临时变量，避免全局污染
                    window._temp_init_page = currentInitPage;
                    console.log("Executing current init_page");
                    currentInitPage();
                }
            } catch (e) {
                console.error("Error parsing init_page:", e);
            }
        }
    });
    initDocContent();
}
/**
 * 自定义接管搜索关键词
 * @param keyword
 * @returns [{ title: "标题", category: "最外层的菜单组；guide", id: "id", content: "内容", icon: "" },{...}
 */
function custom_search_page(keyword){
    let search_url ='/docs/search/'+(app_id || '');
    page_loading('搜索中...');
    return pjax_request(search_url,{
        keyword:keyword,
        _token:getCsrfToken()
    },function (data) {
        Modal.closeAll();
        return data.list;
    },function (err){
        console.log(err);
        Modal.closeAll();
        const errorMsg = `${err.status}: 搜索失败`;
        Modal.error(errorMsg, { position: 'top-center', timeout: 3000 });
    },'POST');
}

// 表单拦截器 (精简强化版)
document.addEventListener('submit', async function(e) {
    const form = e.target.closest('form');
    if (!form) return;

    // 1. 阻止默认行为（先拦截）
    e.preventDefault();
    e.stopImmediatePropagation();

    // 2. 获取当前点击的提交按钮（关键改进！）
    const submitter = e.submitter || form.querySelector('[type="submit"]');
    let submitterText = submitter ? submitter.innerHTML : '';
    if (submitter) {
        submitter.disabled = true;
        submitter.innerHTML = submitterText+'...';
        submitter.style.opacity = '0.7';
        submitter.style.cursor = 'not-allowed';
    }

    let resetSubmit = function(){
        if (submitter) {
            submitter.disabled = false;
            submitter.innerHTML = submitterText;
            submitter.style.opacity = '1';
            submitter.style.cursor = '';
        }
    };

    // 3. 执行业务拦截逻辑
    try {
        // 3.1 执行同步/异步拦截
        if(typeof form_intercept === 'function'){
            if(form_intercept(e) === false){
                resetSubmit();
                return false;
            }
        }
        // 3.2 执行提交前逻辑
        if(typeof form_before === 'function') {
            if (form_before() === false) {
                resetSubmit();
                return false;
            }
        }

        // 4. 所有检查通过，重新触发提交
        form.removeEventListener('submit', arguments.callee, true); // 先移除监听器
        form.submit(); // 同步提交（不会递归）
    } catch (error) {
        // 5. 拦截失败时恢复按钮状态
        resetSubmit();
    }
}, true); // 捕获阶段优先


/**
 * 图片加载失败处理（防止重复替换）
 * @param {string} defaultImage - 默认图片URL
 */
function handleImageFallback(defaultImage) {
    if (!defaultImage) {
        console.error('必须提供默认图片URL');
        return;
    }

    // 处理单个图片
    const processImage = (img) => {
        // 如果已经处理过或者已经是默认图片，则跳过
        if (img.dataset.fallbackProcessed || img.src === defaultImage) {
            return;
        }

        // 标记为已处理
        img.dataset.fallbackProcessed = 'true';

        // 保存原始src
        img.dataset.originalSrc = img.src;

        // 添加错误事件监听（只触发一次）
        const errorHandler = () => {
            // 移除事件监听，防止重复触发
            img.removeEventListener('error', errorHandler);

            // 替换为默认图片
            img.src = defaultImage;
        };

        img.addEventListener('error', errorHandler);

        // 立即检查可能已经失败的图片
        if (img.complete && img.naturalHeight === 0) {
            errorHandler();
        }
    };

    // 初始化处理所有现有图片
    const initExistingImages = () => {
        document.querySelectorAll('img').forEach(processImage);
    };

    // 监听动态添加的图片
    const setupObserver = () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeName === 'IMG') {
                        processImage(node);
                    } else if (node.querySelectorAll) {
                        node.querySelectorAll('img').forEach(processImage);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        return observer;
    };

    // 主初始化
    initExistingImages();
    return setupObserver();
}
// 监听图片加载失败时使用默认图片
handleImageFallback('/static/images/load_error.jpg');
