@extends('errors/base')

@section('code', '419')

@section('content')
    <div class="message-box rect error-tips">
        <h2 class="font-bold">{{ __( !empty($message) ? $message : 'Page Expired') }}</h2>
        <div class="error-desc">
            {!! empty($describe)?'':$describe !!}
        </div>
    </div>
@endsection
