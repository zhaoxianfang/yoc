@extends('home::layouts.home_layout')
@section('title', "系列化和反系列化serialize、unserialize")

@section('use_form', "true")

@section('content')

    <div class="row align-items-center">
        <div class="card">
            <div class="card-header d-block">
                <h5 class="card-title mb-1">系列化和反系列化 <small>serialize、unserialize</small></h5>
                <h6 class="card-subtitle text-body-secondary">轻便、快捷</h6>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-sm-6 border-end border-dashed text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-code-circle-2 fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">需要进行系列化/反系列化的数据</h4>
                        <textarea class="form-control" id="left_string" rows="22" placeholder='{"name": "John", "age": 30}'></textarea>
                    </div>

                    <div class="col-sm-6 text-center">
                        <div class="avatar avatar-xl mx-auto">
                            <span class="avatar-title bg-purple-subtle text-purple rounded-circle fw-bold">
                                <i class="ti ti-code-circle-2 fs-32" id="cody_type_icon"></i>
                            </span>
                        </div>
                        <h4 class="mt-3">进行系列化/反系列化处理后的结果</h4>
                        <textarea class="form-control" id="right_string" rows="22" placeholder='a:2:{s:4:"name";s:4:"John";s:3:"age";i:30;}'></textarea>
                    </div>
                </div>
                <div class="row g-4 align-items-center text-center">
                    <p class="text-muted mb-1">使用说明：在左侧输入区填写 需要进行系列化/反系列化的数据 就会自动完成转换！</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script>
        // 序列化函数
        function serialize(data) {
            const type = typeof data;

            if (data === null) {
                return 'N;';
            }

            if (type === 'boolean') {
                return 'b:' + (data ? '1' : '0') + ';';
            }

            if (type === 'number') {
                return Number.isInteger(data) ?
                    'i:' + data + ';' :
                    'd:' + data + ';';
            }

            if (type === 'string') {
                return 's:' + data.length + ':"' + data + '";';
            }

            if (type === 'object') {
                // 处理数组和对象
                if (Array.isArray(data)) {
                    let result = 'a:' + data.length + ':{';
                    data.forEach((value, index) => {
                        result += serialize(index);
                        result += serialize(value);
                    });
                    return result + '}';
                } else {
                    // 普通对象
                    const keys = Object.keys(data);
                    let result = 'a:' + keys.length + ':{';
                    keys.forEach(key => {
                        result += serialize(key);
                        result += serialize(data[key]);
                    });
                    return result + '}';
                }
            }

            // 其他类型（如undefined、function等）不支持序列化
            throw new Error('Unsupported data type for serialization');
        }

        // 反序列化函数
        function unserialize(str) {
            let index = 0;

            function parseValue() {
                const type = str[index];
                index += 2; // 跳过类型标识和冒号

                if (type === 'N') {
                    // null
                    index++; // 跳过分号
                    return null;
                }

                if (type === 'b') {
                    // boolean
                    const value = str[index] === '1';
                    index += 2; // 跳过值和分号
                    return value;
                }

                if (type === 'i' || type === 'd') {
                    // integer or double
                    let numStr = '';
                    while (str[index] !== ';') {
                        numStr += str[index];
                        index++;
                    }
                    index++; // 跳过分号
                    return type === 'i' ? parseInt(numStr, 10) : parseFloat(numStr);
                }

                if (type === 's') {
                    // string
                    let lenStr = '';
                    while (str[index] !== ':') {
                        lenStr += str[index];
                        index++;
                    }
                    index += 2; // 跳过冒号和引号

                    const length = parseInt(lenStr, 10);
                    const value = str.substr(index, length);
                    index += length + 3; // 跳过值、引号和分号

                    return value;
                }

                if (type === 'a') {
                    // array or object
                    let lenStr = '';
                    while (str[index] !== ':') {
                        lenStr += str[index];
                        index++;
                    }
                    index += 2; // 跳过冒号和左花括号

                    const length = parseInt(lenStr, 10);
                    const result = {};

                    for (let i = 0; i < length; i++) {
                        const key = parseValue();
                        const value = parseValue();
                        result[key] = value;
                    }

                    index++; // 跳过右花括号

                    // 如果键是连续数字，则转换为数组
                    const keys = Object.keys(result);
                    const isArray = keys.every((key, i) => key === i.toString());
                    return isArray ? keys.map(key => result[key]) : result;
                }

                throw new Error('Unknown type identifier: ' + type);
            }

            try {
                return parseValue();
            } catch (e) {
                throw new Error('Invalid serialized string: ' + e.message);
            }
        }

        // 检测字符串是否为序列化格式
        function detectSerializedString(str) {
            if (typeof str !== 'string') {
                return { isSerialized: false, type: '非字符串' };
            }

            // 基本序列化格式检测
            const serializedRegex = /^[a-z]:\d*:/i;
            if (!serializedRegex.test(str)) {
                return { isSerialized: false, type: '非序列化字符串' };
            }

            try {
                const result = unserialize(str);
                return {
                    isSerialized: true,
                    type: Array.isArray(result) ? '数组' : typeof result,
                    value: result
                };
            } catch (e) {
                return { isSerialized: false, type: '无效的序列化字符串' };
            }
        }

        $(function(){
            $('#left_string').bind('input propertychange', function(){
                if($(this).val() != ""){
                    if(detectSerializedString($(this).val()).isSerialized){
                        // 系列化后的字符串 =》 进行反系列化处理
                        $('#left_string').val(unserialize($(this).val()));
                        $('#right_string').val($(this).val());
                    }else{
                        // 反系列化前的字符串 =》 进行系列化处理
                        $('#right_string').val(serialize($(this).val()));
                    }
                }else{
                    $('#right_string').val('');
                };
            });

            $('#right_string').bind('input propertychange', function(){
                if($(this).val() != ""){
                    if(detectSerializedString($(this).val()).isSerialized){
                        // 系列化后的字符串 =》 进行反系列化处理
                        $('#left_string').val(unserialize($(this).val()));
                    }else{
                        // 反系列化前的字符串 =》 进行系列化处理
                        $('#left_string').val($(this).val());
                        $('#right_string').val(serialize($(this).val()));
                    }
                }else{
                    $('#left_string').val('');
                };
            });
        });
    </script>

@endsection
