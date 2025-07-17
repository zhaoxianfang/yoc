@extends('docs::layouts.layout')
@section('title', ( $docs_doc?->title .(!empty($docs_app)? ' | '.$docs_app?->app_name:'' )))
@section('page_has_menu', "true")

@section('head_css')
    @parent
    <link href="{{ asset('static/libs/zxf/css/bootstrap-form.css') }}" rel="stylesheet" type="text/css">
    <style>
        .toc{display: none!important;}
        .app-container{grid-template-columns: 220px 1fr 10px!important;}
    </style>
@endsection

@section('top_nav_tabs')
    <li class="nav-tab nav-tab-item @if(empty($category) || $category == 'guide') active @endif" data-category="guide">指南</li>
    @if(!empty($docs_has_api_category))
    <li class="nav-tab nav-tab-item @if(!empty($category) && $category == 'api') active @endif" data-category="api">API</li>
    @endif
    <li class="nav-tab nav-tab-item @if(!empty($category) && $category == 'faq') active @endif" data-category="faq">常见问题</li>
@endsection

@section('content')
    <div class="row bg-white">
        <div class="col-12">
            <form method="post" class="form_docs unbind-form mb-0">
                @csrf
                <div class="form-group row">
                    <div class="col-sm-12 col-md-4 row no-gutters">
                        <label class="col-sm-2 col-form-label">标题<font color="#FF0000">*</font></label>
                        <div class="col-sm-10"><input type="text" class="form-control" name="title" value="{{ old("title",!empty($docs_doc)?$docs_doc->title:'') }}" data-rule="required" /></div>
                    </div>
                    <div class="col-sm-12 col-md-4 row no-gutters">
                        <label class="col-sm-2 col-form-label">菜单<font color="#FF0000">*</font></label>
                        <div class="col-sm-10">
                            <select class="form-control col-xs-12 col-sm-12" name="doc_menu_id" data-tips title="菜单" required >
                                @foreach ($menus_tree as $menu)
                                    <option value="{{$menu['id']}}" @if (old('doc_menu_id',$docs_menu->id) == $menu['id']) selected @endif>{{$menu['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4 row no-gutters">
                        <label class="col-sm-2 col-form-label">类型<font color="#FF0000">*</font></label>
                        <div class="col-sm-10">
                            <select class="form-control col-xs-12 col-sm-12" name="open_type" data-tips title="公开类型" required >
                                <option value="1" @if (old('open_type', (!empty($docs_doc) ? $docs_doc->open_type : 1)) == '1') selected @endif>公开</option>
                                <option value="2" @if (old('open_type', (!empty($docs_doc) ? $docs_doc->open_type : 1)) == '2') selected @endif>需要登录才可见</option>
                                <option value="3" @if (old('open_type', (!empty($docs_doc) ? $docs_doc->open_type : 1)) == '3') selected @endif>仅自己可见</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        @include("docs::edit/editor/{$editor_name}",['editor_name' => 'content','content_value'=>old('content',(!empty($docs_doc) ? (!empty($docs_doc->content)?$docs_doc->content:($docs_doc->content_html??'')) : ''))])
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-w-m btn-primary width-full">保存并提交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_js_before')
    <script>
        // 定义需要激活的菜单大项[ currentCategory ]
        var nav_category = '{!! $category ?? '' !!}';
        // 菜单数据
        const menuData = JSON.parse('{!! addslashes(json_encode($menus, JSON_UNESCAPED_UNICODE)) !!}');
        // console.log(menuData);
    </script>

@endsection

@section('page_js')

@endsection
