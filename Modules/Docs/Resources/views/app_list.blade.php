@extends('docs::layouts.layout')
@section('title', ($nav_type== 'mine' ? '我的文档' : '广场'))

@section('top_nav_tabs')
    <a href="{{ url('/docs') }}">
        <li class="nav-tab @if(empty($nav_type) || $nav_type== 'home') active @endif">广场</li>
    </a>
    @if (auth()->check())
    <a href="{{ url('/docs/my') }}">
        <li class="nav-tab @if($nav_type== 'mine') active @endif">我的</li>
    </a>
    <a href="{{ url('/docs/create') }}">
        <li class="nav-tab @if($nav_type== 'create') active @endif">新建</li>
    </a>
    @endif
@endsection

@section('head_css')

    <style>
        /* ================ 卡片系统重置 ================ */
        .app-card img {
            max-width: none !important;
            height: auto !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
    </style>
@endsection

@section('content')
    {{-- <div style="border: 1px dashed #0a53be;">--}}
    <div>
        <div class="book-cards row no-gutters">
            @foreach ($docs_apps as $app)
                <div class="no-gutters col-12 col-xl-3 col-lg-3 col-md-4 col-sm-6">
                    <div class="book-card">
                        <a href="{{ url("/docs/{$app->id}") }}">
                            <div class="book-content-wrapper">
                                <div class="book-mark" data-type="{{ ["primary","success","warning","danger","info","purple","gold"][array_rand(["primary","success","warning","danger","info","purple","gold"])] }}">
                                    {{ $app->tag }}@if (!empty($app->tag) && $app->open_type != 1)/@endif{{$app->open_type == 1 ? '' : '私'}}
                                </div>
                                <img src="{{ $app->app_cover }}" alt="" class="book-card-img">
                                <div class="book-content">
                                    <div class="book-name">
                                        {{ $app->app_name }}
                                    </div>
                                    <div class="book-by">
                                        by {{ $app->team_name }}
                                    </div>
                                    <div class="book-sum">
                                        {{ $app->description }}
                                    </div>
                                </div>
                            </div>
                            <div class="book-users">
                                @foreach ($app->users as $user)
                                    <div class="book-user-profile">
                                        <img src="{{ $user->cover??'' }}" alt="" class="book-user-logo">
                                    </div>
                                @endforeach
                                <div class="book-user-name">
                                    <span> {{ $app->users->count() }} </span> 名成员, <span> {{ $app->docs_count }} </span> 篇文章
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
            @if (empty($docs_apps))
                <div class="no-gutters col-12 col-lg-2 col-md-3 col-sm-4">
                    <div class="book-card">

                    </div>
                </div>
            @endif
        </div>

        <!-- Laravel分页演示 -->
        <div class="col-12">
            <div class="text-center bg-gray">
                @if(is_mobile())
                    {{ $docs_apps->appends(['keyword'=>request()->input('keyword','')])->links('pagination::simple-bootstrap-5') }}
                @else
                    {{ $docs_apps->appends(['keyword'=>request()->input('keyword','')])->onEachSide(2)->links('pagination::bootstrap-5') }}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page_js')
    <script>

    </script>
@endsection
