@extends('admin::layouts.admin_layout')
@section('title', '菜单管理')

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
                index_url: '/admin/system/admin_menus',
                add_url: "{{ admin_auth('system/admin_menus/create','/admin/system/admin_menus/create') }}",
                edit_url: "{{ admin_auth('system/admin_menus/update','/admin/system/admin_menus/{id}/edit') }}",
                del_url: "{{ admin_auth('system/admin_menus/delete','/admin/system/admin_menus/{id}/delete') }}",
                detail_url: "",
            },[
                {
                    // "searchable": false, // 是否参与搜索 Boolean
                    "data": "id",
                    // 设置列标题
                    "title":"ID",
                    "type": "string", //数据类型 String
                    // "visible": true, //是否可见 Boolean
                    "orderable": false, //是否参与排序 Boolean
                    // "width": "20px", //列宽 String ..px..x%,..em
                },
                {
                    "data": "title",
                    "title":"标题",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },
                {
                    "data": "icon",
                    "title":"图标",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return '<span class="fs-20 ' + (!row.ismenu || row.status == 'hidden' ? 'text-muted' : '') + '"><i class="' + value + '"></i></span>';
                    }
                },
                {
                    "data": "name",
                    "title":"名称(url)",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "text", //搜索类型 String
                },
                {
                    "data": "weigh",
                    "orderable": false, //是否参与排序 Boolean
                    "title":"权重",
                },
                {
                    "data": "status",
                    "title":"状态",
                    "orderable": false, //是否参与排序 Boolean
                    // "search_type": "select", //搜索类型 下拉
                    // "search_options": {"1":'正常',"0":'禁用'}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'status',
                                'options':'{"0":{"text":"禁用","class":"danger"},"1":{"text":"正常","class":"info"}}',
                                'field':'status',
                                'data':row,
                            }
                        ]);
                    }
                },
                {
                    "data": "ismenu",
                    "title":"菜单",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function ( value, type, row, meta ) {
                        return TableTools.createButtonList([
                            {
                                'type':'toggle',
                                'open_value':1,
                                'field':'ismenu',
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
                            // {
                            //     'text':'自定义',
                            //     'type':'btn',
                            //     'event_type':'callback',
                            //     'class_type':'info',
                            //     'data':row,
                            //     'callback':function (data) {
                            //         console.log('自定义操作',data);
                            //     }
                            // },
                            {
                                'text':'编辑',
                                "title":"编辑["+row.title+']',
                                'type':'btn',
                                "icon": "fa fa-pencil ti ti-pencil", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'layer_open',
                                'class_type':'info',
                                'url_name':'edit_url',
                                'url_params':"{id:"+row.id+"}",
                                // 'options':'{"area":["1000px","600px"]}',
                                // 'options':'{"area":["100%","100%"],"maxmin":false}',
                                'data':row,
                            },{
                                'text':'删除',
                                "title":"确认删除["+row.title+']吗？',
                                'type':'btn',
                                "icon": "fa fa-trash ti ti-trash", // fa 按钮小图标 ,例如 fa fa-pencil
                                'event_type':'confirm_open',
                                'class_type':'danger',
                                'url_name':'del_url',
                                'url_params':"{id:"+row.id+"}",
                                'data':row,
                            }
                        ]);

                    }
                }
            ],{show_custom_search:false,lengthChange:false,info:false,order:[],paging:false},'#table1');

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
