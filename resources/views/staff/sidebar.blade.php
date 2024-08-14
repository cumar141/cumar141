@php
use App\Http\Helpers\UserPermission;
use App\Models\Branch;

$branches = Branch::all();
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="layout-wrapper">
    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box">
                    <a href="#" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('public/trace.svg') }}" alt="">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('public/trace.svg') }}" alt="">
                        </span>
                    </a>
                    <a href="#" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('public/trace.svg') }}" alt="">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('public/trace.svg') }}" alt="">
                        </span>
                    </a>
                </div>
                <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                    id="vertical-menu-btn">
                    <i class="fa fa-fw fa-bars"></i>
                </button>

            </div>

            <div class="d-flex">


                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user"
                            src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}" alt="Header Avatar">

                        @if (Session::has('user_data'))
                        @php
                        $userData = Session::get('user_data');
                        $userid = $userData['id'];
                        @endphp
                        <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ $userData['name'] }}</span>

                        @else
                        @php
                        session()->flash('message', __('Session Expired.'));
                        session()->flash('alert-class', 'alert-danger');

                        return redirect()->route('staff.login');
                        @endphp
                        @endif

                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    @if(isset($userData['teller_uuid']) && !empty($userData['teller_uuid']))
                    <span class="d-none d-xl-inline-block ms-1">Teller ID: {{ $userData['teller_uuid'] }}</span>
                    @endif


                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
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
                <!-- Notification Dropdown -->
                @if (UserPermission::has_permission($userid, 'view_notification'))
                <div class="dropdown d-inline-block ms-2">
                    <button type="button" class="btn header-item waves-effect" id="page-header-notification-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-bell font-size-16 align-middle"></i>
                        <span class="badge bg-danger rounded-pill" id="notification-count">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" id="dropdown-menu-end">
                        <!-- Notification items go here -->
                        <a class="dropdown-item">
                            <i class="bx bx-info-circle font-size-16 align-middle me-1"></i>
                            <span key="t-notification">New notification</span>
                        </a>
                        <!-- Add more notification items as needed -->
                    </div>

                </div>
                @endif
                <!-- End Notification Dropdown -->
                <div id="successMessage" class="alert alert-success" style="display: none;">
                    <span>Transaction rejected successfully!</span>
                </div>


            </div>
        </div>

</div>


</header>

<body>


</body>

<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <!-- <li class="menu-title" key="t-menu">Menu</li> -->
                <li>
                    <a href="{{ route('staff.dashboard') }}" class="waves-effect">
                        <i class='bx bxs-dashboard'></i>
                        <!-- <span class="badge rounded-pill bg-info float-end">04</span> -->
                        <span key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                {{-- accountant links --}}
                @if (UserPermission::has_permission($userid, 'accountant'))

                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Reports</span>
                    </a>
                    <ul><a href="{{ route('staff.treasurer.accountant_treasurer') }}" key="t-products"> Treasurer </a></ul>
                    <ul class="sub-menu" id="branches">
                        <li>
                            <a href="javascript:void(0);" class="has-arrow waves-effect">
                                <i class='bx bx-git-branch'></i>
                                <span key="t-ecommerce">Branch</span>
                            </a>
                            <ul class="sub-menu">
                                @foreach ($branches as $branch)
                                <li><a href="{{ route('staff.treasurer.treasury_report', ['id' => $branch->id]) }}"
                                        key="t-products">{{ $branch->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </li>

                @endif

                {{-- Treasurer Dashboards --}}

                @if (UserPermission::has_permission($userid, 'create_money'))
                <li>
                <li>
                    <a href="{{ route('dashboard.select') }}" class="waves-effect"><i class="bx bx-home-circle"></i>
                        <!-- <span class="badge rounded-pill bg-info float-end">04</span> -->
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
                    <a href="{{ route('staff.treasurer.show_managers') }}" class="waves-effect">
                        <i class='bx bx-briefcase-alt-2'></i>
                        <span key="t-ecommerce">Managers</span>
                    </a>
                </li>


                @endif

                {{-- End Treasurer Links --}}

                {{-- Treasury Report Links Dropdown --}}
                @if (UserPermission::has_permission($userid, 'view_treasury_report'))
                <li>
                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Treasury Reports</span>
                    </a>
                    <ul class="sub-menu" id="branches">
                        <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                        <li>
                            <a href="javascript:void(0);" class="has-arrow waves-effect">
                                <i class='bx bx-git-branch'></i>
                                <span key="t-ecommerce">Branch</span>
                            </a>
                            <ul class="sub-menu">
                                @foreach ($branches as $branch)
                                <li><a href="{{ route('staff.treasurer.treasury_report', ['id' => $branch->id]) }}"
                                        key="t-products">{{ $branch->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif
                {{-- End Treasury Report Links Dropdowm--}}

                {{-- Treasury Report Links Dropdowm--}}


                <li> @if (UserPermission::has_permission($userid, 'make_deposit'))
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Users</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        <li><a href="{{ route('showDeposit') }}" key="t-products">Deposit</a></li>

                        @if (UserPermission::has_permission($userid, 'make_withdraw'))
                        <li><a href="{{ route('Withdrawal') }}" key="t-products">Withdrawal</a></li>
                        <li><a href="{{ route('tellerRequestMoney') }}" key="t-products">Request Money</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (UserPermission::has_permission($userid, 'teller_deposit'))
                <li>
                    <a href="{{ route('showTellerInfo') }}" class="waves-effect">
                        <i class="bx bx-coin"></i>
                        <span key="t-ecommerce">Tellers</span>
                    </a>
                <li>
                    <a href="{{ route('managerRequestMoney') }}" class="waves-effect">
                        <i class="bx bx-coin"></i>
                        <span key="t-ecommerce">Request Money</span>

                    </a>
                </li>
                <li>
                    <a href="{{ route('managerControlPanel') }}" class="waves-effect">
                        <i class="bx bx-coin"></i>
                        <span key="t-ecommerce">Manger Panel</span>

                    </a>
                </li>
                <li>
                    <a href="{{ route('bulkDeposit') }}" class="waves-effect">
                        <i class="bx bx-coin"></i>
                        <span key="t-ecommerce">Deposit All</span>

                    </a>
                </li>
                @endif
                @if (UserPermission::has_permission($userid, 'manager_reports'))
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('managerTellerReport') }}" key="t-products">Teller Report </a></li>
                        <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                    </ul>
                </li>
                @endif
                @if (UserPermission::has_permission($userid, 'teller_reports'))
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        {{-- <li><a href="{{ route('tellerReport') }}" key="t-products">Teller Report For teller</a>
                        </li> --}}
                        <li><a href="{{ route('myReports') }}" key="t-products">My Report</a></li>
                    </ul>
                </li>
                @endif
                @if (UserPermission::has_permission($userid, 'teller_deposit'))
                <li>
                    <a href="{{ route('staff.receipts') }}" class="waves-effect"><i class="bx bx-receipt"></i>
                        <span key="t-ecommerce">Receipts</span>
                    </a>
                </li>

                @endif

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>


</div>





<!-- Left Sidebar End -->
</div>




