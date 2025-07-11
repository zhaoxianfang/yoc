@extends('core::layouts.master')

@section('content')
    <div class="content">
        <div class="message-box rect">
            <p class="gradient">模块: {!! config('core.name') !!}</p>
            <p>文档:https://weisifang.com/docs/2</p>
            <p>插件:composer require zxf/tools&nbsp;&nbsp;&nbsp;</p>
        </div>
    </div>
@endsection
