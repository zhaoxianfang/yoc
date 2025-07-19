@extends('home::layouts.home_layout')
@section('title', (!empty($classify->parent)? $classify->parent->name.'/' : '').$classify->name)

@section('head_css')

@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="row">
                <div class="col-xl-12">
                    <h4 class="mb-3">{{ !empty($classify->parent)? $classify->parent->name.'/' : ''}}{{$classify->name}}</h4>
                    @foreach ($articles as $article)
                    <div class="card mb-1">
                        <a href="{{ url("/article/{$article->id}") }}" style="color: unset;">
                            <div class="card-body p-3 pt-2 pb-2">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted text-uppercase mb-2 fw-semibold">
                                            {{ $article->author ?? ($article->classify->parent->name .'/'. $article->classify->name) }}
                                        </p>

                                        <h4 class="fs-lg mb-2">
                                            <span class="link-reset">{{$article->title ?? '标题'}}</span>
                                        </h4>

                                        <p class="text-muted mb-0">
                                            {{ truncate($article->content ?? '',0,120) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-3 pt-2 pb-2">
                                <p class="d-flex flex-wrap gap-3 text-muted mb-0 align-items-center justify-content-between fs-sm">
                                    <span class="d-flex align-items-center gap-2">
                                        <span>
                                            <span class="link-dark fw-semibold lh-sm d-block">管理员</span>
                                            <span class="text-muted small">1 hour ago</span>
                                        </span>
                                    </span>
                                    <span><i class="ti ti-message-reply"></i> 点赞: 45</span>
                                    <span><i class="ti ti-clock"></i> 时间: {{$article->publish_time ?? $article->created_at}}</span>
                                    <span><i class="ti ti-users"></i> 浏览: {{$article->read ?? '-'}}</span>
                                </p>
                            </div>
                        </a>
                    </div>
                    @endforeach

                    @if (empty($articles))
                        <div class="card mb-1">
                            <div class="text-center p-4 fs-18">
                                暂无数据
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>

        <div class="col-12">
            <ul class="text-sm-center justify-content-center">
                @if(is_mobile())
                    {{ $articles->appends(['keyword'=>request()->input('keyword','')])->links('pagination::simple-bootstrap-5') }}
                @else
                    {{ $articles->appends(['keyword'=>request()->input('keyword','')])->onEachSide(4)->links('pagination::bootstrap-5') }}
                @endif
            </ul>
        </div>
    </div>

@endsection
