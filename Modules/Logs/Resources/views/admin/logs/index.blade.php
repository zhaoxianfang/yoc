@extends('admin::layouts.admin_layout')
@section('title', '日志管理')

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
                index_url: '/admin/system/logs',
                add_url: "",
                edit_url: "",
                del_url: "",
                detail_url: "/admin/system/logs/{id}/detail",
            },[
                {
                    "data": "id",
                    "title":"ID",
                    // "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean
                    "orderable": true, //是否参与排序 Boolean
                    // "width": "20px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "user.nickname",
                    "title":"操作人",
                    "width":"80px",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
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
                    "data": "title",
                    "title":"标题",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "url",
                    "orderable": false, //是否参与排序 Boolean
                    "title":"请求地址",
                    "search_type": "text", //搜索类型 String
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
                    "data": "created_at",
                    "title":"操作时间",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "width":"140px",
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
                    "data": "user_agent",
                    "title":"UserAgent",
                    "width":"140px",
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'sub_str',
                            },
                        ]);
                    }
                },
                {
                    "data": "source_ip",
                    "title":"IP",
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "is_crawler",
                    "title":"是否爬虫",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'是',"0":'否'}, //下拉搜索筛选项
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'toggle',
                                'open_value':1,
                                'field':'is_crawler',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "extra.crawler_name",
                    "title":"爬虫名称",
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "title":"操作",
                    "orderable": false, //是否参与排序 Boolean
                    "width": "60px", //列宽 String ..px..x%,..em
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'详情',
                                "title":"查看日志详情",
                                'type':'btn',
                                "icon": "ti ti-info-circle", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open',
                                'btn_class':'info',
                                'url_name':'detail_url',
                                'url_params':"{id:"+row.id+"}",
                                'data':row,
                            }
                        ]);

                    }
                }
            ],{
                order:[[0,'desc']],
                // 固定列
                fixedColumns:   {
                    leftColumns: 0,      // 固定左侧的列数
                    rightColumns: 1      // 固定右侧的列数（如果需要）
                },
            },'#table1');

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
