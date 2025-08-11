@extends('admin::layouts.admin_layer_layout')

@section('head_css')
    @parent
    <style>
        .rule-handle{padding: 0;}
    </style>
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row g-lg-2 g-1">
                <label for="name" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>名称:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="name" name="row[name]" placeholder="" value="" data-rule="required" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="pid" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>父级分类:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[pid]" style="border-radius:0px;" >
                        <option value="0">根节点</option>
                        @foreach ($classify_list as $classify)
                            <option value="{{ $classify['id'] }}">{{ $classify['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="level" class="control-label col-xs-12 col-sm-2">分类层级:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="number" class="form-control" id="level" name="row[level]" placeholder="" value="1" />
                    <span class="form-text m-b-none">提示: 根据父级分类填写属于第几层级，顶级为1级</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="sort" class="control-label col-xs-12 col-sm-2">排序:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="number" class="form-control" id="sort" name="row[sort]" placeholder="" value="0" />
                    <span class="form-text m-b-none">提示: 值越大越靠前</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>使用类型:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[type]" style="border-radius:0px;" >
                        <option value="1" selected>用户发布</option>
                        <option value="2">爬虫采集</option>
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="show_nav" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>展示位置:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select col-xs-12 col-sm-12" name="row[show_nav]" style="border-radius:0px;" >
                        <option value="0">不展示</option>
                        <option value="1">仅移动端(app)</option>
                        <option value="2" selected>仅后台</option>
                        <option value="3">都展示</option>
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="status" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select col-xs-12 col-sm-12" name="row[status]" style="border-radius:0px;" >
                        <option value="0">待审</option>
                        <option value="1" selected>正常</option>
                        <option value="2">不公开</option>
                        <option value="3">敏感待审核</option>
                    </select>
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

        })
    </script>
@endsection
