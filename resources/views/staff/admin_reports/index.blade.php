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
        border-bottom: 1px solid #dee2e6;

    }

    .table th,
    .table td {
        padding: 8px 12px;
    }

    
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container">
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">All</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Reports</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- a section with select  --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Select Report Type</h5>
                        </div>
                        <div class="card-body">
                           {{-- select2 --}}
                        <select class="form-control select2" name="report_type" id="report_type">
                            <option disabled selected>Select An Option</option>
                            @foreach($report_types as $id => $report_type)
                                <option value="{{ $id }}">{{ $report_type }}</option>
                            @endforeach
                        </select>
                        </div>
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
</div>

@include('staff.layouts.footer')

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
            window.open("{{ route('staff.report.generate') }}" +"?" + $.param(params), "", "width=1000,height=940,scrollbars=yes", "left=40");
            // console.log(params);
        });
        
        $(".select2").select2();
        $(".select2").on("change", function() {
            report = $(this).val();
            url = "{{ route('staff.report.params') }}";
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