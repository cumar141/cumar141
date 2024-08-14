@include('staff.layouts.header')
@include('staff.layouts.sidebar')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Transactions</title>
</head>

<body>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Success and Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Export Buttons -->
                <div class="text-end mb-3">
                    <a href="" class="btn btn-primary" id="csv">CSV</a>
                    <a href="" class="btn btn-primary" id="pdf">PDF</a>
                </div>
                
                <!-- Filters Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h1 class="card-title">Filters</h1>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('staff.transactions.all') }}">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="f-14 fw-bold mb-1" for="daterange-btn">{{ __('Date Range') }}</label><br>
                                        <button type="button" class="btn btn-outline-secondary form-control d-flex justify-content-between align-items-center" id="daterange-btn">
                                            <span id="drp">
                                                <i class="fa fa-calendar"></i> Pick Date Range
                                            </span>
                                            <i class="fa fa-caret-down"></i>
                                        </button>
                                        <!-- Hidden input fields for start and end dates -->
                                        <input type="hidden" name="from" id="start_date">
                                        <input type="hidden" name="to" id="end_date" value="{{ isset($to) ? $to : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="currency">Currency</label>
                                        <select class="form-control" id="currency" name="currency">
                                            <option value="all" {{ ($currency =='all') ? 'selected' : '' }}>All</option>
                                            @if(!empty($currencies))
                                                @foreach($currencies as $value)
                                                    <option value="{{ $value->id }}" {{ ($value->id == $currency) ? 'selected' : '' }}>
                                                        {{ $value->code }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="transactionStatus">Status</label>
                                        <select class="form-control select2" name="transactionStatus" id="transactionStatus">
                                            <option value="all" {{ ($status =='all') ? 'selected' : '' }}>All</option>
                                            @if(!empty($statuses))
                                                @foreach($statuses as $value)
                                                    <option value="{{ $value }}" {{ ($value == $status) ? 'selected' : '' }}>
                                                        {{ $value == 'Blocked' ? "Cancelled" : ($value == 'Refund' ? "Refunded" : $value) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="type">Type</label>
                                        <select class="form-control" id="type" name="type">
                                            <option value="all" {{ ($type =='all') ? 'selected' : '' }}>All</option>
                                            @if(!empty($transactionTypes))
                                                @foreach($transactionTypes as $value)
                                                    <option value="{{ $value->id }}" {{ ($value->id == $type) ? 'selected' : '' }}>
                                                        {{ $value->name == "Withdrawal" ? "Payout" : str_replace('_', ' ', $value->name) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="user">User</label>
                                        <input type="text" class="form-control" id="user_id" name="user_id" placeholder="User" value="{{ isset($user) ? $user : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary mt-4">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Transactions Table -->
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">All Transactions</h1>
                    </div>
                    <div class="card-body">
                        {!! $dataTable->table(['class' => 'table table-striped table-hover f-14 dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('staff.layouts.footer')
    {!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
    
    <script>
     $(document).ready(function() {
    $('#daterange-btn').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    }, function(start, end) {
        var startDate = start.format('YYYY-MM-DD');
        var endDate = end.format('YYYY-MM-DD');
        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
        $('#drp').text(startDate + ' - ' + endDate);
    });

    var start_date = '{{ isset($from) ? $from : '' }}';
    var end_date = '{{ isset($to) ? $to : '' }}';

    if (start_date && end_date) {
        var startDate = moment(start_date);
        var endDate = moment(end_date);

        $('#daterange-btn').data('daterangepicker').setStartDate(startDate);
        $('#daterange-btn').data('daterangepicker').setEndDate(endDate);

        $('#start_date').val(start_date);
        $('#end_date').val(end_date);

        $('#drp').text(start_date + ' - ' + end_date);
    }

    $('#csv').on('click', function(event) {
        event.preventDefault();
        var SITE_URL = "{{ url('/') }}"; // Define the base URL
        var staffPrefix = "{{ config('staff') }}"; // Adjust if necessary
        var startfrom = $('#start_date').val();
        var endto = $('#end_date').val();
        var status = $('#transactionStatus').val();
        var currency = $('#currency').val();
        var type = $('#type').val();
        var user_id = $('#user_id').val();

        // Construct URL for CSV export
        var csvUrl = SITE_URL + "/staff" + staffPrefix + "/transactions/csv?startfrom=" + startfrom
            + "&endto=" + endto 
            + "&status=" + status
            + "&currency=" + currency
            + "&type=" + type
            + "&user_id=" + user_id;
            console.log(csvUrl);

        window.location.href = csvUrl;
    });

    $('#pdf').on('click', function(event) {
        event.preventDefault();
        var SITE_URL = "{{ url('/') }}"; 
        var staffPrefix = "{{ config('staff') }}"; 
        var startfrom = $('#start_date').val();
        var endto = $('#end_date').val();
        var status = $('#transactionStatus').val();
        var currency = $('#currency').val();
        var type = $('#type').val();
        var user_id = $('#user_id').val();

        // Construct URL for PDF export
        var pdfUrl = SITE_URL + "/staff" + staffPrefix + "/transactions/pdf?startfrom=" + startfrom
            + "&endto=" + endto 
            + "&status=" + status
            + "&currency=" + currency
            + "&type=" + type
            + "&user_id=" + user_id;

        window.location.href = pdfUrl;
    });
});

    </script>
</body>

</html>

