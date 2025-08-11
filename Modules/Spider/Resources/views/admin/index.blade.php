@extends('admin::layouts.admin_layout')
@section('title', "爬虫列表")

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
                index_url: '/admin/spider/list',
                add_url: "{{ admin_auth('spider/list/create','/admin/spider/list/create') }}",
                edit_url: "{{ admin_auth('spider/list/update','/admin/spider/list/{id}/edit') }}",
                del_url: "{{ admin_auth('spider/list/delete','/admin/spider/list/{id}/delete') }}",
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
                    "data": "name",
                    "title":"名称",
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "timer",
                    "title":"cron",
                    "width": "100px", //列宽 String ..px..x%,..em
                },
                {
                    "data": "next_run_date",
                    "title":"下次执行时间",
                    "width": "110px", //列宽 String ..px..x%,..em
                },
                {
                    "data": "sub_tasks",
                    "title":"子任务",
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'是',"0":'否'}, //下拉搜索筛选项
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'toggle',
                                'open_value':1,
                                'field':'sub_tasks',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "next_tasks",
                    "title":"下一个任务",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        // 判断value值是否有效或是否为null
                        return (!value)?'-':('(id:'+row.next_tasks.id+')'+row.next_tasks.name);
                        // return value.substring(0,20);
                    }
                },
                {
                    "data": "url",
                    "orderable": false, //是否参与排序 Boolean
                    "title":"采集地址",
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
                    "data": "domain_prefix",
                    "orderable": false, //是否参与排序 Boolean
                    "title":"前缀",
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
                    "data": "run_at",
                    "title":"最近运行时间",
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
                    "data": "type",
                    "title":"类型",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'正文',"2":'文章列表',"3":'报刊',"4":'其他'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"1":{"text":"正文"},"2":{"text":"文章列表"},"3":{"text":"报刊","class":"info"},"4":{"text":"其他","class":"success"}}',
                                'field':'type',
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
                                "title":"确认删除["+row.name+']吗？',
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
            ],{
                // 固定列
                fixedColumns:   {
                    leftColumns: 0,      // 固定左侧的列数
                    rightColumns: 1      // 固定右侧的列数（如果需要）
                },
                addTableHeaderBtn:[
                    {
                        'text':'爬虫规则测试',
                        'type':'btn',
                        'event_type':'callback',
                        'class_type':'info',
                        'icon':'ti ti-asterisk',
                        'data':'null',
                        'callback':function (data) {
                            Modal.iframe('爬虫规则测试', '/admin/spider/list/rule_test', '80%', '80%');
                        }
                    },{
                        'text':'cron 配置帮助',
                        'type':'btn',
                        'event_type':'callback',
                        'class_type':'outline btn-primary',
                        'icon':'ti ti-question-mark',
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
