@extends('admin::layouts.admin_layer')

@section('head_css')
    @parent
    <link href="{{ asset('static/inspinia/v2.9/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('static/inspinia/v2.9/css/plugins/select2/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        .rule-handle{padding: 0;}
        .select2-container--bootstrap4 .select2-selection--single{
            height: 34px!important;
            padding: 0;
            margin: 0;
        }
        .select2{padding: 0!important;}
        #mytip{position: absolute!important;}
        .extend-rule input{ padding: 0; }
        .extend-rule input::placeholder {
            font-size: 8px; /* 你可以根据需要调整字体大小 */
            color: red;
            line-height: 1.2;
        }
        .extend-rule{ max-width: 50px;margin-left: 0px; }
        select.form-control {padding: 6px 0!important;}
        .rule-xpath{padding: 6px;}
    </style>
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row">
                <label for="name" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>站点名称:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="name" name="row[name]" placeholder="" value="" data-rule="required" />
                </div>
            </div>
            <div class="form-group row">
                <label for="url" class="control-label col-xs-12 col-sm-2">采集网址:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="url" name="row[url]" placeholder="" value="" data-rule="url" />
                    <span class="form-text m-b-none">提示:一般情况下，子任务无需填写(针对文章类型),如果是列表类型的就需要填写</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="domain_prefix" class="control-label col-xs-12 col-sm-2">采集前缀:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="domain_prefix" name="row[domain_prefix]" placeholder="" value="" data-rule="" />
                    <span class="form-text m-b-none">提示:采集前缀都是以<code>/</code>结尾；作用有些跳转链接是以 <code>./</code>或<code>../</code>开头的相对位置链接</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="sub_tasks" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>是否子任务:</label>
                <div class="col-xs-12 col-sm-10">
                    <label>
                        <input type="radio" name="row[sub_tasks]" value="0" class="flat-red"> 否
                    </label>
                    <label>
                        <input type="radio" name="row[sub_tasks]" value="1" class="flat-red" checked> 是
                    </label>
                    <span class="form-text m-b-none">提示: 一般地，子任务是由主任务调用的，例如文章列表会去调度文件详情子任务</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="timer" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>cron时间:</label>
                <div class="col-xs-12 col-sm-10">
                    <input type="text" class="form-control" id="timer" name="row[timer]" placeholder="" value="* * * * *" data-rule="" data-tips="bottom" title="使用下面的cron选择器选择" readonly/>
                    <span class="form-text m-b-none">提示: 5个占位的cron时间规则，例如 <code>7 10 * * 4,0</code>表示每周四和周日的10:07,尽量避开凌晨<code>4:00~4:30</code>执行定时任务</span>
                </div>
            </div>

            @include('task::template.cron_select', ['callback_dom'=>'#timer','analysis_value'=>'','show_label'=>'','disabled'=>'false'])

            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>所属类型:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control select2  col-xs-12 col-sm-12" name="row[type]" style="border-radius:0px;" >
                        <option value="1">文章正文</option>
                        <option value="2" selected>文章列表</option>
                        <option value="3">报刊</option>
                        <option value="4">其他</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="rules" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>采集规则:</label>
                <div class="col-xs-12 col-sm-10">
                    <div class="row custom_row_item" style="margin:0;">
                        <input type="text" class="form-control col-sm-2 rule-title p-0" value="" name="rules[title][]" placeholder="例如:title" autocomplete="off" data-tips="bottom" title="url前缀 别名 例如：测试环境" />
                        <div class="form-control col-sm-2 rule-handle p-0">
                            <select class="form-control col-xs-12 col-sm-12 choose-rule-type" name="rules[field_handle][]" style="border-radius:0;"  >
                                <option value="0" >原格式</option>
                                <option value="1" selected>清洗html</option>
                                <option value="2" >提取时间</option>
                                <option value="3" >纯text文字</option>
                                <option value="4" >正则原格式</option>
                                <option value="5" >正则text</option>
                                <option value="6" data-tips="bottom" title="例如：来源等内容是和时间、发布人等都是放在一个html标签中，需要把来源提取出来...">清洗text并识别kickOut字典</option>
                            </select>
                        </div>
                        <input type="text" class="form-control col-sm-4 rule-xpath p-0" value="" name="rules[xpath][]" placeholder="例如://h2 或 #content" autocomplete="off" data-tips="bottom" title="css元素选择器或者XPath规则" />
                        <div class="col-sm-2 p-0">
                            <button type="button" class="btn btn-success add_custom_row_item" ><i class="fa fa-plus" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-danger trash_custom_row_item" data-key="0" style="display: none;" ><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </div>
                        <div class="extend-rule col-sm-2 row p-0" style="display: none;">
                            <input type="text" class="form-control col-xs-12 col-sm-6 rule-title" value="" name="rules[extend_rule][first][]" placeholder="下标1" autocomplete="off" data-tips="bottom" title="[匹配文字部分]提取正则表达式获取内容的数组下标index,一般填1或者2" />
                            <input type="text" class="form-control col-xs-12 col-sm-6 rule-title" value="" name="rules[extend_rule][href][]" placeholder="下标2" autocomplete="off" data-tips="bottom" title="[可选,匹配href部分]提取正则表达式获取第二内容的数组下标index;例如获取href的数组下标,一般填1或者2" />
                        </div>
                    </div>
                    <div class="custom_row_divider"></div>

                    <div id="create_custom_row_box"></div>
                    <button type="button" class="btn btn-primary btn-xs" id="add_new_field">新增一个采集字段</button>
                    <span class="form-text m-b-none">提示: 1、注意<code>/text():仅直接文本节点</code> 和 <code>//text():递归所有子文本</code>在使用上的区别</span>
                    <span class="form-text m-b-none">2、建议把<code>/html/body...</code> 规则替换为 <code>//body...</code>规则</span>
                    <span class="form-text m-b-none">3、建议标题字段设为<code>清洗html</code>网页内容设为<code>原格式</code>时间相关设为<code>提取时间</code></span>
                    <span class="form-text m-b-none">使用正则匹配时的第一个下标数字匹配<code>文字部分</code>,第二个下标数字匹配<code>href链接部分「可选参数」</code></span>
                    <span class="form-text m-b-none">4、字段名称参照:<code>title</code>,<code>content</code>,<code>summary</code>,<code>author</code>,<code>publish_time</code></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="url_can_repeated" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>重复采集:</label>
                <div class="col-xs-12 col-sm-10">
                    <label>
                        <input type="radio" name="row[url_can_repeated]" value="0" class="flat-red" checked> 不能重复采集
                    </label>
                    <label>
                        <input type="radio" name="row[url_can_repeated]" value="1" class="flat-red"> 能重复采集
                    </label>
                    <span class="form-text m-b-none">提示: 内容会自动变更的网页(例如:文章列表)设置为能重复采集，文章详情等一般不变更的设置为能重复采集</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>采集成功后保存的方式:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control select2  col-xs-12 col-sm-12" name="row[success][save]" style="border-radius:0px;" >
                        <option value="default" selected>默认保存到文章数据</option>
                        <option value="custom">自定义</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2">下一个任务:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control select2  col-xs-12 col-sm-12" name="row[next_tasks_id]" style="border-radius:0px;" >
                        <option value="0">无</option>
                        @foreach ($sub_tasks as $subTask)
                            <option value="{{ $subTask['id'] }}">{{ $subTask['name'] }}</option>
                        @endforeach
                    </select>
                    <span class="form-text m-b-none">提示: 如果此任务是列表采集任务，则可能会用到下一个任务来继续采集文章内容等</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>文章关联的分类:</label>
                <div class="col-xs-12 col-sm-10">
                    <select class="form-control select2  col-xs-12 col-sm-12" name="row[extend][classify_id]" style="border-radius:0px;" >
                        <option value="0">无</option>
                        @foreach ($classify_list as $classify)
                            <option value="{{ $classify['id'] }}">{{ $classify['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="content" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-xs-12 col-sm-10">
                    <label>
                        <input type="radio" name="row[status]" value="1" class="flat-red" checked> 开启
                    </label>
                    <label>
                        <input type="radio" name="row[status]" value="2" class="flat-red"> 关闭
                    </label>
                </div>
            </div>
            <div class="form-group row">
            </div>
            <div class="form-group hidden layer-footer">
                <div class="col-xs-12 col-sm-12">
                    <button type="submit" class="btn btn-success btn-embossed ">确定</button>
                    <button type="reset" class="btn btn-default btn-embossed">重置</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('page_js')
    @parent
    <!-- Select2 -->
    <script src="{{ asset('static/inspinia/v2.9/js/plugins/select2/select2.full.min.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            //Initialize
            $(".select2").select2({
                theme: 'bootstrap4',
                placeholder: "请选择",
                allowClear: false
            });
        })
    </script>
    <script type="text/javascript">
        $(function () {
            $('.choose-rule-type').change( function() {
                var extend_rule = $(this).closest('.custom_row_item').find(".extend-rule");
                if([4,5,'4','5'].includes($(this).val())){
                    // 选中了正则表达式
                    extend_rule.show();
                }else{
                    extend_rule.hide();
                }
                extend_rule.find(":input").each(function(i){
                    $(this).val("");
                });
            });
        })
        // =========================
        // 添加/删除域名列表 begin
        $(".trash_custom_row_item").off()
        $(".add_custom_row_item").off()
        $('.trash_custom_row_item').click(function(){
            let key = $(this).data('key');
            // 判断key 是否未定义或者不等于0
            if (typeof(key) != 'undefined' && parseInt(key) === 0) {
                // 删除整个字段的配置
                // 删除当前元素的父级.custom_row_item 到下一个.custom_row_divider 元素
                $(this).parent().parent('.custom_row_item').nextUntil('.custom_row_divider').remove();
            }
            $(this).parent().parent('.custom_row_item').remove();
        });
        $('.add_custom_row_item').click(function(){
            var child = $(".custom_row_item").parent().children(".custom_row_item:first-child").clone(true);
            //清除克隆的数据
            child.find(":input").each(function(i){
                $(this).val("");
            });
            child.find("select").each(function(i){
                $(this).val("1");
            });
            child.find('.extend-rule').hide();
            child.find('.trash_custom_row_item').show().removeAttr('data-key');
            child.find('.add_custom_row_item').remove();
            child.find('.rule-title').css({ visibility: 'hidden' });
            child.find('.rule-handle').css({ visibility: 'hidden' });
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
            child.find("select").each(function(i){
                $(this).val("1");
            });
            child.find('.extend-rule').hide();
            child.find('.trash_custom_row_item').show();
            child.find('.add_custom_row_item').show();
            $("#create_custom_row_box").before(child);
            $("#create_custom_row_box").before('<div class="custom_row_divider"></div>');
        });
        // 添加/删除域名列表 end
        // =========================
    </script>
@endsection
