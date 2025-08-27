@extends('admin::layouts.admin_layout')
@section('title', "用户列表")

@section('use_datatables', "true")

@section('head_css')

    <style>

    </style>
@endsection

@section('content')
    <div class="middle-box-0 text-left">
        <table id="table1" class="table table-striped table-bordered table-hover">
        </table>
    </div>
@endsection

@section('page_js')
    @parent

    <script>
        $(function () {

        });
    </script>
    <script>
        $(function () {
            //
            Table = TableTools.init({
                index_url: '/admin/users/member',
                add_url: "",
                edit_url: "",
                del_url: "",
                detail_url: "",
            },[
                {
                    "data": "id",
                    "orderable": false, //是否参与排序 Boolean
                    // string:无排序效果、无过滤效果
                    // html:删除 HTML 标签进行排序、从过滤字符串中删除 HTML 标签
                    // html-num-fmt:按数字排序、从过滤字符串中删除 HTML 标签
                    // html-num:按数字排序、从过滤字符串中删除 HTML 标签
                    // num:按数字排序、无过滤效果
                    // num-fmt:按数字排序、无过滤效果
                    // date:按时间顺序排序、无过滤效果
                    "type": "string", //数据类型 String
                    "visible": true, //是否可见 Boolean
                    // "width": "20px", //列宽 String ..px..x%,..em
                    // 设置列标题
                    "title":"ID",
                },
                // {
                //     "title":"序号",
                //     "orderable": false, //是否参与排序 Boolean
                //     "render" : function (data, type, row, meta)
                //     {
                //         return meta.row + 1 + meta.settings._iDisplayStart;
                //     }
                // },
                {
                    "data": "cover",
                    "title":"头像",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'image',
                                "value": value,
                            },
                        ]);
                    }
                },
                {
                    "data": "real_name",
                    "title":"姓名",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "nickname",
                    "title":"昵称",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "gender",
                    "title":"性别",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {1:"男",0:"女"}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return data === 1 ? "男" : "女";
                    }
                },
                {
                    "data": "mobile",
                    "title":"手机号",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "mobile_verified_at",
                    "title":"手机号认证时间",
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
                    "data": "email",
                    "title":"邮箱号",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "id_card",
                    "title":"身份证号",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "status",
                    "title":"状态",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'正常',"0":'未激活',"2":'冻结'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"未激活","class":"danger"},"1":{"text":"正常","class":"info"},"2":{"text":"冻结","class":"danger"}}',
                                'field':'status',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "created_at",
                    "title":"创建时间",
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
                    "title":"操作",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'编辑',
                                "title":"编辑:"+row.nickname,
                                'type':'btn',
                                "icon": "ti ti-pencil",
                                'event_type':'layer_open',
                                'class_type':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除"+row.id+'吗？',
                                'type':'btn',
                                "icon": "ti ti-trash",
                                'event_type':'confirm_open',
                                'class_type':'danger',
                                'url_name':'del_url',
                                'url_params':"{id:"+row.id+"}",
                                'data':row,
                            }
                        ]);

                    }
                }
            ],{},'#table1');

            Table.onClick = function (data,row) {
                // console.log('onClick 单击',data,row);
            };
            Table.onDoubleClick = function (data,row) {
                // console.log('onDoubleClick',data,row);
            };
            Table.onSearch = function () {
                // console.log('onSearch');
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
