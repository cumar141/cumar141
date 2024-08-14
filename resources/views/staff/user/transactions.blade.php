@include('staff.layouts.header')
@include('staff.layouts.sidebar')
@section('title', __('Edit User'))


<link
     rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"
   />
   <link
   rel="stylesheet"  href={{ asset('public/admin/customs/css/phone.css') }}>
   
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
    .intl-tel-input {
  width: 100%;
}
.iti {
  width: 100%;
}

#phone:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Create </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">New User</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            {{-- error handling --}}
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
            {{-- this page is for creating new users --}}
            
            @include('staff.user.tab')
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <h3 class="f-24 mb-0">{{ getColumnValue($users) }}</h3>
                    <p class="badge bg-{{ $users->status == 'Active' ? 'success' : ($users->status == 'Inactive' ? 'danger' : 'warning') }} mb-0 ms-1">{{ $users->status == 'Active' ? __('Active') : ($users->status == 'Inactive' ? __('Inactive') : __('Suspended')) }}</p>
                </div>
            </div>
           
            <br>
            
            <div class="col-md-3"></div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transactions</h5>
                </div>
                <div class="card-body bg-slate-500">
                    <div class="table-responsive">
                        <table id="DataTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Tranaction ID</th>
                                    <th>Transaction Type</th>
                                    <th>Status</th>
                                    <th>Amount</th>
          
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{$transaction->created_at}}</td>
                                    <td>{{$transaction->uuid}}</td>
                                    <td>{{ str_replace('_', ' ',$transaction->transaction_type->name)}}</td>
                                    <td style="color: 
                                    @if($transaction->status == 'Success') 
                                    green;
                                    @elseif($transaction->status == 'Cancelled')
                                    red;
                                    @elseif($transaction->status == 'Pending')
                                    orange;
                                    @endif
                                    ">{{$transaction->status}}</td>
                                <td>{{ str_replace('-', ' ',$transaction->total)}}</td>

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
@include('staff.layouts.footer')