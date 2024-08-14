@php
    // use UserPermission helper functions
    use App\Http\Helpers\UserPermission;
    use App\Models\Branch;

    $branches = Branch::all();

@endphp
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Index</title>
    <style>
    .notification-info {
        display: flex;
        flex-direction: column;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }
    .notification-info .notification-sender,
    .notification-info .notification-receiver,
    .notification-info .notification-message,
    .notification-info .notification-amount,
    .notification-info .notification-date {
        margin: 2px 0;
    }
    .notification-info .notification-sender {
        font-weight: bold;
        color: #007bff;
    }
    .notification-info .notification-message {
        color: #6c757d;
    }
    .notification-info .notification-amount {
        color: #28a745;
        font-weight: bold;
    }
    .notification-info .notification-date {
        font-size: 0.9em;
        color: #6c757d;
    }
    .dropdown-item {
        display: block;
        width: 100%;
        padding: 10px;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-align: inherit;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .dropdown-item.show-notification {
        border-left: 5px solid #007bff;
        border-radius: 5px;
        margin: 5px 0;
    }
    .dropdown-item.view-more {
        color: #007bff;
    }
    .dropdown-item.no-notifications {
        color: #6c757d;
    }
</style>
</head>

{{-- auth check --}}
@if (auth()->guard('staff')->check())
    @php
        $userId = auth()->guard('staff')->user()->id;
    @endphp
    @include('staff.spinner')
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="#" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
                            </span>
                        </a>
                        <a href="#" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
                            </span>
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>

         
                <div class="d-flex">
                    <!-- User Dropdown -->
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user"
                                src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1"
                                key="t-henry">{{ auth()->guard('staff')->user()->first_name }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        @if (!empty(auth()->guard('staff')->user()->teller_uuid))
                            <span class="d-none d-xl-inline-block ms-1">Teller ID:
                                {{ auth()->guard('staff')->user()->teller_uuid }} </span>
                        @endif
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- User dropdown menu items -->
                            <a class="dropdown-item" href="{{ route('profile') }}"><i
                                    class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span key="t-profile">Profile</span></a>
                            <a class="dropdown-item" href="{{ route('staff.myWallets') }}"><i
                                    class="bx bx-wallet font-size-16 align-middle me-1"></i>
                                <span key="t-my-wallet">My Wallet</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="{{ route('staff.logout') }}"><i
                                    class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                                    key="t-logout">Logout</span></a>
                        </div>
                    </div>
                    <!-- End User Dropdown -->

                    <!-- Notification Dropdown -->
                    @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'view_notification'))
                        <div class="dropdown d-inline-block ms-2">
                            <button type="button" class="btn header-item waves-effect" id="notification-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-bell font-size-16 align-middle"></i>
                                <span class="badge bg-danger rounded-pill" id="notification-count">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" id="notification-menu">
                                <a class="dropdown-item text-center no-notifications" href="javascript:void(0);">
                                    <strong>No Notification Found</strong>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- End Notification Dropdown -->

    
                </div>
            </div>
        </header>
    </div>

    <div class="vertical-menu">
        <div data-simplebar class="h-100">
            <div id="sidebar-menu">
                <ul class="metismenu list-unstyled" id="side-menu">
                    <li>
                        <a href="{{ route('staff.dashboard') }}" class="waves-effect">
                            <i class='bx bxs-dashboard'></i>
                            <span key="t-dashboards">Dashboards</span>
                        </a>
                    </li>
                    

                    {{-- Tellers --}}
                    @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'tellers'))
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-user"></i>
                                <span key="t-ecommerce">Users</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('showDeposit') }}" key="t-products">Deposit</a></li>
                                <li><a href="{{ route('Withdrawal') }}" key="t-products">Withdrawal</a></li>
                                <li><a href="{{ route('tellerRequestMoney') }}" key="t-products">Request Money</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-report"></i>
                                <span key="t-ecommerce">Reports</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                            </ul>
                        </li>
                    @endif

                    {{-- End Tellers --}}

                    {{-- Managers --}}
                    @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'managers'))
                        <li>
                            <a href="{{ route('staff.user.index') }}" class="waves-effect">
                                <i class="bx bx-user"></i>
                                <span key="t-ecommerce">Users</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('staff.transactions.all') }}" class="waves-effect">
                                <i class="bx bx-money"></i>
                                <span key="t-ecommerce">Transactions</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('staff.autopayout.failed') }}" class="waves-effect">
                                <i class='bx bx-transfer-alt'></i>
                                <span key="t-ecommerce">Autopayout</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('showTellerInfo') }}" class="waves-effect">
                                <i class="bx bx-collection"></i>
                                <span key="t-ecommerce">Control Panel</span>
                            </a>
                        </li>



                        <li>
                            <a href="{{ route('managerRequestMoney') }}" class="waves-effect">
                                <i class="bx bx-git-pull-request"></i>
                                <span key="t-ecommerce">Request Money</span>
                            </a>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-report"></i>
                                <span key="t-ecommerce">Reports</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                                <li><a href="{{ route('teller-report') }}" key="t-products">Teller Report</a></li>
                            </ul>
                        </li>
                    @endif
                    {{-- End Managers --}}


                    {{-- Treasurer --}}
                    @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers'))
                    <li>
                        <a href="{{ route('staff.user.index') }}" class="waves-effect">
                            <i class="bx bx-user"></i>
                            <span key="t-ecommerce">Users</span>
                        </a>
                    </li>

                        <li>
                            <a href="{{ route('dashboard.select') }}" class="waves-effect">
                                <i class="bx bx-home-circle"></i>
                                <span key="t-dashboards">Dashboard Options</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('staff.treasurer.create_money_form') }}" class="waves-effect">
                                <i class="bx bx-wallet"></i>
                                <span key="t-ecommerce">Deposit Treasury</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.partner-balance.index') }}" class="waves-effect">
                                <i class="bx bx-wallet"></i>
                                <span key="t-ecommerce">Partner Balance</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.transactions.all') }}" class="waves-effect">
                                <i class="bx bx-wallet"></i>
                                <span key="t-ecommerce">Transactions</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('staff.treasurer.show_managers') }}" class="waves-effect">
                                <i class='bx bx-briefcase-alt-2'></i>
                                <span key="t-ecommerce">Managers</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-report"></i>
                                <span key="t-ecommerce">Reports</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">

                                <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                                <li>
                                <a href="{{ route('staff.manager') }}"
                                        key="t-products">Managers Report</a>
                                </li>
                                <li><a href="{{ route('teller-report') }}" key="t-products">Teller Report</a></li>
                                </li>

                               

                            </ul>
                        </li>
                    @endif
                    {{-- End Treasurer --}}

                   {{-- <li>
                        <a href="{{ route('test-data-table') }}" class="waves-effect">
                            <i class="bx bx-wallet"></i>
                            <span key="t-ecommerce">Transactions dataTable</span>
                        </a>
                    </li> --}}


                    {{-- Accountant --}}
                    @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'accountant'))
                    <li>
                        <a href="{{ route('staff.transactions.all') }}" class="waves-effect">
                            <i class="bx bx-wallet"></i>
                            <span key="t-ecommerce">Transactions</span>
                        </a>
                    </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-report"></i>
                                <span key="t-ecommerce">Reports</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                {{-- <li><a href="{{ route('staff.reports.autoPayout') }}" key="t-products">AutoPay</a>
                                </li> --}}
                                <li><a href="{{ route('staff.treasurer.accountant_treasurer') }}"
                                        key="t-products">Treasurer</a>
                                </li>
                                <li>
                                <a href="{{ route('staff.manager') }}"
                                        key="t-products">Managers</a>
                                </li>
                                <li><a href="{{ route('teller-report') }}" key="t-products">Teller Report</a></li>
                            </ul>
                        </li>
                    @endif
                    {{-- End Accountant --}}
                     @if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'admin_reports'))
                        <li><a href="{{ route('staff.admin_reports') }}" key="t-products">
                                <i class="bx bx-extension"></i>
                                Admin Report
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('staff.receipts') }}" class="waves-effect">
                            <i class='bx bx-receipt'></i>
                            <span key="t-ecommerce">Receipts</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endif
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#spinner").hide();

        function fetchNotifications() {
            $.ajax({
                url: "{{ route('get-notifications') }}", // Replace with the new endpoint
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log(data); // Log received data
                    updateNotifications(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching notifications:', error);
                }
            });
        }

        function updateNotifications(data) {
    var dropdown = $('#notification-menu');
    dropdown.empty();

    if (data && typeof data.count !== 'undefined' && Array.isArray(data.details)) {
        $('#notification-count').text(data.count);

        if (data.details.length > 0) {
            var detailsToShow = data.details.slice(0, 3); // Get only the first 3 items
            $.each(detailsToShow, function(index, notification) {
                var payload = notification.payload;
                var senderData = payload && payload.sender ? `${payload.sender.name || 'N/A'}` : 'N/A';
                var receiverData = payload && payload.receiver ? `${payload.receiver.name || 'N/A'}` : 'N/A';
                // Remove non-numeric characters from amount
                var cleanAmount = payload && payload.amount ? parseFloat(payload.amount) : null;
                // Round the cleaned amount
                var amount = cleanAmount !== null && !isNaN(cleanAmount) ? `${Math.round(cleanAmount)}` : 'N/A';

                var note = notification.message || 'N/A'; // Use 'message' column for notification message
                var formattedDate = notification.created_at ? new Date(notification.created_at).toLocaleString() : 'N/A';

                dropdown.append(`
                    <a class="dropdown-item show-notification" href="{{ route('ViewMoreTransactions') }}"
                        data-notification-id="${notification.id}" 
                        data-notification-message="${note}"
                        data-notification-sender="${senderData}"
                        data-notification-receiver="${receiverData}"
                        data-notification-date="${formattedDate}">
                        <div class="notification-info">
                            <i class="fas fa-info-circle font-size-16 align-middle me-1"></i>
                            <span class="notification-sender">Sender: ${senderData}</span>
                            <span class="notification-receiver">Receiver: ${receiverData}</span>
                            <span class="notification-message">Note: ${note}</span>
                            <span class="notification-amount">Amount: $${amount}</span>
                            <span class="notification-date">Date: ${formattedDate}</span>
                        </div>
                    </a>
                `);
            });

            // Add "View More" link if there are more than 3 notifications
            if (data.details.length > 3) {
                dropdown.append(`
                    <a class="dropdown-item text-center view-more" href="{{ route('ViewMoreTransactions') }}">
                        <strong>View More</strong>
                    </a>
                `);
            }
        } else {
            // No notifications to show
            dropdown.append(`
                <a class="dropdown-item text-center no-notifications" href="javascript:void(0);">
                    <strong>No new notifications</strong>
                </a>
            `);
        }
    } else {
        // No notifications data received or data is invalid
        dropdown.append(`
            <a class="dropdown-item text-center no-notifications" href="javascript:void(0);">
                <strong>No notifications available</strong>
            </a>
        `);
    }
}


        fetchNotifications();
        setInterval(fetchNotifications, 9000);
    });
    </script>