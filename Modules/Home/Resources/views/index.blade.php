@extends('home::layouts.home_layout')

@section('content')
    @if (show_news_module())
        @include('home::layouts.home_news')
    @endif

    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 align-items-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <a href="/docs" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                    <h5 title="Number of Tasks">在线文档</h5>
                    <div class="d-flex align-items-center gap-2 my-3">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title text-bg-light rounded-circle fs-22">
                                <i class="ti ti-book-2"></i>
                            </span>
                        </div>
                        <h3 class="mb-0"><span data-target="124">0</span></h3>
                        <span class="badge badge-soft-primary fw-medium ms-2 fs-xs ms-auto">+3 New</span>
                    </div>
                    <p class="mb-0">
                        <span class="text-primary"><i class="ti ti-point-filled"></i></span>
                        <span class="text-nowrap text-muted">Total Tasks</span>
                        <span class="float-end"><b>12,450</b></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 title="Number of Messages">文章</h5>
                    <div class="d-flex align-items-center gap-2 my-3">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title text-bg-light rounded-circle fs-22">
                                <i class="ti ti-link"></i>
                            </span>
                        </div>
                        <h3 class="mb-0"><span data-target="69.5">0</span>k</h3>
                        <span class="badge badge-soft-secondary fw-medium ms-2 fs-xs ms-auto">+5 New</span>
                    </div>
                    <p class="mb-0">
                        <span class="text-secondary"><i class="ti ti-point-filled"></i></span>
                        <span class="text-nowrap text-muted">Total Messages</span>
                        <span class="float-end"><b>32.1M</b></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                    <h5 title="Pending Approvals">点赞</h5>
                    <div class="d-flex align-items-center gap-2 my-3">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title text-bg-light rounded-circle fs-22">
                                <i class="ti ti-thumb-up"></i>
                            </span>
                        </div>
                        <h3 class="mb-0"><span data-target="129.5">0</span>k</h3>
                        <span class="badge text-bg-light fw-medium ms-2 fs-xs ms-auto">+999</span>
                    </div>
                    <p class="mb-0">
                        <span class="text-primary"><i class="ti ti-point-filled"></i></span>
                        <span class="text-nowrap text-muted">Total Approvals</span>
                        <span class="float-end"><b>1,024</b></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                    <h5 title="Total Clients">用户</h5>
                    <div class="d-flex align-items-center gap-2 my-3">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title text-bg-light rounded-circle fs-22">
                                <i class="ti ti-users"></i>
                            </span>
                        </div>
                        <h3 class="mb-0"><span data-target="184">0</span></h3>
                        <span class="badge badge-soft-secondary fw-medium ms-2 fs-xs ms-auto">+4 New</span>
                    </div>
                    <p class="mb-0">
                        <span class="text-secondary"><i class="ti ti-point-filled"></i></span>
                        <span class="text-nowrap text-muted">Total Clients</span>
                        <span class="float-end"><b>9,835</b></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg col-md-auto">
            <div class="card">
                <div class="card-body">
                    <a href="index.html#!" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                    <h5 title="Revenue Generated">Revenue</h5>
                    <div class="d-flex align-items-center gap-2 my-3">
                        <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-light rounded-circle fs-22">
                                            <i class="ti ti-credit-card"></i>
                                        </span>
                        </div>
                        <h3 class="mb-0">$<span data-target="125.5">0</span>k</h3>
                        <span class="badge badge-soft-primary fw-medium ms-2 fs-xs ms-auto">+1.5%</span>
                    </div>
                    <p class="mb-0">
                        <span class="text-primary"><i class="ti ti-point-filled"></i></span>
                        <span class="text-nowrap text-muted">Total Revenue</span>
                        <span class="float-end"><b>$12.5M</b></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
