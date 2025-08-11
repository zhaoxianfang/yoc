@extends('admin::layouts.admin_layout')
@section('title', "定时任务")

@section('use_datatables', "true")

@section('head_css')

    <style>

    </style>
@endsection

@section('content')
    <div class="middle-box-0 text-left">
        <table id="table1" class="table table-striped table-bordered table-hover" style="min-width: 1350px">
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
                index_url: '/admin/task/cron',
                add_url: "{{ admin_auth('task/cron/create','/admin/task/cron/create') }}",
                edit_url: "{{ admin_auth('task/cron/update','/admin/task/cron/{id}/edit') }}",
                del_url: "{{ admin_auth('task/cron/delete','/admin/task/cron/{id}/delete') }}",
                detail_url: "",
            },[
                {
                    "data": "id",
                    "title":"ID",
                    "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean
                    "orderable": true, //是否参与排序 Boolean
                    "width": "20px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "name",
                    "title":"名称",
                    "width": "120px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "timer",
                    "title":"cron",
                    "width": "100px", //列宽 String ..px..x%,..em
                },
                {
                    "data": "cron_next_run_date",
                    "title":"下一次运行时间",
                    "width": "110px", //列宽 String ..px..x%,..em
                },
                {
                    "data": "type",
                    "title":"类型",
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"model":'模型',"func":'方法',"curl":'HTTP'}, //下拉搜索筛选项
                    "orderable": false, //是否参与排序 Boolean
                    "width": "40px",
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"model":{"text":" 模型","class":"warning"},"func":{"text":" 方法","class":"info"},"curl":{"text":" HTTP","class":"success"}}',
                                'field':'type',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "run_at",
                    "title":"最近运行时间",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "width": "110px",
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
                    "data": "run_status",
                    "title":"最近运行状态",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "width": "80px",
                    "search_options": {"0":'未运行',"1":'成功',"2":'失败'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"未运行","class":"warning"},"1":{"text":"成功","class":"info"},"2":{"text":"失败","class":"danger"}}',
                                'field':'run_status',
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
                    "search_options": {"1":'正常',"2":'停用'}, //下拉搜索筛选项
                    "width": "40px",
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"1":{"text":"正常","class":"info"},"2":{"text":"停用","class":"danger"}}',
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
                    "width": "110px",
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
                    "width": "100px", //列宽 String ..px..x%,..em
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'编辑',
                                "title":"编辑["+row.name+']',
                                'type':'btn',
                                "icon": "fa fa-pencil", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open',
                                'class_type':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除["+row.name+']吗？',
                                'type':'btn',
                                'event_type':'confirm_open',
                                'class_type':'danger',
                                'url_name':'del_url',
                                'url_params':"{id:"+row.id+"}",
                                'data':row,
                            }
                        ]);

                    }
                }
            ],{
                // 固定列
                fixedColumns:   {
                    leftColumns: 0,      // 固定左侧的列数
                    rightColumns: 1      // 固定右侧的列数（如果需要）
                },
                addTableHeaderBtn:[
                    {
                        'text':'cron 配置帮助',
                        'type':'btn',
                        'event_type':'callback',
                        'class_type':'outline btn-primary',
                        'icon':'fa fa-info',
                        'data':'null',
                        'callback':function (data) {
                            Modal.iframe('cron 配置帮助', '/admin/task/cron/cron_help', '80%', '80%');
                        }
                    }
                ]},'#table1');

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
