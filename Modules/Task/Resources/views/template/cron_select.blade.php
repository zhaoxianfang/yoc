
            <div class="form-group row">
                <label for="" class="control-label col-xs-12 col-sm-2">{{ !empty($show_label)?'Crontab选择器':'' }}</label>
                <div class="col-xs-12 col-sm-10 row m-0">
                    <select class="form-control col-sm custom-select cron-field" multiple="multiple" placeholder="分钟" id="cron_minute" style="border-radius:0;" {{empty($disabled) || !in_array($disabled,['disabled','1','true']) ?'':'disabled'}} >
                        <option value="*" selected>每分钟</option>
                        @for ($i = 0; $i < 60; $i++)
                            <option value="{{ $i }}">{{ $i }}分</option>
                        @endfor
                        <option value="*/5">每5分钟</option>
                        <option value="*/10">每10分钟</option>
                        <option value="*/15">每15分钟</option>
                        <option value="*/20">每20分钟</option>
                        <option value="*/30">每30分钟</option>
                    </select>
                    <select class="form-control col-sm custom-select cron-field" multiple="multiple" placeholder="小时" id="cron_hour" style="border-radius:0;" {{empty($disabled) || !in_array($disabled,['disabled','1','true']) ?'':'disabled'}}   >
                        <option value="*" selected>每小时</option>
                        @for ($i = 0; $i < 24; $i++)
                            <option value="{{ $i }}">{{ $i }}点</option>
                        @endfor
                        <option value="0-8">从8点到8点</option>
                        <option value="9-17">从9点到17点</option>
                        <option value="18-23">从18点到23点</option>
                    </select>
                    <select class="form-control col-sm custom-select cron-field" multiple="multiple" placeholder="日期" id="cron_day_of_month" style="border-radius:0;" {{empty($disabled) || !in_array($disabled,['disabled','1','true']) ?'':'disabled'}}   >
                        <option value="*" selected>每天</option>
                        @for ($i = 1; $i < 32; $i++)
                            <option value="{{ $i }}">{{ $i }}日</option>
                        @endfor
                        <option value="1-15">从1日到15日</option>
                        <option value="15-31">从15日到31日</option>
                    </select>
                    <select class="form-control col-sm custom-select cron-field" multiple="multiple" placeholder="月份" id="cron_month" style="border-radius:0;" {{empty($disabled) || !in_array($disabled,['disabled','1','true']) ?'':'disabled'}}   >
                        <option value="*" selected>每月</option>
                        @for ($i = 1; $i < 13; $i++)
                            <option value="{{ $i }}">{{ $i }}月</option>
                        @endfor
                        <option value="3-7">从3月到7月</option>
                        <option value="9-12">从9月到12月</option>
                    </select>
                    <select class="form-control col-sm custom-select cron-field" multiple="multiple" placeholder="星期" id="cron_day_of_week" style="border-radius:0;" {{empty($disabled) || !in_array($disabled,['disabled','1','true']) ?'':'disabled'}}   >
                        <option value="*" selected>每周</option>
                        <option value="0">周日</option>
                        <option value="1">周一</option>
                        <option value="2">周二</option>
                        <option value="3">周三</option>
                        <option value="4">周四</option>
                        <option value="5">周五</option>
                        <option value="6">周六</option>
                        <option value="1-5">周一到周五</option>
                        <option value="0,6">周末(周六、周日)</option>
                    </select>
                    <span class="form-text m-b-none col-12 p-0 m-0">显示优先级: <code>*</code> > <code>*/数字</code> > <code>其他</code></span>
                </div>
            </div>



@section('page_js')
    @parent
<script type="text/javascript">
    // 接收传入的参数有 callback_dom(选择或解析后的结果赋值给的元素，eg: #test或.test),analysis_value(默认值),disabled(表单是否禁止编辑，true|1|disabled 表示禁止编辑) 三个参数
    var callback_dom = "{{$callback_dom??'#cron'}}"; // 回调的dom
    var analysis_value = "{{$analysis_value??'* * * * *'}}"; // 需要解析的值，例如 把 * * * * * 解析到 表单的select 选项中
    $(function () {
        function generateCronExpression() {
            let minute = getSelectedValues($('#cron_minute'));
            let hour = getSelectedValues($('#cron_hour'));
            let dayOfMonth = getSelectedValues($('#cron_day_of_month'));
            let month = getSelectedValues($('#cron_month'));
            let dayOfWeek = getSelectedValues($('#cron_day_of_week'));

            let cronExpression = `${minute} ${hour} ${dayOfMonth} ${month} ${dayOfWeek}`;
            callback_dom && $(callback_dom).val(cronExpression);
        }

        function getSelectedValues(selectElement) {
            let values = selectElement.val();

            // 如果选中“*”或没有选中任何值，则返回“*”
            if (!values || values.includes('*')) {
                return '*';
            }

            // 如果选中的值里面的某一项包含“*/”符号，则只返回包含“*/”符号的这一个项，例如 时间可以用 "*/5" 表示每5分钟
            let hasStep = values.some(value => value.includes('*/'));
            if (hasStep) {
                return values.filter(value => value.includes('*/'))[0];
            }

            let newCronStr = values.join(',').split(',');
            // 对 newCronStr 里面的数字进行去重，例如 "6,1-5,0,6" => "6,1-5,0"
            return newCronStr.filter((item, index) => newCronStr.indexOf(item) === index);
        }

        // 实时更新 Cron 表达式
        $('.cron-field').on('change', generateCronExpression);

        // 解析analysis_value 到表单选项中
        if(analysis_value){
            // 先清空 所有选项的值
            $('.cron-field').val('');
            let analysis_value_arr = analysis_value.split(' ');
            analysis_value_arr.forEach((item,index)=>{
                // $('#cron_'+['minute','hour','day_of_month','month','day_of_week'][index]).val(item);
                // 处理 item 中包含,分割的多选项 和 */ 的情况
                if(item.includes(',')){
                    item.split(',').forEach(subItem=>{
                        $('#cron_'+['minute','hour','day_of_month','month','day_of_week'][index]).find('option[value="'+subItem+'"]').attr('selected',true);
                    })
                }else if(item.includes('*/')){
                    $('#cron_'+['minute','hour','day_of_month','month','day_of_week'][index]).find('option[value="'+item+'"]').attr('selected',true);
                }else{
                    $('#cron_'+['minute','hour','day_of_month','month','day_of_week'][index]).val(item);
                }
            })
        }

        // 初始生成 Cron 表达式
        generateCronExpression();
    })
</script>
@endsection

