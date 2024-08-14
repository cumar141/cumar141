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

{{-- @include('staff.spinner') --}}
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Bulk Deposit </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Tellers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

           
            <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
                <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
                <input id="user_id" type="hidden" name="user_id" value="{{ isset($user) ? $user : '' }}">

            <div class="row">
                <div class="col-md-8">
                    <h3 class="panel-title text-bold ml-5 f-14">{{ __('All Transactions') }}</h3>
                </div>
                <div class="col-md-4">
                    <div class="btn-group pull-right" role="group" >
                        <a href="" class="btn btn-sm btn-default btn-flat f-14"
                            id="csv">{{ __('CSV') }}</a>
                        <a href="" class="btn btn-sm btn-default btn-flat f-14"
                            id="pdf">{{ __('PDF') }}</a>
                    </div>
                </div>
            </div>
            <div>
                <div class="card">
                    <div class="card-header">Manage Users</div>
                    <div class="card-body">
                        {!! $dataTable->table([
                            'class' => 'table table-striped table-hover dt-responsive transactions',
                            'width' => '100%',
                            'cellspacing' => '0',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('staff.layouts.footer')
@push('extra_body_scripts')
{{-- {!! $dataTable->scripts() !!} --}}
{!! $dataTable->scripts() !!}
<script>
// csv
    $(document).ready(function()
    {
        $('#pdf').on('click', function(event)
        {
          event.preventDefault();

          var startfrom = $('#startfrom').val();
          var endto = $('#endto').val();

          var status = $('#status').val();
          var currency = $('#currency').val();
          var type = $('#type').val();
          var user_id = $('#user_id').val();

          window.location = '/transactions.pdf?startfrom='+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
          +"&type="+type
          +"&user_id="+user_id;

        });

        $('#csv').on('click', function(event)
        {

          event.preventDefault();

          var startfrom = $('#startfrom').val();
          var endto = $('#endto').val();

          var status = $('#status').val();
          var currency = $('#currency').val();
          var type = $('#type').val();
          var user_id = $('#user_id').val();

          window.location = SITE_URL+"/"+ADMIN_PREFIX+"/transactions/pdf?startfrom="+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
          +"&type="+type
          +"&user_id="+user_id;

        });
    });
</script>