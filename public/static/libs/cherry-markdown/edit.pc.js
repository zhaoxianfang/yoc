/**
 * 定义带图表表格的按钮
 */
var customMenuTable = Cherry.createMenuHook('图表',  {
    iconName: 'trendingUp',
    subMenuConfig: [
        { noIcon: true, name: '折线图', onclick: (event)=>{cherry.insert('\n| :line:{x,y} | Header1 | Header2 | Header3 | Header4 |\n| ------ | ------ | ------ | ------ | ------ |\n| Sample1 | 11 | 11 | 4 | 33 |\n| Sample2 | 112 | 111 | 22 | 222 |\n| Sample3 | 333 | 142 | 311 | 11 |\n');} },
        { noIcon: true, name: '柱状图', onclick: (event)=>{cherry.insert('\n| :bar:{x,y} | Header1 | Header2 | Header3 | Header4 |\n| ------ | ------ | ------ | ------ | ------ |\n| Sample1 | 11 | 11 | 4 | 33 |\n| Sample2 | 112 | 111 | 22 | 222 |\n| Sample3 | 333 | 142 | 311 | 11 |\n');} },
    ]
});

const cherryPcConfig = {
    id: 'markdown',
    // 第三方包
    externals: {
        echarts: window.echarts,
        katex: window.katex,
        MathJax: window.MathJax,
    },
    // chatGpt的openai配置
    openai: {
        apiKey: '', // apiKey
        // proxy: {
        //   host: '127.0.0.1',
        //   port: '7890',
        // }, // http & https代理配置
        ignoreError: false, // 是否忽略请求失败，默认忽略
    },
    // 解析引擎配置
    engine: {
        // 全局配置
        global: {
            urlProcessor(url, srcType) {
                // console.log(`url-processor`, url, srcType);
                return url;
            },
            // 是否启用经典换行逻辑
            // true：一个换行会被忽略，两个以上连续换行会分割成段落，
            // false： 一个换行会转成<br>，两个连续换行会分割成段落，三个以上连续换行会转成<br>并分割段落
            classicBr: false,
            /**
             * 额外允许渲染的html标签
             * 标签以英文竖线分隔，如：htmlWhiteList: 'iframe|script|style'
             * 默认为空，默认允许渲染的html见src/utils/sanitize.js whiteList 属性
             * 需要注意：
             *    - 启用iframe、script等标签后，会产生xss注入，请根据实际场景判断是否需要启用
             *    - 一般编辑权限可控的场景（如api文档系统）可以允许iframe、script等标签
             */
            // htmlWhiteList: '',
            htmlWhiteList: 'iframe|script|style', // xss 注入 不用就设为空
            /**
             * 适配流式会话的场景，开启后将具备以下特性：
             * - cherry渲染频率从50ms/次提升到10ms/次
             * - 代码块自动闭合，相当于强制 `engine.syntax.codeBlock.selfClosing=true`
             * - 文章末尾的段横线标题语法（`\n-`）失效
             * - 表格语法自动闭合，相当于强制`engine.syntax.table.selfClosing=true`
             * - 加粗、斜体语法自动闭合，相当于强制`engine.syntax.fontEmphasis.selfClosing=true`
             *
             * 后续如果有新的需求，可提issue反馈
             */
            flowSessionContext: true,
        },
        // 内置语法配置
        syntax: {
            // 语法开关
            // 'hookName': false,
            // 语法配置
            // 'hookName': {
            //
            // }
            link: {
                /** 生成的<a>标签追加target属性的默认值 空：在<a>标签里不会追加target属性， _blank：在<a>标签里追加target="_blank"属性 */
                target: '',
                /** 生成的<a>标签追加rel属性的默认值 空：在<a>标签里不会追加rel属性， nofollow：在<a>标签里追加rel="nofollow：在"属性*/
                rel: '',
            },
            autoLink: {
                /** 生成的<a>标签追加target属性的默认值 空：在<a>标签里不会追加target属性， _blank：在<a>标签里追加target="_blank"属性 */
                target: '',
                /** 生成的<a>标签追加rel属性的默认值 空：在<a>标签里不会追加rel属性， nofollow：在<a>标签里追加rel="nofollow：在"属性*/
                rel: '',
                /** 是否开启短链接 */
                enableShortLink: true,
                /** 短链接长度 */
                shortLinkLength: 20,
            },
            list: {
                listNested: false, // 同级列表类型转换后变为子级
                indentSpace: 2, // 默认2个空格缩进
            },
            table: {
                enableChart: true,
                selfClosing: false, // 自动闭合，为true时，当输入第一行table内容时，cherry会自动按表格进行解析
                // chartRenderEngine: EChartsTableEngine,
                // externals: ['echarts'],
            },
            codeBlock: {
                theme: 'twilight', // 代码主题
                wrap: true, // 超出长度是否换行，false则显示滚动条
                lineNumber: true, // 默认显示行号
                copyCode: true, // 是否显示“复制”按钮
                editCode: true, // 是否显示“编辑”按钮
                changeLang: true, // 是否显示“切换语言”按钮
                expandCode: true, // 是否展开/收起代码块，当代码块行数大于10行时，会自动收起代码块
                selfClosing: true, // 自动闭合，为true时，当md中有奇数个```时，会自动在md末尾追加一个```
                customRenderer: {
                    // 自定义语法渲染器
                },
                mermaid: {
                    svg2img: false, // 是否将mermaid生成的画图变成img格式
                },
                /**
                 * indentedCodeBlock是缩进代码块是否启用的开关
                 *
                 *    在0.6.X之前的版本中默认不支持该语法。
                 *    因为cherry的开发团队认为该语法太丑了（容易误触）
                 *    开发团队希望用```代码块语法来彻底取代该语法
                 *    但在后续的沟通中，开发团队发现在某些场景下该语法有更好的显示效果
                 *    因此开发团队在0.6.X版本中才引入了该语法
                 *    已经引用0.6.x以下版本的业务如果想做到用户无感知升级，可以去掉该语法：
                 *        indentedCodeBlock：false
                 */
                indentedCodeBlock: true,
            },
            emoji: {
                useUnicode: true, // 是否使用unicode进行渲染
            },
            fontEmphasis: {
                /**
                 * 是否允许首尾空格
                 * 首尾、前后的定义： 语法前**语法首+内容+语法尾**语法后
                 * 例：
                 *    true:
                 *           __ hello __  ====>   <strong> hello </strong>
                 *           __hello__    ====>   <strong>hello</strong>
                 *    false:
                 *           __ hello __  ====>   <em>_ hello _</em>
                 *           __hello__    ====>   <strong>hello</strong>
                 */
                allowWhitespace: false,
                selfClosing: false, // 自动闭合，为true时，当输入**XXX时，会自动在末尾追加**
            },
            strikethrough: {
                /**
                 * 是否必须有前后空格
                 * 首尾、前后的定义： 语法前**语法首+内容+语法尾**语法后
                 * 例：
                 *    true:
                 *            hello wor~~l~~d     ====>   hello wor~~l~~d
                 *            hello wor ~~l~~ d   ====>   hello wor <del>l</del> d
                 *    false:
                 *            hello wor~~l~~d     ====>   hello wor<del>l</del>d
                 *            hello wor ~~l~~ d     ====>   hello wor <del>l</del> d
                 */
                needWhitespace: false,
            },
            mathBlock: {
                engine: 'MathJax', // katex或MathJax
                // 或者直接在html中引入 <script src="{{ asset('static/libs/cherry-markdown/tex-svg.js') }}"></script> 文件
                // src: '/static/libs/cherry-markdown/tex-svg.js', // 本地文件路径
                // src: 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js', // 如果使用MathJax plugins，则需要使用该url通过script标签引入
                plugins: false, // 默认加载插件
            },
            inlineMath: {
                engine: 'MathJax', // katex或MathJax
                // src: '',
            },
            toc: {
                /** 默认只渲染一个目录 */
                allowMultiToc: false,
                /** 是否显示自增序号 */
                showAutoNumber: false,
            },
            image: {
                videoWrapper: (link, type, defaultWrapper) => {
                    // console.log(type);
                    return defaultWrapper;
                },
            },
            header: {
                /**
                 * 标题的样式：
                 *  - default       默认样式，标题前面有锚点
                 *  - autonumber    标题前面有自增序号锚点
                 *  - none          标题没有锚点
                 */
                anchorStyle: 'default',
            },
        },
    },
    editor: {
        // id: 'markdown_content-x', // textarea 的id属性值
        // name: $('#markdown_content').data('name') || 'content', // 'content', // textarea 的name属性值
        name: document.getElementById('markdown_content').getAttribute('data-name') || 'content',
        autoSave2Textarea: true, // 是否自动将编辑区的内容回写到textarea里 | 如果`autoSave2Textarea`为`false` 则form表单提交的时候不能自动获取cherry的内容,需通过cherry的api来获取。
        // 编辑器的高度，默认100%，如果挂载点存在内联设置的height则以内联样式为主
        // height: '100%',
        height: ' calc( 100vh - 190px ) ', // 编辑器的高度，默认100%，如果挂载点存在内联设置的height则以内联样式为主
        // defaultModel 编辑器初始化后的默认模式，一共有三种模式：1、双栏编辑预览模式；2、纯编辑模式；3、预览模式
        // edit&preview: 双栏编辑预览模式
        // editOnly: 纯编辑模式（没有预览，可通过toolbar切换成双栏或预览模式）
        // previewOnly: 预览模式（没有编辑框，toolbar只显示“返回编辑”按钮，可通过toolbar切换成编辑模式）
        defaultModel: 'edit&preview',
        // 粘贴时是否自动将html转成markdown
        convertWhenPaste: true,
        // 快捷键风格，目前仅支持 sublime 和 vim
        keyMap: 'sublime',
        codemirror: {
            // 是否自动focus 默认为true
            autofocus: false,
            placeholder: '开始放飞你的灵感~', // 占位符
        },
        writingStyle: 'normal', // 书写风格，normal 普通 | typewriter 打字机 | focus 专注，默认normal
        keepDocumentScrollAfterInit: false, // 在初始化后是否保持网页的滚动，true：保持滚动；false：网页自动滚动到cherry初始化的位置
        showFullWidthMark: true, // 是否高亮全角符号 ·|￥|、|：|“|”|【|】|（|）|《|》
        showSuggestList: true, // 是否显示联想框
    },
    toolbars: {
        showToolbar: true, // false：不展示顶部工具栏； true：展示工具栏; toolbars.showToolbar=false 与 toolbars.toolbar=false 等效
        // https://github.com/Tencent/cherry-markdown/wiki/调整工具栏
        toolbar: [
            // 'switchModel', // 切换编辑/预览模式(用于单栏编辑模式，即点一下是编辑模式，再点一下是预览模式，类似github的交互体验)
            // '|',
            'undo', // 回撤操作
            'redo', // 恢复最近回撤的操作
            'bold', // 加粗
            'italic', // 斜体
            // 'underline', // 下划线
            // 'strikethrough',  // 删除线
            // 把字体样式类按钮都放在拼音按钮下面
            {
                'ruby':[
                    'sub', // 下标
                    'sup', // 上标
                    'ruby', // 拼音
                ]
            },
            {
                'ol':[
                    'ol',
                    'ul',
                    'checklist'
                ]
            },
            '|', // 分隔符，单纯的分割工具栏，无任何作用
            'size',// 文字尺寸
            // 'quote',// 引用
            'color', // 文字颜色
            '|',
            'header', //标题菜单，自带二级菜单，二级菜单里可以选 1~5级标题
            'list', // 列表菜单，自带二级菜单，二级菜单里可选 有序列表、无序列表、任务清单
            'justify', // 对齐方式，自带二级菜单，二级菜单里可以选 左对齐、居中、右对齐
            {
                'insert': [
                    'image', // 插入图片
                    'audio', // 插入音频
                    'video', // 插入视频
                    'link', // 插入链接
                    'hr', // 插入水平分割线
                    'br', // 插入新行
                    'code', // 代码块
                    'formula', // 插入数学公式
                    // 'toc', // 插入目录
                    'table', // 插入表格
                    'line-table',
                    'bar-table',
                    // 'pdf', // 插入pdf
                    // 'word', // 插入word文档
                    'file', // 插入普通文件
                    'drawIo', // 插入draw.io画图，点击后会出现draw.io画图面板
                    'detail', // 手风琴，即可以展开收起内容
                ],
            },
            'customMenuTable',
            'togglePreview',
            'search',
            // 'shortcutKey',
            'graph', // 插入画图，自带二级菜单，二级菜单里可选 流程图、时序图、状态图、类图、饼图、甘特图
            '|',
            'panel', // 信息面板，自带二级菜单，二级菜单里可以选 tips、info、warning、danger、success
            // '|',
            // 'theme', // 主题
            // 'codeTheme', // 切换代码块的主题，自带二级菜单
            // 'mobilePreview', // 把预览区域变成h5模式
            // 'copy', // 复制预览区域的html内容到剪贴板
            // 'export', // 导出，自带二级菜单，二级菜单里可选 导出PDF、导出长图、导出markdown、导出html
            // 'fullScreen', // 全屏/取消全屏
            // 'settings', //设置，自带二级菜单，二级菜单里可选 常规换行/经典换行切换、关闭/打开预览、隐藏工具栏 （不推荐用了，完全可以自行实现）
            // 'chatgpt',
        ],
        // 定义顶部右侧工具栏，默认为空
        // toolbarRight: ['fullScreen', '|', 'changeLocale', 'wordCount'],
        toolbarRight: ['fullScreen', '|', 'wordCount'],
        // 侧边栏操作按钮,默认为空
        // sidebar: ['mobilePreview', 'copy', 'theme', 'publish'],
        sidebar: ['mobilePreview', 'copy', 'theme','export'],
        bubble: ['bold', 'italic', 'underline', 'strikethrough', 'sub', 'sup', 'quote', '|', 'size', 'color'], // array or false
        float: ['h1', 'h2', 'h3', '|', 'checklist', 'quote', 'table', 'code'], // array or false
        hiddenToolbar: [], // 不展示在编辑器中的工具栏，只使用工具栏的api和快捷键功能
        // toc: false, // 不展示悬浮目录
        toc: {
          updateLocationHash: false, // 要不要更新URL的hash
          defaultModel: 'pure', // pure: 精简模式/缩略模式，只有一排小点； full: 完整模式，会展示所有标题
          showAutoNumber: false, // 是否显示自增序号
          position: 'absolute', // 悬浮目录的悬浮方式。当滚动条在cherry内部时，用absolute；当滚动条在cherry外部时，用fixed
          cssText: '', // 自定义样式
        },
        customMenu: {
            customMenuTable, // 插入图表
        },
        // // 自定义快捷键
        // shortcutKeySettings: {
        //     /** 是否替换已有的快捷键, true: 替换默认快捷键； false： 会追加到默认快捷键里，相同的shortcutKey会覆盖默认的 */
        //     isReplace: false,
        //     shortcutKeyMap: {
        //         // 'Alt-Digit1': {
        //         //   hookName: 'header',
        //         //   aliasName: '标题',
        //         // },
        //         // 'Control-Shift-KeyB': {
        //         //   hookName: 'bold',
        //         //   aliasName: '加粗',
        //         // },
        //     },
        // },
        // 一些按钮的配置信息
        config: {
            formula: {
                showLatexLive: false, // true: 显示 www.latexlive.com 外链； false：不显示
                templateConfig: false, // false: 使用默认模板
            },
            changeLocale: [
                {
                    locale: 'zh_CN',
                    name: '中文',
                },
                {
                    locale: 'en_US',
                    name: 'English',
                }
            ],
        }
    },
    // 打开draw.io编辑页的url，如果为空则drawio按钮失效
    drawioIframeUrl: '/static/libs/cherry-markdown/examples/drawio_demo.html',
    // drawio iframe的样式
    drawioIframeStyle: 'border: none;',
    keydown: [],
    extensions: [],
    /**
     * 上传文件的时候用来指定文件类型
     */
    fileTypeLimitMap: {
        video: 'video/*',
        audio: 'audio/*',
        image: 'image/*',
        word: '.doc,.docx',
        pdf: '.pdf',
        file: '*',
    },
    /**
     * 上传文件的时候是否开启多选
     */
    multipleFileSelection: {
        video: false,
        audio: false,
        image: true,
        word: false,
        pdf: false,
        file: false,
    },
    callback: {
        /**
         * 全局的URL处理器
         * @param {string} url 来源url
         * @param {'image'|'audio'|'video'|'autolink'|'link'} srcType 来源类型
         * @returns
         */
        // urlProcessor: callbacks.urlProcessor,
        urlProcessor: (url, srcType) => url,
        // 上传文件的回调
        // fileUpload: callbacks.fileUpload,
        // 文件上传
        fileUpload: function(file, callback) {
            if (file.size / 1024 / 1024 > 100) { // 100M
                my && my.msg('上传文件过大...');
                return false;
            }

            my && my.msg('上传中...');
            // callback('images/demo-dog.png');
            var url='';
            // 获取 name 属性为 "csrf-token" 的 meta 标签的内容
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // 向 FormData 中追加文件
            var fd = new FormData();
            if (/video/i.test(file.type)) {
                fd.append('video', file);
                url = '/files/uploads/cherry/video/docs?_token='+csrfToken;
            } else if (/image/i.test(file.type)) {
                fd.append('image', file);
                url = '/files/uploads/cherry/img/docs?_token='+csrfToken;
            } else{
                var allowFileTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                      'application/vnd.ms-word',
                                      'application/vnd.oasis.opendocument.text',
                                      'application/pdf',
                                      'application/zip',
                                      'application/x-zip-compressed',
                                      'application/x-rar-compressed',
                                      'application/x-7z-compressed',
                                      'application/gzip',
                                      'text/plain'];
                if(allowFileTypes.includes(file.type) || /audio/i.test(file.type) || /text/i.test(file.type) || /\.md$/.test((file.name).toLowerCase())){
                    fd.append('file', file);
                    // url = '/files/uploads/cherry/file/docs?_token='+$("meta[name=\"csrf-token\"]").attr("content");
                    url = '/files/uploads/cherry/file/docs?_token='+csrfToken;
                }else{
                    my && my.msg('暂不支持的格式');
                    return false;
                }
            }
            $.ajax({
                method: 'POST',
                url: url,
                data: fd, //将formData作为参数传入
                contentType: false,// 不设置Content-Type头信息（必须），使用 FormData 默认的 Content-Type 值
                processData: false,//不处理发送的数据（必须），而是将 FormData 数据原样发送到服务器
                success: function(res) {
                    if(res.code !== 200 || !res.url){
                        my && my.msg(res.message || '上传失败');
                        return false;
                    }
                    var options = {};
                    if(res.filename){
                        options.name = res.filename;
                    }
                    if(res.poster){
                        options.poster = res.poster;
                    }
                    callback(res.url,options);
                },
                error: function(err) {
                    my && my.msg(err.message || '上传失败')
                },
                complete: function(res) {
                    // console.log('complete',res)
                },
            })

        },
        // 上传多文件的回调
        // fileUploadMulti: callbacks.fileUploadMulti,
        // beforeImageMounted: callbacks.beforeImageMounted,
        beforeImageMounted: (srcProp, src) => ({ srcProp, src }),
        // 预览区域点击事件，previewer.enablePreviewerBubble = true 时生效
        // onClickPreview: callbacks.onClickPreview,
        onClickPreview: (event) => {},
        // 复制代码块代码时的回调
        // onCopyCode: callbacks.onCopyCode,
        onCopyCode: (event, code) => {
            // 阻止默认的粘贴事件
            // return false;
            // 对复制内容进行额外处理
            return code;
        },
        // 展开代码块代码时的回调
        // onExpandCode: callbacks.onExpandCode,
        // 缩起代码块代码时的回调
        // onUnExpandCode: callbacks.onUnExpandCode,
        // 把中文变成拼音的回调，当然也可以把中文变成英文、英文变成中文
        // changeString2Pinyin: callbacks.changeString2Pinyin,
        // 获取中文的拼音
        changeString2Pinyin: (string) => {
            /**
             * 推荐使用这个组件：https://github.com/liu11hao11/pinyin_js
             *
             * 可以在 ../scripts/pinyin/pinyin_dist.js 里直接引用
             */
            return string;
        },
        /**
         * 粘贴时触发
         * @param {ClipboardEvent['clipboardData']} clipboardData
         * @returns
         *    false: 走cherry粘贴的默认逻辑
         *    string: 直接粘贴的内容
         */
        // onPaste: callbacks.onPaste,
        // afterChange: callbacks.afterChange,
        // afterInit: callbacks.afterInit,
        afterInit: ({ text, html }) => {
            console.log('afterInit 0',text, html);
            remove_loading();
        },
        // beforeImageMounted: callbacks.beforeImageMounted,
        // // 预览区域点击事件，previewer.enablePreviewerBubble = true 时生效
        // onClickPreview: callbacks.onClickPreview,
        // // 复制代码块代码时的回调
        // onCopyCode: callbacks.onCopyCode,
        // // 把中文变成拼音的回调，当然也可以把中文变成英文、英文变成中文
        // changeString2Pinyin: callbacks.changeString2Pinyin,
    },
    event: {
        // 当编辑区内容有实际变化时触发
        // afterChange: callbacks.afterChange,
        afterChange: (text, html) => {
            console.log('afterChange');
        },
        // afterInit: callbacks.afterInit,
        afterInit: ({ text, html }) => {
            console.log('afterInit 1',text, html);
            remove_loading();
        },
        focus: ({ e, cherry }) => {},
        blur: ({ e, cherry }) => {},
        selectionChange: ({ selections, lastSelections, info }) => {},
        afterChangeLocale: (locale) => {},
        changeMainTheme: (theme) => {
            remove_loading();
        },
        changeCodeBlockTheme: (theme) => {},
    },
    previewer: {
        dom: false,
        // 自定义markdown预览区域class
        className: 'cherry-markdown',
        // 是否启用预览区域编辑能力（目前支持编辑图片尺寸、编辑表格内容）
        enablePreviewerBubble: true,
        floatWhenClosePreviewer: false,
        /**
         * 配置图片懒加载的逻辑
         * - 如果不希望图片懒加载，可配置成 lazyLoadImg = {noLoadImgNum: -1}
         * - 如果希望所有图片都无脑懒加载，可配置成 lazyLoadImg = {noLoadImgNum: 0, autoLoadImgNum: -1}
         * - 如果一共有15张图片，希望：
         *    1、前5张图片（1~5）直接加载；
         *    2、后5张图片（6~10）不论在不在视区内，都无脑懒加载；
         *    3、其他图片（11~15）在视区内时，进行懒加载；
         *    则配置应该为：lazyLoadImg = {noLoadImgNum: 5, autoLoadImgNum: 5}
         */
        lazyLoadImg: {
            // 加载图片时如果需要展示loading图，则配置loading图的地址
            loadingImgPath: '',
            // 同一时间最多有几个图片请求，最大同时加载6张图片
            maxNumPerTime: 2,
            // 不进行懒加载处理的图片数量，如果为0，即所有图片都进行懒加载处理， 如果设置为-1，则所有图片都不进行懒加载处理
            noLoadImgNum: 5,
            // 首次自动加载几张图片（不论图片是否滚动到视野内），autoLoadImgNum = -1 表示会自动加载完所有图片
            autoLoadImgNum: 5,
            // 针对加载失败的图片 或 beforeLoadOneImgCallback 返回false 的图片，最多尝试加载几次，为了防止死循环，最多5次。以图片的src为纬度统计重试次数
            maxTryTimesPerSrc: 2,
            // 加载一张图片之前的回调函数，函数return false 会终止加载操作
            beforeLoadOneImgCallback: (img) => {
                return true;
            },
            // 加载一张图片失败之后的回调函数
            failLoadOneImgCallback: (img) => {},
            // 加载一张图片之后的回调函数，如果图片加载失败，则不会回调该函数
            afterLoadOneImgCallback: (img) => {},
            // 加载完所有图片后调用的回调函数
            afterLoadAllImgCallback: () => {},
        },
    },
    /** 定义cherry缓存的作用范围，相同nameSpace的实例共享localStorage缓存 */
    nameSpace: 'cherry',
    // 主题设置
    themeSettings: {
        // 主题列表，用于切换主题
        themeList: [
            { className: 'default', label: '默认' },
            { className: 'dark', label: '暗黑' },
            { className: 'light', label: '明亮' },
            { className: 'green', label: '清新' },
            { className: 'red', label: '热情' },
            { className: 'violet', label: '淡雅' },
            { className: 'blue', label: '清幽' },
        ],
        mainTheme: localStorage.getItem('docs_theme') || document.documentElement.getAttribute('data-theme') || 'light',// 'light',
        codeBlockTheme: 'default',
        inlineCodeTheme: 'red', // red or black
        toolbarTheme: 'dark', // light or dark 优先级低于mainTheme
    },
    // 预览页面不需要绑定事件
    isPreviewOnly: false,
    // 预览区域跟随编辑器光标自动滚动
    autoScrollByCursor: true,
    // 外层容器不存在时，是否强制输出到body上
    forceAppend: true,
    // The locale Cherry is going to use. Locales live in /src/locales/
    locale: 'zh_CN',
    // Supplementary locales
    locales: {},
    // cherry初始化后是否检查 location.hash 尝试滚动到对应位置
    autoScrollByHashAfterInit: true,
};

// 检查是否是移动端浏览器
function is_mobile_browser() {
    // 使用正则表达式检查userAgent是否包含移动设备标识
    const mobileUserAgent = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
    if (mobileUserAgent.test(navigator.userAgent)) {
        return true;
    }

    // 检查视口宽度是否小于等于一个典型的移动设备屏幕宽度
    // 使用matchMedia来避免直接访问innerWidth导致的布局变化
    const mobileViewport = window.matchMedia('(max-width: 767px)');
    if (mobileViewport.matches) {
        return true;
    }

    // 检查是否支持触摸事件
    // 注意：这可能会在某些桌面浏览器中返回true，特别是当使用开发者工具模拟触摸时
    // if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
    //     return true;
    // }

    // 检查是否缺少某些典型的桌面浏览器特性
    // 例如，检查是否没有鼠标滚轮事件或没有window.external对象（通常存在于IE中）
    // if (!('onmousewheel' in document) || !window.external) {
    //     return true; // 极容易误判
    // }

    // 如果以上条件都不满足，则默认为非移动设备
    return false;
}
function remove_loading() {
    setTimeout(() => {
        // 删除元素 .editor_loading
        document.querySelector('.editor_loading')?.remove();
        // 找到 .cherry 并添加 .cherry_mobile_editor 类
        var cherrr_class = document.querySelector('.cherry');
        if(is_mobile_browser()) {
            cherrr_class && cherrr_class.classList.add('cherry_mobile_editor');
            // 设置 .cherry-sidebar 的 display 值为 none
            const elementSidebar = document.querySelector('.cherry-sidebar');
            if (elementSidebar) {
                elementSidebar.style.display = 'none';
            }
            const elementToc = document.querySelector('.cherry-flex-toc');
            if (elementToc) {
                elementToc.style.display = 'none';
            }
            document.querySelector('.toolbar-right')?.remove();
            document.querySelector('.toolbar-left').style.marginRight = 0;
            document.querySelector('.cherry-toolbar').style.padding = 0;
        }else{
            // 移除 .cherry_mobile_editor 类
            cherrr_class && cherrr_class.classList.remove('cherry_mobile_editor');
        }
    }, 1000);
}

if(is_mobile_browser()){
    // 移动端
    cherryPcConfig.toolbars.toolbar.unshift('switchModel');
    cherryPcConfig.editor.defaultModel='editOnly';
    cherryPcConfig.toolbars.toolbarRight=[];
}

const cherryConfig = Object.assign({}, cherryPcConfig, {value: document.getElementById("markdown_content").value});
window.cherry = new Cherry(cherryConfig);

setTimeout(() => { remove_loading(); }, 6000);
