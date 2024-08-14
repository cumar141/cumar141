@include('staff.layouts.header')
@include('staff.layouts.sidebar')
    
@include('staff.treasurer.otp')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card rounded shadow border-primary mb-4">
                <div class="card-body">
                    @if(Session::has('error'))
                    <div class="alert alert-danger">
                        {{ Session::get('error') }}
                    </div>
                @endif
                
                    <h5 class="card-title text-center text-primary mb-4">Treasurer Info</h5>
                    <div class="text-center mb-4">
                        <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}" alt="user-image" class="avatar-lg rounded-circle border border-primary">
                    </div>
                    <div class="text-center">
                        <div class="mb-3">
                            <label class="form-label text-primary">Name:</label>
                            <p class="text-muted">{{ $treasurer->first_name }} {{ $treasurer->last_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Email:</label>
                            <p class="text-muted">{{ $treasurer->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Phone:</label>
                            <p class="text-muted">{{ $treasurer->phone }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-primary">Formatted Phone:</label>
                            <p class="text-muted">{{ $treasurer->formattedPhone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center text-primary mb-4">Request Money Form</h5>
                    <form id="ManagerRequestForm" action="{{ route('managerRequestHandler') }}" method="POST">
                        @csrf
                        <div id="errorMessage" style="display: none; color: red;"></div>
                        <div class="mb-3">
                            <label for="currentAmount " class="form-label text-primary">Currency</label>
                            <select class="form-select"  name="currency" id="currency">
                                <option selected>Select currency</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount " class="form-label text-primary">Amount</label>
                            <input type="number" class="form-control" name="amount" id="amount" placeholder="Enter Amount">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-primary">Password</label>
                            <input type="text" class="form-control" id="password" name="password" placeholder="xxxxxx">
                        </div>
                       
                        <div class="mb-3">
                            <label for="note" class="form-label text-primary">Note</label>
                            <textarea class="form-control" name="note" id="note" rows="3" placeholder="Enter your note"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-primary" onclick="validateForm()">Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Modal -->
        <div class="modal fade" id="confirmationModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="confirmationModalLabel"
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
        @include('staff.layouts.footer')
@php
$user_id=auth()->guard('staff')->user()->id;
@endphp


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

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
    // Function to show the confirmation modal with transfer details
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
                url: '{{ route("verifyOtps") }}',
                data: {
                    otp: otp,
                    user_id: {{ $user_id }},
                    
                },
                success: function(response) {
                    if (response.success) {
                        console.log("the response is :", response);
                       
                         document.getElementById('ManagerRequestForm').submit();
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

        function getOTP() {
            var otpInput1 = document.getElementById('otpInput1').value;
            var otpInput2 = document.getElementById('otpInput2').value;
            var otpInput3 = document.getElementById('otpInput3').value;
            var otpInput4 = document.getElementById('otpInput4').value;
            var otpInput5 = document.getElementById('otpInput5').value;
            var otpInput6 = document.getElementById('otpInput6').value;
    
            var otp = otpInput1 + otpInput2 + otpInput3 + otpInput4 + otpInput5 + otpInput6;
    
            return otp;
        }
    
</script>