@extends('errors/base')

@section('code', '404')

@section('content')
    <div class="message-box rect error-tips">
        <h2 class="font-bold">{{ __( !empty($message) ? $message : 'Page Not Found') }}</h2>
        <div class="error-desc">
            {!! empty($describe)?'':$describe !!}
        </div>
    </div>
@endsection
