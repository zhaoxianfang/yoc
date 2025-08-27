!function (s) {
    "use strict";
    var TableTools = {
        tableObj: null, // 实例化后的表格对象
        tableElement: "#table", // 表格元素
        customSearchElement: ".custom-datatable-search", // 表格搜索表单元素
        // https://datatables.net/reference/option/
        //设置错误模式:throw抛出异常、alert弹出警告、none不做任何处理
        errMode: "none",
        // 定时器
        dataTableTimes: null,
        options: {},
        detailRows: [],
        // 事件
        onClick: null,// (function)
        onDoubleClick: null,// (function)
        onSearch: null,// (function)
        // 点击展开更多详情(function),注意，列中需要定义 【"class": 'dt-control'】属性,固定样式dt-control
        showMore: null,// (function)

        // 每当 DataTables 进行绘制时发出通知：
        drawCallback: null,// (function)
        // 更改回调时页脚的内容：
        footerCallback: null,// (function)
        // 表头显示回调函数。
        headerCallback: null,// (function)
        // 初始化完成回调。
        initComplete: null,// (function)
        // 表汇总信息显示回调
        infoCallback: null,// (function)
        // 预绘制回调。
        preDrawCallback: null,// (function)
        // 每当为表主体创建 TR 元素时进行回调
        createdRow: null,// (function)

        // 是否显示表格顶部刷新按钮

        urls: {
            index_url: "",
            add_url: "",
            edit_url: "",
            del_url: "",
            detail_url: "",
        },
        // 表格顶部按钮列表, 同 table 操作按钮
        addTableHeaderBtn: [
            // {
            //     "text": "操作",
            //     "title": "标题",
            //     "class_type": "primary",
            //     "type": "btn",
            //     "icon": "", // ti 按钮小图标 ,例如 ti ti-pencil
            //     "event_type": "layer_open", //callback:自定义回调操作, layer_open:弹出框打开url,confirm_open:对话操作,jump_url:跳转url,tips:仅提示
            //     "url_name": "",// index_url,add_url,edit_url,del_url,detail_url 等在urls里面定义的url名称
            //     "url_params": "{}", // 替换url的参数
            //     "options": '{"maxmin":false}',
            // }
        ],

        // 给内部临时挂载函数的地方
        tempFunc: {},
        // table 选中单行/多行 的选中数据
        multi_select_data: [],

        defaults: {
            // ----------------
            // 功能参数(Features)
            // ----------------
            autoWidth: true, //自动宽度 Boolean
            // 延迟渲染: 定义在render时是否仅仅render显示的dom,在显示大量数据的情况下强烈建议设为true,显示少量数据或者真翻页方案可以设为false,注意在设为true时无法通过函数获取所有行的dom对象—因为它们并不存在.
            deferRender: false, //延迟渲染 Boolean
            // 控制在列过多过宽是,是否出现水平滚动条.注意使用这个参数时最好关闭响应式设计
            scrollX: true, //水平滚动 Boolean
            // 定义一个高度,当列表内容超过这个高度时,显示垂直滚动条,这个高度不算表头和翻页搜索等工具条的空间.支持数字或者css写法比如:200或者’200px’
            scrollY: "100%", // 垂直滚动 Number /   String 例如：'200px' ，"100%"，'50vh'(动态高度)
            // 当设为true时,列表的过滤,搜索和排序信息会传递到Server端进行处理,实现真翻页方案的必需属性.反之,所有的列表功能都在客户端计算并执行
            serverSide: true, //开启服务器模式 Boolean


            // 控制总数信息(标准界面右下角显示总数和过滤条数的控件)的显隐
            info: true, //是否显示页脚信息，DataTables插件左下角显示记录数
            // 控制是否能够调整每页的条数,如果设为false,标准的每页条数控制控件也会被隐藏.
            lengthChange: true, //是否允许用户改变表格每页显示的记录数，配合lengthMenu使用 Boolean
            lengthMenu: [10, 15, 20, 25, 50], // 数量选择下拉框内容 ，"ALL" 或者-1表示全部
            // lengthMenu: [10, 25, 50, 75, 100,"ALL"], // 数量选择下拉框内容 ，"ALL" 或者-1表示全部
            // 全局控制列表的所有排序功能,Boolean值,默认为true,即开启排序功能
            // ordering: true, //是否允许排序 Boolean
            // 全局控制列表的翻页功能,如果设为false,所有的默认翻页控件会被隐藏
            paging: true, //是否允许翻页 Boolean
            // 控制是否在数据加载时出现”Processing”的提示,一般在远程加载并且比较慢的情况下才会出现. 样式需要定义,否则比较丑.
            processing: true, //显示“处理中...” Boolean
            // 控制控件的搜索功能,如果为false,控件的搜索功能被完全禁用,而且默认搜索组件会被隐藏.
            searching: false, //是否显示搜索框 Boolean 建议使用 show_custom_search
            show_custom_search: true, // 是否显示自定义搜索
            show_custom_search_form: false, // 是否显示自定义搜索表单 显示或者隐藏，show_custom_search 为true时才有效
            // 状态保存 - 在页面重新加载时恢复cookies表状态（其分页位置、排序状态等）
            stateSave: false, //状态保存 Boolean

            showRefreshBtn: true, // 是否显示表格顶部刷新按钮
            // 固定列
            fixedColumns: {
                leftColumns: 0,      // 固定左侧的列数
                rightColumns: 0      // 固定右侧的列数（如果需要）
            },

            // 表格控件定位【不建议修改】: info提示信息，search搜索框，pageLength显示分页条数，paging翻页、 null不显示
            layout: {
                // topStart: null,
                // 自定义控件
                topStart: function () {
                    setTimeout(function () {
                        // 取消 元素上绑定的所有事件
                        $(".date-table-tools-search-btn").off();
                        // 点击自定义搜索按钮
                        $(".date-table-tools-search-btn").on("click", function () {
                            // 判断 .custom-datatable-search 是否有 .show ,如果有则移除并添加上.hidden,否则添加上.show并移除.hidden
                            if ($(".custom-datatable-search").hasClass("show")) {
                                $(".custom-datatable-search").removeClass("show").addClass("hidden");
                            } else {
                                $(".custom-datatable-search").removeClass("hidden").addClass("show");
                            }
                        });
                    }, 1000);
                    return $(".table-top-buttons").html();
                },
                // topEnd: 'search',
                topEnd: {
                    search: {
                        placeholder: "关键字搜索"
                    }
                },
                // 左下角显示2个信息
                bottomStart:['pageLength', 'info'],
                // bottomStart: "pageLength",
                // bottom2Start: 'info',
                bottomEnd: "paging"
                // bottomEnd: {
                //     paging: {
                //         buttons: 4 // 设置按钮个数(包含...按钮)
                //     }
                // }
            },

            // ----------------
            // 数据回调
            // ----------------
            // 每当为表主体创建 TR 元素时进行回调
            "createdRow": function (row, data, dataIndex) {
                // if ( data[4] == "A" ) {
                //     $(row).addClass( 'important' );
                // }
                (typeof (this.createdRow) == "function") && this.createdRow(row, data, dataIndex);

            },
            //每当 DataTables 进行绘制时发出通知：
            "drawCallback": function (settings) {
                // 渲染每一页都需要重新绑定事件
                TableTools.events.bindClickEvent();
                (typeof (TableTools.drawCallback) == "function") && TableTools.drawCallback(settings);
                // var api = this.api();
            },
            // 更改回调时页脚的内容：
            "footerCallback": function (tfoot, data, start, end, display) {
                // $(tfoot).find('th').eq(0).html( "Starting index is "+start );
                (typeof (TableTools.footerCallback) == "function") && TableTools.footerCallback(tfoot, data, start, end, display);
            },
            // 表头显示回调函数。
            "headerCallback": function (thead, data, start, end, display) {
                // $(thead).find('th').eq(0).html( 'Displaying '+(end-start)+' records' );
                (typeof (TableTools.headerCallback) == "function") && TableTools.headerCallback(thead, data, start, end, display);
            },
            // 初始化完成回调。
            "initComplete": function (settings, json) {
                (typeof (TableTools.initComplete) == "function") && TableTools.initComplete(settings, json);

                // 监听表格宽度的变化,判断是否支持ResizeObserver
                if ("ResizeObserver" in window) {
                    TableTools.resizeTable(function (width, height) {
                        // console.log('表格宽度有变化')
                        // 调整列
                        DataTable.tables({visible: true, api: true}).columns.adjust();
                    });
                }
            },
            // 表汇总信息显示回调
            "infoCallback": function (settings, start, end, max, total, pre) {
                if(typeof (TableTools.infoCallback) == "function"){
                    TableTools.infoCallback(settings, start, end, max, total, pre);
                }else{
                    // return "显示第 " + start + " 至 " + end + " 项结果，共 " + total + " 项";
                    return ", 共 " + total + " 条";
                }
            },
            // 预绘制回调。
            "preDrawCallback": function (settings) {
                (typeof (TableTools.preDrawCallback) == "function") && TableTools.preDrawCallback(settings);
            },
            // 定义应在何处以及如何加载已保存状态的回调。
            // "stateLoadCallback": function( settings, callback ) {
            //     $.ajax( {
            //         url: '/state_load',
            //         dataType: 'json',
            //         success: function (json) {
            //             callback( json );
            //         }
            //     } );
            // },
            // 状态加载 - 数据操作回调
            // "stateLoadParams": function (settings, data) {
            //     data.search.search = "";
            // },

            // ----------------
            // 数据 选项
            // ----------------
            // 销毁与选择器匹配的任何现有表并替换为新选项。如果为true，则销毁现有表格，否则只是更新它。
            destroy: false, //销毁已存在的表格,重新加载新数据 Boolean
            displayStart: 0, //表格中的第一个显示的数据的索引 Integer
            // dom: 'lBfrtip', //控制表格的元素控件布局 String
            // order: [[ 1, "asc" ]], //默认排序的列 Integer
            order: [], // 禁用初始排序
            // DataTables 通过向该列中的单元格添加一个类来突出显示用于对表主体中的内容进行排序的列，该类又将 CSS 应用于这些类以突出显示这些单元格
            orderClasses: true, //控制表头单元格是否应该被排序,默认为true,即开启排序功能 Boolean
            orderMulti: true, //多列排序 Boolean
            // 更改初始页面长度（每页的行数）。
            pageLength: 15, //每页显示的记录数 Integer
            // pagingTag: "ul", //分页标签 String
            // 分页类型
            // numbers- 仅页码按钮（1.10.8）
            // simple- 仅“上一个”和“下一个”按钮
            // simple_numbers- “上一页”和“下一页”按钮以及页码
            // full- “第一个”、“上一个”、“下一个”和“最后一个”按钮
            // full_numbers- “第一个”、“上一个”、“下一个”和“最后一个”按钮以及页码
            // first_last_numbers- “第一个”和“最后一个”按钮，以及页码
            pagingType: "first_last_numbers", //分页控件的样式 String
            // 显示组件渲染器类型
            // renderer: {
            //     "header": "bootstrap",
            //     "pageButton": "bootstrap"
            // },
            rowId: "id", //行ID属性 String
            scrollCollapse: true, //当表格高度小于表格的数据时，是否允许表格自动收缩以适应数据的高度 Boolean
            search: {
                regex: true, //是否启用正则表达式搜索 Boolean
                caseInsensitive: true, //是否启用大小写不敏感 Boolean
                smart: true, //是否启用智能过滤 Boolean
                search: "", //搜索框提示文字 String
            },
            // searchCols: [ //搜索列配置 Array
            //     {
            //         search: "搜索",
            //         regex: true,
            //         caseInsensitive: true,
            //         smart: true
            //     }
            // ],
            // 设置搜索节流频率
            searchDelay: 1200, //搜索延迟 Integer
            // stripeClasses: [ 'strip1', 'strip2', 'strip3' ], //条纹样式 Array
            // 用于键盘导航的选项卡索引控件。
            // tabindex默认情况下，DataTables 允许通过向所需元素添加属性来对表进行键盘导航（排序、分页和过滤） 。这允许最终用户通过选项卡浏览控件并按 Enter 键激活它们，从而无需鼠标即可访问表格控件。
            // 默认 tabindex 为 0，这意味着选项卡遵循文档的流程。如果您愿意，您可以使用此参数否决这一点。使用值 -1 禁用内置键盘导航，但出于可访问性原因不建议这样做。
            tabIndex: 0, //tab键索引 Integer


            // ----------------
            // 自动填充
            // ----------------
            // "autoFill": {
            //     ...
            // },
            // ----------------
            // 固定列
            // ----------------
            // "fixedColumns": {
            //     left: 2, // 要固定到表格左侧的列数 Integer
            //     right: 1, // 要固定到表格右侧的列数 Integer
            //     leftColumns: 2, //要固定到表格左侧的列数 Integer
            //     rightColumns: 1, //要固定到表格右侧的列数 Integer
            // },
            // // ----------------
            // // 固定表头
            // // ----------------
            // "fixedHeader": {
            //     header: true, //启用/禁用固定标头
            //     footer: true, //启用/禁用固定页脚
            //     headerOffset: 0, //偏移表格的固定标题
            //     footerOffset: 0, //偏移表格的固定页脚
            // },
            // ----------------
            // 数据表 搜索
            // ----------------
            // "searchBuilder": {
            //     // 限制可以过滤哪些列
            //     columns: [ 0, 1, 2, 3, 4, 5 ], //搜索构建器应该搜索的列
            //     // 为 SearchBuilder 定义自定义条件
            //     // conditions: { //搜索构建器的条件
            //     //     "date": {
            //     //         "search": "date"
            //     //     },
            //     //     "number": {
            //     //         "search": "number"
            //     //     },
            //     //     "string": {
            //     //         "search": "string"
            //     //     }
            //     // },
            //     depthLimit: 3, //搜索构建器的深度限制
            //     defaultCondition: "contains", //搜索构建器的默认条件
            //     escapeHtml: false, //搜索构建器是否应该转义HTML
            //     greyscale: false, //搜索构建器是否应该是灰度的
            //     logic: "AND", //搜索构建器的逻辑
            //     orthogonal: { //搜索构建器的正交
            //         "display": "display",
            //         "search": "filter"
            //     },
            //     preDefined: false, //搜索构建器是否应该是预定义的
            //     template: null, //搜索构建器的模板
            //     time: { //搜索构建器的时间
            //         "between": "between",
            //         "equals": "equals",
            //         "not": "not"
            //     },
            //     enterSearch: true, //搜索构建器是否应该在输入时搜索
            // },
            // ----------------
            // 数据表 选择
            // ----------------
            select: true,

            // ----------------
            // 数据参数(Data)
            // ----------------
            // 从服务器获取数据,需要开启serverSide
            "ajax": {
                "url": "",
                "type": "GET",
                //返回数据中的数据,例如返回数据为{tableData:[{},{},{}]}时,tableData为数据源,如果返回数据为[{},{},{}]时,dataSrc为空
                // "dataSrc": "list",// 默认data
                "dataSrc": function (json) {
                    // 隐藏加载提示
                    $(".dt-processing").hide();
                    // recordsTotal 数据总条数
                    json.recordsTotal = json.total || json.count || json.recordsTotal || (json.meta ? json.meta.total : 0);
                    // (int)筛选后的总记录数
                    json.recordsFiltered = json.recordsTotal;
                    // 展示数据
                    return json.data || json.list || json.rows || json;
                },
                // 搜索参数
                "data": function (searchParams, oSettings) {
                    return TableTools.getSearchParams(searchParams);
                    // return searchParams;
                },
                "error": function (jqXHR, textStatus, errorThrown) {
                    console.log("jqXHR", jqXHR);

                    TableTools.events.bindClickEvent();
                    // 隐藏加载提示
                    $(".dt-processing").hide();
                    const errorMsg = (jqXHR.status === 404) ? jqXHR.status+"请求的资源不存在" : jqXHR.status+"获取数据异常~";

                    $('.dt-empty').text(errorMsg);

                    // 这个 "error" 回调实际上不会被触发，因为这不是 DataTables 的标准配置方式
                    if(typeof Modal != "undefined"){
                        Modal.error(errorMsg, {
                            position: 'center',
                            timeout: 3000
                        });
                    }else if(typeof myTools != "undefined"){
                        myTools.msg(errorMsg,3);
                    }else{
                    }
                }
            }
        },
        // ----------------
        // 自定义 实现获取查询参数
        // ----------------
        getSearchParams(params) {
            // console.log("自定义 实现获取查询参数:params", params);
            let aoData = {
                // draw 相当于是 datatables 插件需要展示的页码编号，[相当重要]
                draw: params.draw,
            };
            var searchForm = $(".custom-datatable-search").serializeArray();
            searchForm.forEach(function (item, index) {
                // 判断 item.name 是否为[]结尾
                if (item.name.indexOf("[]") > - 1) {
                    var name = item.name.replace("[]", "");
                    if ( !aoData[name]) {
                        aoData[name] = [];
                    }
                    aoData[name].push(item.value);
                } else {
                    aoData[item.name] = item.value;
                }
            });
            if ( !params.length || params.length <= 0) {
                aoData.page = 1;
                aoData.offset = 0;
                aoData.limit = TableTools.options.pageLength;
            } else {
                aoData.offset = params.start;
                aoData.limit = params.length;
                aoData.page = (params.start / params.length) + 1;
            }
            aoData.sort = ""; // 设置排序字段
            aoData.order = "desc"; // 设置排序方式 asc/desc
            aoData.multi_order = []; // 使用多字段排序，例如：[{field:'id',order:'asc'},{field:'age',order:'desc'},...]

            // 排序
            if (params.order && params.order.length > 0) {
                var columnIdx, orderColumnField;
                columnIdx = params.order[0]["column"]; // 获取排序列的索引
                orderColumnField = params.columns[columnIdx].data; // 获取排序列的字段名称
                // 判断orderColumnField是否存在或者是否为字符串
                if (orderColumnField && typeof orderColumnField === "string") {
                    aoData.sort = orderColumnField; // 设置排序字段
                    aoData.order = params.order[0]["dir"]; // 设置排序方式 asc/desc
                }
                if (params.order.length > 1) {
                    // 多类排序
                    for (var i = 0; i < params.order.length; i ++) {
                        columnIdx = params.order[i]["column"]; // 获取排序列的索引
                        orderColumnField = params.columns[columnIdx].data; // 获取排序列的字段名称
                        // 判断orderColumnField是否存在或者是否为字符串
                        if (orderColumnField && typeof orderColumnField === "string") {
                            aoData.multi_order.push({
                                field: orderColumnField,
                                order: params.order[i]["dir"]
                            });
                        }
                    }
                } else {
                    delete aoData.multi_order;
                }
            }
            if (params.search && params.search.value) {
                aoData.search = params.search.value;
            } else {
                aoData.search = "";
            }
            return aoData;
        },
        // 监听元素的宽度变化
        resizeTable(callback = null) {
            // 获取 DOM 元素
            var tableElement = document.querySelector(".dt-container");
            if (tableElement) {
                var debounceTimer;
                // 创建 ResizeObserver 实例
                var resizeObserver = new ResizeObserver(entries => {
                    for (let entry of entries) {
                        // 清除之前的定时器
                        if (debounceTimer) {
                            clearTimeout(debounceTimer);
                        }
                        // 设置新的定时器，延迟0.8秒后执行回调
                        debounceTimer = setTimeout(() => {
                            callback && callback(entry.contentRect.width, entry.contentRect.height);
                        }, 800);
                    }
                });
                // 观察 DataTables 表格元素
                resizeObserver.observe(tableElement);
            } else {
                // console.error('未找到表格元素');
            }
        },
        // ----------------
        // 数据展示 国际化
        // ----------------
        // https://cdn.datatables.net/plug-ins/2.1.2/i18n/
        language: {
            "url": "/static/libs/DataTables/DataTables-2.3.3/language/zh.json",
            // 不显示 已选择n行、m列 的提示信息
            select: {
                rows: '',  // 隐藏选择行的信息
                columns: '',  // 隐藏选择列的信息
                cells: ''  // 隐藏选择单元格的信息
            }
        },
        reset: function () {
            this.tableObj = "#table";
            this.tableElement = "#table";
            this.customSearchElement = ".custom-datatable-search";
            this.errMode = "none";
            this.dataTableTimes = null;
            this.options = {};
            this.detailRows = [];
            // 事件
            this.onClick = null;
            this.onDoubleClick = null;
            this.onSearch = null;
            this.showMore = null;
            this.drawCallback = null;
            this.footerCallback = null;
            this.headerCallback = null;
            this.initComplete = null;
            this.infoCallback = null;
            this.preDrawCallback = null;
            this.createdRow = null;
            this.urls = {
                index_url: "",
                add_url: "",
                edit_url: "",
                del_url: "",
                detail_url: "",
            };
            this.addTableHeaderBtn = [];
            this.tempFunc = {};
        },
        init: function (urls, columns = [], options = {}, tableElement = this.tableElement, customSearchElement = this.customSearchElement) {
            // 重新初始化TableTools参数
            this.reset();

            this.tableElement = tableElement;
            this.customSearchElement = customSearchElement;
            // https://datatables.net/reference/option/
            //设置错误模式:throw抛出异常、alert弹出警告、none不做任何处理
            $.fn.dataTable.ext.errMode = this.errMode || "none";

            this.tempFunc = {};

            this.urls = $.extend(true, {}, this.urls, urls);
            this.defaults.ajax.url = this.urls.index_url;

            this.options = $.extend(true, {}, this.defaults, options, {
                "language": this.language,
                // ----------------
                // 数据表 列
                // ----------------
                // https://datatables.net/reference/option/columnDefs
                // columnDefs: [
                //     {
                //         targets: 0,
                //         visible: false,
                //         ...
                //     }
                // ],
                "columns": columns,

            });

            return this;
        },
        drawTable: function () {
            var _this = this;

            //DataTable对象
            this.tableObj = $(this.tableElement).DataTable(this.options);

            // initOnClick
            $(this.tableElement + " tbody").on("click", "tr", function () {
                var row = _this.tableObj.row(this);
                var data = row.data();
                clearTimeout(_this.dataTableTimes);
                // 选中行后会有一定的延时才能给选中行加上 .selected 类,所以需要使用 setTimeout
                _this.dataTableTimes = setTimeout(function () {
                    // 选中了行

                    // 处理单行/多行 选中触发的数据
                    var selectedRows = _this.tableObj.rows(".selected").data();
                    var rowsData = [];
                    if (selectedRows.length > 0) {
                        // 移除 .date-table-multi-select-btn 元素上的 disabled 属性 和disabled类
                        $(".date-table-multi-select-btn").removeAttr("disabled").removeClass("disabled");
                        // 遍历选中数据
                        selectedRows.each(function (item, index) {
                            rowsData.push(item);
                        });
                        TableTools.multi_select_data = rowsData;
                    } else {
                        TableTools.multi_select_data = [];
                        $(".date-table-multi-select-btn").attr("disabled", "disabled").addClass("disabled");
                    }

                    // 触发自定义的全局点击行事件
                    (typeof (_this.onClick) == "function") && _this.onClick(data, row);
                }, 300);
            });
            // initOnDoubleClick
            $(this.tableElement + " tbody").on("dblclick", "tr", function () {
                clearTimeout(_this.dataTableTimes);
                var row = _this.tableObj.row(this);
                var data = row.data();
                (typeof (_this.onDoubleClick) == "function") && _this.onDoubleClick(data, row);
            });

            // 点击展开详情
            // Array to track the ids of the details displayed rows

            $(this.tableElement + " tbody").on("click", "tr td.dt-control", function () {
                if (typeof (_this.showMore) != "function") {
                    return;
                }
                var tr = $(this).closest("tr");
                var row = _this.tableObj.row(tr);
                var idx = _this.detailRows.indexOf(tr.attr("id"));

                if (row.child.isShown()) {
                    tr.removeClass("details");
                    row.child.hide();

                    // Remove from the 'open' array
                    _this.detailRows.splice(idx, 1);
                } else {
                    tr.addClass("details");
                    row.child(_this.showMore(row.data(), row, tr, tr.attr("id"))).show();

                    // Add to the 'open' array
                    if (idx === - 1) {
                        _this.detailRows.push(tr.attr("id"));
                    }
                }
            });

            // 重新设置排序字段
            _this.tableObj.on("order.dt", function (e, settings, columns) {
                // 当用户点击排序按钮时触发
            });

            // On each draw, loop over the `detailRows` array and show any child rows
            _this.tableObj.on("draw", function (a, b, c) {
                _this.detailRows.forEach(function (id, i) {
                    $("#" + id + " td.dt-control").trigger("click");
                });
            });

            /**
             * 点击展开/关闭子数据
             * 封装点击查看详情（额外子行）操作
             * 列格式：
             *           {
             *                 className: 'dt-control', // 固定格式，设置绑定对象
             *                 orderable: false,
             *                 data: null,
             *                 defaultContent: '', // 默认文本
             *                 // 固定使用 show_detail 作为回调函数
             *                 show_detail: function (row, index, node,table) {
             *                     console.log('callbakack',row, index, node,table)
             *                     return '根据行数据row自定义封装返回html';
             *                 }
             *             }
             */
            $(this.tableElement + " tbody").on("click", "td.dt-control", function (e) {
                var table = Table.tableObj;
                // var row = table.row(this);
                // 或
                var tr = $(this).closest("tr");
                var row = table.row(tr);
                var data = row.data();

                var rowIndex = e.target._DT_CellIndex.row; // 点击了第几行（从0开始）
                var columnIndex = e.target._DT_CellIndex.column; // 点击了第几列（从0开始）

                if (row.child.isShown()) {
                    row.child.hide();
                } else {
                    let detailContent = "<div class=\"table_row_detail\">no data</div>";
                    // 判断 是否定义了 row.context[0].aoColumns[columnIndex].show_detail，并且是函数
                    if (row.context[0].aoColumns[columnIndex].show_detail && typeof row.context[0].aoColumns[columnIndex].show_detail === "function") {
                        detailContent = "<div class=\"table_row_detail\">" + row.context[0].aoColumns[columnIndex].show_detail(data, rowIndex, row, table) + "</div>";
                    }
                    row.child(detailContent).show();
                }
            });

            // 切换tab时自动调整表格,避免错位或者因为 某个tab从未激活变为激活时候样式展示异常
            $("[data-bs-toggle=\"tab\"],[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
                // 调整列
                DataTable.tables({visible: true, api: true}).columns.adjust();
            });


            // 搜索
            // init  custom-datatable-search
            // $(this.customSearchElement).on("submit", function (e) {
            //     // 搜索
            //     e.preventDefault();
            //     (typeof (_this.onSearch) == "function") && _this.onSearch(e);
            //     _this.tableObj.draw();
            // });

            // 添加搜索表单
            CommonSearch.clearSearchForm();
            this.options.columns.forEach(function (item, index) {
                if (item.search_type) {
                    CommonSearch.createSearchFormItem(item);
                }
            });

            CommonSearch.writeSearchForm(TableTools.getTableElement(), TableTools.getCustomSearchElement(), TableTools.getObj(), this.options.show_custom_search_form);

            // 添加一个 按钮放置区域
            $(TableTools.getTableElement()).parents().eq(0).before("<div class=\"table-top-buttons\" style='display: none;'></div>");
            // let btnBoxArea = $(TableTools.getTableElement()).parents().eq(1).find('.table-top-buttons');
            let btnBoxArea = $(TableTools.getTableElement()).parents().eq(0).siblings(".table-top-buttons");

            // 添加刷新按钮
            if ((this.options.showRefreshBtn === undefined || this.options.showRefreshBtn) && this.urls.index_url && this.urls.index_url.length > 0) {
                var refreshBtn = "<button type=\"button\"  class=\"btn btn-info m-1 btn-xs date-table-tools-refresh-btn\"><i class=\"ti ti-refresh fs-14\"></i>&nbsp;刷新</button>";
                btnBoxArea.append(refreshBtn);
            }
            // 添加创建按钮
            if (this.urls.add_url && this.urls.add_url.length > 0) {
                var addBtn = "<button type=\"button\" data-url=\"" + this.urls.add_url + "\"  data-title=\"添加\" class=\"btn btn-success m-1 btn-xs date-table-tools-plus-btn\"><i class=\"ti ti-plus fs-14\"></i>&nbsp;添加</button>";
                btnBoxArea.append(addBtn);
            }
            // 附加表头按钮
            if (this.options.addTableHeaderBtn && this.options.addTableHeaderBtn.length > 0) {
                var headerBtnHtml = TableTools.createButtonList(this.options.addTableHeaderBtn);
                btnBoxArea.append(headerBtnHtml);
            }
            // 添加自定义搜索按钮
            if (this.options.show_custom_search) {
                var searchBtn = "<button type=\"button\"  class=\"btn btn-primary m-1 border-0 btn-xs date-table-tools-search-btn float-right\"><i class=\"ti ti-search fs-14\"></i>&nbsp;搜索</button>";
                btnBoxArea.append(searchBtn);
            }

            // 点击自定义搜索按钮
            $(".date-table-tools-search-btn").on("click", function () {
                // console.log('点击了搜索按钮');
                // 判断 .custom-datatable-search 是否有 .show ,如果有则移除并添加上.hidden,否则添加上.show并移除.hidden
                if ($(".custom-datatable-search").hasClass("show")) {
                    $(".custom-datatable-search").removeClass("show").addClass("hidden");
                } else {
                    $(".custom-datatable-search").removeClass("hidden").addClass("show");
                }
            });


            return this.tableObj;
        },
        // 把自定义函数挂载到 TableTools.getObj().tempFunc 上
        // func_name: 函数名称,可以是一个任意字符串
        // func: 函数体
        // args: 函数参数
        addCustomFunc: function (func_name, func, args) {
            this.tempFunc[func_name] = {fun: func, args: args};
        },
        // 调用自定义函数,一般情况下不需要传入 data参数
        runCustomFunc: function (func_name = "", data = null) {
            var func = this.tempFunc[func_name];
            func && func.fun && func.fun(data ?? func.args);
        },
        getTableElement: function () {
            return this.tableElement;
        },
        getCustomSearchElement: function () {
            return this.customSearchElement;
        },
        getObj: function () {
            return this;
        },
        tools: {
            status: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "type": "status",
                    // 'options':'{"0":{"text":"禁用","class":"danger"},"1":{"text":"正常","class":"info""}}', // 可以缺少 class
                    // 'field':'status',
                    // 'data':obj,
                }, opts);
                // text-* 的样式，例如 text-primary
                var colorArr = ["primary", "secondary", "success", "info", "danger", "warning", "purple", "dark", "light", "body","body-secondary","white"];
                // 根据 opts.options 中的值来取模获取colorArr中的颜色值
                var color = "success";

                var text = "none";
                if (config.data && (config.data).hasOwnProperty(config.field) && config.options) {
                    // 判断 config.options 是否为字符串,则转为对象
                    if (typeof config.options === "string") {
                        config.options = eval("(" + config.options + ")");
                    }
                    var value = config.data[config.field];
                    text = config.options[value] ? config.options[value].text : value;
                    color = colorArr[value % colorArr.length] || "primary";
                    color = (config.options[value] && config.options[value].class) ? config.options[value].class : color;
                }
                return "<span class=\"text-" + color + "\"><i class=\"ti ti-circle-filled fs-14\"></i>&nbsp;" + text + "</span>";
            },
            // badge 显示标签，badge-*可用类型：eg:(badge badge-outline-success)
            // default,
            // outline-(dark,light,purple,danger,warning,info,success,secondary,primary)
            // soft-dark,soft-light,soft-purple,soft-danger,soft-warning,soft-info,soft-success,soft-secondary,soft-primary
            label: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "label",
                    "type": "label",
                    "class_type": "plain",// plain,primary,success,info,warning,danger
                }, opts);
                return "<span class=\"badge badge-" + config.class_type + "\">" + (config.text ? config.text : "none") + "</span>";
            },
            icon: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "type": "icon",
                    "text": "", // 「可选」opts配置需要显示的文字
                    "class_type": "planet",// ti 的字体图标
                }, opts);
                //渲染 tabler.io 图标
                return "<i class=\"ti ti-" + config.class_type + "\"></i>&nbsp;" + config.text;
            },
            sub_str: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "",
                    "type": "sub_str",
                    "start": 0, // 开始位置
                    "length": 20, // 截取长度
                }, opts);
                var value = config.text || "";
                return "<span data-tips title=\"" + value + "\">" + value.substring(config.start, config.length) + "</span>";
            },
            datetime: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "-",
                    "type": "datetime",
                    "format": "YYYY-MM-DD HH:mm:ss",
                }, opts);
                if (isNaN(config.text)) {
                    return config.text ? moment(config.text).format(config.format) : "空";
                } else {
                    return config.text ? moment(parseInt(config.text) * 1000).format(config.format) : "空";
                }
            },
            toggle: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "open_value": 1, // 1:开启,其他:关闭
                    "type": "toggle",
                    // "field": "字段名称",
                    // 'data':obj,
                }, opts);
                var toggle_status = "off";
                if (config.data && config.data[config.field]) {
                    toggle_status = (config.data[config.field] == config.open_value) ? "on" : "off";
                }

                // ti 方式一
                // return '<div class="form-check form-check-'+(toggle_status == "on" ?'success':'secondary')+' form-switch mb-2"><input type="checkbox" disabled class="form-check-input" '+(toggle_status == "on"?'checked': '')+'></div>';
                // ti 方式二
                return "<a href='javascript:;' data-toggle='tooltip' class='btn-change' ><i class='ti " + (toggle_status == "on" ? "ti-toggle-right-filled " : "ti-toggle-left-filled ") + (toggle_status == "on" ? "text-success" : "text-danger") + " fs-28'></i></a>";
            },
            image: function (opts = {}) {
                const config = $.extend(true, {}, {
                    "type": "image",
                    // "value": "图片字段的值",
                    "class": "img-sm img-center",// 自定义的样式
                }, opts);
                // 加载失败的默认图片
                let imgUrl = "/static/images/system/load_error.jpg";

                if (config.value && !myTools.func.isEmpty(config.value)) {
                    imgUrl = config.value;
                }
                return "<a href=\"" + imgUrl + "\" target=\"_blank\"><img class=\"" + config.class + "\" src=\"" + imgUrl + "\" /></a>";
            },
            input: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "",
                    "type": "input",
                    "event_type": "text",
                }, opts);
                return "<input type=\"" + config.event_type + "\" placeholder='' class=\"form-control\" value=\"" + (config.text ? config.text : "") + "\">";
            },
            url: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "链接",
                    "type": "url",
                }, opts);
                return "<div class=\"input-group input-group-sm\" style=\"width:250px;margin:0 auto;\"><input type=\"text\" class=\"form-control input-sm\" value=\"" + config.text + "\"><span class=\"input-group-btn input-group-sm\"><a href=\"" + config.text + "\" target=\"_blank\" class=\"btn btn-default btn-sm\" style='height: 32px;border-radius: 0;'><i class=\"ti ti-link fs-16\"></i></a></span></div>";
            },
            ip: function (opts = {}) {
                return "<a class=\"btn btn-xs btn-ip badge badge-outline-primary\"><i class=\"ti ti-map-pin fs-14\"></i>&nbsp;" + opts.text + "</a>";
            },
            // type: default,primary,success,info,warning,danger,link
            btn: function (opts = {}) {
                var config = $.extend(true, {}, {
                    "text": "操作", // 按钮文字
                    "title": "标题", // 操作标题
                    "class_type": "primary",// bootstrap 按钮的样式类型，不需要带 btn-前缀，eg:btn-primary
                    "type": "btn",
                    "icon": "", // ti 按钮小图标 ,例如 ti ti-pencil
                    "event_type": "layer_open", //callback:自定义回调操作, multi_select:选中表单行/多行后激活按钮,layer_open:弹出框打开url,confirm_open:对话操作,jump_url:跳转url,tips:仅提示
                    "url_name": "",// index_url,add_url,edit_url,del_url,detail_url 等在urls里面定义的url名称
                    "url_params": "{}", // 替换url的字符串参数
                    "options": "{\"maxmin\":false}",
                }, opts);

                // table 选中单行/多行后激活按钮
                if (config.event_type == "multi_select" && typeof config.callback == "function") {
                    // 点击自定义回调操作
                    var callback_name = "callback_" + Math.random().toString(36).substr(2) + (new Date()).getTime();
                    TableTools.getObj().addCustomFunc(callback_name, config.callback, null);
                    // 把 callback_name 传递给按钮,点击后调用 TableTools.getObj().tempFunc[callback_name] 方法
                    return "<button type=\"button\" disabled class=\"btn btn-xs m-1 disabled btn-" + config.class_type + " date-table-multi-select-btn \" data-callback=" + callback_name + ">" + (config.icon ? "<i class=\"" + config.icon + "\"></i>&nbsp;" : "") + (config.text ? config.text : "BTN") + "</button>";
                }

                if (config.event_type == "callback" && typeof config.callback == "function") {
                    // 点击自定义回调操作
                    var callback_name = "callback_" + Math.random().toString(36).substr(2) + (new Date()).getTime();
                    TableTools.getObj().addCustomFunc(callback_name, config.callback, config.data);
                    // 把 callback_name 传递给按钮,点击后调用 TableTools.getObj().tempFunc[callback_name] 方法
                    return "<button type=\"button\" class=\"btn btn-xs m-1 btn-" + config.class_type + " date-table-callback-btn \" data-callback=" + callback_name + ">" + (config.icon ? "<i class=\"" + config.icon + "\"></i>&nbsp;" : "") + (config.text ? config.text : "BTN") + "</button>";
                }

                var ext_class = "";
                switch (config.event_type) {
                    case "layer_open":
                        ext_class = "date-table-open-iframe-btn";
                        break;
                    case "jump_url":
                        ext_class = "date-table-jump-url-btn";
                        break;
                    case "confirm_open":
                        ext_class = "date-table-confirm-btn";
                        break;
                    case "tips":
                        ext_class = "date-table-tips-btn";
                        break;
                }

                // 如果 config.url_params 为字符串,则转为对象
                if (typeof config.url_params === "string") {
                    config.url_params = eval("(" + config.url_params + ")");
                } else {
                    config.url_params = {};
                }
                // 未设置或者无权限
                if (myTools.func.isEmpty(config.url_name) || myTools.func.isEmpty(TableTools.getObj().urls[config.url_name])) {
                    config.url_name = "";
                }
                var url = !myTools.func.isEmpty(config.url_name) ? myTools.func.replaceString(TableTools.getObj().urls[config.url_name], config.url_params) : "javascript:;";
                return "<button type=\"button\" data-url=\"" + url + "\" data-title=\"" + config.title + "\" data-options='" + (config.options || "{}") + "' class=\"btn btn-xs btn-" + config.class_type +
                    " " + ext_class + "\">" + (config.icon ? "<i class=\"" + config.icon + "\"></i>&nbsp;" : "") + (config.text ? config.text : "BTN") + "</button>";
            },
        },
        // 创建按钮列表
        createButtonList: function (buttons = {}) {
            let btnsHtml = "";
            // forEach 遍历 buttons 里面的 type属性,如果 tools 里面有对应的方法,则调用对应的方法
            buttons.forEach(function (item) {
                if (item.type && TableTools.tools[item.type]) {
                    btnsHtml += TableTools.tools[item.type](item);
                } else {
                    btnsHtml += TableTools.tools.label(item);
                }
            });
            return btnsHtml;
        },
        /**
         * json 对象转url参数
         * @param obj
         * @param parentKey
         * @returns {string}
         */
        jsonToUrl: function (obj, parentKey = null) {
            return Object.entries(obj)
                .map(([key, value]) => {
                    const currentKey = parentKey ? `${parentKey}[${key}]` : key;

                    if (value !== null && typeof value === "object") {
                        if (Array.isArray(value)) {
                            // Handle array elements with the same key
                            return value.map((item, index) => `${currentKey}[${index}]=${encodeURIComponent(item)}`).join("&");
                        } else {
                            // Recursive call for nested objects
                            return TableTools.jsonToUrl(value, currentKey);
                        }
                    } else {
                        // Handle other data types
                        return `${currentKey}=${encodeURIComponent(value)}`;
                    }
                }).join("&");
        },
        events: {
            bindClickEvent: function () {
                // 先解绑点击事件
                $(".date-table-open-iframe-btn").off("click");
                $(".date-table-confirm-btn").off("click");
                $(".date-table-tips-btn").off("click");
                $(".date-table-jump-url-btn").off("click");
                $(".date-table-multi-select-btn").off("click");
                $(".date-table-callback-btn").off("click");
                $(".date-table-tools-plus-btn").off("click");
                $(".date-table-tools-refresh-btn").off("click");

                // 单选/多选行触发事件
                $(".date-table-multi-select-btn").on("click", function (e) {
                    e.preventDefault();
                    var callback = $(this).data("callback");
                    // 单选/多选行 对应的数据
                    TableTools.getObj().runCustomFunc(callback, TableTools.multi_select_data);
                });
                $(".date-table-callback-btn").on("click", function (e) {
                    e.preventDefault();
                    var callback = $(this).data("callback");
                    TableTools.getObj().runCustomFunc(callback);
                });
                $(".date-table-tools-refresh-btn").on("click", function (e) {
                    e.preventDefault();
                    // 刷新当前页面
                    TableTools.getObj().tableObj.draw();
                });
                $(".date-table-tools-plus-btn").on("click", function (e) {
                    e.preventDefault();
                    var url = $(this).data("url");
                    var title = $(this).data("title");
                    var options = $(this).data("options") || {"maxmin": false};

                    // Iframe弹窗 并处理iframe 完全加载完成事件：
                    let eventModal = Modal.iframe(title, url, '80%', '80%');

                    // eventModal.onIframeComplete((_modal) => {
                    //     console.log('complete 第一个事件',_modal);
                    // }).onIframeComplete((_modal) => {
                    //     console.log('complete 第二个事件',_modal);
                    // });
                });
                $(".date-table-open-iframe-btn").on("click", function (e) {
                    e.preventDefault();
                    var url = $(this).data("url");
                    var title = $(this).data("title");
                    var options = $(this).data("options") || "{}";

                    // Iframe弹窗 并处理iframe 完全加载完成事件：
                    let eventModal = Modal.iframe(title, url, '80%', '80%');

                    // eventModal.onIframeComplete((_modal) => {
                    //     console.log('complete 第一个事件',_modal);
                    // }).onIframeComplete((_modal) => {
                    //     console.log('complete 第二个事件',_modal);
                    // });
                });
                $(".date-table-jump-url-btn").on("click", function (e) {
                    e.preventDefault();
                    var url = $(this).data("url");
                    // 新窗口打开url
                    window.open(url);
                });
                $(".date-table-confirm-btn").on("click", function (e) {
                    e.preventDefault();
                    var _this = this;
                    var url = $(this).data("url");
                    var title = $(this).data("title") || "确认进行此操作?";
                    var options = $(this).data("options") || "{}";

                    var top = $(this).offset().top - $(window).scrollTop();
                    var left = $(this).offset().left - $(window).scrollLeft() - 260;
                    if (top + 154 > $(window).height()) {
                        top = top - 154;
                    }
                    if ($(window).width() < 480) {
                        top = left = undefined;
                    }

                    new Modal({
                        title: '温馨提示',
                        content: title,
                        theme: 'primary',
                        buttons: [
                            { text: '取消', type: 'secondary' },
                            { text: '确定', type: 'primary', click: function(e, modal) {
                                    myTools.http.post(url, {}).then(function (res) {
                                        myTools.msg(res.message || "已请求操作");
                                        res.wait = res.wait ? res.wait : 3;
                                        if (typeof (res.url) != "undefined") {
                                            setTimeout(function () {
                                                //跳转
                                                window.location.href = res.url;
                                            }, res.wait * 1000);
                                        }
                                    }).catch(function (err) {
                                    });
                            }}
                        ],
                        offset: [top, left],
                        buttonsAlign: 'right',
                        showActionIcons:false
                    }).open();
                });
                $(".date-table-tips-btn").on("click", function (e) {
                    e.preventDefault();
                    var title = $(this).data("title") || "提示信息";
                    var top = $(this).offset().top - $(window).scrollTop();
                    var left = $(this).offset().left - $(window).scrollLeft() - 260;
                    if (top + 154 > $(window).height()) {
                        top = top - 154;
                    }
                    if ($(window).width() < 480) {
                        top = left = undefined;
                    }
                    new Modal({
                        title: '',
                        content: title,
                        theme: 'dark',
                        offset: [top, left]
                    }).open();
                });
            }
        }
    };
    window.TableTools = TableTools;
    var CommonSearch = {
        commonSearchFormHtml: "",
        clearSearchForm: function () {
            this.commonSearchFormHtml = "";
        },
        writeSearchForm: function (tableElement, customSearchElement, obj, show_custom_search_form = false) {
            if (this.commonSearchFormHtml.length < 1) {
                return;
            }
            this.commonSearchFormHtml += this.submitBtn();
            let show_custom_search_form_str = show_custom_search_form ? "show" : "hidden";
            var searchFormHtml = "<form class='form-inline unbind-form row custom-datatable-search " + show_custom_search_form_str + "' autocomplete=\"off\">" +
                "<div class='col-lg-12'>" +
                "<div class='ibox'>" +
                "<div class='ibox-content pb-4'>" +
                "<div class='row'>" +
                this.commonSearchFormHtml +
                "</div>" +
                "</div>" +
                "</div>" +
                "</div>" +
                "</form>";

            // 把 searchFormHtml 写入到 tableElement的父级的父级的父级 之前
            $(tableElement).parents().eq(0).before(searchFormHtml);

            // 绑定搜索事件
            this.bind.submit(obj);
            // 绑定时间事件
            this.bind.dataTimeEvent();
        },
        createSearchFormItem: function (item) {
            var itemHtml = "";
            switch (item.search_type) {
                case "select":
                    itemHtml = this.select(item);
                    break;
                case "text":
                    itemHtml = this.text(item);
                    break;
                case "datetimerange":
                    itemHtml = this.datatime(item, "datetimerange");
                    break;
                case "daterange":
                    itemHtml = this.datatime(item, "daterange");
                    break;

                case "date":
                    itemHtml = this.datatime(item, "date");
                    break;
                case "datetime":
                    itemHtml = this.datatime(item, "datetime");
                    break;
                case "between":
                    itemHtml = this.between(item);
                    break;
                default:
                    break;
            }
            this.commonSearchFormHtml += itemHtml;
            return itemHtml;
        },
        bind: {
            submit: function (obj) {
                $(obj.getCustomSearchElement()).on("submit", function (e) {
                    // 搜索
                    e.preventDefault();
                    (typeof (obj.onSearch) == "function") && obj.onSearch(e);
                    obj.tableObj.draw();
                });
            },
            dataTimeEvent: function () {
                // 时间段
                $(".datetimerange").daterangepicker({
                    showDropdowns: false, //年月份下拉框
                    autoUpdateInput: true, //关闭自动赋值，使初始值为空
                    opens: "center",//日期选择框的弹出位置
                    timePicker: true,//显示时分时间选择
                    timePicker24Hour: true,//设置小时为24小时制
                    timePickerSeconds: true, //时间显示到秒
                    showWeekNumbers: false, // 显示周数
                    defaultFill: false, // 是否默认填充时间
                    // timePickerIncrement: 30,
                    locale: {
                        format: "YYYY-MM-DD HH:mm:ss",
                        applyLabel: "确定",
                        cancelLabel: "取消",
                        fromLabel: "从",
                        separator: " ~ ",
                        toLabel: "到",
                        weekLabel: "周",
                        customRangeLabel: "自定义",
                        daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                        monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        firstDay: 1,
                        meridiem: ["上午", "下午"],
                        today: "今天",
                    }
                }).on("cancel.daterangepicker", function (ev, picker) {
                    // 在这里可以执行清空操作
                    $(this).val("");
                });
                // 时间段
                $(".daterange").daterangepicker({
                    showDropdowns: false, //年月份下拉框
                    autoUpdateInput: true, //关闭自动赋值，使初始值为空
                    opens: "center",//日期选择框的弹出位置
                    showWeekNumbers: false, // 显示周数
                    defaultFill: false, // 是否默认填充时间
                    // timePickerIncrement: 30,
                    locale: {
                        format: "YYYY-MM-DD",
                        applyLabel: "确定",
                        cancelLabel: "取消",
                        fromLabel: "从",
                        separator: " ~ ",
                        toLabel: "到",
                        weekLabel: "周",
                        customRangeLabel: "自定义",
                        daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                        monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        firstDay: 1,
                        meridiem: ["上午", "下午"],
                        today: "今天",
                    }
                }).on("cancel.daterangepicker", function (ev, picker) {
                    // 在这里可以执行清空操作
                    $(this).val("");
                });
                // 时间
                $(".datetime").daterangepicker({
                    singleDatePicker: true, // 单个日期选择
                    showDropdowns: false, //年月份下拉框
                    autoUpdateInput: true, //关闭自动赋值，使初始值为空
                    minYear: 1901, //最小年份
                    timePicker: true,
                    timePicker24Hour: true,//设置小时为24小时制
                    timePickerSeconds: true, //时间显示到秒
                    defaultFill: false, // 是否默认填充时间
                    // timePickerIncrement: 30,
                    locale: {
                        format: "YYYY-MM-DD HH:mm:ss",
                        applyLabel: "确定",
                        cancelLabel: "取消",
                        fromLabel: "从",
                        toLabel: "到",
                        weekLabel: "周",
                        customRangeLabel: "自定义",
                        daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                        monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        firstDay: 1,
                        meridiem: ["上午", "下午"],
                        today: "今天",
                    }
                }).on("cancel.daterangepicker", function (ev, picker) {
                    // 在这里可以执行清空操作
                    $(this).val("");
                });
                // 时间
                $(".date").daterangepicker({
                    singleDatePicker: true, // 单个日期选择
                    showDropdowns: false, //年月份下拉框
                    autoUpdateInput: false, //关闭自动赋值，使初始值为空
                    minYear: 1901, //最小年份
                    defaultFill: false, // 是否默认填充时间
                    timePicker: false,
                    locale: {
                        format: "YYYY-MM-DD",
                        applyLabel: "确定",
                        cancelLabel: "取消",
                        daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                        monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        firstDay: 1,
                        today: "今天",
                    }
                }).on("cancel.daterangepicker", function (ev, picker) {
                    // 在这里可以执行清空操作
                    $(this).val("");
                });
            }
        },
        text: function (item) {
            let defVal = decodeURIComponent(myTools.func.urlQuery(item.data) || '') || '';
            return "<div class='form-group col-xs-12 col-sm-6 col-md-4 col-lg-3'>" +
                "<label class='col-md-4 col-form-label p-0'>" + item.title + "</label>" +
                "<div class='col-md-12 p-0'>" +
                "<input type='text' class='form-control w-100' name='" + item.data + "' value='"+ defVal +"' placeholder='" + item.title + "'>" +
                "</div>" +
                "</div>";
        },
        select: function (item) {
            let defVal = decodeURIComponent(myTools.func.urlQuery(item.data) || '') || '';
            var html = "";
            if (item.search_options) {
                html = "<div class='form-group col-xs-12 col-sm-6 col-md-4 col-lg-3'>" +
                    "<label class='col-md-4 col-form-label p-0'>" + item.title + "</label>" +
                    "<div class='col-md-12 p-0'>" +
                    "<select class='form-control w-100' name='" + item.data + "'>" +
                    "<option value=''>请选择</option>";

                for (var key in item.search_options) {
                    html += "<option value='" + key + "' "+(!!defVal && (key == defVal) ? 'selected':'')+">" + item.search_options[key] + "</option>";
                }
                html += "</select></div></div>";
            }
            return html;
        },
        datatime: function (item, type = "datetime") {
            return "<div class='form-group col-xs-12 col-sm-6 col-md-4 col-lg-3'>" +
                "<label class='col-md-4 col-form-label p-0'>" + item.title + "</label>" +
                "<div class='col-md-12 p-0'>" +
                "<input type='text' class='form-control w-100 " + type + "' name='" + item.data + "' placeholder='" + item.title + "'>" +
                "</div>" +
                "</div>";
        },
        between: function (item) {
            return "<div class='form-group col-xs-12 col-sm-6 col-md-4 col-lg-3'>" +
                "<label class='col-md-4 col-form-label p-0'>" + item.title + "</label>" +
                "<div class='col-md-12 p-0'>" +
                "<div class='row row-between'>" +
                "<div class='col-sm-6 pr-1'>" +
                "<input type='text' class='form-control w-100' name='" + item.data + "[]' value='' placeholder='" + item.title + "' id='" + item.data + "-min'>" +
                "</div>" +
                "<div class='col-sm-6 pl-1'>" +
                "<input type='text' class='form-control w-100' name='" + item.data + "[]' value='' placeholder='" + item.title + "' id='" + item.data + "-max'>" +
                "</div>" +
                "</div>" +
                "</div>" +
                "</div>";
        },
        submitBtn: function () {
            return "<div class='form-group col-xs-12 col-sm-6 col-md-4 col-lg-3'>" +
                "<label class='col-md-4 col-form-label p-0'></label>" +
                "<div class='col-md-8 col-12 p-0'>" +
                "<button type='submit' class='btn btn-sm btn-success mr-2'>搜索</button>" +
                "<button type='reset' class='btn btn-sm btn-default'>重置</button>" +
                "</div>" +
                "</div>";
        }
    };
}(jQuery);
