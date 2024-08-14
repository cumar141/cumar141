@include('staff.layouts.header')
@include('staff.layouts.sidebar')

<head>
    <!-- Other head content -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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
</style>
</head>
<div class="main-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="page-content">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if(Session::has('message'))
        <div class="alert alert-success">
            {{ Session::get('message') }}
        </div>
        @endif

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div id="message" class="alert alert-success" style="display: none;">
                            <span>Transaction rejected successfully!</span>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Pending Transactions</h4>

                            </div>
                            <div class="table-responsive widh-max">
                                @if(count($transactions) > 0)
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Sender</th>
                                            <th>Receiver</th>
                                            <th>Transaction Type</th>
                                            <th>Amount</th>
                                            <th>Note</th>
                                            <th>Date</th>
                                            <th>Approve</th>
                                            <th>Reject</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $item)
                                        <tr>
                                            <td>{{ $item['notification_info']['payload']['uuid'] }}</td>
                                            <td>{{ $item['notification_info']['payload']['sender']['name'] }}</td>
                                            <td>{{ $item['notification_info']['payload']['receiver']['name'] }}</td>
                                            <td>{{ str_replace('_',' ', $item['transaction_info']['transaction_type']['name'])}}</td>
                                            <td>{{ $item['transaction_info']->currency->symbol }}{{ number_format( str_replace('-', '' ,$item['notification_info']['payload']['amount']), 2) }} {{$item['transaction_info']['currency_id']}}</td>
                                            <td>{{ $item['transaction_info']->note }}</td>

                                            <td>{{ $item['notification_info']['created_at']->format('F j, Y, g:i a') }}</td>
                                            <td>
                                              
                                                <form id="approve-form-{{ $item['notification_info']['id'] }}" action="{{ route('approveRequest') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="transaction_uuid" value="{{ $item['notification_info']['payload']['uuid'] }}">
                                                    <input type="hidden" name="notificationId" value="{{ $item['notification_info']['id'] }}">
                                                    <button type="button" class="btn btn-primary" onclick="confirmAction('approve', {{ $item['notification_info']['id'] }})"><i class="fa fa-check" aria-hidden="true">
                                                    </i>
                                                        Approve</button>
                                                </form>
                       
                                            </td>
                                            <td>
                                                <form id="reject-form-{{ $item['notification_info']['id'] }}" action="{{route('rejectRequest')}}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="transaction_uuid" value="{{ $item['notification_info']['payload']['uuid'] }}">
                                                    <input type="hidden" name="notificationId" value="{{ $item['notification_info']['id'] }}">
                                                    <button type="button" class="btn btn-danger" onclick="confirmAction('reject', {{ $item['notification_info']['id'] }})"><i class="fa fa-ban" aria-hidden="true"></i>

                                                        Reject 
                                                        </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="alert alert-info">No pending transactions found.</div>
                                @endif
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('staff.layouts.footer')
    <script>
    function confirmAction(action, id) {
        const formId = action === 'approve' ? `approve-form-${id}` : `reject-form-${id}`;
        const actionText = action === 'approve' ? 'approve' : 'reject';
        
        console.log([formId, actionText]);
        

        Swal.fire({
            title: `Are you sure you want to ${actionText} this request?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${actionText} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
    </script>
</body>
