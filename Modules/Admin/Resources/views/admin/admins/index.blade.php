@extends('admin::layouts.admin_layout')
@section('title', "管理员管理")

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
                index_url: '/admin/system/admins',
                add_url: "{{ admin_auth('system/admins/create','/admin/system/admins/create') }}",
                edit_url: "{{ admin_auth('system/admins/update','/admin/system/admins/{id}/edit') }}",
                del_url: "{{ admin_auth('system/admins/delete','/admin/system/admins/{id}/delete') }}",
                detail_url: "",
            },[
                {
                    "data": "id",
                    "title":"ID",
                    "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean
                    "orderable": true, //是否参与排序 Boolean
                    // "width": "20px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "user.cover",
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
                    "data": "nickname",
                    "title":"昵称",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "mobile",
                    "title":"手机号",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
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
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "id_card",
                    "title":"身份证号",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
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
                    "data": "gender",
                    "title":"性别",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'男',"0":'未设置',"2":'女'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"未设置","class":"danger"},"1":{"text":"男","class":"info"},"2":{"text":"女","class":"warning"}}',
                                'field':'gender',
                                'data':row,
                            }
                        ]);
                    }
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
                                'options':'{"0":{"text":"未激活","class":"muted"},"1":{"text":"正常","class":"info"},"2":{"text":"冻结","class":"danger"}}',
                                'field':'status',
                                'data':row,
                            }
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
                                "title":"编辑["+row.nickname+']',
                                'type':'btn',
                                "icon": "ti ti-pencil", // fa 按钮小图标 ,例如 ti ti-pencil
                                'event_type':'layer_open',
                                'class_type':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除["+row.nickname+']吗？',
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
