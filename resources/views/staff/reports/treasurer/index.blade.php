<!-- resources/views/users/index.blade.php -->

@include('../staff.layouts.header')
@include('../staff.layouts.sidebar')

<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    /* #currencyModal {
        display: none;
    } */
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container">
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">
                            @if(isset($branch))
                            {{ $branch->name }}
                            @endif
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Tellers</li>
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
                <!-- Managers Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Managers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        <tbody>
                                            @foreach($managers as $user)
                                            <tr>
                                                <td>
                                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}"
                                                        alt="user-image" class="img-fluid rounded-circle mb-2"
                                                        style="width: 50px; height: 50px;">
                                                </td>
                                                <td><strong></strong> {{ $user->first_name." ".$user->last_name }}
                                                </td>
                                                <td><strong></strong> {{ $user->formattedPhone }}</td>
                                                <td><strong></strong> {{ $user->email }}</td>
                                                <td>
                                                    <form method="post" action="#" id="managerForm">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="branch_id"
                                                            value="{{ $user->branch_id }}">
                                                        <input type="hidden" name="type" value="manager">
                                                        <button type="submit" name="action" value="closeAccount"
                                                            class="btn btn-primary">View Report</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tellers Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Tellers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        <tbody>
                                            @foreach($tellers as $user)
                                            <tr>
                                                <td>
                                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}"
                                                        alt="user-image" class="img-fluid rounded-circle mb-2"
                                                        style="width: 50px; height: 50px;">
                                                </td>
                                                <td><strong></strong> {{ $user->first_name." ".$user->last_name }}
                                                </td>
                                                <td><strong></strong> {{ $user->formattedPhone }}</td>
                                                <td><strong></strong> {{ $user->email }}</td>
                                                <td>
                                                    <form method="post" action="#" id="tellerForm">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="branch_id"
                                                            value="{{ $user->branch_id }}">
                                                        <input type="hidden" name="type" value="teller">
                                                        <button type="submit" name="action" value="closeAccount"
                                                            class="btn btn-danger">View Report</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal -->
            <div class="modal fade" id="currencyModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="currencyModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="currencyModalLabel">Select Currency and Date Range</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- dismissable error div --}}
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error"
                                style="display: none;"></div>

                            <select id="reportType" class="form-select select2" style="width: 100%;">
                                <option disabled selected>Select Report Type</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="transaction">Transaction</option>
                            </select>

                            <!-- Currency selection -->
                            <label for="currency">Select Currency:</label>
                            <select class="form-select select2" id="currency">
                                <option disabled selected>Select Currency</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                @endforeach
                            </select>

                            {{-- hidden input for manager_id, teller_id, type --}}
                            <input type="hidden" name="manager_id" value="">
                            <input type="hidden" name="user_id" value="">
                            <input type="hidden" name="type" value="">


                            <!-- Date range selection -->
                            <div class="mt-3">
                                <div id="daterange"
                                    style="background: #fff; color: darkgrey; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary" id="generate_report">Generate</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('../staff.layouts.footer')





<script type="text/javascript">

    // on submit manager form or teller form show modal to select currency and date range
    $('#managerForm, #tellerForm').submit(function(e) {
        e.preventDefault();

        var form = $(this);
        var type = form.find('input[name="type"]').val();
        var user_id = form.find('input[name="user_id"]').val();
 


        $('#currencyModal input[name="user_id"]').val(user_id);
        $('#currencyModal input[name="type"]').val(type);


        $('#currencyModal').modal('show');
    });

    // select2
    $(document).ready(function() {
        $('.select2').select2({
            dropdownParent: $("#currencyModal")
        });

        // fix select2 width issue
        $('.select2-container').css('width', '100%');
    });



    $(function() {
    
        var start = moment().subtract(29, 'days');
        var end = moment();
    
        function cb(start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
    
        $('#daterange').daterangepicker({
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


    // generate report
    $('#generate_report').click(function() {

        date = $('#daterange span').text();
        start_date = date.split(" - ")[0];
        end_date = date.split(" - ")[1];

        var currency = $('#currency').val();
        var user_id = $('#currencyModal input[name="user_id"]').val();
        var type = $('#currencyModal input[name="type"]').val();
        var reportType = $('#reportType').val();


        if(currency == '' || start_date == '' || end_date == '' || reportType == '') {
            $('#error').show().text('All fields are required.');
            return;
        }

        var params = {
            currencyID: currency,
            start_date: start_date,
            end_date: end_date,
            user_id: user_id,
            type: type,
            report_type: reportType

        };

        // console.log(params);
         window.location.href = "{{ route('staff.reports.treasurer') }}" +"?" + $.param(params);


    });
</script>