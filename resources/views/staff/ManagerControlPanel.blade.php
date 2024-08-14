@include('staff.layouts.header')
@include('staff.layouts.sidebar')

@include('staff.otp2')
<style>

    .modal-body span {
        color: #333; /* Change to your desired font color */
    }


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

    /* Style for Manager Information card */


    /* Style for Actions card */
    .actions-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        margin-top: 20px;
    }

    /* Style for modal header */
    .modal-header {
        border-bottom: none;
    }

    /* Style for modal title */
    .modal-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-right: auto;
    }

    /* Style for modal body */
    .modal-body {
        padding: 20px;
    }

    /* Style for modal footer */
    .modal-footer {
        border-top: none;
    }

    /* Custom button style */
    .btn-custom {
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        cursor: pointer;
    }

    /* Custom button hover effect */
    .btn-custom:hover {
        background-color: #0056b3;
    }

    .card:hover {
        transform: translateY(-3px);
    }
</style>



<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Bulk Close </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Tellers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @include('staff.layouts.miniNav')
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <div class="col-md-12">
                <div class="card manager-actions-card">
                    <div class="card-header">
                        <h5 class="card-title">Close All Accounts</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Form Column -->
                            <div class="col-md-6">
                                <form id="actionForm" action="{{ route('handleTransaction') }}" method="POST" class="mb-4">
                                    @csrf
                                    <div class="mb-3 mt-2">
                                        <div id="errorMessage" style="display: none; color: red;"></div>
                                        <textarea class="form-control" name="note" id="note" rows="3" placeholder="Enter your note"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <input type="password" class="form-control" id="password" required name="password" placeholder="Enter Password">
                                    </div>
                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary" onclick="validateForm()">Close All Accounts</button>
                                    </div>
                                </form>
                            </div>
                            <!-- Table Column -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="bg-info text-white rounded">
                                            <tr>
                                                <th>Name</th>
                                                @foreach ($currencies as $currency)
                                                    <th>{{ $currency->code }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $currencyTotals = array_fill_keys($currencies->pluck('id')->toArray(), 0);
                                            @endphp
                                            @foreach ($user as $teller)
                                                <tr class="teller">
                                                    <td>{{ $teller->first_name }}  {{ $teller->last_name }}</td>
                                                    @foreach ($currencies as $currency)
                                                        @php
                                                            $balance = $teller->wallets->where('currency_id', $currency->id)->first()->balance ?? 0;
                                                            $currencyTotals[$currency->id] += $balance;
                                                        @endphp
                                                        <td data-currency="{{ $currency->id }}">{{ number_format($balance, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr class="bg-primary text-light rounded-md">
                                                <td>Total</td>
                                                @foreach ($currencies as $currency)
                                                    <td>{{ number_format($currencyTotals[$currency->id], 2) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
                             
                            
                                
                                  
                            
 
<!-- Modal -->
<div class="modal fade" id="confirmationModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Close All Accounts Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Total Amount for Each Currency</h6>
                @foreach ($currencies as $currency)
                <p><strong>{{ $currency->code }}:</strong> <span id="modalTotalAmount{{ $currency->id }}"></span></p>
                @endforeach
                <p><strong>Total Number of Tellers:</strong> <span id="modalTotalTellers"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="showModal()" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>

      @php
      $user_id=auth()->guard('staff')->user()->id;
      @endphp
@include('staff.layouts.footer')

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    
function validateForm() {
  
  var password = document.getElementById('password').value;
  var note = document.getElementById('note').value;

  if (!note || !password) {
   
      var errorMessageDiv = document.getElementById('errorMessage');
      errorMessageDiv.textContent = "Please fill in all fields.";
      errorMessageDiv.style.display = 'block';
      return false;
  }


  showConfirmationModal();
  return false; 
}

function showModal() {

        $('#spinner').show();
        $.ajax({
            type: 'get',
            url: "{{ route('sendOtp') }}",
            data: {
                user_id: {{$user_id}}
            },
            
            success: function(response) {
                console.log(response);
                if (response.success === true) {
                    $('#confirmationModal').modal('hide');
                 
                    showOtpModal();
                } else {
                    $('#confirmationModal').modal('hide');
                    alert('Failed to generate OTP. Please try again.');
                  
                }
            },
            error: function() {
              
                alert('Error occurred while generating OTP. Please try again.');
            }
        });
    }
    function calculateTotals() {
        var currencyTotals = {}; // Object to store total balance for each currency
        var totalTellers = 0; // Total count of tellers

        // Initialize currency totals object with zeros
        @foreach ($currencies as $currency)
            currencyTotals['{{ $currency->id }}'] = 0;
        @endforeach

        // Iterate through each table row
        $('.table tbody .teller').each(function() {
            var $row = $(this);
            totalTellers++; // Increment total tellers count

            // Iterate through each currency column in the row
            @foreach ($currencies as $currency)
                var balance = $row.find('td[data-currency="{{ $currency->id }}"]').text().trim(); // Get the balance data
                currencyTotals['{{ $currency->id }}'] += parseFloat(balance) || 0; // Add balance to corresponding currency total
            @endforeach
        });

        return { currencyTotals: currencyTotals, totalTellers: totalTellers };
    }

    // Function to update modal with totals and show it
    function showConfirmationModal() {
        var totals = calculateTotals();

        // Set total amount for each currency in the modal
        @foreach ($currencies as $currency)
            $('#modalTotalAmount{{ $currency->id }}').text(totals.currencyTotals['{{ $currency->id }}'].toFixed(2)); // Access currency totals using string index
        @endforeach

        // Set total number of tellers in the modal
        $('#modalTotalTellers').text(totals.totalTellers);

        // Show the confirmation modal
        $('#confirmationModal').modal('show');
    }
function showOtpModal() {
        var modal = new bootstrap.Modal(document.getElementById('otpModal'));
        modal.show();
        $('#confirmationModal').modal('hide');
    }
</script>

