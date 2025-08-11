@extends('admin::layouts.admin_layer_layout')

@section('layer_layout', "true")

@section('head_css')
    @parent

    <style>
        #chooseicon {
            margin:10px;
        }
        #chooseicon ul {
            margin:5px 0 0 0;
        }
        #chooseicon ul li{
            width:30px;height:30px;
            line-height:30px;
            border:1px solid #ddd;
            padding:1px;
            margin:1px;
            text-align: center;
            float: left;
        }
        #chooseicon ul li:hover{
            border:1px solid #2c3e50;
            cursor:pointer;
        }
    </style>
@endsection

@section('content')


    <!-- Horizontal Form -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">提示:规则名称请仔细填写，包含大小写的区别</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form id="add-form" class="form-horizontal form-ajax" role="form" data-toggle="validator" method="POST" action="">
            @csrf
            <input type="hidden" name="row[id]" value="{{ $row['id']  }}">
            <div class="card-body row">
                <div class="form-group row g-lg-2 g-1">
                    <label for="inputEmail3" class="col-sm-2 col-form-label">是否为菜单:</label>
                    <div class="col-sm-10">
                        <label>
                            <input type="radio" name="row[ismenu]" value="1" class="flat-red" @if ($row->ismenu == 1) checked @endif>
                            是
                        </label>
                        <label>
                            <input type="radio" name="row[ismenu]" value="0" class="flat-red" @if ($row->ismenu == 0) checked @endif>
                            否
                        </label>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>

                <div class="form-group row g-lg-2 g-1">
                    <label for="name" class="col-form-label col-sm-2"><font color="#FF0000">*</font>父级:</label>
                    <div class="col-sm-10">
                        <select class="form-control custom-select col-xs-12 col-sm-12" name="row[pid]" style="border-radius:0px;">
                            <option selected="selected" value="0">根节点</option>
                            @foreach ($menu_list as $menu)
                                <option value="{{ $menu['id'] }}" @if ($row->pid == $menu['id']) selected="selected" @endif >{{ $menu['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>

                <div class="form-group row g-lg-2 g-1">
                    <label for="name" class="col-form-label col-sm-2"><font color="#FF0000">*</font>规则名称:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="row[name]" placeholder="控制器名/方法名" value="{{ $row->name }}" data-join="check_name" data-rule="required|remote(/admin/system/config/unique)" />
                        <span style="color:#999;" class="fs-10">从跟目录下开始添加，例如 "/admin/test/add"</span>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>

                <div class="form-group row g-lg-2 g-1">
                    <label for="module" class="col-form-label col-sm-2"><font color="#FF0000">*</font>标题:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="title" name="row[title]" value="{{ $row->title }}" data-rule="required" />
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="icon" class="col-form-label col-sm-2">小图标:</label>
                    <div class="col-sm-10">
                        <div class="input-group input-groupp-md">
                            <div class="input-group">
                                <span class="input-group-text fs-20"><i class="{{ $row->icon }}" id="icon-preview"></i></span>
                                <input type="text" class="form-control" id="icon" name="row[icon]" value="{{ $row->icon }}" placeholder="小图标" />
                                <a href="javascript:;" class="input-group-text btn-search-icon input-group-addon">设置小图标</a>
                            </div>
                        </div>
                        <span style="color:#999;" class="fs-10">快捷搜索=><a href="https://tabler.io/icons" target="_blank">tabler</a> 3.33版本</span>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="weigh" class="col-form-label col-sm-2">权重:</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="weigh" name="row[weigh]" value="{{ $row->weigh }}" data-rule="required" />
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>

                <div class="form-group row g-lg-2 g-1">
                    <label for="badge_text" class="col-form-label col-sm-2">菜单徽章文字:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="badge_text" name="row[badge_text]" value="{{ $row->badge_text }}" placeholder="一般不设置" />
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>

                <div class="form-group row g-lg-2 g-1">
                    <label for="badge_text_style" class="col-form-label col-sm-2">菜单徽章样式:</label>
                    <div class="col-sm-10">
                        <select class="form-control custom-select col-xs-12 col-sm-12" name="row[badge_text_style]" style="border-radius:0px;">
                            <option value="badge-default" @if ($row->badge_text_style == 'badge-default') selected="selected" @endif >&nbsp;&nbsp;(默认)白色</option>
                            <option value="text-bg-primary" @if ($row->badge_text_style == 'text-bg-primary') selected="selected" @endif >&nbsp;&nbsp;主要(primary)</option>
                            <option value="text-bg-secondary" @if ($row->badge_text_style == 'text-bg-secondary') selected="selected" @endif >&nbsp;&nbsp;次要(secondary)</option>
                            <option value="text-bg-success" @if ($row->badge_text_style == 'text-bg-success') selected="selected" @endif >&nbsp;&nbsp;成功(success)</option>
                            <option value="text-bg-danger" @if ($row->badge_text_style == 'text-bg-danger') selected="selected" @endif >&nbsp;&nbsp;危险(danger)</option>
                            <option value="text-bg-warning" @if ($row->badge_text_style == 'text-bg-warning') selected="selected" @endif >&nbsp;&nbsp;警告(warning)</option>
                            <option value="text-bg-info" @if ($row->badge_text_style == 'text-bg-info') selected="selected" @endif >&nbsp;&nbsp;信息(info)</option>
                            <option value="text-bg-light" @if ($row->badge_text_style == 'text-bg-light') selected="selected" @endif >&nbsp;&nbsp;明亮(light)</option>
                            <option value="text-bg-dark" @if ($row->badge_text_style == 'text-bg-dark') selected="selected" @endif >&nbsp;&nbsp;黑暗(dark)</option>
                            <option value="text-bg-purple" @if ($row->badge_text_style == 'text-bg-purple') selected="selected" @endif >&nbsp;&nbsp;紫色(purple)</option>
                        </select>
                        <span style="color:#ccc;" class="fs-10">仅在设置了菜单徽章文字后生效</span>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>


                <div class="form-group row g-lg-2 g-1">
                    <label for="remark" class="col-form-label col-sm-2">备注:</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" id="remark" name="row[remark]">{{ $row->remark }}</textarea>
                    </div>
                </div>
                <div class="border-top border-dashed my-2"></div>
                <div class="form-group row g-lg-2 g-1">
                    <label for="content" class="col-form-label col-sm-2"><font color="#FF0000">*</font>状态:</label>
                    <div class="col-sm-10">
                        <label>
                            <input type="radio" name="row[status]" value="1" class="flat-red" @if ($row->status == 1) checked @endif>
                            启用
                        </label>
                        <label>
                            <input type="radio" name="row[status]" value="0" class="flat-red" @if ($row->status == 0) checked @endif>
                            停用
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
            </div>
        </form>
    </div>
    <!-- /.card -->


@endsection

@section('page_js')
    @parent
    <script>
        $(function () {

        });
        function check_name(ele){
            return {
                'type':'add_menu_check_name',
                'name':$(ele).val(),
                'id':"{{ $row['id']  }}"
            }
        }
    </script>
    <script id="chooseicontpl" type="text/html">
        <div id="chooseicon">
            <div>
                <form onsubmit="return false;">
                    <div class="input-group input-groupp-md">
                        <div class="input-group-addon" style="padding:7px 3px 0 0">搜索</div>
                        <input class="js-icon-search form-control" type="text" placeholder="">
                    </div>
                </form>
            </div>
            <div>
                <ul class="list-inline">
                    <% for(var i=0; i<iconlist.length; i++){ %>
                    <li data-font="<%=iconlist[i]%>" title="<%=iconlist[i]%>">
                        <i class="ti ti-<%=iconlist[i]%>"></i>
                    </li>
                    <% } %>
                </ul>
            </div>
        </div>
    </script>


    <!-- /*搜索icon 使用*/ -->
    <script type="text/javascript" src="{{ asset('static/libs/art-template/dist/template-native.js') }}"></script>
    <script type="text/javascript">
        var iconModal = null;
        //点击icon小图标
        $(document).on('click', '#chooseicon ul li', function () {
            // 赋值给input
            $("input[name='row[icon]']").val('ti ti-' + $(this).data("font"));
            $("#icon-preview").attr('class', 'ti ti-' + $(this).data("font"));

            if(iconModal){ iconModal.close();}
        });
        //点击icon小图标
        $(document).on('keyup', 'input.js-icon-search', function () {
            $("#chooseicon ul li").show();
            if ($(this).val() != '') {
                $("#chooseicon ul li:not([data-font*='" + $(this).val() + "'])").hide();
            }
        });
        // #icon输入框的值变动时#icon-preview的值一起变动
        $(document).on('keyup', '#icon', function () {
            $("#icon-preview").attr('class', $(this).val());
        })
        //点击search icon小图标弹出层
        $(document).on('click', ".btn-search-icon", function () {
            var iconlist = [];
            if (iconlist.length == 0) {
                $.get( "{{ asset('static/inspinia/v4.0/assets/css/variables.less') }}", function (ret) {
                    var exp = /ti-var-(.*):/ig;
                    var result;
                    while ((result = exp.exec(ret)) != null) {
                        iconlist.push(result[1]);
                    }

                    iconModal = new Modal({
                        title: '搜索icon小图标',
                        content: template('chooseicontpl', {iconlist: iconlist}),
                        width: 600,
                        height: 400,
                        bodyScroll:false,
                    }).open();
                });
            } else {
                iconModal = new Modal({
                    title: '搜索icon小图标',
                    content: template('chooseicontpl', {iconlist: iconlist}),
                    width: 600,
                    height: 400,
                    bodyScroll:false,
                }).open();
            }
        });

    </script>

@endsection
