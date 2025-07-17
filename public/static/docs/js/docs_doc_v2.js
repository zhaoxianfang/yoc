function initMenuHandleBtn() {
    // 菜单操作按钮
    const menuHandleBtn = document.querySelectorAll('.docs-app-menu-handle-btn');
    menuHandleBtn.forEach(menuBtn => {
        // 点击 触发事件
        menuBtn.addEventListener('click', (e) => {
            let data = e.target.dataset;
            if(data.type === 'create_root_dir'){
                // 创建根目录
                Modal.form({
                    title: '添加 跟菜单目录',
                    width: 300,
                    labelAlign: 'top', // 标签左对齐
                    labelWidth: 90, // 标签宽度120px
                    darkTheme: true, // 使用暗黑主题
                    fields: [
                        {
                            type: 'text',
                            name: 'name',
                            label: '目录名称',
                            placeholder: '请输入子菜单名称',
                            required: true,
                        },
                        {
                            type: 'select',
                            name: 'open_type',
                            label: '状态',
                            required: true,
                            options: [
                                { value: '1', label: '默认「公开」'},
                                { value: '2', label: '需要登录' },
                                { value: '3', label: '仅自己可见' }
                            ],
                            default: '1'
                        }
                    ],
                    onSubmit: (formData, modal) => {
                        let tipModal = Modal.tips('请稍后...');
                        http_request(`/docs/menus/${data.app_id}/store`,formData,function (res) {
                            tipModal.remove();
                            Modal.tips('添加成功！');
                            setTimeout(function (){
                                // modal.close();
                                Modal.closeAll();
                                // 刷新页面
                                window.location.href= res.url || '/docs/'+data.app_id;
                            },2000);
                        },function (err) {
                            console.log(err);
                        },'POST');
                    }
                }).open();
            }
        })
    })
}

// docs-app-menu-handle-btn
function init_right_menu() {

    initMenuHandleBtn();

    // 为特殊元素创建独立菜单
    // new RightMenu({
    //     selector: '#content',
    //     itemWidth: 120,
    //     menus: [
    //         {
    //             text: '关于我们',
    //             icon: '&#x1f4a1;',
    //             callback: (el, menu, target) => {
    //                 console.log('触发元素:', el);
    //                 console.log('菜单项:', menu);
    //                 console.log('触发元素 dom', target);
    //                 console.log('触发dataset', target.parentNode.dataset);
    //             }
    //         },
    //     ]
    // });
    //  文章
    new RightMenu({
        selector: 'a[data-edit-doc]',
        menus: [
            {
                text: '编辑文章',
                icon: '&#x270F;',
                callback: (el, menu, target) => {
                    // console.log('触发元素:', el);
                    // console.log('菜单项:', menu);
                    // console.log('触发元素 dom', target);
                    // console.log('触发dataset', target.parentNode.dataset);
                    let data = target.parentNode.dataset;
                    window.location.href='/docs/doc/'+data.id+'/update';
                }
            },
            {
                text: '删除文章',
                icon: '&#x1F5D1;',
                callback: (el, menu, target) => {
                    // console.log('触发元素:', el);
                    // console.log('菜单项:', menu);
                    // console.log('触发元素 dom', target);
                    // console.log('触发dataset', target.parentNode.dataset);
                    let data = target.parentNode.dataset;
                    new Modal({
                        title: '提示',
                        theme: 'danger',
                        content: '请确认是否删除此文章「'+target.innerText+'」',
                        buttons: [
                            { text: '取消' },
                            { text: '确定',type: 'danger' , click: function(e, modal) {
                                let tipModal = Modal.tips('请稍后...');
                                http_request(`/docs/doc/${data.id}/delete`,{},function (res) {
                                    tipModal.remove();
                                    show_tips('删除成功');
                                    setTimeout(function (){
                                        Modal.closeAll();
                                        // 刷新页面
                                        window.location.href= res.url || '/docs/'+data.app_id;
                                    },2000);
                                },function (err) {
                                    console.log(err);
                                },'POST');
                            }},
                        ]
                    }).open();
                }
            }
        ]
    });
    // 目录
    new RightMenu({
        selector: 'a[data-edit-dir]',
        menus: [
            {
                text: '添加 子菜单',
                icon: '&#x1F4C2;',
                callback: (el, menu, target) => {
                    // console.log('触发元素:', el);
                    // console.log('菜单项:', menu);
                    // console.log('触发元素 dom', target);
                    // console.log('触发dataset', target.parentNode.dataset);
                    let data = target.parentNode.dataset;
                    Modal.form({
                        title: '添加 子菜单',
                        width: 300,
                        labelAlign: 'top', // 标签左对齐
                        labelWidth: 90, // 标签宽度120px
                        darkTheme: true, // 使用暗黑主题
                        fields: [
                            {
                                type: 'text',
                                name: 'name',
                                label: '目录名称',
                                placeholder: '请输入子菜单名称',
                                required: true,
                            },
                            {
                                type: 'select',
                                name: 'open_type',
                                label: '状态',
                                required: true,
                                options: [
                                    { value: '1', label: '默认「公开」'},
                                    { value: '2', label: '需要登录' },
                                    { value: '3', label: '仅自己可见' }
                                ],
                                default: '1'
                            }
                        ],
                        onSubmit: (formData, modal) => {
                            formData.id = target.parentNode.dataset.id;
                            let tipModal = Modal.tips('请稍后...');
                            http_request(`/docs/menus/${formData.id}/store_child`,formData,function (res) {
                                tipModal.remove();
                                Modal.tips('添加成功！');
                                setTimeout(function (){
                                    // modal.close();
                                    Modal.closeAll();
                                    // 刷新页面
                                    window.location.href= res.url || '/docs/'+data.app_id;
                                },2000);
                            },function (err) {
                                console.log(err);
                            },'POST');


                        }
                    }).open();
                }
            },
            {
                text: '编辑 菜单',
                icon: '&#x1F5C2;',
                callback: (el, menu, target) => {
                    let data = target.parentNode.dataset;
                    Modal.form({
                        title: '编辑菜单「'+target.innerText+'」',
                        width: 300,
                        labelAlign: 'top', // 标签左对齐
                        labelWidth: 90, // 标签宽度120px
                        darkTheme: true, // 使用暗黑主题
                        fields: [
                            {
                                type: 'text',
                                name: 'name',
                                label: '目录名称',
                                placeholder: '请输入目录名称',
                                required: true,
                                default: target.innerText || ''
                            },
                            {
                                type: 'select',
                                name: 'open_type',
                                label: '状态',
                                required: true,
                                options: [
                                    { value: '1', label: '默认「公开」'},
                                    { value: '2', label: '需要登录' },
                                    { value: '3', label: '仅自己可见' }
                                ],
                                default: target.parentNode.dataset.open_type || '1'
                            }
                        ],
                        onSubmit: (formData, modal) => {
                            formData.id = target.parentNode.dataset.id;
                            let tipModal = Modal.tips('请稍后...');
                            http_request(`/docs/menus/${formData.id}/update`,formData,function (res) {
                                tipModal.remove();
                                Modal.tips('编辑成功！');
                                setTimeout(function (){
                                    Modal.closeAll();
                                    // modal.close();
                                    // 刷新页面
                                    window.location.href= res.url || '/docs/'+data.app_id;
                                },2000);
                            },function (err) {
                                console.log(err);
                            },'POST');
                        }
                    }).open();
                }
            },
            {
                text: '删除 菜单',
                icon: '&#x1F5D1;',
                callback: (el, menu, target) => {
                    let data = target.parentNode.dataset;
                    // 带标题和按钮的简单弹窗
                    new Modal({
                        title: '提示',
                        theme: 'danger',
                        content: '请确认是否删除菜单「'+target.innerText+'」',
                        buttons: [
                            { text: '取消' },
                            { text: '确定',type: 'danger' , click: function(e, modal) {
                                    let id = target.parentNode.dataset.id;
                                    let tipModal = Modal.tips('请稍后...');
                                    http_request(`/docs/menus/${id}/delete`,{},function (res) {
                                        tipModal.remove();
                                        Modal.tips('删除成功！');
                                        setTimeout(function (){
                                            Modal.closeAll();
                                            // modal.close();
                                            // 刷新页面
                                            window.location.href= res.url || '/docs/'+data.app_id;
                                        },2000);
                                    },function (err) {
                                        console.log(err);
                                    },'POST');
                                    return false;
                            }},
                        ]
                    }).open();
                }
            },
            'divider',
            {
                text: '添加MD文档',
                icon: '#️⃣',
                callback: (el, menu, target) => {
                    // console.log('触发元素:', el);
                    // console.log('菜单项:', menu);
                    // console.log('触发元素 dom', target);
                    // console.log('触发dataset', target.parentNode.dataset);
                    let data = target.parentNode.dataset;
                    window.location.href='/docs/doc/'+data.app_id+'/create/'+data.id+'/markdown';
                }
            },
            {
                text: '添加富文本文档',
                icon: '📄',
                callback: (el, menu, target) => {
                    // console.log('触发元素:', el);
                    // console.log('菜单项:', menu);
                    // console.log('触发元素 dom', target);
                    // console.log('触发dataset', target.parentNode.dataset);
                    let data = target.parentNode.dataset;
                    window.location.href='/docs/doc/'+data.app_id+'/create/'+data.id+'/editor';
                }
            },
        ]
    });
}


function set_user_rule(dataset) {
    // 创建根目录
    Modal.form({
        title: '设置权限',
        width: 300,
        labelAlign: 'top', // 标签左对齐
        labelWidth: 90, // 标签宽度120px
        darkTheme: true, // 使用暗黑主题
        fields: [
            {
                type: 'text',
                name: 'nickname',
                label: '申请人昵称',
                placeholder: '申请人昵称',
                required: false,
                default: dataset.nickname || '',
            },
            {
                type: 'text',
                name: 'extra_nickname',
                label: '申请人备注(姓名)',
                placeholder: '申请人姓名',
                required: true,
                default: dataset.extra_nickname || '',
            },
            {
                type: 'select',
                name: 'role',
                label: '授权',
                required: true,
                options: [
                    { value: '3', label: '参与者/伙伴'},
                    { value: '5', label: '文档编辑' },
                    { value: '7', label: '文档管理员' }
                ],
                default: '5'
            }
        ],
        onSubmit: (formData, modal) => {
            let tipModal = Modal.tips('请稍后...');
            http_request(`/docs/user/${dataset.appid}/pass/${dataset.userid}/role`,formData,function (res) {
                tipModal.remove();
                Modal.tips('操作成功！');
                setTimeout(function (){
                    // modal.close();
                    Modal.closeAll();
                    // 刷新页面
                    if(res.url){
                        window.location.href= res.url;
                    }else{
                        window.location.reload();
                    }
                },2000);
            },function (err) {
                console.log(err);
            },'POST');
        }
    }).open();
}
// 监听操作用户事件处理

function confirm_exclude_user(url,message) {
    // 带标题和按钮的简单弹窗
    new Modal({
        title: '操作确认',
        content: message || '请确认是否进行此操作?',
        theme: 'dark', // 使用暗黑主题
        buttons: [
            { text: '取消', type: 'info'},
            { text: '确定', type: 'primary', click: function(e, modal) {
                let tipModal = Modal.tips('请稍后...');
                http_request(url,{},function (res) {
                    tipModal.remove();
                    Modal.tips('操作成功！');
                    setTimeout(function (){
                        // modal.close();
                        Modal.closeAll();
                        // 刷新页面
                        if(res.url){
                            window.location.href= res.url;
                        }else{
                            window.location.reload();
                        }
                    },2000);
                },function (err) {
                    console.log(err);
                },'POST');
            }}
        ]
    }).open();
}
// 最简单的形式 - 一行代码实现 监听点击
document.addEventListener('click', e => {
    const setRule = e.target.closest('.docs_app_user_set_rule'); // 赋权
    const kickOutUser = e.target.closest('.docs_app_user_kick_out'); // 踢出
    const allowJoin = e.target.closest('.docs_app_user_allow_join'); // 同意加入
    const refuseUser = e.target.closest('.docs_app_user_refuse'); // 驳回申请
    if (setRule) {
        // 赋权
        set_user_rule(setRule.dataset);
    }
    if (allowJoin){
        // 同意加入->赋权
        set_user_rule(allowJoin.dataset);
    }
    if(kickOutUser){
        let dataset = kickOutUser.dataset;
        confirm_exclude_user(`/docs/user/${dataset.appid}/kick_out/${dataset.userid}`,'是否确认踢出:'+(dataset.extra_nickname || dataset.nickname));
    }

    if(refuseUser){
        let dataset = refuseUser.dataset;
        confirm_exclude_user(`/docs/user/${dataset.appid}/refuse/${dataset.userid}`,'是否确认驳回「'+(dataset.extra_nickname || dataset.nickname)+'」的加入申请');
    }
}, true);

