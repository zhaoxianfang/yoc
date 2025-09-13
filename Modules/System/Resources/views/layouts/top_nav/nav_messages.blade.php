@if (!empty($top_nav_messages) && !empty($top_nav_messages['total']) && !empty($top_nav_messages['items']))
<!-- Messages Dropdown -->
<div class="topbar-item">
    <div class="dropdown">
        <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
            <i data-lucide="mails" class="fs-xxl"></i>
            <span class="badge text-bg-success badge-circle topbar-badge">{{$top_nav_messages['total']}}</span>
        </button>

        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
            <div class="px-3 py-2 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-md fw-semibold">用户消息</h6>
                    </div>
                    <div class="col text-end">
                        <a href="#!" class="badge badge-soft-success badge-label py-1">{{$top_nav_messages['total']}} 条</a>
                    </div>
                </div>
            </div>

            <div style="max-height: 300px;" data-simplebar>
                <!-- item -->
                @foreach($top_nav_messages['items'] as $item)
                <div class="dropdown-item notification-item py-2 text-wrap active" id="message-{{$item['id']}}">
                    <a href="{{!empty($item['url'])?$item['url']:'#!'}}" target="_blank">
                        <span class="d-flex gap-3">
                            <span class="flex-shrink-0">
                                <img src="{{ asset('static/images/system/default_user.png') }}" class="avatar-md rounded-circle" alt="User Avatar">
                            </span>
                            <span class="flex-grow-1 text-muted">
                                <span class="fw-medium text-body">{{$item['user_name']}}</span>
                                <br>
                                <span class="fw-medium text-body">{{$item['title']}}</span>
                                <br>
                                <span class="fs-xs">{{$item['date']}}</span>
                            </span>
                            <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-1">
                                <i class="ti ti-xbox-x-filled fs-xxl"></i>
                            </button>
                        </span>
                    </a>
                </div>
                @endforeach

            </div>

            <!-- All-->
            <a href="javascript:void(0);" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                查看所有消息
            </a>

        </div> <!-- End dropdown-menu -->
    </div> <!-- end dropdown-->
</div>

@endif
