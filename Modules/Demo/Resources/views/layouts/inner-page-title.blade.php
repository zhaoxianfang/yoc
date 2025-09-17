<div class="page-title-head d-flex align-items-center">
    <div class="flex-grow-1">
        <h4 class="fs-sm text-uppercase fw-bold m-0">@yield('page_inner_title','')</h4>
    </div>

    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ config('app.name','威四方') }}</a></li>

            <li class="breadcrumb-item"><a href="javascript: void(0);">示例</a></li>

            <li class="breadcrumb-item active">@yield('page_inner_title','')</li>
        </ol>
    </div>
</div>
