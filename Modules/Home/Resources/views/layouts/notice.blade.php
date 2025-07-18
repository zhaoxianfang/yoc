<!-- Notification Dropdown -->
<div class="topbar-item">
    <div class="dropdown">
        <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
            <i data-lucide="bell" class="fs-xxl"></i>
            <span class="badge badge-square text-bg-warning topbar-badge">4</span>
        </button>

        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
            <div class="px-3 py-2 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-md fw-semibold">消息通知</h6>
                    </div>
                    <div class="col text-end">
                        <a href="layouts-horizontal.html#!" class="badge text-bg-light badge-label py-1">4 Alerts</a>
                    </div>
                </div>
            </div>

            <div style="max-height: 300px;" data-simplebar>
                <!-- item 1 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="notification-1">
                    <span class="d-flex gap-2">
                        <span class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle text-danger rounded fs-22">
                                <i data-lucide="server-crash" class="fs-xl fill-danger"></i>
                            </span>
                        </span>
                        <span class="flex-grow-1 text-muted">
                            <span class="fw-medium text-body">Critical alert: Server crash detected</span>
                            <br>
                            <span class="fs-xs">30 minutes ago</span>
                        </span>
                        <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#notification-1">
                            <i class="ti ti-xbox-x-filled fs-xxl"></i>
                        </button>
                    </span>
                </div>

                <!-- item 2 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="notification-2">
                    <span class="d-flex gap-2">
                        <span class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning rounded fs-22">
                                <i data-lucide="alert-triangle" class="fs-xl fill-warning"></i>
                            </span>
                        </span>
                        <span class="flex-grow-1 text-muted">
                            <span class="fw-medium text-body">High memory usage on Node A</span>
                            <br>
                            <span class="fs-xs">10 minutes ago</span>
                        </span>
                        <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#notification-2">
                            <i class="ti ti-xbox-x-filled fs-xxl"></i>
                        </button>
                    </span>
                </div>

                <!-- item 3 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="notification-3">
                    <span class="d-flex gap-2">
                        <span class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded fs-22">
                                <i data-lucide="check-circle" class="fs-xl fill-success"></i>
                            </span>
                        </span>
                        <span class="flex-grow-1 text-muted">
                            <span class="fw-medium text-body">Backup completed successfully</span>
                            <br>
                            <span class="fs-xs">1 hour ago</span>
                        </span>
                        <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#notification-3">
                            <i class="ti ti-xbox-x-filled fs-xxl"></i>
                        </button>
                    </span>
                </div>

                <!-- item 4 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="notification-4">
                    <span class="d-flex gap-2">
                        <span class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded fs-22">
                                <i data-lucide="user-plus" class="fs-xl fill-primary"></i>
                            </span>
                        </span>
                        <span class="flex-grow-1 text-muted">
                            <span class="fw-medium text-body">New user registration: Sarah Miles</span>
                            <br>
                            <span class="fs-xs">Just now</span>
                        </span>
                        <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#notification-4">
                            <i class="ti ti-xbox-x-filled fs-xxl"></i>
                        </button>
                    </span>
                </div>
            </div> <!-- end dropdown-->

            <!-- All-->
            <a href="javascript:void(0);" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                View All Alerts
            </a>

        </div>
    </div>
</div>
