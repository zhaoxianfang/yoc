@extends('admin::layouts.admin_layout')
@section('title', "黑名单ip列表")

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
                index_url: '/admin/users/blacklist',
                add_url: "{{ admin_auth('admin/users/blacklist/create','/admin/users/blacklist/create') }}",
                edit_url: "{{ admin_auth('admin/users/blacklist/update','/admin/users/blacklist/{id}/edit') }}",
                del_url: "{{ admin_auth('admin/users/blacklist/delete','/admin/users/blacklist/{id}/delete') }}",
                show_log: "{{ '/admin/system/logs?title={like_str}' }}", // 查看相关日志
                detail_url: "",
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
                    "data": "ip",
                    "title":"IP",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "data": "remark",
                    "title":"备注",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "orderable": false, //是否参与排序 Boolean
                },
                {
                    "data": "type",
                    "title":"类型",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {1:"黑名单",0:"可疑"}, //下拉搜索筛选项
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"可疑","class":"warning"},"1":{"text":"黑名单","class":"danger"}}',
                                'field':'type',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "visits",
                    "title":"访问次数",
                    "width": "60px", //列宽 String ..px..x%,..em
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "between", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "ban_duration",
                    "title":"封号时长(h)",
                    "width": "70px", //列宽 String ..px..x%,..em
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "ban_deadline",
                    "title":"封号截止时间",
                    "width": "130px", //列宽 String ..px..x%,..em
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "datetimerange", //搜索类型 datetimerange
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'text':data,
                                'type':'datetime',
                            },
                        ]) + ((new Date(data) > new Date())?'&nbsp;<span class="badge-danger p-1">封</span>':'');
                    }
                },
                {
                    "data": "created_at",
                    "title":"创建时间",
                    "width": "110px", //列宽 String ..px..x%,..em
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
                    "data": "updated_at",
                    "title":"最近访问时间",
                    "width": "110px", //列宽 String ..px..x%,..em
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
                                'text':'查看',
                                "title":"打开新窗口提示",
                                'type':'btn',
                                "icon": "ti ti-eye", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'jump_url',
                                'btn_class':'primary',
                                'url_name':'show_log',
                                'url_params':"{like_str:'blacklist->"+row.id+"'}",
                            },{
                                'text':'编辑',
                                "title":"编辑"+row.id,
                                'type':'btn',
                                "icon": "ti ti-pencil",
                                'event_type':'layer_open',
                                'btn_class':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除"+row.id+'吗？',
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
            ],{order:[[6,'desc']]},'#table1');

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
