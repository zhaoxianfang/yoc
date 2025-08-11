@extends('admin::layouts.admin')
@section('page_inner_title', "用户列表")

@section('use_datatables', "1")

@section('head_css')

    <style>

    </style>
@endsection

@section('content')
    <div class="middle-box-0 text-center">
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
            var url = "{{ route('admin.users.member.search') }}";
            Table = TableTools.init({
                index_url: url,
                add_url: "",
                edit_url: "/admin/users/member/{id}/edit",
                del_url: "/admin/users/member/{id}/delete",
                detail_url: "",
            },[
                {
                    "class": 'dt-control', // 可展开列面版
                    "orderable": false,
                    "data": null,
                    "defaultContent": ''
                },
                {
                    "searchable": false, // 是否参与搜索 Boolean
                    "data": "userId",
                    "defaultContent": "", //默认内容 String
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
                    "title":"<input type='checkbox' id='checkAll' />全选",
                    // 渲染（处理）数据以在表中使用
                    "render": function (data, type, row, meta)
                    {
                        return "<input type='checkbox' name='checkItem' value=" + data + " />";
                    }
                },
                {
                    "title":"序号",
                    "orderable": false, //是否参与排序 Boolean
                    "render" : function (data, type, row, meta)
                    {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                },
                {
                    "data": "uuid",
                    "title":"用户名称",
                    // search_type: text、select、date、datetime、daterange、datetimerange、between
                    "search_type": "text", //搜索类型 String
                    "orderable": true, //是否参与排序 Boolean
                },
                {
                    "data": "sex",
                    "title":"性别",
                    "orderable": true, //是否参与排序 Boolean
                    "search_type": "select", //搜索类型 下拉
                    "search_options": {1:"男",0:"女"}, //下拉搜索筛选项
                    "render" : function ( data, type, row, meta ) {
                        var conent = data == 1 ? "男" : "女";
                        return conent;
                    }
                },
                { "data": "departmentName","title":"部门名称","search_type": "between" },
                { "data": "blogRemark","title":"创建时间","search_type": "datetimerange" },
                {
                    "title":"操作",
                    "render" : function ( data, type, row, meta )
                    {
                        // console.log(data, type, row, meta);
                        return TableTools.createButtonList([
                            {
                                'text':'自定义',
                                'type':'btn',
                                'event_type':'callback',
                                'class_type':'info',
                                'data':row,
                                'callback':function (data) {
                                    console.log('自定义操作',data);
                                }
                            },{
                                'text':'编辑',
                                "title":"编辑"+row.id,
                                'type':'btn',
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
                                'event_type':'confirm_open',
                                'class_type':'danger',
                                'url_name':'del_url',
                                'url_params':"{id:"+row.id+"}",
                                'data':row,
                            },{
                                'text':'跳转',
                                'type':'url',
                                'class_type':'link',
                                'href':'http://www.baidu.com',
                            },{
                                'text':'http://www.baidu.com',
                                'type':'input',
                                'event_type':'text', // input 的type类型
                            },{
                                'text':'LAB',
                                'type':'label',
                                'class_type':'info',
                            },{
                                'type':'status',
                                'options':'{"0":{"text":"禁用","class":"danger"},"1":{"text":"正常","class":"plain"}}',
                                'field':'status',
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
            Table.showMore = function (data,ele,tr,idx) {
                // console.log('showMore',data,ele,tr,idx);
                return (
                    'nickname: ' +
                    data.nickname +
                    ' ' +
                    data.uuid +
                    '<br>' +
                    'uuid: ' +
                    data.uuid +
                    '<br>' +
                    'The child row can contain any data you wish, including links, images, inner tables etc.'
                );
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


            // TEST
            // my.layer.open('https://www.baidu.com','百度',{maxmin:false,area:['1000px','600px']});
        });

        {{--    setTimeout(function () {--}}
        {{--        // 根据输入的值进行搜索和过滤--}}
        {{--        // 搜索文本框--}}
        {{--        // tableModel.search('搜索点什么').draw();--}}
        {{--        // 调用搜索事件--}}
        {{--        // tableModel.draw();--}}

        {{--        // 销毁datatable--}}
        {{--        // // tableModel.fnDestroy(false);--}}
        {{--    },2000)--}}

    </script>
@endsection
