@include('staff.header')
@include('staff.sidebar')

<head>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <!-- JavaScript -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</head>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">

    
    <div class="page-content">
        <div id="message" class="text-center mt-2" style="display: none;"></div>

        <div class="container-fluid">
            <div class="d-flex justify-content-center">
                <div class="container mt-3">
                    <table id="pending-requests-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Phone</th>
                                <th>Amount</th>
                                <th>Sender</th>
                                <th>currency</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                     
                            @foreach ($pendingRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->user->phone }}</td>
                                <td>{{ $request->amount }}</td>
                                <td>{{ $request->user->first_name }}</td>
                                <td>{{ $request->currency->code }}</td>
                             
                                <td>
                                
        <!-- Approve button -->
        <button class="btn btn-success approve-btn" data-request-id="{{ $request->id }}" data-bs-toggle="modal" data-bs-target="#approveModal">Approve</button>
                                            <button class="btn btn-danger reject-btn" data-request-id="{{ $request->id }}">Reject</button>
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
<!-- Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card" id="cardFoam">
                <div class="card-body">
                    <form id="approveForm" action="{{ route('approveRequest') }}" method="POST">
                        @csrf

                <div class="modal-body">
                   
                    <input type="hidden" id="requestIdInput" name="requestId">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Accept Amount:</label>
                        <input type="number" class="form-control" id="amount" name="amount" required placeholder="Enter Amount">
                    </div>
                   
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Approve</button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>


@include('staff.footer')
<script>
$('.reject-btn').on('click', function() {
    var requestId = $(this).data('request-id'); // Get the request ID from data attribute
    $.ajax({
        url: '{{ route("rejectRequest") }}', // Use named route instead of hardcoded URL
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: requestId // Pass the request ID as data
        },
        success: function(response) {
            console.log(response);
            $('#message').text('Request rejected successfully').removeClass('text-danger').addClass('text-success').show();
            setTimeout(function() {
                location.reload();
            }, 1000);
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            $('#message').text('Error: ' + xhr.responseText).removeClass('text-success').addClass('text-danger').show();
        }
    });
});


    // $('#approveForm').submit(function(event) {
    //     event.preventDefault();
    //     var requestId = $('#requestIdInput').val();
    //     var amount = $('#amount').val();
    //     console.log(requestId);
    //     $.ajax({
    //         url: '{{ route("approveRequest") }}',
    //         method: 'POST',
    //         data: {
    //             requestId: requestId,
    //             amount: amount,
    //             _token: '{{ csrf_token() }}'
    //         },
    //         success: function(response) {
    //             console.log('Request approved successfully:', response);
    //             $('#approveModal').modal('hide');
    //             window.location.reload();
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error approving request:', error);
    //         }
    //     });
    // });

    $('.approve-btn').on('click', function() {
        // Get the request ID from the button's data attribute
        var requestId = $(this).data('request-id');
        
        // Set the request ID value to the hidden input field
        $('#requestIdInput').val(requestId);
    });
</script>

