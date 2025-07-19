<div class="scrollbar" data-simplebar>

    <!-- User -->
    <div class="sidenav-user">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="pages-profile.html" class="link-reset">
                    <img src="{{ asset('static/images/system/default_user.png') }}" alt="user-image" class="rounded-circle mb-2 avatar-md">
                    <span class="sidenav-user-name fw-bold">Damian D.</span>
                    <span class="fs-12 fw-semibold" data-lang="user-role">Art Director</span>
                </a>
            </div>
            <div>
                <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="pages-empty.html#!" aria-haspopup="false" aria-expanded="false">
                    <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                </a>

                <div class="dropdown-menu">
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

    <!--- Sidenav Menu -->
    <ul class="side-nav">
        <li class="side-nav-title" data-lang="menu-title">Menu</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
                <span class="menu-text" data-lang="dashboards">Dashboards</span>
                <span class="badge bg-success">5</span>
            </a>
            <div class="collapse" id="sidebarDashboards">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="index.html" class="side-nav-link">
                            <span class="menu-text" data-lang="dashboard-one">Dashboard v.1</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="dashboard-2.html" class="side-nav-link">
                            <span class="menu-text" data-lang="dashboard-two">Dashboard v.2</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="dashboard-3.html" class="side-nav-link">
                            <span class="menu-text" data-lang="dashboard-three">Dashboard v.3</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-empty.html#!" class="side-nav-link disabled">
                            <span class="menu-text" data-lang="dashboard-four">Dashboard v.4</span>
                            <span class="badge text-bg-light opacity-50" data-lang="dashboard-soon">soon</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-empty.html#!" class="side-nav-link disabled">
                            <span class="menu-text" data-lang="dashboard-five">Dashboard v.5</span>
                            <span class="badge text-bg-light opacity-50" data-lang="dashboard-soon">soon</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a href="landing.html" target="_blank" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-stack-front"></i></span>
                <span class="menu-text" data-lang="landing-page">Landing Page</span>
            </a>
        </li>

        <li class="side-nav-title" data-lang="apps-title">Apps</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarEcommerce" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-basket"></i></span>
                <span class="menu-text" data-lang="ecommerce">Ecommerce</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarEcommerce">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarProducts" aria-expanded="false" aria-controls="sidebarProducts" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-products">Products</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarProducts">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="ecommerce-products.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-list">Products</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-products-grid.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-grid">Products Grid</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-product-details.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-details">Product Details</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-add-product.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-add">Add Product</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a href="ecommerce-categories.html" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-categories">Categories</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarOrders" aria-expanded="false" aria-controls="sidebarOrders" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-orders">Orders</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarOrders">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="ecommerce-orders.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-orders-list">Orders</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-order-details.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-order-details">Order Details</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a href="ecommerce-customers.html" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-customers">Customers</span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarSellers" aria-expanded="false" aria-controls="sidebarSellers" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-sellers"> Sellers </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarSellers">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="ecommerce-sellers.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-sellers-list">Sellers</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-seller-details.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-sellers-details">Sellers Details</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a href="ecommerce-reviews.html" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-reviews">Reviews</span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarReports" aria-expanded="false" aria-controls="sidebarReports" class="side-nav-link">
                            <span class="menu-text" data-lang="eco-reports"> Reports </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarReports">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="ecommerce-product-views.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-views">Product Views</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="ecommerce-sales.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="eco-pro-sales">Sales</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarEmail" aria-expanded="false" aria-controls="sidebarEmail" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-mail"></i></span>
                <span class="menu-text" data-lang="email">Email</span>
                <span class="badge text-bg-danger">New</span>
            </a>
            <div class="collapse" id="sidebarEmail">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="email.html" class="side-nav-link">
                            <span class="menu-text" data-lang="email-inbox">Inbox</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="email-details.html" class="side-nav-link">
                            <span class="menu-text" data-lang="email-details">Details</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="email-compose.html" class="side-nav-link">
                            <span class="menu-text" data-lang="email-compose">Compose</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarUsers" aria-expanded="false" aria-controls="sidebarUsers" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-users"></i></span>
                <span class="menu-text" data-lang="users"> Users </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarUsers">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="contacts.html" class="side-nav-link">
                            <span class="menu-text" data-lang="contacts">Contacts</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="roles.html" class="side-nav-link">
                            <span class="menu-text" data-lang="roles">Roles</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="permissions.html" class="side-nav-link">
                            <span class="menu-text" data-lang="permissions">Permissions</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarProjects" aria-expanded="false" aria-controls="sidebarProjects" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-briefcase"></i></span>
                <span class="menu-text" data-lang="projects"> Projects </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarProjects">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="projects.html" class="side-nav-link">
                            <span class="menu-text" data-lang="projects">My Projects</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="projects-list.html" class="side-nav-link">
                            <span class="menu-text" data-lang="projects-list">Projects List</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="project-details.html" class="side-nav-link">
                            <span class="menu-text" data-lang="project-details">View Project</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="project-kanban.html" class="side-nav-link">
                            <span class="menu-text" data-lang="project-kanban">Kanban Board</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="project-team-board.html" class="side-nav-link">
                            <span class="menu-text" data-lang="project-team-board">Team Board</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="project-activity.html" class="side-nav-link">
                            <span class="menu-text" data-lang="project-activity">Activity Steam</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a href="file-manager.html" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-folder"></i></span>
                <span class="menu-text" data-lang="file-manager"> File Manager </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="chat.html" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-message-dots"></i></span>
                <span class="menu-text" data-lang="chat"> Chat </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="calendar.html" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-calendar"></i></span>
                <span class="menu-text" data-lang="calendar"> Calendar </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarInvoice" aria-expanded="false" aria-controls="sidebarInvoice" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-invoice"></i></span>
                <span class="menu-text" data-lang="invoice"> Invoice</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarInvoice">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="invoices.html" class="side-nav-link">
                            <span class="menu-text" data-lang="invoices">Invoices</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="invoice-details.html" class="side-nav-link">
                            <span class="menu-text" data-lang="invoice-details">Single Invoice</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="invoice-create.html" class="side-nav-link">
                            <span class="menu-text" data-lang="invoice-create">New Invoice</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarOtherApps" aria-expanded="false" aria-controls="sidebarOtherApps" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-apps"></i></span>
                <span class="menu-text" data-lang="otherapps"> Other Apps </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarOtherApps">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="companies.html" class="side-nav-link">
                            <span class="menu-text" data-lang="companies">Companies</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="clients.html" class="side-nav-link">
                            <span class="menu-text" data-lang="clients">Clients</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="outlook.html" class="side-nav-link">
                            <span class="menu-text" data-lang="outlook">Outlook View</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="vote-list.html" class="side-nav-link">
                            <span class="menu-text" data-lang="vote-list">Vote List</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="issue-tracker.html" class="side-nav-link">
                            <span class="menu-text" data-lang="issue-tracker">Issue Tracker</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="api-keys.html" class="side-nav-link">
                            <span class="menu-text" data-lang="api-keys">API Keys</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="blog.html" class="side-nav-link">
                            <span class="menu-text" data-lang="blog">Blog</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="article.html" class="side-nav-link">
                            <span class="menu-text" data-lang="article">Article</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pin-board.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pin-board">Pin Board</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="forum-view.html" class="side-nav-link">
                            <span class="menu-text" data-lang="forum-view">Forum View</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="forum-post.html" class="side-nav-link">
                            <span class="menu-text" data-lang="forum-post">Forum Post</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-title mt-2" data-lang="pages-title">Custom Pages</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarPages" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-files"></i></span>
                <span class="menu-text" data-lang="pages"> Pages </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarPages">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="pages-profile.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-profile">Profile</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-faq.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-faq">FAQ</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-pricing.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-pricing">Pricing</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-empty.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-empty">Empty Page</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-timeline.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-timeline">Timeline</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-search-results.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-search-results">Search Results</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-coming-soon.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-coming-soon">Coming Soon</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="pages-terms-conditions.html" class="side-nav-link">
                            <span class="menu-text" data-lang="pages-terms-conditions">Terms & Conditions</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarMiscellaneous" aria-expanded="false" aria-controls="sidebarMiscellaneous" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-package"></i></span>
                <span class="menu-text" data-lang="miscellaneous"> Miscellaneous </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarMiscellaneous">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="misc-nestable.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-nestable">Nestable List</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-text-diff.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-text-diff">Text Diff</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-pdf-viewer.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-pdf-viewer">PDF Viewer</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-i18.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-i18">i18 Support</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-sweet-alerts.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-sweet-alerts">Sweet Alerts</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-idle-timer.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-idle-timer">Idle Timer</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-pass-meter.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-pass-meter">Password Meter</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-live-favicon.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-live-favicon">Live Favicon</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-clipboard.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-clipboard">Clipboard</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-tree-view.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-tree-view">Tree View</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-loading-buttons.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-loading-buttons">Loading Buttons</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-gallery.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-gallery">Gallery</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-masonry.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-masonry">Masonry</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-tour.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-tour">Tour</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="misc-animation.html" class="side-nav-link">
                            <span class="menu-text" data-lang="misc-animation">Animation</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarPagesAuth" aria-expanded="false" aria-controls="sidebarPagesAuth" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-lock"></i></span>
                <span class="menu-text" data-lang="authentication"> Authentication </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarPagesAuth">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarOneAuth" aria-expanded="false" aria-controls="sidebarOneAuth" class="side-nav-link">
                            <span class="menu-text" data-lang="version-1"> Version 1 </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarOneAuth">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="auth-sign-in.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-sign-in">Sign In</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-sign-up.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-sign-up">Sign Up</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-reset-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-reset-pass">Reset Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-new-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-new-pass">New Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-two-factor.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-two-factor">Two Factor</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-lock-screen.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-lock-screen">Lock Screen</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-success-mail.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-success-mail">Success Mail</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-login-pin.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-login-pin">Login with PIN</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-delete-account.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-delete-account">Delete Account</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarTwoAuth" aria-expanded="false" aria-controls="sidebarTwoAuth" class="side-nav-link">
                            <span class="menu-text" data-lang="version-2"> Version 2 </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarTwoAuth">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="auth-2-sign-in.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-sign-in">Sign In</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-sign-up.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-sign-up">Sign Up</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-reset-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-reset-pass">Reset Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-new-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-new-pass">New Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-two-factor.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-two-factor">Two Factor</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-lock-screen.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-lock-screen">Lock Screen</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-success-mail.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-success-mail">Success Mail</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-login-pin.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-login-pin">Login with PIN</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-2-delete-account.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-2-delete-account">Delete Account</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarThreeAuth" aria-expanded="false" aria-controls="sidebarThreeAuth" class="side-nav-link">
                            <span class="menu-text" data-lang="version-3"> Version 3 </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarThreeAuth">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="auth-3-sign-in.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-sign-in">Sign In</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-sign-up.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-sign-up">Sign Up</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-reset-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-reset-pass">Reset Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-new-pass.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-new-pass">New Password</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-two-factor.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-two-factor">Two Factor</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-lock-screen.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-lock-screen">Lock Screen</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-success-mail.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-success-mail">Success Mail</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-login-pin.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-login-pin">Login with PIN</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="auth-3-delete-account.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="auth-3-delete-account">Delete Account</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarPagesError" aria-expanded="false" aria-controls="sidebarPagesError" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-alert-hexagon"></i></span>
                <span class="menu-text" data-lang="error-pages"> Error Pages </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarPagesError">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="error-400.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-400">400</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="error-401.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-401">401</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="error-403.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-403">403</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="error-404.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-404">404</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="error-408.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-408">408</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="error-500.html" class="side-nav-link">
                            <span class="menu-text" data-lang="error-500">500</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="maintenance.html" class="side-nav-link">
                            <span class="menu-text" data-lang="maintenance">Maintenance</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-title mt-2" data-lang="layouts-title">Layouts</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarLayouts" aria-expanded="false" aria-controls="sidebarLayouts" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-layout"></i></span>
                <span class="menu-text" data-lang="layout-options"> Layout Options </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarLayouts">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="layouts-scrollable.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="layouts-scrollable">Scrollable</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="layouts-compact.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="layouts-compact">Compact</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="layouts-boxed.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="layouts-boxed">Boxed</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="layouts-horizontal.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="layouts-horizontal">Horizontal</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="layouts-preloader.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="layouts-preloader">Preloader</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarSidebars" aria-expanded="false" aria-controls="sidebarSidebars" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-layout-sidebar"></i></span>
                <span class="menu-text" data-lang="sidebars"> Sidebars </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarSidebars">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="sidebar-light.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-light">Light Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-gradient.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-gradient">Gradient Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-gray.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-gray">Gray Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-image.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-image">Image Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-compact.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-compact">Compact Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-icon-view.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-icon-view">Icon View Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-on-hover.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-on-hover">On Hover Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-on-hover-active.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-on-hover-active">On Hover Active</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-offcanvas.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-offcanvas">Offcanvas Menu</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-no-icons.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-no-icons">No Icons with Lines</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="sidebar-with-lines.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="sidebar-with-lines">Sidebar with Lines</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarTopbars" aria-expanded="false" aria-controls="sidebarTopbars" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-layout-bottombar"></i></span>
                <span class="menu-text" data-lang="topbar"> Topbar </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarTopbars">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="topbar-dark.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="topbar-dark">Dark Topbar</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="topbar-gray.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="topbar-gray">Gray Topbar</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="topbar-gradient.html" target="_blank" class="side-nav-link">
                            <span class="menu-text" data-lang="topbar-gradient">Gradient Topbar</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="topbar-sub-items.html" class="side-nav-link">
                            <span class="menu-text" data-lang="topbar-sub-items">Topbar with Sub Items</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="topbar-tools.html" class="side-nav-link">
                            <span class="menu-text" data-lang="topbar-tools">Topbar with Tools</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-title mt-2" data-lang="components-title">Components</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarBaseUI" aria-expanded="false" aria-controls="sidebarBaseUI" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-palette"></i></span>
                <span class="menu-text" data-lang="base-ui"> Base UI </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarBaseUI">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="ui-accordions.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-accordions">Accordions</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-alerts.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-alerts">Alerts</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-images.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-images">Images</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-badges.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-badges">Badges</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-breadcrumb.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-breadcrumb">Breadcrumb</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-buttons.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-buttons">Buttons</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-cards.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-cards">Cards</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-carousel.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-carousel">Carousel</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-collapse.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-collapse">Collapse</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-colors.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-colors">Colors</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-dropdowns.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-dropdowns">Dropdowns</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-videos.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-videos">Videos</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-grid.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-grid">Grid Options</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-links.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-links">Links</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-list-group.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-list-group">List Group</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-modals.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-modals">Modals</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-notifications.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-notifications">Notifications</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-offcanvas.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-offcanvas">Offcanvas</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-placeholders.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-placeholders">Placeholders</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-pagination.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-pagination">Pagination</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-popovers.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-popovers">Popovers</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-progress.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-progress">Progress</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-scrollspy.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-scrollspy">Scrollspy</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-spinners.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-spinners">Spinners</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-tabs.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-tabs">Tabs</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-tooltips.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-tooltips">Tooltips</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-typography.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-typography">Typography</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="ui-utilities.html" class="side-nav-link">
                            <span class="menu-text" data-lang="ui-utilities">Utilities</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a href="widgets.html" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-category"></i></span>
                <span class="menu-text" data-lang="widgets"> Widgets </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="metrics.html" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-chart-histogram"></i></span>
                <span class="menu-text" data-lang="metrics"> Metrics </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarCharts" aria-expanded="false" aria-controls="sidebarCharts" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-chart-donut"></i></span>
                <span class="menu-text" data-lang="graphs"> Graphs </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarCharts">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarApexCharts" aria-expanded="false" aria-controls="sidebarApexCharts" class="side-nav-link">
                            <span class="menu-text" data-lang="apex-charts"> Apex Charts </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarApexCharts">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="charts-apex-area.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-area">Area</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-bar.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-bar">Bar</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-bubble.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-bubble">Bubble</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-candlestick.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-candlestick">Candlestick</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-column.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-column">Column</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-heatmap.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-heatmap">Heatmap</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-line.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-line">Line</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-mixed.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-mixed">Mixed</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-timeline.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-timeline">Timeline</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-boxplot.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-boxplot">Boxplot</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-treemap.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-treemap">Treemap</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-pie.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-pie">Pie</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-radar.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-radar">Radar</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-radialbar.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-radialbar">RadialBar</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-scatter.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-scatter">Scatter</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-polar-area.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-polar-area">Polar Area</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-sparklines.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-sparklines">Sparklines</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-range.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-range">Range</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-funnel.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-funnel">Funnel</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-apex-slope.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-apex-slope">Slope</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarEcharts" aria-expanded="false" aria-controls="sidebarEcharts" class="side-nav-link">
                            <span class="menu-text" data-lang="echarts"> Echarts </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarEcharts">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="charts-echart-line.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-line">Line</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-bar.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-bar">Bar</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-pie.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-pie">Pie</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-scatter.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-scatter">Scatter</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-geo-map.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-geo-map">GEO Map</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-gauge.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-gauge">Gauge</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-candlestick.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-candlestick">Candlestick</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-area.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-area">Area</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-radar.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-radar">Radar</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-heatmap.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-heatmap">Heatmap</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="charts-echart-other.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="charts-echart-other">Other</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a href="charts-apextree.html" class="side-nav-link">
                            <span class="menu-text" data-lang="charts-apextree">Apex Tree</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="charts-apexsankey.html" class="side-nav-link">
                            <span class="menu-text" data-lang="charts-apexsankey">Apex Sankey</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarForms" aria-expanded="false" aria-controls="sidebarForms" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-checkup-list"></i></span>
                <span class="menu-text" data-lang="forms">Forms</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarForms">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="form-elements.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-elements">Basic Elements</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-pickers.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-pickers">Pickers</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-select.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-select">Select</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-validation.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-validation">Validation</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-wizard.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-wizard">Wizard</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-fileuploads.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-fileuploads">File Uploads</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-text-editors.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-text-editors">Text Editors</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-range-slider.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-range-slider">Range Slider</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-layouts.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-layouts">Layouts</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="form-other-plugins.html" class="side-nav-link">
                            <span class="menu-text" data-lang="form-other-plugins">Other Plugins</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarTables" aria-expanded="false" aria-controls="sidebarTables" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-table-column"></i></span>
                <span class="menu-text" data-lang="tables">Tables</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarTables">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="tables-static.html" class="side-nav-link">
                            <span class="menu-text" data-lang="tables-static">Static Tables</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="tables-custom.html" class="side-nav-link">
                            <span class="menu-text" data-lang="tables-custom">Custom Tables</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarDataTables" aria-expanded="false" aria-controls="sidebarDataTables" class="side-nav-link">
                            <span class="menu-text" data-lang="datatables">DataTables</span>
                            <span class="badge bg-success">12</span>
                        </a>
                        <div class="collapse" id="sidebarDataTables">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="tables-datatables-basic.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-basic">Basic</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-export-data.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-export-data">Export Data</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-select.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-select">Select</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-ajax.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-ajax">Ajax</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-javascript.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-javascript">Javascript Source</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-rendering.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-rendering">Data Rendering</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-scroll.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-scroll">Scroll</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-columns.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-columns">Show & Hide Column</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-child-rows.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-child-rows">Child Rows</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-column-searching.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-column-searching">Column Searching</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-range-search.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-range-search">Range Search</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-fixed-header.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-fixed-header">Fixed Header</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-add-rows.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-add-rows">Add Rows</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="tables-datatables-checkbox-select.html" class="side-nav-link">
                                        <span class="menu-text" data-lang="tables-datatables-checkbox-select">Checkbox Select</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarIcons" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-icons"></i></span>
                <span class="menu-text" data-lang="icons">Icons</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarIcons">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="icons-tabler.html" class="side-nav-link">
                            <span class="menu-text" data-lang="icons-tabler">Tabler</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="icons-lucide.html" class="side-nav-link">
                            <span class="menu-text" data-lang="icons-lucide">Lucide</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="icons-flags.html" class="side-nav-link">
                            <span class="menu-text" data-lang="icons-flags">Flags</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarMaps" aria-expanded="false" aria-controls="sidebarMaps" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-map"></i></span>
                <span class="menu-text" data-lang="maps">Maps</span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarMaps">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a href="maps-google.html" class="side-nav-link">
                            <span class="menu-text" data-lang="maps-google">Google Maps</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="maps-vector.html" class="side-nav-link">
                            <span class="menu-text" data-lang="maps-vector">Vector Maps</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="maps-leaflet.html" class="side-nav-link">
                            <span class="menu-text" data-lang="maps-leaflet">Leaflet Maps</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-title mt-2" data-lang="items-title">Menu Items</li>

        <li class="side-nav-item">
            <a data-bs-toggle="collapse" href="pages-empty.html#sidebarMenuLevels" aria-expanded="false" aria-controls="sidebarMenuLevels" class="side-nav-link">
                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                <span class="menu-text" data-lang="menu-levels"> Menu Levels </span>
                <span class="menu-arrow"></span>
            </a>
            <div class="collapse" id="sidebarMenuLevels">
                <ul class="sub-menu">
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarSecondLevel" aria-expanded="false" aria-controls="sidebarSecondLevel" class="side-nav-link">
                            <span class="menu-text" data-lang="second-level"> Second Level </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarSecondLevel">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">
                                        <span class="menu-text">Item 2.1</span>
                                    </a>
                                </li>
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">
                                        <span class="menu-text">Item 2.2</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="pages-empty.html#sidebarThirdLevel" aria-expanded="false" aria-controls="sidebarThirdLevel" class="side-nav-link">
                            <span class="menu-text" data-lang="third-level"> Third Level </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarThirdLevel">
                            <ul class="sub-menu">
                                <li class="side-nav-item">
                                    <a href="javascript: void(0);" class="side-nav-link">Item 1</a>
                                </li>
                                <li class="side-nav-item">
                                    <a data-bs-toggle="collapse" href="pages-empty.html#sidebarFourthLevel" aria-expanded="false" aria-controls="sidebarFourthLevel" class="side-nav-link">
                                        <span class="menu-text"> Item 2 </span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <div class="collapse" id="sidebarFourthLevel">
                                        <ul class="sub-menu">
                                            <li class="side-nav-item">
                                                <a href="javascript: void(0);" class="side-nav-link">
                                                    <span class="menu-text">Item 3.1</span>
                                                </a>
                                            </li>
                                            <li class="side-nav-item">
                                                <a href="javascript: void(0);" class="side-nav-link">
                                                    <span class="menu-text">Item 3.2</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </li>

        <li class="side-nav-item">
            <a href="pages-empty.html#!" class="side-nav-link disabled">
                <span class="menu-icon"><i class="ti ti-ban"></i></span>
                <span class="menu-text" data-lang="disabled-menu"> Disabled Menu </span>
            </a>
        </li>

        <li class="side-nav-item">
            <a href="pages-empty.html#!" class="side-nav-link special-menu">
                <span class="menu-icon"><i class="ti ti-star"></i></span>
                <span class="menu-text" data-lang="special-menu"> Special Menu </span>
            </a>
        </li>

    </ul>
</div>
