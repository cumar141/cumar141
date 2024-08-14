@extends('admin.layouts.master')
@section('title', __('Reports'))

@section('head_style')
<!-- Bootstrap daterangepicker -->
<link rel="stylesheet" type="text/css"
    href="{{ asset('public/dist/plugins/daterangepicker-3.1/daterangepicker.min.css')}}">

<!-- jquery-ui-1.12.1 -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css')}}">
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


@section('page_content')

 <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h3>Report Type</h3>
                </div>
            </div>

            <div class="card-body">
                <form class="form-horizontal" action="{{ route('admin.report.index') }}" method="GET"
                    id='transaction_form'>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex flex-wrap">
                                    <label class="f-14 fw-bold mb-1" for="daterange-btn">{{ __('Report Type')
                                        }}</label><br>
                                    <select class="form-control select2" name="report_type" id="report_type">
                                        <option disabled selected>Select Option</option>
                                        @foreach($report_types as $id => $report_type)
                                        <option value="{{ $id }}">{{ $report_type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h3>Report Parameters</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 f-14">
                        <div id="report_params"></div>
                        <div id="reportrange"
                            style="background: #fff; color: darkgrey; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                            <i class="fa fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                        </div>
                        <button class="btn btn-primary mt-3" id="generate_report">Generate</button>
                    </div>
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                
                </div>
            </div>
        </div>
    </div>
  
    
    </div>
</div> 
@include('admin.layouts.partials.message_boxes')
@endsection

@push('extra_body_scripts')

<!-- Bootstrap daterangepicker -->
<script src="{{ asset('public/dist/plugins/daterangepicker-3.1/daterangepicker.min.js') }}" type="text/javascript">
</script>

<!-- jquery-ui-1.12.1 -->
<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#generate_report").on("click", function () {
            // create parameters that include the report type and the date range and phone 
            date = $('#reportrange span').text();
            start_date = date.split(" - ")[0];
            end_date = date.split(" - ")[1];
            var params = {
                report_type: $('#report_type').val(),
                start_date:start_date,
                end_date:end_date,
                phone: $('#phone').val(),
                currencyID: $('#currency').val(),
                status: $('#_status').val(),
                singall: $('#singall').val(),
                payment_method: $('#payment_method').val(),
                platform: $('#platform').val(),
                partner: $('#partner').val(),
            };
            window.open("{{ route('admin.report.generate') }}" +"?" + $.param(params), "", "width=1000,height=940,scrollbars=yes", "left=40");
            // console.log(params);
        });
        
        $(".select2").select2({});
        $(".select2").on("change", function() {
            report = $(this).val();
            url = "{{ route('admin.report.params') }}";
            data = {report : report};
            $('#report_params').empty();
            $('#generate_report').data("type", report);
            $.get(url, data, function(response) {
                if (response) {
                    $('#report_params').html(response);
                }
            });
        });
        
        $("#daterange-btn").mouseover(function() {
            $(this).css('background-color', 'white');
            $(this).css('border-color', 'grey !important');
        });

        

    });
</script>

<script type="text/javascript">
    $(function() {
    
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

@endpush