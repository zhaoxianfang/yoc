@extends('admin::layouts.admin_layout')
@section('title', '看板')

@section('content')
    {{-- 页面异常提示--}}
    <div class="pt-5">
        <div class="middle-box text-center">
            <h3 class="font-bold">{{ $code ?? '500' }}</h3>
            <div class="error-desc">
                {{ $message ?? '服务器异常' }}
                <br/><a href="{{ route('admin.home') }}" class="btn btn-primary mt-4">返回首页</a>
            </div>
        </div>
    </div>

@endsection
