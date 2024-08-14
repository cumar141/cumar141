@include('staff.layouts.header')
@include('staff.layouts.sidebar')

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
@php

$userid = auth()->guard('staff')->user()->id;
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container">

            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Reports </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                                <li class="breadcrumb-item active">my reports</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <div class="container">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">My Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 f-14">
                                <div id="report_params">
                                    <div class="card-body bg-white">
                                        <label class="f-14 fw-bold mb-1" for="transaction_type">{{ __('Select Type')
                                            }}</label><br>
                                        <select name="transaction_type" id="transaction_type" class="form-control f-14 w-100">
                                            <option disabled selected>Select Option</option>
                                            <option value="deposit">My Deposit</option>
                                            <option value="withdrawal">My Withdrawal</option>
                                            <option value="transaction">My Transactions</option>
                                        </select>
                                        <hr>
                                        <label class="f-14 fw-bold mb-1" for="currency">{{ __('Wallet')
                                            }}</label><br>
                                        <select id="currency" name="currency" class="form-control f-14 w-100">
                                            <option disabled selected>Select Wallet</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div id="reportrange" class="mt-3 mb-3 p-3 bg-white w-100"
                                    style="color: darkgrey; cursor: pointer; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                                <button class="btn btn-primary mt-3" id="generate_report">Generate</button>
                            </div>
                            @if(session('error'))
                            <div class="col-md-12 mt-3">
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>



            @if(isset($transactions))
            <div class="card">
                <div class="card-header ">
                    <div class="card-title">
                        <h3>Todays Reports</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="Datatable">
                      
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th scope="col">S/N</th>
                                    <th scope="col">Transaction Date</th>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Account Information</th>
                                    <th scope="col">Transaction Type</th>
                                    <th scope="col">Currency</th>
                                    <th scope="col">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $key => $transaction)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $transaction->created_at }}</td>
                                    <td>{{ $transaction->uuid }}</td>
                                    <td>{{ $transaction->user->first_name }} {{
                                        $transaction->user->last_name }}</td>
                                    <td>{{ str_replace('_', '
                                        ',$transaction->transaction_type->name) }}</td>
                                    <td>{{ $transaction->currency->code }}</td>
                                    <td>{{ str_replace('-', ' ',number_format($transaction->total,
                                        2)) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            @else
                            <div class="alert alert-danger">
                                No data available
                            </div>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('staff.layouts.footer')



<script>
    // Update data attribute on form submission
    $("#transaction_form").on("submit", function () {
        $('#generate_report').data("phone", $("#customer_phone").val());
        // Add data for the currency if needed
        $('#generate_report').data("currency", $("#currency").val());
    });



        // document ready function
        $(document).ready(function () {
        // generate chart for today
        // dataTable
      $('#DataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
      });

    });
</script>




<script>
    $(document).ready(function () {

        $("#generate_report").on("click", function () {
            // create parameters that include the report type and the date range and phone 
            date = $('#reportrange span').text(); 
            start_date = date.split(" - ")[0];
            end_date = date.split(" - ")[1];
            var params = {
                report_type: 'mr',
                start_date: start_date,
                end_date: end_date,
                currencyID: $('#currency').val(),
                transaction_type: $('#transaction_type').val(),
            };
            window.location.href = "{{ route('staff.reports.mr') }}" +"?" + $.param(params);
            // console.log(params);
        });
        
        $("#daterange-btn").mouseover(function () {
            $(this).css('background-color', 'white');
            $(this).css('border-color', 'grey !important');
        });
    });
</script>

<script type="text/javascript">
    $(function () {
        var start = moment().subtract(29, 'days');
        var end = moment();
        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        cb(start, end);
    });
</script>
{{-- @include('staff.footer'); --}}