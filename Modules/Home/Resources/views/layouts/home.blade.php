<!DOCTYPE html>
<html lang="zh_CN"  data-layout="topnav" data-topbar-color="light" data-menu-color="light" data-skin="modern" data-bs-theme="light" data-layout-position="fixed" data-sidenav-size="condensed" data-sidenav-user="true">

<head>
    <meta charset="utf-8">
    <title>Horizontal Menu | 威四方 - 响应式模版</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Inspinia is the #1 best-selling admin dashboard template on Wrapmarket. Perfect for building CRM, CMS, project management tools, and custom web apps with clean UI, responsive design, and powerful features.">
    <meta name="keywords" content="Inspinia, admin dashboard, Wrapmarket, Wrapbootstrap, HTML template, Bootstrap admin, CRM template, CMS template, responsive admin, web app UI, admin theme, best admin template">
    <meta name="author" content="weisifang.com,威四方">
    <meta property="og:url" content="https://weisifang.com">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('static/inspinia/v4.0/assets/images/favicon.ico') }}">

    <!-- Theme Config Js -->
    <script src="{{ asset('static/inspinia/v4.0/assets/js/config.js') }}"></script>

    <!-- Vendor css -->
    <link href="{{ asset('static/inspinia/v4.0/assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="{{ asset('static/inspinia/v4.0/assets/css/app.min.css') }}" rel="stylesheet" type="text/css">
    <style>

    </style>
</head>

<body>
<!-- Begin page -->
<div class="wrapper">

    <!-- Topbar Start -->
    <header class="app-topbar">
        <div class="container-fluid topbar-menu">
            <div class="d-flex align-items-center gap-1">
                <!-- Topbar Brand Logo -->
                <div class="logo-topbar">
                    <!-- Logo light -->
                    <a href="index.html" class="logo-light">
                            <span class="logo-lg">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/logo.png') }}" alt="logo">
                            </span>
                        <span class="logo-sm">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-sm.png') }}" alt="small logo">
                            </span>
                    </a>

                    <!-- Logo Dark -->
                    <a href="index.html" class="logo-dark">
                            <span class="logo-lg">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-black.png') }}" alt="dark logo">
                            </span>
                        <span class="logo-sm">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/logo-sm.png') }}" alt="small logo">
                            </span>
                    </a>
                </div>

                <!-- Sidebar Menu Toggle Button -->
                <button class="sidenav-toggle-button btn btn-primary btn-icon">
                    <i class="ti ti-menu-4 fs-22"></i>
                </button>

                <!-- Horizontal Menu Toggle Button -->
                <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <i class="ti ti-menu-4 fs-22"></i>
                </button>

                <!-- Mega Menu Dropdown -->
                <div class="topbar-item d-none d-sm-flex">
                    {!! empty($mega_menu)?'':$mega_menu !!}
                </div>

                <!-- 自定义调整的顶部菜单-->
                <header class="topnav topbar-item flex-horizontal-topnav">
                    <nav class="navbar navbar-expand-md">
                        <nav class="container-fluid">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <ul class="navbar-nav">
                                    {!! empty($classify_top_nav)?'':$classify_top_nav !!}
                                </ul>
                            </div>
                        </nav>
                    </nav>
                </header>

                <!-- Search -->
                <div class="app-search d-xl-flex flex-horizontal-top-search">
                    <input type="search" class="form-control topbar-search" name="search" placeholder="搜索...">
                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                </div>
            </div> <!-- .d-flex-->

            <div class="d-flex align-items-center gap-1">
                <!-- Language Dropdown -->
                <div class="topbar-item">
                    <div class="dropdown">
                        <button class="topbar-link fw-bold" data-bs-toggle="dropdown" data-bs-offset="0,21" type="button" aria-haspopup="false" aria-expanded="false">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/cn.svg') }}" alt="user-image" class="w-100 rounded me-1" height="18" id="selected-language-image">
                            <span id="selected-language-code"> 中文 </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="cn" title="中文">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/cn.svg') }}" alt="中文" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">中文</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="en" title="English">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/us.svg') }}" alt="English" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">English</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="de" title="German">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/de.svg') }}" alt="German" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">Deutsch</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="it" title="Italian">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/it.svg') }}" alt="Italian" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">Italiano</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="es" title="Spanish">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/es.svg') }}" alt="Spanish" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">Español</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="ru" title="Russian">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/ru.svg') }}" alt="Russian" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">Русский</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="hi" title="Hindi">
                                <img src="{{ asset('static/inspinia/v4.0/assets/images/flags/in.svg') }}" alt="Hindi" class="me-1 rounded" height="18" data-translator-image>
                                <span class="align-middle">हिन्दी</span>
                            </a>
                        </div> <!-- end dropdown-menu-->
                    </div> <!-- end dropdown-->
                </div> <!-- end topbar item-->

                <!-- Messages Dropdown -->

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

                <!-- Light/Dark Mode Button -->
                <div class="topbar-item d-none d-sm-flex">
                    <button class="topbar-link" id="light-dark-mode" type="button">
                        <i data-lucide="moon" class="fs-xxl mode-light-moon"></i>
                        <i data-lucide="sun" class="fs-xxl mode-light-sun"></i>
                    </button>
                </div>

                <!-- User Dropdown -->
                <div class="topbar-item nav-user">
                    <div class="dropdown">
                        <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,16" href="layouts-horizontal.html#!" aria-haspopup="false" aria-expanded="false">
                            <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-2.jpg') }}" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                            <div class="d-lg-flex align-items-center gap-1 d-none">
                                <h5 class="my-0">Damian D.</h5>
                                <i class="ti ti-chevron-down align-middle"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- Header -->
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Welcome back!</h6>
                            </div>

                            <!-- My Profile -->
                            <a href="pages-profile.html" class="dropdown-item">
                                <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Profile</span>
                            </a>

                            <!-- Notifications -->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="ti ti-bell-ringing me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Notifications</span>
                            </a>

                            <!-- Wallet -->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="ti ti-credit-card me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Balance: <span class="fw-semibold">$985.25</span></span>
                            </a>

                            <!-- Settings -->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="ti ti-settings-2 me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Account Settings</span>
                            </a>

                            <!-- Support -->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="ti ti-headset me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Support Center</span>
                            </a>

                            <!-- Divider -->
                            <div class="dropdown-divider"></div>

                            <!-- Lock -->
                            <a href="auth-lock-screen.html" class="dropdown-item">
                                <i class="ti ti-lock me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Lock Screen</span>
                            </a>

                            <!-- Logout -->
                            <a href="javascript:void(0);" class="dropdown-item text-danger fw-semibold">
                                <i class="ti ti-logout-2 me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Log Out</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Topbar End -->

    <!-- Horizontal Menu Start -->
    <!-- 已经调整到顶部导航中去了-->
    <!-- Horizontal Menu End -->

    <!-- ============================================================== -->
    <!-- Start Main Content -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="container-fluid">

            <div class="page-title-head d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="fs-sm text-uppercase fw-bold m-0">Horizontal</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inspinia</a></li>

                        <li class="breadcrumb-item active">Horizontal</li>
                    </ol>
                </div>
            </div>


            <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">
                <!-- Total Sales Widget -->
                <div class="col">
                    <div class="card">
                        <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                            <h5 class="card-title">Total Sales</h5>
                            <span class="badge badge-soft-success"> Monthly</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                                <div class="text-end">
                                    <h3 class="mb-2 fw-normal">$<span data-target="250">0</span>K</h3>
                                    <p class="mb-0 text-muted"><span>Monthly Total Sales</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->

                <!-- Total Orders Widget -->
                <div class="col">
                    <div class="card">
                        <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                            <h5 class="card-title">Total Orders</h5>
                            <span class="badge badge-soft-primary"> Monthly</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                                <div class="text-end">
                                    <h3 class="mb-2 fw-normal"><span data-target="180">0</span></h3>
                                    <p class="mb-0 text-muted"><span>Monthly Total Orders</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->

                <!-- New Customers Widget -->
                <div class="col">
                    <div class="card">
                        <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                            <h5 class="card-title">New Customers</h5>
                            <span class="badge badge-soft-info"> Monthly</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                                <div class="text-end">
                                    <h3 class="mb-2 fw-normal"><span data-target="50,895">0</span></h3>
                                    <p class="mb-0 text-muted"><span>Monthly New Customers</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->

                <!-- Monthly Revenue Widget -->
                <div class="col">
                    <div class="card">
                        <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                            <h5 class="card-title">Revenue</h5>
                            <span class="badge badge-soft-warning"> Monthly</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                                <div class="text-end">
                                    <h3 class="mb-2 fw-normal">$<span data-target="50.33">0</span>K</h3>
                                    <p class="mb-0 text-muted"><span>Monthly Revenue</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
            </div><!-- end row -->

            <div class="row">
                <div class="col-xxl-6">
                    <div data-table data-table-rows-per-page="5" class="card">
                        <div class="card-header justify-content-between align-items-center border-dashed">
                            <h4 class="card-title mb-0">Product Inventory</h4>
                            <div class="d-flex gap-2">
                                <a href="ecommerce-add-product.html" class="btn btn-sm btn-soft-secondary">
                                    <i class="ti ti-plus me-1"></i> Add Product
                                </a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary">
                                    <i class="ti ti-file-export me-1"></i> Export CSV
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/products/1.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-product-details.html" class="text-body">Smart Watch</a></h5>
                                                    <span class="text-muted fs-xs">Wearables</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Stock</span>
                                            <h5 class="fs-base mt-1 fw-normal">120 units</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Price</span>
                                            <h5 class="fs-base mt-1 fw-normal">$89.99</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Ratings</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                        <span class="text-warning">
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star"></span>
                                                        </span>
                                                <span class="ms-1"><a href="ecommerce-reviews.html" class="link-reset fw-semibold">(45)</a></span>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-success"></i> Active</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Edit Product</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/products/2.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-product-details.html" class="text-body">Bluetooth Speaker</a>
                                                    </h5>
                                                    <span class="text-muted fs-xs">Audio</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Stock</span>
                                            <h5 class="fs-base mt-1 fw-normal">75 units</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Price</span>
                                            <h5 class="fs-base mt-1 fw-normal">$39.50</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Ratings</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                        <span class="text-warning">
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star"></span>
                                                            <span class="ti ti-star"></span>
                                                        </span>
                                                <span class="ms-1"><a href="ecommerce-reviews.html" class="link-reset fw-semibold">(20)</a></span>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-warning"></i> Low Stock</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Edit Product</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/products/4.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-product-details.html" class="text-body">Gaming Mouse</a></h5>
                                                    <span class="text-muted fs-xs">Accessories</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Stock</span>
                                            <h5 class="fs-base mt-1 fw-normal">0 units</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Price</span>
                                            <h5 class="fs-base mt-1 fw-normal">$24.99</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Ratings</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                        <span class="text-warning">
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                        </span>
                                                <span class="ms-1"><a href="ecommerce-reviews.html" class="link-reset fw-semibold">(14)</a></span>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-danger"></i> Out of Stock</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Edit Product</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/products/5.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-product-details.html" class="text-body">4K Action Camera</a>
                                                    </h5>
                                                    <span class="text-muted fs-xs">Cameras</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Stock</span>
                                            <h5 class="fs-base mt-1 fw-normal">60 units</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Price</span>
                                            <h5 class="fs-base mt-1 fw-normal">$149.00</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Ratings</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                        <span class="text-warning">
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star"></span>
                                                        </span>
                                                <span class="ms-1"><a href="ecommerce-reviews.html" class="link-reset fw-semibold">(31)</a></span>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-success"></i> Active</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Edit Product</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/products/6.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-product-details.html" class="text-body">Fitness Tracker Band</a>
                                                    </h5>
                                                    <span class="text-muted fs-xs">Wearables</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Stock</span>
                                            <h5 class="fs-base mt-1 fw-normal">220 units</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Price</span>
                                            <h5 class="fs-base mt-1 fw-normal">$34.95</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Ratings</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                        <span class="text-warning">
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-filled"></span>
                                                            <span class="ti ti-star-half-filled"></span>
                                                        </span>
                                                <span class="ms-1"><a href="ecommerce-reviews.html" class="link-reset fw-semibold">(18)</a></span>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-success"></i> Active</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Edit Product</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div> <!-- end card-body-->

                        <div class="card-footer border-0">
                            <div class="align-items-center justify-content-between row text-center text-sm-start">
                                <div class="col-sm">
                                    <div data-table-pagination-info="products"></div>
                                </div>
                                <div class="col-sm-auto mt-3 mt-sm-0">
                                    <div data-table-pagination></div>
                                </div> <!-- end col-->
                            </div> <!-- end row-->
                        </div> <!-- end card-footer-->
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-xxl-6">
                    <div data-table data-table-rows-per-page="5" class="card">
                        <div class="card-header justify-content-between align-items-center border-dashed">
                            <h4 class="card-title mb-0">Recent Orders</h4>
                            <div class="d-flex gap-2">
                                <a href="javascript:void(0);" class="btn btn-sm btn-soft-secondary">
                                    <i class="ti ti-plus me-1"></i> Add Order
                                </a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary">
                                    <i class="ti ti-file-export me-1"></i> Export CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-1.jpg') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-order-details.html" class="text-body">#ORD-1001</a></h5>
                                                    <span class="text-muted fs-xs">John Doe</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Product</span>
                                            <h5 class="fs-base mt-1 fw-normal">Smart Watch</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Date</span>
                                            <h5 class="fs-base mt-1 fw-normal">2025-04-29</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Amount</span>
                                            <h5 class="fs-base mt-1 fw-normal">$89.99</h5>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal">
                                                <i class="ti ti-circle-filled fs-xs text-success"></i> Delivered
                                            </h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">View Details</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Cancel Order</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Repeat for other orders -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-2.jpg') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-order-details.html" class="text-body">#ORD-1002</a></h5>
                                                    <span class="text-muted fs-xs">Emma Watson</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted fs-xs">Product</span>
                                            <h5 class="fs-base mt-1 fw-normal">Bluetooth Speaker</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Date</span>
                                            <h5 class="fs-base mt-1 fw-normal">2025-04-28</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Amount</span>
                                            <h5 class="fs-base mt-1 fw-normal">$39.50</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-warning"></i> Pending</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">View Details</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Cancel Order</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-4.jpg') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-order-details.html" class="text-body">#ORD-1003</a></h5>
                                                    <span class="text-muted fs-xs">Liam Johnson</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted fs-xs">Product</span>
                                            <h5 class="fs-base mt-1 fw-normal">Smart Watch</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Date</span>
                                            <h5 class="fs-base mt-1 fw-normal">2025-04-27</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Amount</span>
                                            <h5 class="fs-base mt-1 fw-normal">$89.99</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-success"></i> Completed</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">View Details</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Cancel Order</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-6.jpg') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-order-details.html" class="text-body">#ORD-1004</a></h5>
                                                    <span class="text-muted fs-xs">Olivia Brown</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted fs-xs">Product</span>
                                            <h5 class="fs-base mt-1 fw-normal">Gaming Mouse</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Date</span>
                                            <h5 class="fs-base mt-1 fw-normal">2025-04-26</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Amount</span>
                                            <h5 class="fs-base mt-1 fw-normal">$24.99</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-danger"></i> Cancelled</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">View Details</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Cancel Order</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('static/inspinia/v4.0/assets/images/users/user-5.jpg') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                <div>
                                                    <h5 class="fs-base my-1"><a href="ecommerce-order-details.html" class="text-body">#ORD-1005</a></h5>
                                                    <span class="text-muted fs-xs">Noah Smith</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-muted fs-xs">Product</span>
                                            <h5 class="fs-base mt-1 fw-normal">Fitness Tracker Band</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Date</span>
                                            <h5 class="fs-base mt-1 fw-normal">2025-04-25</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Amount</span>
                                            <h5 class="fs-base mt-1 fw-normal">$34.95</h5>
                                        </td>
                                        <td><span class="text-muted fs-xs">Status</span>
                                            <h5 class="fs-base mt-1 fw-normal"><i class="ti ti-circle-filled fs-xs text-success"></i> Completed</h5>
                                        </td>
                                        <td style="width: 30px;">
                                            <div class="dropdown">
                                                <a href="layouts-horizontal.html#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical fs-lg"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">View Details</a>
                                                    <a href="layouts-horizontal.html#" class="dropdown-item">Cancel Order</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div> <!-- end card-body-->

                        <div class="card-footer border-0">
                            <div class="align-items-center justify-content-between row text-center text-sm-start">
                                <div class="col-sm">
                                    <div data-table-pagination-info="orders"></div>
                                </div>
                                <div class="col-sm-auto mt-3 mt-sm-0">
                                    <div data-table-pagination></div>
                                </div> <!-- end col-->
                            </div> <!-- end row-->
                        </div> <!-- end card-footer-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div> <!-- end row-->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header justify-content-between align-items-center">
                            <h5 class="card-title">Transactions Worldwide</h5>
                            <div class="card-action">
                                <a href="layouts-horizontal.html#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                                <a href="layouts-horizontal.html#!" class="card-action-item" data-action="card-refresh"><i class="ti ti-refresh"></i></a>
                                <a href="layouts-horizontal.html#!" class="card-action-item" data-action="card-close"><i class="ti ti-x"></i></a>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div class="row align-items-center">
                                <div class="col-xl-6">
                                    <div class="table-responsive">
                                        <table class="table table-custom table-nowrap table-hover table-centered mb-0">
                                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                            <tr class="text-uppercase fs-xxs">
                                                <th class="text-muted">Tran. No.</th>
                                                <th class="text-muted">Order</th>
                                                <th class="text-muted">Date</th>
                                                <th class="text-muted">Amount</th>
                                                <th class="text-muted">Status</th>
                                                <th class="text-muted">Payment Method</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><a href="layouts-horizontal.html#!" class="link-reset fw-semibold">#TR-3468</a></td>
                                                <td>#ORD-1003 - Smart Watch</td>
                                                <td>27 Apr 2025 <small class="text-muted">02:15 PM</small></td>
                                                <td class="fw-semibold">$89.99</td>
                                                <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                                <td>
                                                    <img src="{{ asset('static/inspinia/v4.0/assets/images/cards/mastercard.svg') }}" alt="" class="me-2" height="28"> xxxx 1123
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><a href="layouts-horizontal.html#!" class="link-reset fw-semibold">#TR-3469</a></td>
                                                <td>#ORD-1004 - Gaming Mouse</td>
                                                <td>26 Apr 2025 <small class="text-muted">09:42 AM</small></td>
                                                <td class="fw-semibold">$24.99</td>
                                                <td><span class="badge badge-soft-danger fs-xxs"><i class="ti ti-point-filled"></i> Failed</span></td>
                                                <td>
                                                    <img src="{{ asset('static/inspinia/v4.0/assets/images/cards/visa.svg') }}" alt="" class="me-2" height="28"> xxxx 3490
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><a href="layouts-horizontal.html#!" class="link-reset fw-semibold">#TR-3470</a></td>
                                                <td>#ORD-1005 - Fitness Tracker Band</td>
                                                <td>25 Apr 2025 <small class="text-muted">11:10 AM</small></td>
                                                <td class="fw-semibold">$34.95</td>
                                                <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                                <td>
                                                    <img src="{{ asset('static/inspinia/v4.0/assets/images/cards/american-express.svg') }}" alt="" class="me-2" height="28"> xxxx 8765
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><a href="layouts-horizontal.html#!" class="link-reset fw-semibold">#TR-3471</a></td>
                                                <td>#ORD-1006 - Wireless Keyboard</td>
                                                <td>24 Apr 2025 <small class="text-muted">08:58 PM</small></td>
                                                <td class="fw-semibold">$59.00</td>
                                                <td><span class="badge badge-soft-warning fs-xxs"><i class="ti ti-point-filled"></i> Pending</span></td>
                                                <td>
                                                    <img src="{{ asset('static/inspinia/v4.0/assets/images/cards/mastercard.svg') }}" alt="" class="me-2" height="28"> xxxx 5566
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><a href="layouts-horizontal.html#!" class="link-reset fw-semibold">#TR-3472</a></td>
                                                <td>#ORD-1007 - Portable Charger</td>
                                                <td>23 Apr 2025 <small class="text-muted">05:37 PM</small></td>
                                                <td class="fw-semibold">$45.80</td>
                                                <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                                <td>
                                                    <img src="{{ asset('static/inspinia/v4.0/assets/images/cards/visa.svg') }}" alt="" class="me-2" height="28"> xxxx 9012
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div> <!-- end table-responsive-->

                                    <div class="text-center mt-3">
                                        <a href="layouts-horizontal.html#!" class="link-reset text-decoration-underline fw-semibold link-offset-3">
                                            View All Transactions <i class="ti ti-send-2"></i>
                                        </a>
                                    </div>
                                </div> <!-- end col-->
                                <div class="col-xl-6">
                                    <div id="map_1" class="w-100 mt-4 mt-xl-0" style="height: 297px"></div>
                                </div> <!-- end col-->
                            </div><!-- end row-->
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div> <!-- end row-->
        </div>
        <!-- container -->

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        © 2014 - <script>document.write(new Date().getFullYear())</script> Inspinia By <span class="fw-semibold">weisifang.com</span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end d-none d-md-block">
                            10GB of <span class="fw-bold">250GB</span> Free.
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

    </div>

    <!-- ============================================================== -->
    <!-- End of Main Content -->
    <!-- ============================================================== -->

</div>
<!-- END wrapper -->

<!-- Vendor js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/vendors.min.js') }}"></script>

<!-- App js -->
<script src="{{ asset('static/inspinia/v4.0/assets/js/app.min.js') }}"></script>

</body>

</html>
