@extends('demo::layouts.demo_layout')

@section('title', "Datatables 示例")

@section('use_datatables', "true")

@section('head_css')
    <style>
        /* DataTables 里面配置 width 无效，需要手动设置 th和td 的列宽 */
        /*.dt-container thead th:nth-child(14),.dt-container tbody td:nth-child(14){*/
        /*    width: 215px!important;*/
        /*    !*必须设置 min-width*!*/
        /*    min-width: 215px!important;*/
        /*}*/
        /*.dt-container tbody td:nth-child(14){*/
        /*    overflow: hidden;*/
        /*    height: 100%;*/
        /*    vertical-align: middle;*/
        /*}*/

    </style>
    <style>
        /*自定义展开子表样式*/
        .child_table {
            display: table;
            width: 750px;
            border-collapse: collapse;
            background-color: #fff;
        }

        .child_table_row {
            display: table-row;
        }

        .child_table_cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .child_table_row .child_table_cell:nth-child(odd) {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: right;
        }
    </style>
@endsection

@section('content')
    <h1>Datatables V2.3.3 使用示例</h1>

    <div class="">
        <div class="">
            <div class="card">
                {{-- 搜索条件会渲染在这里 table 的上一级元素--}}
                <div class="card-body">
                    <table id="example" class="table table-striped table-bordered dt-responsive align-middle mb-0"></table>
                </div>
            </div>
            <div> 为了避免 <code>table</code> 展示列数过多倒置太拥挤不易阅读，可以在 <code>table</code> 中设置最小宽度来解决美观性，例如： <code>style="min-width: 1200px;"</code> ,不建议使用<code>width</code>属性而是使用<code>min-width</code></div>
            <div> 按住 <code>Shift</code> 键单击某一列 可以进行多列排序（将单击的顺序列添加为次要、第三等排序列）</div>
            <div> <code>table</code> 样式【table-bordered有边框的单元格、table-hover 鼠标悬停时突出显示某一行、table-striped 斑马条纹行、cell-border 单元格边框、compact 单元格紧凑、order-column 突显排序列】</div>
            <div> 如果出现 <code>DataTables</code> 里面配置 <code>width</code> 实在是无效，则需要手动设置列宽</div>
            <div>
                <pre>
// 【重要】如果 css 文件中配置了类似下面的 display: none 会影响到 thead 和 tbody 的列对齐 样式，需要注释掉【重要】
div.dt-scroll-body tfoot tr,div.dt-scroll-body thead tr {
    /*20250827 zxf 修改，此行会影响表格head 和body 对齐*/
    /*display: none!important*/
}
                </pre>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    @parent

    <script>
        $(function () {

        });
    </script>
    <script>
        // 表头添加额外的按钮
        var addTableHeaderBtn=[
            {
                'text':'table 选中单行/多行后激活按钮',
                'type':'btn',
                'event_type':'multi_select', // multi_select: table 选中单行/多行后激活按钮
                'class_type':'success',
                'icon':'ti ti-check fs-14',
                // 有回调函数callback的时候才会返回data参数
                'callback':function (rows) {
                    console.log(rows)
                    // 遍历rows下的id
                    let ids = ''
                    for (var i = 0; i < rows.length; i++) {
                        ids +=','+rows[i].id
                    }
                    myTools.msg('选中了'+rows.length+'行数据'+ids)
                }
            },{
                'text':'附加：弹窗按钮',
                'type':'btn',
                'event_type':'callback',
                'class_type':'info',
                'icon':'ti ti-layers-subtract fs-14',
                'data':{info:'hello world'},
                // 有回调函数callback的时候才会返回data参数
                'callback':function (data) {
                    console.log('callback',data)
                    Modal.iframe('弹窗','/docs', '80%', '80%');
                }
            },{
                'text':'附加：新页面',
                'type':'btn',
                'event_type':'callback',
                'class_type':'outline btn-primary',
                'icon':'ti ti-link fs-14',
                'data':'null',
                'callback':function (data) {
                    window.open('/docs')
                }
            }
        ];
        $(function () {
            //
            Table = TableTools.init({
                index_url: "{{ url('demo/table/data') }}",
                add_url: "{{ url('/demo') }}",
                edit_url: "{{ url('/') }}",
                del_url: "{{ url('/demo/{id}/delete') }}", // 参数使用{}包围，例如{name}
                docs_url: "{{ url('/docs') }}",
                detail_url: "",
            },[
                // 设置可以展开的列
                // 点击展开/关闭子数据
                {
                    className: 'dt-control', // 固定格式，设置绑定对象
                    orderable: false,
                    data: null,
                    title: '#',
                    defaultContent: '',
                    // 固定使用 show_detail 作为回调函数
                    show_detail: function (data, index, node,table) {
                        // data: 当前行数据
                        // index: 当前行索引
                        // node: 当前行节点
                        // table: 当前表格对象
                        // console.log(data,index,node,table)

                        var html = '<div class="child_table">';
                        var data_index =-1 ;
                        for (const key in data) {
                            if (data.hasOwnProperty(key)) {
                                const value = data[key];
                                if (typeof value === "object" && value !== null) {
                                    // console.log(`Key: ${key}, Value: Object`);
                                    // TODO 递归调用
                                } else {
                                    data_index +=1;

                                    html += (data_index % 3 === 0?'<div class="child_table_row">':'') +
                                        '<div class="child_table_cell">' + key + ':</div>' +
                                        '<div class="child_table_cell">' + value + '</div>' +
                                        (data_index % 3 === 2?'</div>':'');
                                }
                            }
                        }
                        return html + '</div>';
                    }
                },
                {
                    "data": "id",
                    "title":"ID",
                    // "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean 隐藏列
                    "orderable": true, //是否参与排序 Boolean
                    "width": "25px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String select、text、datetimerange、daterange、date、datetime、between
                },
                {
                    "data": "user.cover",
                    "title":"Image",
                    "width": "35px",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'image',
                                "value": value,
                                "class": 'img-sm',
                            },
                        ]);
                    }
                },
                {
                    "data": "label",
                    "title":"badge(label)",
                    "width": "35px",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'label',
                                "text": value,
                                "class_type": value,
                            },
                        ]);
                    }
                },
                {
                    "data": "icon",
                    "title":"Icon",
                    "width": "35px",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'icon',
                                // "text": value,
                                "class_type": value + ' fs-18',
                            },
                        ]);
                    }
                },
                {
                    "data": "user_agent",
                    "title":"sub_str",
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'sub_str',
                                "start": 0, // 开始位置
                                "length": 20, // 截取长度
                            },
                        ]);
                    }
                },
                {
                    "data": "input",
                    "title":"Input",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                    "width": "180px",
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'input',
                                "event_type": "text", // text, number, password, email 等 input 的 type 类型值
                            },
                        ]);
                    }
                },
                {
                    "data": "user.cover",
                    "title":"Url",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'url',
                            },
                        ]);
                    }
                },
                {
                    "data": "ip",
                    "title":"IP",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'ip',
                            },
                        ]);
                    }
                },
                {
                    "data": "mobile_verified_at",
                    "title":"Datetime",
                    "width":"140px",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'datetime',
                                "format": "YYYY-MM-DD HH:mm", //YYYY-MM-DD HH:mm:ss
                            },
                        ]);
                    }
                },
                {
                    "data": "nickname",
                    "title":"String",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "mobile",
                    "title":"自定义",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                    "render" : function ( value, type, row, meta ) {
                        return '<i class="ti ti-confetti">'+value+'</i>';
                    }
                },
                {
                    "data": "email",
                    "title":"邮箱号",
                    "width":"140px",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'input',
                                'event_type':'text', // input 的type类型
                            },
                        ]);
                    }
                },
                {
                    "data": "created_at",
                    "title":"创建时间",
                    "width":"140px",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'datetime',
                            },
                        ]);
                    }
                },
                {
                    "data": "gender",
                    "title":"性别",
                    "width":"40px",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'男',"0":'未设置',"2":'女'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"未设置","class":"muted"},"1":{"text":"男","class":"info"},"2":{"text":"女","class":"warning"}}',
                                'field':'gender',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "status",
                    "title":"Status",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'正常',"0":'未激活',"2":'冻结'}, //下拉搜索筛选项
                    "width":"60px",
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"未激活","class":"info"},"1":{"text":"正常","class":"success"},"2":{"text":"冻结","class":"danger"}}',
                                'field':'status',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "sub_tasks",
                    "title":"Toggle",
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'是',"0":'否'}, //下拉搜索筛选项
                    "orderable": false, //是否参与排序 Boolean
                    "width":"80px",
                    "render" : function ( value, type, row, meta ) {
                        // 创建一个 toggle类型的开关
                        return TableTools.createButtonList([
                            {
                                'type':'toggle',
                                'open_value':1, // 表示开启的值 string|int
                                'field':'sub_tasks',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "title":"操作",
                    "orderable": false, //是否参与排序 Boolean
                    "width": "215px", //列宽 String ..px..x%,..em
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'编辑',
                                "title":"编辑["+row.nickname+']',
                                'type':'btn', // btn、status、label、icon、sub_str、datetime、toggle、image、input、url、ip
                                "icon": "ti ti-pencil fs-14", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open', //callback:自定义回调操作, layer_open:弹出框打开url,confirm_open:对话操作,jump_url:跳转url,tips:仅提示
                                'class_type':'info', // bootstrap 按钮的样式类型，不需要带 btn-前缀
                                'url_name':'edit_url', // index_url,add_url,edit_url,del_url,detail_url 等在urls里面定义的url名称
                                'url_params':"{id:"+row.id+"}",// 替换url的参数
                                // 'options':'{"area":["1000px","600px"]}', // 设置弹出层窗口大小
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                            },{
                                'text':'删除',
                                "title":"确认删除["+row.nickname+']吗？',
                                'type':'btn',
                                "icon": "ti ti-trash fs-14", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'confirm_open',
                                'class_type':'danger',
                                'url_name':'del_url',
                                'url_params':"{id:"+row.id+"}",
                            },{
                                'text':'提示',
                                "title":"提示内容是：此行ID为["+row.id+']',
                                'type':'btn',
                                "icon": "ti ti-info-circle fs-14", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'tips',
                                'class_type':'link'
                            }
                            // 跳转必须配置url_name、url_params 参数
                            ,{
                                'text':'跳转',
                                "title":"打开新窗口提示",
                                'type':'btn',
                                "icon": "ti ti-link fs-14", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'jump_url',
                                'class_type':'link',
                                'url_name':'docs_url'
                            }
                        ]);
                    }
                }
            ],{
                searching:false, // 是否在表格右上角显示搜索框 Boolean 建议使用 show_custom_search
                show_custom_search: true, // 是否显示自定义搜索 表格右上角触发显示/隐藏 搜索表单
                show_custom_search_form:false, // 是否显示自定义搜索表单 Boolean 默认展示还是点击搜索按钮后展示
                info: true, //是否显示页脚信息，DataTables插件左下角显示记录数
                // 控制是否能够调整每页的条数,如果设为false,标准的每页条数控制控件也会被隐藏.
                lengthChange: true, //是否允许用户改变表格每页显示的记录数，配合lengthMenu使用 Boolean
                lengthMenu: [10, 15, 20, 50], // 数量选择下拉框内容
                // lengthMenu: [10, 15, 20, 50, "ALL"], // 数量选择下拉框内容
                // 全局控制列表的所有排序功能,Boolean值,默认为true,即开启排序功能
                // ordering: true, //是否允许排序 Boolean
                // 全局控制列表的翻页功能,如果设为false,所有的默认翻页控件会被隐藏
                paging: true, //是否允许翻页 Boolean
                showRefreshBtn: true, // 是否显示表格顶部刷新按钮
                // order: [[ 1, "asc" ]], //默认排序的列 Integer
                // order: [], // 禁用初始排序
                // 更改初始页面长度（每页的行数）。
                pageLength: 15, //每页显示的记录数 Integer
                // 分页类型
                // numbers- 仅页码按钮（1.10.8）
                // simple- 仅“上一个”和“下一个”按钮
                // simple_numbers- “上一页”和“下一页”按钮以及页码
                // full- “第一个”、“上一个”、“下一个”和“最后一个”按钮
                // full_numbers- “第一个”、“上一个”、“下一个”和“最后一个”按钮以及页码
                // first_last_numbers- “第一个”和“最后一个”按钮，以及页码
                pagingType: "first_last_numbers", //分页控件的样式 String
                scrollCollapse: true, //当表格高度小于表格的数据时，是否允许表格自动收缩以适应数据的高度 Boolean
                // 状态保存 - 在页面重新加载时恢复cookies表状态（其分页位置、排序状态等）
                stateSave: false, //状态保存 Boolean
                // 数据表 选择
                select: true,
                // 设置列的默认配置
                columnDefs: [
                    // // 表格中的第一列和第二列将显示，而所有其他列将被隐藏。
                    // { targets: [0, 1], visible: true},
                    // { targets: '_all', visible: false },
                    // // 禁用第一列的过滤
                    // { targets: 0, searchable: false },
                    // // 禁用第一列和第三列的排序
                    // { targets: [0, 2], orderable: false},
                    // {
                    //     targets: [0],
                    //     orderData: [0, 1]
                    // },
                    // {
                    //     targets: [1],
                    //     orderData: [1, 0]
                    // },
                    // {
                    //     targets: [4],
                    //     orderData: [4, 0]
                    // },
                    // // 设置第4列(从0开始计数)的格式渲染为date
                    // {
                    //     target: 4,
                    //     render: DataTable.render.date() // 可选： date,datetime('YYYY MM dd'),time,number,text
                    // },
                    // 在第5列(从0开始计数)的内容前面加上$符号
                    {
                        target: 5,
                        // render: DataTable.render.number(null, null, 0, '$')
                        render: function (data, type, row) {
                            return "￥" + data + "元";
                        }
                    },
                    { "width": "20%", "targets": 1 },
                    { "width": "200px", "targets": 13 },
                ],
                autoWidth: true, //自动宽度 Boolean
                // 定义一个高度,当列表内容超过这个高度时,显示垂直滚动条,这个高度不算表头和翻页搜索等工具条的空间.支持数字或者css写法比如:200或者’200px’
                scrollY: "100%", // 垂直滚动 Number /   String 例如：'200px' ，"100%"，'50vh'(动态高度)
                // 控制在列过多过宽是,是否出现水平滚动条.注意使用这个参数时最好关闭响应式设计
                scrollX: true, //水平滚动 Boolean
                // ----------------
                // 数据展示 国际化
                // ----------------
                // https://cdn.datatables.net/plug-ins/2.1.2/i18n/
                // language: {
                //     "url": "/static/libs/DataTables/DataTables-2.3.3/language/zh.json"
                // },

                // 固定列
                fixedColumns:   {
                    leftColumns: 0,      // 固定左侧的列数
                    rightColumns: 1      // 固定右侧的列数（如果需要）
                },
                // 附加表头按钮
                addTableHeaderBtn:addTableHeaderBtn
            },'#example');

            Table.onClick = function (data,row) {
                // console.log('onClick 单击',data,row);
            };
            Table.onDoubleClick = function (data,row) {
                // console.log('onDoubleClick',data,row);
            };

            // ----------------
            // 数据回调
            // ----------------
            // 初始化完成回调。
            Table.initComplete= function (settings, json) {
                // console.log("初始化完成回调 DataTables has finished its initialisation.", settings, json);
            };
            // 最后一步再绘制表格。
            Table.drawTable();

        });


    </script>
@endsection
