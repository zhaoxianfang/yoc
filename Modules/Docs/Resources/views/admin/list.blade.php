@extends('admin::layouts.admin_layout')
@section('title', "文档管理")

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
                index_url: '/admin/docs/list',
                add_url: "",
                edit_url: "",
                del_url: "",
                detail_url: "/docs/{id}",
            },[
                {
                    "data": "id",
                    "orderable": true, //是否参与排序 Boolean
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
                    "data": "uni_code",
                    "title":"uni_code",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "data": "app_name",
                    "title":"名称",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "data": "app_cover",
                    "title":"封面",
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
                    "data": "description",
                    "title":"描述",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "data": "open_type",
                    "title":"公开类型",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {1:"公开",2:"仅成员可见"}, //下拉搜索筛选项
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"1":{"text":"公开","class":"success"},"2":{"text":"仅成员可见","class":"info"}}',
                                'field':'open_type',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "team_name",
                    "title":"团队名称",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
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
                    "width": "60px", //列宽 String ..px..x%,..em
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'查看',
                                "title":"打开新窗口提示",
                                'type':'btn',
                                "icon": "ti ti-brand-telegram fs-16", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'jump_url',
                                'class_type':'link',
                                'url_name':'detail_url',
                                'url_params':"{id:"+row.id+"}",
                            }
                        ]);

                    }
                }
            ],{
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
