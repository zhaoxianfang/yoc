@extends('home::layouts.home')
@section('title', truncate($article->title,0,25))

@section('head_css')
<style>
    .article-content img,.article-content video{ max-width: 100%!important; height: auto; }
</style>
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="article-content mw-100">
                                    <div class="text-center">
                                        <h1 class="fs-xxxl mb-3 mw-100">
                                            {{ $article->title }}
                                        </h1>
                                    </div>

                                    <div class="fs-sm  mw-100">
                                        {!! $article->content ?? '暂无内容' !!}
                                    </div>
                                </div>
                            </div>


                            <p class="d-flex flex-wrap gap-1 text-muted mt-3 mb-0 pb-3 align-items-center justify-content-between fs-sm mw-100">
                                <span><i class="ti ti-eye-search"></i> 浏览: {{$article->read ?? '-'}}</span>
                                <span><i class="ti ti-clock"></i> 发布时间: {{$article->publish_time ?? $article->created_at}}</span>
                                <span><i class="ti ti-home-infinity"></i> 文章来源: {{ $article->author ?? ($article->classify->parent->name .'/'. $article->classify->name) }}</span>
                                @if (!empty($article->source_url))
                                    <a href="{{ $article->source_url }}" target="_blank" class="link-reset"> <span><i class="ti ti-link"></i> 查看原文</span> </a>
                                @endif
                            </p>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_js')
    <script type="text/javascript">
        function showToast(){
            let e=document.getElementById("toast-container"),t=(e||((e=document.createElement("div")).id="toast-container",e.className="toast-container position-fixed top-0 end-0 p-3",e.style.zIndex=1100,document.body.appendChild(e)),document.createElement("div"));t.className="toast text-bg-primary border-0 fade",t.setAttribute("role","alert"),t.setAttribute("aria-live","assertive"),t.setAttribute("aria-atomic","true"),t.innerHTML=`
        <div class="toast-header bg-white bg-opacity-10 text-white border-0">
            <img src="/static/images/logo/logo_mini.png" alt="brand-logo" height="16" class="me-1" />
            <strong class="me-auto text-white">提示</strong>
            <small>{{ config('app.name','威四方') }}</small>
            <button type="button" class="ms-2 btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            请自主甄别 <strong>网页内容</strong> ,谨防上当受骗。
        </div>
    `,e.appendChild(t),new bootstrap.Toast(t,{delay:7e3}).show(),t.addEventListener("hidden.bs.toast",()=>{t.remove()})
        }

        setTimeout(() =>{
            showToast()
        },600);
    </script>
@endsection
