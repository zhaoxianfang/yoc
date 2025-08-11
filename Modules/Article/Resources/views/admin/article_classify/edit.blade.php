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
                    <input type="text" class="form-control" id="name" name="row[name]" placeholder="" value="{{$info->name??''}}" data-rule="required" />
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="pid" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>父级分类:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[pid]" style="border-radius:0px;" >
                        <option value="0">根节点</option>
                        @foreach ($classify_list as $classify)
                            <option value="{{ $classify['id'] }}" @if($classify['id']==$info['pid']) selected @endif>{{ $classify['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="level" class="control-label col-xs-12 col-sm-2">分类层级:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="number" class="form-control" id="level" name="row[level]" placeholder="" value="{{ (empty($info['level']) || $info['level'] < 1)?1:$info['level']}}" />
                    <span class="form-text m-b-none">提示: 根据父级分类填写属于第几层级，顶级为1级</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="sort" class="control-label col-xs-12 col-sm-2">排序:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="number" class="form-control" id="sort" name="row[sort]" placeholder="" value="{{ (empty($info['sort']) || $info['sort'] < 0)?0:$info['sort']}}" />
                    <span class="form-text m-b-none">提示: 值越大越靠前</span>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>使用类型:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select  col-xs-12 col-sm-12" name="row[type]" style="border-radius:0px;" >
                        <option value="1" @if($info['type']==1) selected @endif>用户发布</option>
                        <option value="2" @if($info['type']==2) selected @endif>爬虫采集</option>
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="show_nav" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>展示位置:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select col-xs-12 col-sm-12" name="row[show_nav]" style="border-radius:0px;" >
                        <option value="0" @if($info['show_nav']==0) selected @endif>不展示</option>
                        <option value="1" @if($info['show_nav']==1) selected @endif>仅移动端(app)</option>
                        <option value="2" @if($info['show_nav']==2) selected @endif>仅后台</option>
                        <option value="3" @if($info['show_nav']==3) selected @endif>都展示</option>
                    </select>
                </div>
            </div>
            <div class="border-top border-dashed my-2"></div>
            <div class="form-group row g-lg-2 g-1">
                <label for="status" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>状态:</label>
                <div class="col-xs-12 col-sm-8">
                    <select class="form-control custom-select col-xs-12 col-sm-12" name="row[status]" style="border-radius:0px;" >
                        <option value="0" @if($info['status']==0) selected @endif>待审</option>
                        <option value="1" @if($info['status']==1) selected @endif>正常</option>
                        <option value="2" @if($info['status']==2) selected @endif>不公开</option>
                        <option value="3" @if($info['status']==3) selected @endif>敏感待审核</option>
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
