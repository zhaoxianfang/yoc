@extends('admin::layouts.admin_layer_layout')
@section('use_datepicker', "true")

@section('head_css')
    <link rel="stylesheet" href="{{ asset('static/libs/bootstrap-ztree3/css/bootstrapStyle/bootstrapStyle.css') }}" type="text/css">
@endsection

@section('content')


    <!-- Horizontal Form -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">提示:规则名称请仔细填写，包含大小写的区别</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form id="add-form" class="form-horizontal form-ajax" role="form" data-toggle="validator" method="POST" action="">
            @csrf
            <div class="card-body row">
                <div class="form-group row g-lg-2 g-1">
                    <label for="name" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>父级组:</label>
                    <div class="col-xs-12 col-sm-8">
                        <select class="form-control select2  col-xs-12 col-sm-12" name="row[pid]" style="border-radius:0px;">
                            <option value="0" >超级管理员</option>
                            @foreach ($group_list as $group)
                                <option value="{{ $group['id'] }}">{{ $group['group_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-top border-dashed"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="name" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>管理员组名称:</label>
                    <div class="col-xs-12 col-sm-8">
                        <input type="text" class="form-control" id="name" name="row[group_name]" placeholder="" value="" data-rule="required" />
                    </div>
                </div>
                <div class="border-top border-dashed"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="name" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>过期时间:</label>
                    <div class="col-xs-12 col-sm-8 datepicker-date">
                        <div class="input-group m-b date">
                            <input type="text" class="form-control date" name="row[expiration_at]" value="2099-12-31" data-rule="required">
                            <div class="input-group-append">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                        <span class="form-text m-b-none">提示:授权此管理员组的可用期限,有效期至 选择日期那天的23:59:59</span>
                    </div>
                </div>
                <div class="border-top border-dashed"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="remark" class="control-label col-xs-12 col-sm-2">权限节点:</label>
                    <div class="col-xs-12 col-sm-8">
                        <input type="hidden" name="row[rules]" id="rule_node">
                        <div id="treelist" class="ztree">正在加载中...</div>
                    </div>
                </div>
                <div class="border-top border-dashed"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="remark" class="control-label col-xs-12 col-sm-2">备注:</label>
                    <div class="col-xs-12 col-sm-8">
                        <textarea class="form-control" id="remark" name="row[remark]"></textarea>
                    </div>
                </div>
                <div class="border-top border-dashed"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="content" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                    <div class="col-xs-12 col-sm-8">
                        <label>
                            <input type="radio" name="row[status]" value="1" class="flat-red" checked>
                            启用
                        </label>
                        <label>
                            <input type="radio" name="row[status]" value="0" class="flat-red">
                            停用
                        </label>
                    </div>
                </div>
                <div class="form-group row g-lg-2 g-1">

                </div>

                {{-- 操作按钮 使用 .layer-bottom-btns 元素盒子--}}
                <div class="layer-bottom-btns">
                    <button class="btn btn-light" onclick="parent.postMessage({type: 'close'}, '*')">取消</button>
                    <button class="btn btn-primary" type="submit">提交</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.card -->


@endsection

@section('page_js')
    @parent
    <!-- tree -->
    <script type="text/javascript" src="{{ asset('static/libs/bootstrap-ztree3/js/jquery.ztree.core.js') }}"></script>
    <script type="text/javascript" src="{{ asset('static/libs/bootstrap-ztree3/js/jquery.ztree.excheck.js') }}"></script>
    <script type="text/javascript" src="{{ asset('static/libs/bootstrap-ztree3/js/jquery.ztree.exedit.js') }}"></script>

    <script>
        $(function () {

        });
        // tree
        var setting = {
            view: {
                selectedMulti: false
            },
            check: {
                enable: true,
                chkboxType: { "Y": "p", "N": "s" } // Y:checkbox 被勾选后; N:checkbox 取消勾选后; p:操作会影响父级节点; s:操作会影响子级节点,请注意大小写,默认值为 { "Y": "ps", "N": "ps" }
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            edit: {
                enable: false
            },
            callback:{ //回调函数
                onCheck: zTreeOnCheck
            }
        };
        var nodeArr = new Array();
        var treeObj;

        function zTreeOnCheck(event, treeId, treeNode) {
            nodeArr.length = 0; //清空
            var nodes = treeObj.getCheckedNodes(true);
            for (var i=0, l=nodes.length; i < l; i++) {
                nodeArr.push(nodes[i].id);
            }
            var checknode = nodeArr.join(",");
            //赋值
            $("#rule_node").val(checknode)
        };

        $(document).ready(function(){
            //自定义
            var sysUrl = "{{ url('/admin/system/admin_groups/get_tree') }}";
            $.post(sysUrl,{'_token':$("meta[name=\"csrf-token\"]").attr("content")},function(res){
                if(typeof(res) == 'string') { // json 解析
                    res= JSON.parse(res);
                }
                if(res.status == 1){
                    // var zNodes =createNode(res.data);
                    var zNodes =res.data;
                    treeObj  = $.fn.zTree.init($("#treelist"), setting, zNodes);
                }else{
                    console.log(res.info);
                    // myTools.msg(res.info);
                }
            });
            return false;

        });

        var newCount = 1;
    </script>

    <script type="text/javascript">
        $(function () {
        })
    </script>

@endsection
