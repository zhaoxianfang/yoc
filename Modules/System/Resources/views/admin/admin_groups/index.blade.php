@extends('admin::layouts.admin_layout')
@section('title', '角色组管理')

@section('use_datatables', "true")

@section('content')
    <div class="row col-12">
        <div class="middle-box-0 text-left">
            <table id="table1" class="table table-striped table-bordered table-hover">
            </table>
        </div>
    </div>
@endsection

@section('page_js')
    <script>

        $(function () {
            //
            Table = TableTools.init({
                index_url: '/admin/system/admin_groups',
                add_url: "{{ admin_auth('system/admin_groups/create','/admin/system/admin_groups/create') }}",
                edit_url: "{{ admin_auth('system/admin_groups/update','/admin/system/admin_groups/{id}/edit') }}",
                del_url: "{{ admin_auth('system/admin_groups/delete','/admin/system/admin_groups/{id}/delete') }}",
                detail_url: "",
            },[
                {
                    // "searchable": false, // 是否参与搜索 Boolean
                    "data": "id",
                    // 设置列标题
                    "title":"ID",
                    "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean
                    "orderable": true, //是否参与排序 Boolean
                    // "width": "20px", //列宽 String ..px..x%,..em
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "group_name",
                    "title":"名称",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "text", //搜索类型 String
                },
                {
                    "data": "expiration_at",
                    "title":"过期时间",
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
                    "data": "status",
                    "title":"状态",
                    "orderable": false, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {"1":'正常',"0":'禁用'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"禁用","class":"danger"},"1":{"text":"正常","class":"plain"}}',
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
                                "title":"编辑["+row.group_name+']',
                                'type':'btn',
                                "icon": "ti ti-pencil", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open',
                                'btn_class':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除["+row.group_name+']吗？',
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
            ],{columnDefs:[{orderable: false,targets: "_all"}],info:false,paging:false},'#table1');

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
        // 提交之后
        function form_after(resp){
        }
    </script>
@endsection
