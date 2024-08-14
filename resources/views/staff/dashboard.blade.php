@include('staff.layouts.header')
@include('staff.layouts.sidebar')
@php
use App\Http\Helpers\UserPermission;

@endphp

<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .card-header {
        background-color: #f8f9fa;
        /* Add consistent background color for card headers */
        border-bottom: 1px solid #dee2e6;
        /* Add border for card headers */
    }

    .table th,
    .table td {
        padding: 8px 12px;
        /* Adjust padding for better spacing */
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Welcome, {{auth()->guard('staff')->user()->first_name}}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'accountant'))

            <div class="container">
                <ul class="nav nav-fill nav-tabs bg-white" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0"
                            role="tab" aria-controls="fill-tabpanel-0" aria-selected="true">Summary</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab"
                            aria-controls="fill-tabpanel-1" aria-selected="false"> Admin </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="fill-tab-2" data-bs-toggle="tab" href="#fill-tabpanel-2" role="tab"
                            aria-controls="fill-tabpanel-2" aria-selected="false"> Treasurer </a>
                    </li>
                </ul>
                <div class="tab-content pt-5" id="tab-content" style="width: 100%;">
                    <div class="tab-pane active" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0"
                        style="width: 100%;">
                        @if(isset($data['transactionsSummary']) && count($data['transactionsSummary']) > 0)

                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Transactions Summary Today</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="dataTable" class="table align-middle table-nowrap mb-0">
                                                    <thead class="table-light">
                                                        <th>Transaction Time</th>
                                                        <th>Transaction ID</th>
                                                        <th>User</th>
                                                        <th>Account Info</th>
                                                        <th>Transaction Type</th>
                                                        <th>Currency</th>
                                                        <th>Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($data['transactionsSummary'] as $transaction)
                                                        <tr>
                                                            <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                                                            <td>{{ $transaction->uuid }}</td>
                                                            <td>{{ optional($transaction->user)->role->name }} </td>
                                                            <td>{{ optional($transaction->user)->first_name }}
                                                                {{ optional($transaction->user)->last_name }} - (
                                                                {{ optional($transaction->user)->formattedPhone }} )
                                                            </td>
                                                            <td>{{ str_replace('_', '
                                                                ',$transaction->transaction_type->name) }}</td>
                                                            <td>{{ $transaction->currency->code }}</td>
                                                            <td>{{ str_replace('-', '
                                                                ',number_format($transaction->total, 2)) }}</td>
                                                        </tr>
                                                        @endforeach
                                                        @if(count($data['transactionsSummary']) <= 0) <tr>
                                                            <td colspan="2">No transactions today</td>
                                                            </tr>
                                                            @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        @endif
                    </div>
                    <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                        <div class="container">
                            <div class="row">
                                @if(isset($data['adminApprovesTreasurerReports']) && count($data['adminApprovesTreasurerReports']) > 0)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Admin Approved Transactions</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="dataTable" class="table align-middle table-nowrap mb-0">
                                                    <thead class="table-light">
                                                        <th>Transaction Type</th>
                                                        <th>Currency</th>
                                                        <th>Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($data['adminApprovesTreasurerReports'] as $transaction)
                                                        <tr>
                                                            <td>{{ str_replace('_', '
                                                                ',$transaction->transaction_type->name) }}</td>
                                                            <td>{{ $transaction->currency->code }}</td>
                                                            <td>{{ str_replace('-', '
                                                                ',number_format($transaction->total, 2)) }}</td>

                                                        </tr>
                                                        @endforeach
                                                        @if(count($data['adminApprovesTreasurerReports']) <= 0) <tr>
                                                            <td colspan="2">No approved transactions today</td>
                                                            </tr>
                                                            @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($data['adminCancelTreasurerReports']) && count($data['adminCancelTreasurerReports']) > 0)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Admin Cancelled Transactions</h5>
                                        </div>
                                        <div class="card-body">
                                            <table id="dataTable" class="table align-middle table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <th>Transaction Type</th>
                                                    <th>Currency</th>
                                                    <th>Amount</th>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['adminCancelTreasurerReports'] as $transaction)
                                                    <tr>
                                                        <td>{{ str_replace('_', '
                                                            ',$transaction->transaction_type->name) }}</td>
                                                        <td>{{ $transaction->currency->code }}</td>
                                                        <td>{{ str_replace('-', '
                                                            ',number_format($transaction->total, 2)) }}</td>
                                                    </tr>
                                                    @endforeach
                                                    @if(count($data['adminCancelTreasurerReports']) <= 0) <tr>
                                                        <td colspan="2">No cancelled transactions today</td>
                                                        </tr>
                                                        @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane" id="fill-tabpanel-2" role="tabpanel" aria-labelledby="fill-tab-2">
                        @if(isset($data['treasurersSummary']) && count($data['treasurersSummary']) > 0)
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title">Treasurers Summary Today</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="dataTable" class="table align-middle table-nowrap mb-0">
                                                    <thead class="table-light">
                                                        <th>Transaction Time</th>
                                                        <th>Transaction ID</th>
                                                        <th> Account Info</th>
                                                        <th>Transaction Type</th>
                                                        <th>Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($data['treasurersSummary'] as $transaction)
                                                        <tr>
                                                            <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                                                            <td>{{ $transaction->uuid }}</td>
                                                            <td>{{ optional($transaction->user)->first_name }}
                                                                {{optional($transaction->user)->last_name }} - (
                                                                {{optional($transaction->user)->formattedPhone }} )</td>
                                                            <td>{{ str_replace('_', '
                                                                ',$transaction->transaction_type->name) }}</td>
                                                            <td>{{ str_replace('-', '
                                                                ',number_format($transaction->total, 2)) }}</td>
                                                        </tr>
                                                        @endforeach
                                                        @if(count($data['treasurersSummary']) <= 0) <tr>
                                                            <td colspan="2">No transactions today</td>
                                                            </tr>
                                                            @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if(!UserPermission::has_permission(auth()->guard('staff')->user()->id, 'accountant'))
                <!-- end page title -->
                {{-- @if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'view_staff')) --}}
                <div class="row">

                    {{-- <div class="card overflow-hidden"> --}}

                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Totals</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($financialData as $label => $data)
                                        <div class="col-md-6 mb-3 mt-2">
                                            <div class="card mini-stats-wid h-100">
                                                {{-- card header --}}
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Total {{$label}}</h5>
                                                </div>
                                                <div class="card-body d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-0">
                                                            Deposit: {{ number_format($data['Deposit'], 2) }} <br>
                                                            <br> <!-- Add space here -->
                                                            Withdraw: {{ number_format($data['Withdraw'], 2) }} <br>
                                                        </h5>
                                                    </div>
                                                    <div class="flex-shrink-0 align-self-center">
                                                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                                            <span class="avatar-title"><i
                                                                    class="fa fa-money-bill font-size-24"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- @endif --}}

                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Wallet Totals</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php $walletCount = 0; @endphp
                                        @foreach($balances as $balance)
                                        <div class="col-md-6">
                                            <!-- Set column width to 4 to display 3 wallets per row -->
                                            <div class="card mini-stats-wid">
                                                {{-- card haeder --}}
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">{{$balance->currency->name}}</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted fw-medium">{{$balance->currency->code}}
                                                            </p>
                                                            <h4 class="mb-0">{{$balance->currency->symbol}}
                                                                {{number_format($balance->balance, 2)}}</h4>
                                                        </div>
                                                        <div class="flex-shrink-0 align-self-center">
                                                            <div
                                                                class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                                                <span class="avatar-title">
                                                                    @switch($balance->currency->code)
                                                                    @case('USD')
                                                                    <i class="fa fa-usd font-size-24"></i>
                                                                    <!-- Font Awesome USD icon -->
                                                                    @break
                                                                    @case('GBP')
                                                                    <i class="fa fa-gbp font-size-24"></i>
                                                                    <!-- Font Awesome GBP icon -->
                                                                    @break
                                                                    @case('EUR')
                                                                    <i class="fa fa-eur font-size-24"></i>
                                                                    <!-- Font Awesome EUR icon -->
                                                                    @break
                                                                    @case('BTC')
                                                                    <i class="fa fa-btc font-size-24"></i>
                                                                    <!-- Font Awesome BTC icon -->
                                                                    @break
                                                                    @default
                                                                    <i class="fas fa-money-bill font-size-24"></i>
                                                                    <!-- Default money icon -->
                                                                    @endswitch
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $walletCount++; @endphp
                                        @if ($walletCount % 2 == 0)
                                    </div>
                                    <div class="row">
                                        <!-- Close and reopen row after every third wallet -->
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- end row -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-4">Transactions Chart</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-sm-flex flex-wrap">
                                    <div class="ms-auto">
                                        <ul class="nav nav-pills">
                                            <li class="nav-item"><a id="week-link" class="nav-link"
                                                    style="cursor: pointer;" onclick="generateChart('today')">Today</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="transactions-chart" class="apex-charts"></div>
                            </div>
                        </div>



                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-4">Latest Transaction</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle " id="dataTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 20px;">
                                                    <div class="form-check font-size-16 align-middle">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="transactionCheck01">
                                                        <label class="form-check-label"
                                                            for="transactionCheck01"></label>
                                                    </div>
                                                </th>
                                                <th class="align-middle">Transaction Date</th>
                                                <th class="align-middle">Account Info</th>
                                                <th class="align-middle">Transaction ID</th>
                                                <th class="align-middle">Currency</th>
                                                <th class="align-middle">Transaction Type</th>
                                                <th class="align-middle">Amount</th>

                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach($transactionsToday as $transaction)
                                            @if($transaction->subtotal !== null && $transaction->subtotal != 0)
                                            <tr>
                                                <td>
                                                    <div class="form-check font-size-16">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="transactionCheck{{ $loop->iteration }}">
                                                        <label class="form-check-label"
                                                            for="transactionCheck{{ $loop->iteration }}"></label>
                                                    </div>
                                                </td>
                                                <td>{{ $transaction->created_at }}</td>
                                                <td>{{ optional($transaction->end_user)->first_name }}
                                                    {{optional($transaction->end_user)->last_name }} - (
                                                    {{optional($transaction->end_user)->formattedPhone }} )</td>
                                                <td>{{ $transaction->uuid }}</td>
                                                <td>{{ $transaction->currency->code }}</td>
                                                <td>{{ str_replace('_', '
                                                    ',optional($transaction->transaction_type)->name)}}
                                                </td>
                                                <td>{{ number_format($transaction->subtotal, 2) }}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            @endif
            <!-- end table-responsive -->
        </div>
    </div>
    @include('staff.layouts.footer')


    <script>
        $(document).ready(function () {
        // Initialize DataTable
        $('#dataTable').DataTable();

        // Render the chart
        function renderChart(data) {
    if (data && data.length > 0) {
        var options = {
            series: [{
                name: "Transactions",
                data: data
            }],
            chart: {
                type: 'area',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            title: {
                text: 'Transactions Chart',
                align: 'left'
            },
            subtitle: {
                text: 'Transaction Movements',
                align: 'left'
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    formatter: function (val) {
                        return new Date(val).toLocaleTimeString();
                    }
                }
            },
            yaxis: {
                opposite: true
            },
            legend: {
                horizontalAlign: 'left'
            }
        };

        var chart = new ApexCharts(document.querySelector("#transactions-chart"), options);
        chart.render();
    } else {
        // Handle case when there is no data
        // console.log("No data available to render the chart.");
    }
}


        // Call renderChart with your data
        renderChart(@json($chartDataToday));
    });
    </script>