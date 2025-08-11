@extends('admin::layouts.admin_layer_layout')

@section('head_css')
@endsection

@section('content')

    <div class="card card-info">
        <form id="add-form" class="form-horizontal form-ajax card-body" role="form" data-toggle="validator" method="POST" action="">

            <div class="form-group row g-lg-2 g-1">
                <label for="ip" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>IP:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="ip" name="row[ip]" placeholder="" value="{{$info->ip}}" data-rule="required|ip" />
                </div>
            </div>
            <div class="form-group row g-lg-2 g-1">
                <label for="visits" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>访问次数:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="number" min="1" step="1" class="form-control" id="visits" name="row[visits]" placeholder="" value="{{$info->visits}}" data-rule="required" />
                </div>
            </div>

            <div class="form-group row g-lg-2 g-1">
                <label for="type" class="control-label col-xs-12 col-sm-2"><font color="#FF0000">*</font>类型:</label>
                <div class="col-xs-12 col-sm-8">
                    <label>
                        <input type="radio" name="row[type]" value="0" class="flat-red" @if ($info->type == 0) checked @endif> 可疑
                    </label>
                    <label>
                        <input type="radio" name="row[type]" value="1" class="flat-red" @if ($info->type == 1) checked @endif> 黑名单
                    </label>
                </div>
            </div>
            <div class="form-group row g-lg-2 g-1">
                <label for="remark" class="control-label col-xs-12 col-sm-2">备注:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="remark" name="row[remark]" placeholder="" value="{{$info->remark}}" data-rule="" />
                </div>
            </div>

            <div class="form-group row g-lg-2 g-1">

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
