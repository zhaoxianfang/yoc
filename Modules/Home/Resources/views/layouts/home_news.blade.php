<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header justify-content-between align-items-center">
                <h5 class="card-title">文章推荐
                    <span class="badge bg-primary-subtle text-primary">+</span>
                </h5>
                <a href="javascript:;" class="badge text-bg-light fs-xs fw-semibold p-1">本周收录 {{ $week_article_count ?? 0 }} 条</a>
            </div>

            <div class="card-body pt-1">
                <ul class="list-group list-group-flush mb-3">
                    @foreach ($article_top as $article)
                    <li class="list-group-item px-0 border-light">
                        <a href="{{ url("/article/{$article->id}") }}" style="color: unset;">
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1 text-muted">
                                <h2 class="text-body mb-1 fs-base d-flex justify-content-between">
                                    {{$article->title ?? '文章标题'}}
                                </h2>
                                <p class="mb-1">{{ truncate($article->content ?? '',0,70)}}</p>
                                <small class="badge fs-xs text-body-secondary">{{$article->publish_time ?? $article->created_at}}</small>
                                <a href="javascript:;" class="badge badge-soft-primary p-1 float-end">来源:{{ $article->author ?? ($article->classify->parent->name .'/'. $article->classify->name) }}</a>
                            </div>
                        </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    @foreach ($random_classify as $classify)
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header justify-content-between align-items-center">
                    <h5 class="card-title">「{{ ($classify->parent?->name).'/'.$classify->name }}」
                        <span class="badge bg-primary-subtle text-primary">+</span>
                    </h5>
                    <a href="javascript:;" class="badge text-bg-light fs-xs fw-semibold p-1">已收录 {{ $classify->articles_count ?? 0 }} 条</a>
                </div>

                <div class="card-body pt-1">
                    <ul class="list-group list-group-flush mb-3">
                        @foreach ($classify->recommend_article as $article)
                            <li class="list-group-item px-0 border-light">
                                <a href="{{ url("/article/{$article->id}") }}" style="color: unset;">
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1 text-muted">
                                        <h2 class="text-body mb-1 fs-base d-flex justify-content-between">
                                            {{$article->title ?? '文章标题'}}
                                        </h2>
                                        <p class="mb-1">{{ truncate($article->content ?? '',0,70)}}</p>
                                        <small class="badge fs-xs text-body-secondary">{{$article->publish_time ?? $article->created_at}}</small>
                                        <a href="javascript:;" class="badge badge-soft-primary p-1 float-end">来源:{{ $article->author ?? ($article->classify->parent->name .'/'. $article->classify->name) }}</a>
                                    </div>
                                </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="text-center mt-3">
                        <a href="javascript:;" class="link-reset text-decoration-underline fw-semibold link-offset-3">
                            查看更多 <i class="ti ti-send-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>
