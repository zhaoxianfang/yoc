@extends('admin::layouts.admin_layer_layout')

@section('head_css')
    <style>
        #pre_rule_res{
            background-color: #fff;
            padding: 6px;
            max-height: calc(100vh - 285px);
            overflow: auto;
            font-size: 16px;
            border: 2px solid #f3f3f4;
        }
        #pre_rule_res .test_pre_item{
            text-indent: 2em;
            background-color: #f3f3f4;
            margin-bottom: 2px;
        }
        #preview_box{
            max-height: 300px;
            overflow: auto!important;
        }
        .wrapper-content{
            padding: 10px!important;
        }
    </style>
@endsection

@section('content')

    <div class="alert alert-success">
        方式一：仅选择一个自带采集网址的任务进行测试. <a class="alert-link" href="javascript:;">不填写采集地址URL</a>.<br>
        方式二：选择一个任务并填写 <a class="alert-link" href="javascript:;">采集地址URL</a>进行测试.<br>
        方式三：不选择任务或没有任务时，仅使用<a class="alert-link" href="javascript:;">采集地址URL</a>和<a class="alert-link" href="javascript:;">爬虫采集规则</a>两个配置项进行测试.<br>
    </div>

    <div class="card card-info">

        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2">测试已有采集任务:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[task_id]" style="border-radius:0px;" >
                        <option value="0">不使用或没有任务</option>
                        @foreach ($tasks as $task)
                            <option value="{{ $task['id'] }}">[{{ $task['id'] }}]: {{ $task['name'] }}</option>
                        @endforeach
                    </select>
                    <span class="form-text m-b-none">提示: 测试任务不会保存采集数据和执行后一个采集任务</span>
                </div>
            </div>

            <div class="hr-line-dashed"></div>

            <div class="form-group row">
                <label for="url" class="control-label col-xs-12 col-sm-2">采集地址URL</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="url" name="row[url]" placeholder="例如: https://example.com" value="" data-rule="" />
                </div>
            </div>
            <div class="form-group row">
                <label for="rule" class="control-label col-xs-12 col-sm-2">爬虫采集规则:<br><code>xpath</code>或<code>css</code>元素选择器</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="rule" name="row[rule]" placeholder="例如: //h2 或者 #content" value="" data-rule="" />
                    <span class="form-text m-b-none">提示: 1、注意<code>/text():仅直接文本节点</code> 和 <code>//text():递归所有子文本</code>在使用上的区别</span>
                    <span class="form-text m-b-none">2、建议把<code>/html/body...</code> 规则替换为 <code>//body...</code>规则</span>
                </div>
            </div>

            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2">采集类型:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[type]" style="border-radius:0px;" >
                        <option value="1" selected>文章详情</option>
                        <option value="2" >文章列表</option>
                    </select>
                </div>
            </div>

            <div class="hr-line-dashed"></div>

            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2">调试模式:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[is_debug]" style="border-radius:0px;" >
                        <option value="debug" selected>调试模式「极力推荐」</option>
                        <option value="0" >补采模式</option>
                    </select>
                    <span class="form-text m-b-none">提示: 一般情况使用<code>调试模式</code>，极少数情况才使用补采模式；<code>补采模式</code>仅针对选择了一个任务的情况下才生效</span>
                </div>
            </div>

            <div class="hr-line-dashed"></div>

            <div class="form-group row">
                <label class="control-label col-xs-12 col-sm-2"></label>
                <div class="col-xs-12 col-sm-10">
                    <button type="submit" class="btn btn-success btn-embossed ">提交测试</button>
                </div>
            </div>

            <div class="hr-line-dashed"></div>

            <div class="form-group row">
                <label class="control-label col-xs-12 col-sm-2">结果预览</label>
                <div class="col-xs-12 col-sm-10">
                    <pre id="preview_box" style="display: none;"></pre>
                    <div id="pre_rule_res" style="display: none;"></div>
                </div>
            </div>

            <div class="form-group hidden layer-footer">
                <div class="col-xs-12 col-sm-12">
                    {{--                    <button type="submit" class="btn btn-success btn-embossed ">确定</button>--}}
                    {{--                    <button type="reset" class="btn btn-default btn-embossed">重置</button>--}}
                </div>
            </div>
        </form>
    </div>

@endsection

@section('page_js')
    <script type="text/javascript">
        $(function () {

        })

        function form_after(res){
            $('#pre_rule_res').empty(); // 清空div
            if(!my.isEmpty(res.data)){
                // 采集到数据
                $('#preview_box').show();
                $('#pre_rule_res').show();
                $.each(res.data.list, function(field, content){
                    $('#pre_rule_res').append('<p class="test_pre_item">字段名/索引:' + field + '</p>');
                    // 判断 content 是否为数组
                    if(Array.isArray(content)){
                        $.each(content, function(index, item){
                            $('#pre_rule_res').append('<div class="test_pre_item">内容' +(index+1)+'=>'+ item + '</div>');
                        });
                    }else{
                        $('#pre_rule_res').append('<div class="test_pre_item">内容:' + content + '</div>');
                    }
                });
                $('#preview_box').html(JSON.stringify(res.data, null, 2));
            }else{
                // 未采集到数据
                $('#pre_rule_res').show();
                $('#preview_box').hide();
                $('#pre_rule_res').append('<p class="test_pre_item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+ ( res.message || '没有采集到数据') +'</p>');
            }
            return false;
        }
    </script>
    <script type="text/javascript">
        $(function () {

        })
        // =========================
        // 添加/删除域名列表 begin
        $(".trash_custom_row_item").off()
        $(".add_custom_row_item").off()
        $('.trash_custom_row_item').click(function(){
            $(this).parent().parent('.custom_row_item').remove();
        });
        $('.add_custom_row_item').click(function(){
            var child = $(".custom_row_item").parent().children(".custom_row_item:first-child").clone(true);
            //清除克隆的数据
            child.find(":input").each(function(i){
                $(this).val("");
            });
            child.find('.trash_custom_row_item').show();
            child.find('.add_custom_row_item').remove();
            child.find('.rule-title').css({ visibility: 'hidden' });
            // 查找当前元素的第一个父级.custom_row_item 之后的第一个.custom_row_divider 元素
            var currentDivider =$(this).closest(".custom_row_item").nextAll(".custom_row_divider").first();
            // 然后把新元素插入到这个元素之前
            $(currentDivider).before(child);

        });
        $('#add_new_field').click(function(){
            var child = $(".custom_row_item").parent().children(".custom_row_item:first-child").clone(true);
            //清除克隆的数据
            child.find(":input").each(function(i){
                $(this).val("");
            });
            child.find('.trash_custom_row_item').show();
            child.find('.add_custom_row_item').show();
            $("#create_custom_row_box").before(child);
            $("#create_custom_row_box").before('<div class="custom_row_divider"></div>');
        });
        // 添加/删除域名列表 end
        // =========================
    </script>
@endsection
