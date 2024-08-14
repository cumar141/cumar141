@php
use App\Models\Branch;
$branch = Branch::all();
@endphp
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dashboard | Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="{{ asset('public/dist/images/default-favicon.png')}}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap Css -->
    <link rel="stylesheet" href="{{ asset('public/staff/assets/css/bootstrap.min.css')}}" />


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/fontawesome.min.css" >

    <!-- Icons Css -->
    <link rel="stylesheet" href="{{ asset('public/staff/assets/css/icons.min.css')}}" />
    <!-- App Css-->
    <link rel="stylesheet" href="{{ asset('public/staff/assets/css/app.min.css')}}" />
    <link rel="stylesheet" href="{{ asset('public/staff/toastr/toastr.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
    




<link rel="stylesheet" type="text/css"
href="{{ asset('public/dist/plugins/daterangepicker-3.1/daterangepicker.min.css')}}">

<!-- jquery-ui-1.12.1 -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css')}}">





</head>

<body>
    <section class="ftco-section">
        <div class="container">
            <nav class="navbar navbar-expand-lg ftco_navbar ftco-navbar-light " id="ftco-navbar">
                <div class="container">
                    <a class="navbar-brand fs-2" href="{{ route('admin2.dashboard') }}">{{settings('name')}}</a>
                </div>
                <div class="collapse navbar-collapse" id="ftco-nav">
                    <ul class="navbar-nav ml-auto mr-md-3">
                        <li class="nav-item " id="dashboard"><a href="{{ route('admin2.dashboard') }}"
                                class="nav-link fs-4">Dashboard</a></li>
                        <li class="nav-item dropdown" id="reports">
                            <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="fs-4"> Reports</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin2.treasurer.accountant_treasurer') }}" key="t-products">Treasurer Report</a>
                                    @if(isset($branch) && count($branch) > 0)
                                    @foreach($branch as $b)
                                    <li><a class="dropdown-item" href="{{ route('admin2.reports', ['id' => $b->id]) }}">{{
                                        $b->name }}</a></li>
                                @endforeach
                                @endif
                                <li><a class="dropdown-item btn" onclick="openPopup('{{ route('admin2.approved') }}')" key="t-products">Approved Transactions</a>
                            </ul>
                        </li>
                        <li class="nav-item dropdown" id="profile">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}" alt="Avatar"
                                    class="rounded-circle avatar-img" style="width: 40px; height: 40px;">
                                <!-- Adjust width and height as needed -->
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin2.profile') }}">View Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('staff.logout') }}">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <script>
                function openPopup(url) {
                    var width = 900;
                    var height = 700;
                    var left = (screen.width - width) / 2;
                    var top = (screen.height - height) / 2;
                    var options = 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + width + ', height=' + height + ', top=' + top + ', left=' + left;
            
                    window.open(url, 'Popup', options);
                }
            </script>