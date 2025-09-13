@if (!empty($top_nav_notification) && !empty($top_nav_notification['total']) && !empty($top_nav_notification['items']))
<!-- Notification Dropdown -->
<div class="topbar-item">
    <div class="dropdown">
        <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
            <i data-lucide="bell" class="fs-xxl"></i>
            <span class="badge badge-square text-bg-warning topbar-badge">{{$top_nav_notification['total']}}</span>
        </button>

        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
            <div class="px-3 py-2 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-md fw-semibold">系统通知</h6>
                    </div>
                    <div class="col text-end">
                        <a href="#!" class="badge text-bg-light badge-label py-1">{{$top_nav_notification['total']}} 条</a>
                    </div>
                </div>
            </div>

            <div style="max-height: 300px;" data-simplebar>
                <!-- item -->
                @foreach($top_nav_notification['items'] as $item)
                    <div class="dropdown-item notification-item py-2 text-wrap" id="notification-{{$item['id']}}">
                        <a href="{{!empty($item['url'])?$item['url']:'javascript:;'}}" target="_blank">
                            <span class="d-flex gap-2">
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title bg-danger-subtle text-danger rounded fs-22">
                                        <i data-lucide="{{$item['lucide']}}" class="fs-xl fill-{{$item['color']}}"></i>
                                    </span>
                                </span>
                                <span class="flex-grow-1 text-muted">
                                    <span class="fw-medium text-body">{{$item['title']}}</span>
                                    <br>
                                    <span class="fs-xs">{{$item['date']}}</span>
                                </span>
                                <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#notification-{{$item['id']}}">
                                    <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                </button>
                            </span>
                        </a>
                    </div>
                @endforeach

            </div> <!-- end dropdown-->

            <!-- All-->
            <a href="javascript:void(0);" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                查看所有通知
            </a>

        </div>
    </div>
</div>

@endif
