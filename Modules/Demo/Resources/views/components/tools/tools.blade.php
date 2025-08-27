@extends('demo::layouts.demo_layout')
@section('title', "Tools 使用示例")
@section('page_inner_title', "Tools 使用示例 !")

@section('head_css')
{{--    <link rel="stylesheet" href="{{ asset('static/libs/zxf/css/tools.min.css') }}">--}}

@endsection

@section('content')
    <h1>Tools 使用示例</h1>

    <div class="custom-form ">

    <div class="form-container">
        <h1 class="form-title">表单处理</h1>

        <div class="layout-switcher">
            <button class="layout-btn" onclick="setFormLayout('top')">顶部对齐</button>
            <button class="layout-btn" onclick="setFormLayout('left')">左对齐</button>
            <button class="layout-btn" onclick="setFormLayout('inline')">内联对齐</button>
        </div>

        {{-- 对齐方式 ：form-align-left、form-align-top、form-align-inline--}}
        <form id="demoForm" action="/submit" class="form-align-left" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username" class="required">用户名</label>
                <div class="form-control-container">
                    <input type="text" id="username" name="username" class="form-control"
                           data-rule="required|zh_CN|len:3,10" placeholder="请输入中文用户名">
                    <div class="error-message"></div>
                    <div class="form-notice">3-10位中文</div>
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="required">邮箱</label>
                <div class="form-control-container">
                    <input type="email" id="email" name="email" class="form-control"
                           data-rule="required|email" placeholder="请输入邮箱地址">
                    <div class="error-message"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="required">密码</label>
                <div class="form-control-container">
                    <input type="password" id="password" name="password" class="form-control"
                           data-rule="required|strong_password|len:8,20" placeholder="请输入密码">
                    <div class="error-message"></div>
                    <div class="form-notice">8-20位，必须包含大小写字母和数字</div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="required">确认密码</label>
                <div class="form-control-container">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                           data-rule="required|same:password" placeholder="请再次输入密码">
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="age">年龄</label>
                <div class="form-control-container">
                    <input type="number" id="age" name="age" class="form-control"
                           data-rule="nullable|number|between:1,99" placeholder="请输入年龄(1-99)">
                    <div class="error-message" id="ageError"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="required">性别</label>
                <div class="form-control-container">
                    <div class="radio-group">
                        <input type="radio" id="gender_male" name="gender" value="male" data-rule="required">
                        <label for="gender_male">男</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="gender_female" name="gender" value="female">
                        <label for="gender_female">女</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="gender_other" name="gender" value="other">
                        <label for="gender_other">其他</label>
                    </div>
                    <div class="error-message" id="genderError"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="required">爱好</label>
                <div class="form-control-container">
                    <div class="checkbox-group">
                        <input type="checkbox" id="ck_1" name="aihao" value="ppq" data-rule="required">
                        <label for="ck_1">乒乓球</label>
                    </div>
                    <div class="radio-group">
                        <input type="checkbox" id="ck_2" name="aihao" value="mv">
                        <label for="ck_2">看美女</label>
                    </div>
                    <div class="radio-group">
                        <input type="checkbox" id="ck_3" name="aihao" value="xz">
                        <label for="ck_3">写作</label>
                    </div>
                    <div class="error-message"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="phone" class="required">手机号</label>
                <div class="form-control-container">
                    <input type="tel" id="phone" name="phone" class="form-control"
                           data-rule="required|phone" placeholder="请输入手机号码">
                    <div class="error-message" id="phoneError"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="file" class="required">上传头像</label>
                <div class="form-control-container">
                    <div class="file-input-wrapper" title=".nav-header" data-tips="track">
                        <input type="file" id="file" name="file" class="file-input"
                               data-rule="required|file:jpg,png,gif|max_size:2">
                        <div class="file-info" id="fileInfo">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                                <path d="M19 13V19H5V13H3V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V13H19ZM13 12V3H11V12H8L12 16L16 12H13Z" fill="#5F6368"/>
                            </svg>
                            <span>点击或拖拽文件到此处</span>
                        </div>
                        <img class="file-preview" id="filePreview" alt="文件预览">
                    </div>
                    <div class="error-message" id="fileError"></div>
                    <div class="form-notice">支持 JPG, PNG, GIF 格式，最大2MB</div>
                </div>
            </div>

            <div class="form-group">
                <label for="color" class="required">喜欢的颜色</label>
                <div class="form-control-container" data-tips="left" title='只能填写红,绿,蓝'>
                    <select id="color" name="color" class="form-control" data-rule="required|in:红,绿,蓝">
                        <option value="">请选择颜色</option>
                        <option value="红">红</option>
                        <option value="绿">绿</option>
                        <option value="蓝">蓝</option>
                    </select>
                    <div class="error-message" id="colorError"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="birthday" class="required">生日</label>
                <div class="form-control-container" data-tips="bottom" title='格式：YYYY-MM-DD'>
                    <input type="text" id="birthday" name="birthday" class="form-control"
                           data-rule="required|date" placeholder="YYYY-MM-DD">
                    <div class="error-message"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="bio">个人简介</label>
                <div class="form-control-container ">
                    <textarea id="bio" name="bio" class="form-control" rows="4"  data-tips="bottom" title='请输入个人简介(最多200字)'
                              data-rule="nullable|max:200" placeholder="请输入个人简介(最多200字)"></textarea>
                    <div class="error-message" id="bioError"></div>
                    <div class="form-notice" id="bioCounter">0/200</div>
                </div>
            </div>

            <div class="form-group">
                <label for=""></label>
                <div class="checkbox-group">
                    <input type="checkbox" id="agree" name="agree" data-rule="required">
                    <label for="agree" class="required">我已阅读并同意用户协议</label>
                </div>
                <div class="error-message" id="agreeError"></div>
            </div>

            <h4>Select 测试</h4>

            <!-- 单选Select -->
            <div class="form-group">
                <label for="">单选</label>
                <div class="form-control-container">
                    <!-- 单选Select - 仅需添加custom-select类 -->
                    <select class="form-control custom-select" name="select1">
                        <option value="">请选择...</option>
                        <option value="1">选项一</option>
                        <option value="2">选项二</option>
                        <option value="3" disabled>禁用选项</option>
                        <option value="4">选项四</option>
                    </select>
                    <div class="error-message"></div>
                </div>

            </div>

            <!-- 多选Select -->
            <div class="form-group">
                <label for="">多选1</label>
                <!-- 多选Select - 仅需添加custom-select类 -->
                <select class="form-control custom-select" multiple  name="select_multiple">
                    <option value="1">选项一</option>
                    <option value="2">选项二</option>
                    <option value="3" disabled>禁用选项</option>
                    <option value="4">选项四</option>
                </select>
            </div>

            <!-- 禁用状态的Select -->
            <div class="form-group">
                <label for="">禁用</label>
                <select class="form-control custom-select" disabled>
                    <option value="">禁用选择框</option>
                    <option value="1">选项一</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                    <span id="submitText">提交表单</span>
                </button>
            </div>
        </form>


        <h3>表单配置和处理</h3>
        <p> 在表单<code>form</code>标签里设置<code>use-ajax</code>属性或者是<code>method="post"</code>提交方式的表单，会使用ajax提交表单数据，此种方式的表单中不论是否包含上传文件都可以不用设置<code>enctype="multipart/form-data"</code>属性</p>
        <p> 在表单<code>form</code>标签里设置<code>not-use-ajax</code>属性，会使用默认表单提交方式提交表单数据，优先级较高</p>
        <p> 在表单项中添加<code>.error-message</code>元素用于显示错误信息</p>
        <p> 在页面中定义<code>function form_before(event){...}</code>方法可以在提交表单前拦截/自定义处理表单提交数据，如果返回<code>false</code>值则中止提交数据</p>
        <p> 在页面中定义<code>function form_after(respones){...}</code>方法可用于接管处理ajax表单响应后的数据</p>

        <pre>
表单对齐方式 ：form-align-left:左对齐、form-align-top：顶部对齐、form-align-inline：行内对齐
&lt;form ... class="form-align-left" ...>
    &lt;div class="form-group">
        &lt;label for="username" class="required">用户名&lt;/label>
        &lt;div class="form-control-container">
            &lt;input type="text" id="username" name="username" class="form-control"
                   data-rule="required|zh_CN|len:3,10" placeholder="请输入中文用户名">
            &lt;div class="error-message">&lt;/div>
            &lt;div class="form-notice">3-10位中文&lt;/div>
        &lt;/div>
    &lt;/div>
    ...
&lt;/form>
        </pre>
        <pre>
// 设置POST 提交表单默认使用 ajax提交表单
&lt;form action="" ... method="post" enctype="multipart/form-data">
&lt;/form>
// 不论是否设置POST 提交表单，只要设置了 not-use-ajax 属性就表示使用默认表单提交方式提交表单
&lt;form action="" ... method="post" not-use-ajax enctype="multipart/form-data">
&lt;/form>
// 虽然不是POST 提交表单，但是设置了 use-ajax 属性就表示使用ajax方式提交表单
&lt;form action="" ... method="get" use-ajax>
&lt;/form>
// 同时设置了 use-ajax 和 not-use-ajax 属性，not-use-ajax优先级高，表示使用不ajax方式提交表单
&lt;form action="" ... use-ajax not-use-ajax>
&lt;/form>
        </pre>
        <pre>
// 页面中定义表单提交前的处理函数
function form_before(e) {
    // e.preventDefault(); // 阻止表单提交
    // 自定义 修改表单提交元素/表单值等处理
    // return false; // 返回false中断表单提交
    // 不设置 return 或者 不返回false则继续表单提交
}
// 仅仅是 ajax 表单提交方式才有效
function form_after(resp) {
    // resp: 服务器响应数据
    // 自定义 处理表单提交后的逻辑，无需设置返回值
}
        </pre>
    </div>

    <div class="form-container">
        <h1 class="form-title">Http请求</h1>

        <h3>1. 基本配置</h3>

        <pre>
// 全局配置
myTools.http.config({
    baseURL: 'https://api.example.com', // 基础URL
    timeout: 5000, // 超时时间5秒
    headers: { // 默认请求头
        'X-Requested-With': 'XMLHttpRequest'
    }
});
        </pre>

        <h3>2. 请求方法使用 - GET 请求</h3>

        <pre>
// 简单GET请求
myTools.http.get('/users')
    .then(data => console.log(data))
    .catch(error => console.error(error));

// 带参数的GET请求
myTools.http.get('/users', {
    page: 1,
    limit: 10
}).then(users => {
    console.log('用户列表:', users);
});
        </pre>

        <h3>3. 请求方法使用 - POST 请求</h3>

        <pre>
// JSON格式POST请求
myTools.http.post('/users', {
    name: '张三',
    age: 25
}).then(user => {
    console.log('创建的用户:', user);
});

// FormData格式POST请求
myTools.http.post('/login', new URLSearchParams({
    username: 'admin',
    password: '123456'
}), {
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    }
});
        </pre>

        <h3>4. 请求方法使用 - PUT/DELETE 请求</h3>

        <pre>
// PUT请求
myTools.http.put('/users/123', {
    name: '李四',
    age: 30
});

// DELETE请求
myTools.http.delete('/users/123');
        </pre>

        <h3>5. 文件上传</h3>

        <pre>
// 获取文件输入
var fileInput = document.getElementById('fileInput');

// 创建FormData
var formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('description', '这是一个文件');

// 上传文件
myTools.http.upload('/upload', formData)
    .then(response => {
        console.log('上传成功:', response);
    });
        </pre>

        <h3>6. 文件下载</h3>

        <pre>
// 下载文件并指定文件名
myTools.http.download('/files/report.pdf', '月度报告.pdf')
    .then(() => {
        console.log('文件下载完成');
    });

// 下载文件使用默认文件名
myTools.http.download('/files/report.pdf');
        </pre>

        <h3>7. 拦截器使用 - 请求拦截器</h3>

        <pre>
// 添加请求拦截器(添加认证token)
myTools.http.intercept('request', config => {
    var token = localStorage.getItem('token');
    if (token) {
        config.headers.set('Authorization', 'Bearer ' + token);
    }
    return config;
});

// 添加请求拦截器(修改请求URL)
myTools.http.intercept('request', config => {
    if (config.url.startsWith('/api')) {
        config.url = '/v2' + config.url;
    }
    return config;
});
        </pre>

        <h3>8. 拦截器使用 - 响应拦截器</h3>

        <pre>
// 添加响应拦截器(处理特定状态码)
myTools.http.intercept('response', response => {
    if (response.status === 401) {
        // 未授权跳转到登录页
        window.location.href = '/login';
    }
    return response;
});

// 添加响应拦截器(修改响应数据)
myTools.http.intercept('response', async response => {
    var data = await response.json();
    return {
        success: response.ok,
        data: data,
        status: response.status
    };
});
        </pre>

        <h3>9. 拦截器使用 - 错误拦截器</h3>

        <pre>
// 添加错误拦截器
myTools.http.intercept('error', error => {
    if (error.name === 'AbortError') {
        alert('请求超时，请稍后重试');
    } else if (error.response) {
        alert('服务器错误: ' + error.response.status);
    } else {
        alert('网络错误: ' + error.message);
    }
});
        </pre>

        <h3>10. 创建独立实例</h3>

        <pre>
// 创建API模块独立实例
var AuthAPI = myTools.http.create({
    baseURL: 'https://api.example.com/auth'
});

// 配置该实例的拦截器
AuthAPI.intercept('request', config => {
    config.headers.set('X-Auth-Module', 'true');
    return config;
});

// 使用独立实例
AuthAPI.post('/login', {
    username: 'admin',
    password: '123456'
});
        </pre>

        <h3>11. 高级功能 - 取消请求</h3>

        <pre>
// 创建AbortController
var controller = new AbortController();

// 发起可取消的请求
myTools.http.get('/large-data', null, {
    signal: controller.signal
}).catch(error => {
    if (error.name === 'AbortError') {
        console.log('请求已被取消');
    }
});

// 取消请求
controller.abort();
        </pre>

        <h3>12. 高级功能 - 获取不同响应类型</h3>

        <pre>
// 获取文本响应
myTools.http.get('/text-data', null, {
    responseType: 'text'
}).then(text => console.log(text));

// 获取二进制数据
myTools.http.get('/image', null, {
    responseType: 'blob'
}).then(blob => {
    var img = document.createElement('img');
    img.src = URL.createObjectURL(blob);
    document.body.appendChild(img);
});
        </pre>

    </div>

    <div class="form-container">
        <h1 class="form-title">简易消息提示</h1>
        <pre>
const msgId = myTools.msg("这是一条消息", 30); // 30秒后自动关闭
msgId.close(); // 调用手动关闭
        </pre>
    </div>

    <div class="form-container">
        <h1 class="form-title">页面加载监听</h1>

        <p>1. DOM加载完成（dom）</p>
        <pre>
myTools.load.on('dom', function() {
    console.log('DOM加载完成');
});
        </pre>

        <p>2. DOM和JS加载完成（all）</p>
        <pre>
myTools.load.on('all', function() {
    console.log('DOM和JS全部加载完成');
});
        </pre>

        <p>3. 强大的动态内容变化监听（dynamic）「会返回变化前后的节点信息」</p>
        <pre>
myTools.load.on('dynamic', function(changes) {
    changes.forEach(change => {
        switch(change.type) {
            case 'text':
                console.log('文本变化:', {
                    element: change.target,
                    before: change.oldValue,
                    after: change.newValue
                });
                break;

            case 'attribute':
                console.log('属性变化:', {
                    element: change.target,
                    attribute: change.attribute,
                    before: change.oldValue,
                    after: change.newValue
                });
                break;

            case 'removed':
                console.log('节点删除:', {
                    element: change.target,
                    before: change.oldValue,
                    after: null
                });
                break;

            case 'added':
                console.log('节点添加:', {
                    element: change.target,
                    before: null,
                    after: change.newValue
                });
                break;
        }
    });
});
// 会返回变化前后的节点信息
{
    newValue: '变化后的节点string',
    oldValue: '变化前的节点string',
    target: 可操作的节点信息,
    type: "节点类型：attribute，text，removed，added，parent"
}
        </pre>

        <p>4. 链式调用</p>
        <pre>
myTools.load.on('dom', function() {
    console.log('DOM加载完成');
}).on('all', function() {
    console.log('全部资源加载完成');
});
        </pre>

        <p>5. 不需要时销毁监听器</p>
        <pre>
myTools.load.destroy();
        </pre>

        <p>6. 监听dom尺寸变化</p>
        <pre>
myTools.load.onResize('body',function() {
    console.log('body尺寸发生变化');
});
        </pre>
    </div>
    </div>

@endsection

@section('page_js')
{{--    <script src="{{ asset('static/libs/zxf/js/tools.min.js') }}" type='text/javascript'></script>--}}

    <script>
        // 设置对齐方式
        function setFormLayout(align = 'left') {
            const containers = document.querySelectorAll('form');
            containers.forEach(container => {
                // 移除所有布局类
                ['left', 'top', 'inline'].forEach(l => {
                    container.classList.remove('form-align-' + l);
                });
                // 添加新的布局类
                container.classList.add('form-align-' + align);
            });
        }
    </script>
@endsection
