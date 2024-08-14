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
            <div class="container">
<!-- Inside your Blade view -->
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<!-- Inside your Blade view -->
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Failed transactions</h1>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="DataTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Sender</th>
                                        <th>Receiver</th>
                                        <th>Cleared Amount</th>
                                        <th>Amount</th>
                                        <th>Rate</th>
                                        <th>Fee</th>
                                        <th>Platform</th>
                                        <th>Payment Method</th>
                                        <th>Trx Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>{{ ++$loop->index }}</td>
                                            <td>{{ $transaction->created_at }}</td>
                                            <td>{{ $transaction->sender }}</td>
                                            <td>{{ $transaction->receiver }}</td>
                                            <td>{{ $transaction->cleared_amount }}</td> 
                                            <td>{{ $transaction->amount }}</td>
                                            <td>{{ $transaction->rate }}</td>
                                            <td>{{ $transaction->fee }}</td>
                                            <td>
                                                @if(in_array($transaction->platform, ["eDahab", "EVC Plus"]))
                                                    USSD
                                                @else
                                                    {{ settings('name') }}
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $miscData = (array) json_decode($transaction->misc);
                                                    $_ = "";
                                                    if(!empty($miscData)) $_ = implode(", ", array_values($miscData)); $_ = "<br><b>$_</b>";
                                                @endphp
                                                {{ $transaction->payment_method }} - {{ $transaction->partner }}{!! $_ !!}</td>
                                       
                                            <td>{{ $transaction->trx_reference }}</td>

                                            <td>
                                                <div class="text-end">
                                                <div class="btn-group" role="group" aria-label="Action buttons">
                                                    <button type="button" class="btn btn-info btn-sm retry" data-id="{{ $transaction->trx_reference }}" data-toggle="tooltip" data-placement="top" title="Retry">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm approve" data-id="{{ $transaction->trx_reference }}" data-toggle="tooltip" data-placement="top" title="Approve">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm block" data-id="{{ $transaction->trx_reference }}" data-toggle="tooltip" data-placement="top" title="Block">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </div>
                                                </div>
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

</body>
</html>

@include('staff.layouts.footer')
<script>
    $(document).ready(function() {
        $('#DataTable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            "pageLength": 10,
            "order": [
                [1, "desc"]
            ],
        });
    toastr.options = {"positionClass": "toast-top-center", "preventDuplicates": true},
    $('body').on('click', '.retry', function() {
        let url = `{{ route('staff.autopayout.retry') }}`;
        processRequest(url, this);
    });
    
    $('body').on('click', '.approve', function() {
        let url = `{{ route('staff.autopayout.approve') }}`;
        processRequest(url, this);
    });
    
    $('body').on('click', '.block', function() {
        let url = `{{ route('staff.autopayout.block') }}`;
        processRequest(url, this);
    });
    
    function processRequest(url, element) {
        let id = $(element).data('id');
        $(element).parent().find('button').each(function() {
            $(this).attr({'disabled': true});
        });
        showToastr("info", "Processing, please wait...");
        $.get(url, {transaction: id}).done(function( data ) {
            data = JSON.parse(data);
            showToastr(data.status, data.message);
            $(element).parent().find('button').each(function() {
                $(this).attr({'disabled': false});
            });
            window.location.href = window.location.href;
        }).fail(function() {
            showToastr("error", `Request failed, please contact developers! ${id}`);
            $(element).parent().find('button').each(function() {
                $(this).attr({'disabled': false});
            });
        });
    }
    
    function showToastr(status, message) {
        toastr.clear();
        toastr[status](message);
    }
});
</script>
