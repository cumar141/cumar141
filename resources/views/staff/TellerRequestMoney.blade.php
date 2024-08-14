@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<div class="container mb-5">
                <div class="row justify-content-center  mb-5">
                    <div class="col-md-6">
                        <div class="card rounded shadow border-primary mb-4">
                            <div class="card-body">
                                @if(Session::has('error'))
                                <div class="alert alert-danger">
                                    {{ Session::get('error') }}
                                </div>
                            @endif
                                <h5 class="card-title text-center text-primary mb-4">Manager Info</h5>
                                <div class="text-center mb-4">
                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}"
                                        alt="user-image" class="avatar-lg rounded-circle border border-primary">
                                </div>
                                <div class="text text-center">
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Name:</label>
                                        <p class="text-muted">{{ $manager->first_name }} {{ $manager->last_name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Email:</label>
                                        <p class="text-muted">{{ $manager->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Phone:</label>
                                        <p class="text-muted">{{ $manager->phone }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Formatted Phone:</label>
                                        <p class="text-muted">{{ $manager->formattedPhone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center mb-1 text-primary">Request Money Form</h5>
                                <form id="TellerRequestForm" action="{{ route('TellerRequestHandler') }}" method="POST">
                                    <div id="errorMessage" style="display: none; color: red;"></div>
                                    @csrf
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $branchID }}">
                                    @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label for="currentAmount" class="form-label text-primary">Currencies</label>
                                        <select class="form-select" name="currency" id="currency">
                                            <option selected>Select currency</option>
                                            @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="amount" class="form-label text-primary">Amount</label>
                                        <input type="number" class="form-control" name="amount" id="amount"
                                            placeholder="Enter Amount">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label text-primary">Password</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Enter your password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="note" class="form-label text-primary">Note</label>
                                        <textarea class="form-control" name="note" id="note" rows="3"
                                            placeholder="Enter your note"></textarea>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary"
                                            onclick="validateForm()">Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Modal -->
        <div class="modal fade" id="confirmationModal"  data-bs-backdrop="static"  tabindex="-1" aria-labelledby="confirmationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Confirm Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Display transfer details here -->
                        <p><strong>Currency:</strong> <span id="modalCurrency"></span></p>
                        <p><strong>Amount:</strong> <span id="modalAmount"></span></p>
                        <p><strong>Description:</strong> <span id="modalDescription"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" onclick="showModal()" id="confirmTransferBtn"
                            class="btn btn-primary">Confirm Transfer</button>
                    </div>
                </div>
            </div>
        </div>
@include('staff.treasurer.otp')
@include('staff.layouts.footer')
<script>

function validateForm() {
  
  var currency = document.getElementById('currency').value;
  var amount = document.getElementById('amount').value;
  var note = document.getElementById('note').value;
  var password = document.getElementById('password').value;


  if (!currency || !amount || !note || !password) {
   
      var errorMessageDiv = document.getElementById('errorMessage');
      errorMessageDiv.textContent = "Please fill in all fields.";
      errorMessageDiv.style.display = 'block';
      return false;
  }


  showConfirmationModal();
  return false; 
}
function showConfirmationModal() {
        var currency = $('#currency option:selected').text();
        var amount = $('#amount').val();
        var description = $('#note').val();

        // Populate the modal with transfer details
        $('#modalCurrency').text(currency);
        $('#modalAmount').text(amount);
        $('#modalDescription').text(description);

        // Show the modal
        $('#confirmationModal').modal('show');
    }
     function verify() {
            var otp = getOTP();
    
            $.ajax({
                type: 'GET',
                url: '{{ route('verifyOtps') }}',
                data: {
                    otp: otp,
                    user_id: {{ $user_id }},
                    otp: otp
                },
                success: function(response) {
                    if (response.success) {
                        console.log("the response is :", response);
                        hideConfirmationModal();
                         document.getElementById('TellerRequestForm').submit();
                    } else {
                        clearOTPInputs();
                        $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
                    }
                },
                error: function() {
                    $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
                }
            });
        }
</script>