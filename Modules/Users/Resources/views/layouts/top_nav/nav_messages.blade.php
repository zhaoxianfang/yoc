<!-- Messages Dropdown -->
<div class="topbar-item">
    <div class="dropdown">
        <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
            <i data-lucide="mails" class="fs-xxl"></i>
            <span class="badge text-bg-success badge-circle topbar-badge">7</span>
        </button>

        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
            <div class="px-3 py-2 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-md fw-semibold">Messages</h6>
                    </div>
                    <div class="col text-end">
                        <a href="#!" class="badge badge-soft-success badge-label py-1">09 Notifications</a>
                    </div>
                </div>
            </div>

            <div style="max-height: 300px;" data-simplebar>
                <!-- item 1 -->
                <div class="dropdown-item notification-item py-2 text-wrap active" id="message-1">
                                <span class="d-flex gap-3">
                                    <span class="flex-shrink-0">
                                        <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-1.jpg') }}" class="avatar-md rounded-circle" alt="User Avatar">
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Liam Carter</span> uploaded a new document to <span class="fw-medium text-body">Project Phoenix</span>
                                        <br>
                                        <span class="fs-xs">5 minutes ago</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-1">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>

                <!-- item 2 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="message-2">
                                <span class="d-flex gap-3">
                                    <span class="flex-shrink-0">
                                        <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-2.jpg') }}" class="avatar-md rounded-circle" alt="User Avatar">
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Ava Mitchell</span> commented on <span class="fw-medium text-body">Marketing Campaign Q3</span>
                                        <br>
                                        <span class="fs-xs">12 minutes ago</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-2">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>

                <!-- item 3 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="message-3">
                                <span class="d-flex gap-3">
                                    <span class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-info rounded-circle fs-22">
                                            <i data-lucide="shield-user" class="fs-22 fill-white"></i>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Noah Blake</span> updated the status of <span class="fw-medium text-body">Client Onboarding</span>
                                        <br>
                                        <span class="fs-xs">30 minutes ago</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-3">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>

                <!-- item 4 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="message-4">
                                <span class="d-flex gap-3">
                                    <span class="flex-shrink-0">
                                        <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-4.jpg') }}" class="avatar-md rounded-circle" alt="User Avatar">
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Sophia Taylor</span> sent an invoice for <span class="fw-medium text-body">Service Renewal</span>
                                        <br>
                                        <span class="fs-xs">1 hour ago</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-4">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>

                <!-- item 5 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="message-5">
                                <span class="d-flex gap-3">
                                    <span class="flex-shrink-0">
                                        <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-5.jpg') }}" class="avatar-md rounded-circle" alt="User Avatar">
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Ethan Moore</span> completed the task <span class="fw-medium text-body">UI Review</span>
                                        <br>
                                        <span class="fs-xs">2 hours ago</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-5">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>

                <!-- item 6 -->
                <div class="dropdown-item notification-item py-2 text-wrap" id="message-6">
                                <span class="d-flex gap-3">
                                    <span class="flex-shrink-0">
                                        <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-6.jpg') }}" class="avatar-md rounded-circle" alt="User Avatar">
                                    </span>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Olivia White</span> assigned you a task in <span class="fw-medium text-body">Sales Pipeline</span>
                                        <br>
                                        <span class="fs-xs">Yesterday</span>
                                    </span>
                                    <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0" data-dismissible="#message-6">
                                        <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                    </button>
                                </span>
                </div>
            </div>

            <!-- All-->
            <a href="javascript:void(0);" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                Read All Messages
            </a>

        </div> <!-- End dropdown-menu -->
    </div> <!-- end dropdown-->
</div>
