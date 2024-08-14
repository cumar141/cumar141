@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
    .form-outline {
        display: inline-block;
    }

    .form-outline input {
        width: 300px;
        /* Set the width to your desired value */
        display: inline-block;
        margin: 0;
    }

    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.1/css/dataTables.dateTime.min.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>print reciept</title>
<!-- Include CSRF Token Meta Tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="container mt-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Receipts</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($transactions))
                        <div class="table-responsive">
                            <table id="example" class="display nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction ID</th>
                                        <th>Account info</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at }}</td>
                                        <td>{{ $transaction->uuid }}</td>
                                        <td>{{ $transaction->user->first_name }}</td>
                                        <td>{{ $transaction->currency->code }}</td>
                                        <td>{{ $transaction->subtotal }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary print-button"
                                                onClick="printReceipt('{{ $transaction->id }}');">Print</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="https://cdn.datatables.net/datetime/1.5.1/js/dataTables.dateTime.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    function printReceipt(id) {

    console.log(id);
    // Redirect to the new route with transaction ID
    window.location.href = "printRoute/" + id;
}

var table; // Declare the table variable outside the scope of any function

$(document).ready(function() {


    // DataTables initialisation
    table = new DataTable('#example');


});

   

</script>

@include('staff.layouts.footer')