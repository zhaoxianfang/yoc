@extends('home::layouts.home_layout')
@section('title', "时间/时区转换")

@section('use_datepicker', "true")
@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">时间/时区转换</h5>
                <h6 class="card-subtitle text-body-secondary"></h6>
            </div>

            <div class="card-body">
                <div class="row g-4 pt-4">

                    <div class="form-group row">
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">时间:</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="req_current_date_time">
                            </div>
                        </div>
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">时间戳：</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="req_current_timestamp">
                            </div>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="form-group row">
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">当前时间:</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="current_date_time">
                            </div>
                        </div>
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">当前时间戳：</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="current_timestamp">
                            </div>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="form-group row">
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">时间戳转时间:</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="number" min="0" step="1" class="form-control" id="custom_timestamp">
                            </div>
                        </div>
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label" >
                                <a href="javascript:;" class="btn btn-primary btn-sm" id="timestamp_to_date_time_btn">开始转换</a>
                            </label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="timestamp_to_date_time">
                            </div>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="form-group row">
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label">时间转时间戳:</label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="custom_date_time">
                            </div>
                        </div>
                        <div class="col-sm-6 row no-gutters">
                            <label class="col-sm-4 col-form-label" >
                                <a href="javascript:;" class="btn btn-primary btn-sm" id="date_time_to_timestamp_btn">开始转换</a>
                            </label>
                            <div class="col-sm-8 row no-gutters">
                                <input type="text" class="form-control" id="date_time_to_timestamp">
                            </div>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label" style="padding-right: 0;">时区转换:</label>
                        <div class="col-sm-10 row no-gutters"  style="padding-left: 0;">
                            <div class="col-sm-2">
                                <select class="form-control m-b custom-select" name="form_time_zone" id="form_time_zone">
                                    <option selected value="Asia/Shanghai">北京时间</option>
                                    <option value="America/New_York">纽约时间</option>
                                    <option value="JST">日本时间</option>
                                    <option value="KST">韩国时间</option>
                                    <option value="Asia/Kolkata">印度时间</option>
                                    <option value="UTC">协调标准时间</option>
                                    <option value="Atlantic/Reykjavik">冰岛时间</option>
                                    <option value="Africa/Cairo">埃及时间</option>
                                    <option value="Africa/Maputo">南非时间</option>
                                    <option value="Europe/Minsk">莫斯科时间</option>
                                    <option value="Pacific/Auckland">新西兰时间</option>
                                    <option value="Pacific/Honolulu">夏威夷时间</option>
                                    <option value="America/Anchorage">阿拉斯加时间</option>
                                    <option value="Asia/Baghdad">阿拉伯加时间</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="from_time_zone_date_time" name="from_time_zone_date_time" placeholder="例如:2024-11-11 11:11:33">
                            </div>
                            <div class="col-sm-2">
                                <a href="javascript:;" class="btn btn-primary btn-sm w-100" id="change_timezone_date_btn">开始转换</a>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control m-b custom-select" name="to_time_zone" id="to_time_zone">
                                    <option value="Asia/Shanghai">北京时间</option>
                                    <option selected value="America/New_York">纽约时间</option>
                                    <option value="JST">日本时间</option>
                                    <option value="KST">韩国时间</option>
                                    <option value="Asia/Kolkata">印度时间</option>
                                    <option value="UTC">协调标准时间</option>
                                    <option value="Atlantic/Reykjavik">冰岛时间</option>
                                    <option value="Africa/Cairo">埃及时间</option>
                                    <option value="Africa/Maputo">南非时间</option>
                                    <option value="Europe/Minsk">莫斯科时间</option>
                                    <option value="Pacific/Auckland">新西兰时间</option>
                                    <option value="Pacific/Honolulu">夏威夷时间</option>
                                    <option value="America/Anchorage">阿拉斯加时间</option>
                                    <option value="Asia/Baghdad">阿拉伯加时间</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="change_timezone_to_date_time">
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        $(function() {
            $('#req_current_date_time').val(get_current_date_time());
            $('#req_current_timestamp').val(get_current_timestamp());

            // 每秒定时刷新
            setInterval(function() {
                $('#current_date_time').val(get_current_date_time());
                $('#current_timestamp').val(get_current_timestamp());
            }, 1000);

            $('#timestamp_to_date_time_btn').on('click',function () {
                $('#timestamp_to_date_time').val(get_current_date_time($('#custom_timestamp').val()));
            })

            $('#date_time_to_timestamp_btn').on('click',function () {
                var date = new Date($('#custom_date_time').val());
                $('#date_time_to_timestamp').val( Math.floor(date.getTime() / 1000) );
            })

            $('#change_timezone_date_btn').on('click',function () {
                var form_time_zone = $('#form_time_zone').val();
                var from_time_zone_date_time = $('#from_time_zone_date_time').val();
                var to_time_zone = $('#to_time_zone').val();

                myTools.http.request('POST','/tools/string/timezone',{
                    form_time_zone:form_time_zone,
                    from_time_zone_date_time:from_time_zone_date_time,
                    to_time_zone:to_time_zone
                }).then(res => {
                        $('#change_timezone_to_date_time').val(res.data.to_date_time || '');
                    }
                ).catch(error => {
                        Modal && Modal.error('错误:'+error.message, {
                            position: 'top-right',
                            timeout: 5000
                        });
                    }
                );
            })
        });

        // 当前时间
        function get_current_date_time(date = '') {
            let now;
            // 判断date 是不是空
            if (date === '' || date === undefined || date === null) {
                now = new Date();
            }else if (typeof date === 'number') {
                now = new Date(date * 1000);
            }else if (typeof date === 'object') {
                now = date;
            }else if (typeof date === 'string' && date.length === 10) {
                now = new Date(date * 1000);
            }else{
                now = new Date();
            }
            // 获取当时的时间
            var year = now.getFullYear();
            var month = now.getMonth() + 1;
            var day = now.getDate();
            var hour = now.getHours();
            var minute = now.getMinutes();
            var second = now.getSeconds();
            if (month < 10) {
                month = "0" + month;
            }
            if (day < 10) {
                day = "0" + day;
            }
            if (hour < 10) {
                hour = "0" + hour;
            }
            if (minute < 10) {
                minute = "0" + minute;
            }
            if (second < 10) {
                second = "0" + second;
            }
            return year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
        }
        // 获取当前10位时间戳，只保留整数
        function get_current_timestamp() {
            return Math.floor(new Date().getTime() / 1000);
        }
    </script>

@endsection
