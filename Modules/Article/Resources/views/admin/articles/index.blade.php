@extends('admin::layouts.admin_layout')
@section('title', "文章管理")

@section('use_datatables', "true")

@section('head_css')
    <style>

    </style>
@endsection

@section('content')
    <div class="middle-box-0 text-left">
        <table id="table1" class="table table-striped table-bordered table-hover" style="min-width: 1500px;">
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
                index_url: '/admin/articles',
                add_url: "",
                edit_url: "{{ admin_auth('articles/update','/admin/articles/update/{id}') }}",
                del_url: "{{ admin_auth('articles/delete','/admin/articles/delete/{id}') }}",
                detail_url: "",
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
                    "data": "title",
                    "title":"标题",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                    "width": "220px", //列宽 String ..px..x%,..em
                },{
                    "data": "author",
                    "title":"发布者",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },{
                    "data": "publish_time",
                    "title":"发布时间",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "width": "120px", //列宽 String ..px..x%,..em
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'datetime',
                            },
                        ]);
                    }
                },{
                    "data": "classify.name",
                    "title":"分类",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },
                {
                    "data": "sort",
                    "title":"排序",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },{
                    "data": "type_text",
                    "title":"文章类型",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },{
                    "data": "source_type_text",
                    "title":"来源类型",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },{
                    "data": "source_url",
                    "title":"来源URL",
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
                    "data": "status",
                    "title":"状态",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"0":'待审',"1":'正常',"2":'不公开',"3":'敏感待审核'}, //下拉搜索筛选项
                    "width": "80px", //列宽 String ..px..x%,..em
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"待审"},"1":{"text":"正常","class":"info"},"2":{"text":"不公开","class":"muted"},"3":{"text":"敏感待审核","class":"danger"}}',
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
                                "title":"编辑",
                                'type':'btn',
                                "icon": "ti ti-pencil", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open',
                                'btn_class':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                'options':'{"area":["80%","80%"]}',
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除吗？",
                                'type':'btn',
                                "icon": "ti ti-trash",
                                'event_type':'confirm_open',
                                'btn_class':'danger',
                                'url_name':'del_url',
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
                }
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
