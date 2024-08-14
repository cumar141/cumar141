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
</style>
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
                    <div class="card-header ">
                        <div class="d-flex justify-content-center">
                            <div class="form-outline mt-5">
                                <form id="searchUser" action="{{route('SearchReceipt')}}" method="get">
                                    @csrf
                                    <div class="input-group">
                                        <input type="search" id="phone" name="phone" class="form-control"
                                            placeholder="Type User phone" aria-label="Search" style="width: 500px;" />
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="text-danger" id="error"></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flash-container">
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
            
                            {{-- success message --}}
                            @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif

                           
            
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
                                            <td>{{ number_format($transaction->subtotal, 2) }}</td>
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
</div>

@include('staff.layouts.footer')

<script>
var table; // Declare the table variable outside the scope of any function

$(document).ready(function() {

    // DataTables initialisation
    table = new DataTable('#example');

   

   
});

function printReceipt(id) {

    console.log(id);
    // Redirect to the new route with transaction ID
    window.location.href = "printRoute/" + id;
}


</script>
