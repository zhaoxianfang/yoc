@extends('admin::layouts.admin_layer_layout')

@section('head_css')
@endsection

@section('content')

<div class="card card-info">
    <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

        <div class="form-group row g-lg-2 g-1">
            <label for="ip" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>IP:</label>
            <div class="col-xs-12 col-sm-8">
                <input type="text" class="form-control" id="ip" name="row[ip]" placeholder="" value="" data-rule="required|ip" />
            </div>
        </div>
        <div class="border-top border-dashed my-2"></div>
        <div class="form-group row g-lg-2 g-1">
            <label for="visits" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>访问次数:</label>
            <div class="col-xs-12 col-sm-8">
                <input type="number" min="1" step="1" class="form-control" id="visits" name="row[visits]" placeholder="" value="1" data-rule="required" />
            </div>
        </div>
        <div class="border-top border-dashed my-2"></div>
        <div class="form-group row g-lg-2 g-1">
            <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>类型:</label>
            <div class="col-xs-12 col-sm-8">
                <label>
                    <input type="radio" name="row[type]" value="0" class="flat-red"> 可疑
                </label>
                <label>
                    <input type="radio" name="row[type]" value="1" class="flat-red" checked> 黑名单
                </label>
            </div>
        </div>
        <div class="border-top border-dashed my-2"></div>
        <div class="form-group row g-lg-2 g-1">
            <label for="remark" class="control-label col-xs-12 col-sm-2">备注:</label>
            <div class="col-xs-12 col-sm-8">
                <input type="text" class="form-control" id="remark" name="row[remark]" placeholder="" value="" data-rule="" />
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

<script type="text/javascript">
    $(function () {
    })
</script>
@endsection
