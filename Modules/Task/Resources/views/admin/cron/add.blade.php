@extends('admin::layouts.admin_layer_layout')

@section('head_css')
    @parent
    <style>
        .rule-handle{padding: 0;}
        #mytip{position: absolute!important;}
        .extend-rule input{ padding: 0; }
        .extend-rule input::placeholder {
            font-size: 8px; /* 你可以根据需要调整字体大小 */
            color: red;
            line-height: 1.2;
        }
        .extend-rule{ max-width: 50px;margin-left: 0px; }
        select.form-control {
            padding: 6px 0!important;
        }
    </style>
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row g-lg-2 g-1">
                <label for="name" class="control-label col-sm-2"><font color="#FF0000">*</font>任务名称:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="name" name="row[name]" placeholder="" value="" data-rule="required" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="timer" class="control-label col-sm-2"><font color="#FF0000">*</font>cron时间:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="timer" name="row[timer]" placeholder="" value="* * * * *" data-rule="" data-tips="bottom" title="使用下面的cron选择器选择" readonly/>
                    <span class="form-text m-b-none">提示: 5个占位的cron时间规则，例如 <code>7 10 * * 4,0</code>表示每周四和周日的10:07, <code>14 13 2 1 *</code>表示每年的1月2日的13时14分执行一次; 尽量避开凌晨<code>4:00~4:30</code>执行定时任务</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            @include('task::template.cron_select', ['callback_dom'=>'#timer','analysis_value'=>'','show_label'=>'','disabled'=>'false'])

            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="type" class="control-label col-sm-2"><font color="#FF0000">*</font>执行类型:</label>
                <div class="col-sm-10">
                    <select class="form-control custom-select  col-sm-12 col-sm-12" name="row[type]" id="cron_type" style="border-radius:0px;" >
                        <option value="model">数据库模型</option>
                        <option value="func" selected>类/方法</option>
                        <option value="curl">CURL请求/HTTP请求</option>
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            {{-- 模型调用配置 --}}
            <div class="form-group row model-form-group">
                <label for="executable_type" class="control-label col-sm-2"><font color="#FF0000">*</font>方法调用地址:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="executable_type" name="row[executable_type]" placeholder="" value="" data-rule="required" />
                    <span class="form-text m-b-none">「示例」数据库模型调用: <code>\Modules\Spider\Models\SpiderTask</code></span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row model-form-group">
                <label for="executable_id" class="control-label col-sm-2"><font color="#FF0000">*</font>调用参数:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="executable_id" name="row[executable_id]" placeholder="" value="" data-rule="required" />
                    <span class="form-text m-b-none">「示例」数据库模型参数示例(模型id): <code>123</code></span>
                </div>
            </div>
            {{-- 模型调用配置 end --}}

            <div class="border-top border-dashed my-2"></div>

            {{-- 方法调用配置 --}}
            <div class="form-group row func-form-group">
                <label for="execute_class_or_func" class="control-label col-sm-2"><font color="#FF0000">*</font>方法调用地址:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="execute_class_or_func" name="row[execute_class_or_func]" placeholder="" value="" data-rule="required" />
                    <span class="form-text m-b-none">「示例」类/方法调用: <br />静态方法<code>\Modules\Task\Services\TestCronTaskService::init</code>&nbsp;&nbsp; 或<br />普通方法<code>['\Modules\Task\Services\TestCronTaskService','test']</code></span>
                </div>
            </div>
            <div class="form-group row func-form-group">
                <label for="class_or_func_params" class="control-label col-sm-2">调用参数:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="class_or_func_params" name="row[class_or_func_params]" placeholder="" value="" />
                    <span class="form-text m-b-none">「示例」类/方法调用示例(json字符串): <code>{"a":"1","b":"2"}</code></span>
                </div>
            </div>
            {{-- 方法调用配置 end --}}
            <div class="border-top border-dashed my-2"></div>
            {{-- CURL 调用配置 --}}
            <div class="form-group row curl-form-group">
                <label for="execute_class_or_func" class="control-label col-sm-2"><font color="#FF0000">*</font>请求地址:</label>
                <div class="col-sm-10 row m-0">
                    <select class="form-control col-sm-3 custom-select curl-field" placeholder="请求方式" name="row[curl_method]" id="params_curl_method" style="border-radius:0;" >
                        <option value="POST" selected>POST</option>
                        <option value="GET">GET</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                        <option value="PATCH">PATCH</option>
                    </select>
                    <input type="text" class="form-control col-sm-9" id="params_curl_url" name="row[curl_url]" placeholder="http://example.com" value="" data-rule="required" />
                    <span class="form-text m-b-none">「示例」: POST <code>http://example.com</code>&nbsp;&nbsp; 或 GET <code>http://example.com</code></span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row curl-form-group">
                <label for="class_or_func_params" class="control-label col-sm-2">HTTP请求头Headers:</label>
                <div class="col-sm-10">
                    <textarea type="text" class="form-control" id="params_curl_headers" name="row[curl_headers]" placeholder="HTTP请求头,例如： &#13;&#10;{&#13;&#10;&nbsp;&nbsp;&nbsp;&nbsp;'Authorization':'Bearer YourToken',&#13;&#10;&nbsp;&nbsp;&nbsp;&nbsp;'Accept':'*/*'&#13;&#10;}" rows="5"></textarea>
                    <span class="form-text m-b-none">HTTP请求头参数(json字符串): <code>{"Authorization":"Bearer YourToken","Accept":"*/*"}</code></span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row curl-form-group">
                <label for="class_or_func_params" class="control-label col-sm-2">HTTP请求Body:</label>
                <div class="col-sm-10">
                    <textarea type="text" class="form-control" id="params_curl_body" name="row[curl_body]" placeholder="HTTP请求Body,例如： &#13;&#10;{&#13;&#10;&nbsp;&nbsp;&nbsp;&nbsp;'id':1,&#13;&#10;&nbsp;&nbsp;&nbsp;&nbsp;'type':'query'&#13;&#10;}" rows="5"></textarea>
                    <span class="form-text m-b-none">HTTP请求Body参数(json字符串): <code>{"id":1,"type":"query"}</code></span>
                </div>
            </div>
            {{-- CURL 调用配置 end --}}

            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="content" class="control-label col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-sm-10">
                    <label>
                        <input type="radio" name="row[status]" value="1" class="flat-red" checked> 开启
                    </label>
                    <label>
                        <input type="radio" name="row[status]" value="2" class="flat-red"> 关闭
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
        </form>
    </div>

@endsection

@section('page_js')
    @parent

    <script type="text/javascript">
        $(function () {
            // 监听 #cron_type 下拉选择的变化
            $("#cron_type").change(function () {
                var type = $(this).val();
                set_cron_type_items(type)
            });

            // init
            var type = $("#cron_type").val();
            set_cron_type_items(type)
        })
        function set_cron_type_items(type) {
            if (type == 'model') {
                // 模型 model
                $('.model-form-group').show();
                $('.model-form-group').find('input').attr('disabled', false)
                // $("#executable_type").val('\Modules\Spider\Models\SpiderTask');
                // $("#executable_id").val('');

                $('.func-form-group').hide();
                $('.func-form-group').find('input').attr('disabled', true)

                $('.curl-form-group').hide();
                $('.curl-form-group').find('input').attr('disabled', true)
            }
            if (type == 'func') {
                // 方法 func
                $('.model-form-group').hide();
                $('.model-form-group').find('input').attr('disabled', true)

                $('.func-form-group').show();
                $('.func-form-group').find('input').attr('disabled', false)
                // $("#execute_class_or_func").val('[\Modules\Task\Services\TestCronTaskService','test']');
                // $("#class_or_func_params").val('{"a":"1","b":"2"}');

                $('.curl-form-group').hide();
                $('.curl-form-group').find('input').attr('disabled', true)
            }
            if (type == 'curl') {
                // curl 网络请求
                $('.model-form-group').hide();
                $('.model-form-group').find('input').attr('disabled', true)

                $('.func-form-group').hide();
                $('.func-form-group').find('input').attr('disabled', true)

                $('.curl-form-group').show();
                $('.curl-form-group').find('input').attr('disabled', false)
            }
        }
    </script>
@endsection
