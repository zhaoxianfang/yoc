@extends('admin::layouts.admin_layer_layout')

@section('head_css')
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row g-lg-2 g-1">
                <label for="group_ids" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>所属角色组:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[group_ids][]" style="border-radius:0px;" multiple="multiple">
                        @foreach ($group_list as $group)
                            <option value="{{ $group['id'] }}" @if(in_array($group['id'],$group_ids)) selected @endif>{{ $group['group_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="nickname" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>姓名:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="nickname" name="row[nickname]" placeholder="" value="{{$info->nickname ?? ''}}" data-rule="required" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="mobile" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>手机号:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="mobile" name="row[mobile]" placeholder="" value="{{$info->mobile ?? ''}}" data-join="check_field" data-rule="required|mobile|remote(/admin/system/admins/check_field)" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="password" class="control-label col-xs-12 col-sm-2">密码:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="mobile" name="row[password]" placeholder="" value="" data-rule="strong_pwd" />
                    <span class="form-text m-b-none">提示: 强密码 (至少8位(数字、大写字母、小写字母、特殊字符 至少四选三))</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="email" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>邮箱号:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="email" name="row[email]" placeholder="" value="{{$info->email ?? ''}}" data-join="check_field" data-rule="required|email|remote(/admin/system/admins/check_field)" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="id_card" class="control-label col-xs-12 col-sm-2">身份证号:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="id_card" name="row[id_card]" placeholder="" value="{{$info->id_card ?? ''}}" data-join="check_field" data-rule="id_card|remote(/admin/system/admins/check_field)" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="gender" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>性别:</label>
                <div class="col-xs-12 col-sm-8">
                    <label>
                        <input type="radio" name="row[gender]" value="1" class="flat-red" @if ($info->gender == 1) checked @endif> 男
                    </label>
                    <label>
                        <input type="radio" name="row[gender]" value="2" class="flat-red" @if ($info->gender == 2) checked @endif> 女
                    </label>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="remark" class="control-label col-xs-12 col-sm-2">备注:</label>
                <div class="col-xs-12 col-sm-8">
                    <textarea class="form-control" id="remark" name="row[remark]">{{$info->remark??''}}</textarea>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>

            <div class="form-group row g-lg-2 g-1">
                <label for="content" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-xs-12 col-sm-8">
                    <label>
                        <input type="radio" name="row[status]" value="1" class="flat-red" @if ($info->status == 1) checked @endif> 启用
                    </label>
                    <label>
                        <input type="radio" name="row[status]" value="2" class="flat-red" @if ($info->status == 2) checked @endif> 冻结
                    </label>
                </div>
            </div>
            <div class="form-group row">

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
    <script type="text/javascript">
        $(function () {
        })
        function check_field(ele){
            return {
                'id':"{{ $info['id']  }}"
            }
        }
    </script>
@endsection
