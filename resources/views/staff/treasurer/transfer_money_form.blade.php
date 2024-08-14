<!-- resources/views/users/index.blade.php -->
@include('staff.layouts.header')
@include('staff.layouts.sidebar')



@include('staff.treasurer.otp')
<!-- start page title -->
<div class="main-content">
    <div class="page-content">
        <div class="container">
            {{-- @include('staff.miniNav') --}}

            <div class="row">
                <div class="col-12">
                    @if(isset($user))
                    <form id="transferForm" action="{{ route('staff.treasurer.transfer_money') }}" method="post">
                        @csrf
                        <div id="errorMessage" style="display: none; color: red;"></div>
                        {{-- display user info --}}
                        <div class="card">
                            <div class="card-body">
                                <center>
                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}"
                                        alt="user-image" class="img-fluid rounded-circle mb-2"
                                        style="width: 100px; height: 100px;">
                                    <p class="card-text"><strong>Name:</strong> {{ $user->first_name."
                                        ".$user->last_name }}</p>
                                    <p class="card-text"><strong>Phone:</strong> {{ $user->formattedPhone }}</p>
                                    <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
                                </center>
                            </div>
                        </div>
                        <!-- Check for general error message -->
                        @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <!-- Check for validation errors -->
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif


                        {{-- SELECT CURRENCY TYPE --}}
                        <div class="form-outline">
                            <label class="form-label" for="currency">Currency</label>
                            <select required class="form-select" name="currency" id="currency">
                                @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}"> {{$currency->code}}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="form-outline">
                            <label class="form-label" for="amount">Amount</label>
                            <input required type="number" id="amount" class="form-control" name="amount" />
                        </div>
                        <div class="form-outline">
                            <label class="form-label" for="note">Description</label>
                            <input required type="text" id="note" class="form-control" name="note" />
                        </div>
                        <input type="hidden" name="type" value="transfer">
                        <input type="hidden" name="manager_id" value="{{ $user->id }}">

                        {{-- password field --}}
                        <div class="form-outline  mt-2">
                            <label class="form-label" for="password">Password</label>
                            <input required type="password" id="password" class="form-control" name="password" />
                        </div>
                        <button type="submit" onclick="validateForm()" class="btn btn-primary mt-3">Transfer</button>
                    </form>
                    @endif
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

    </div>
</div>
@php
$user_id=auth()->guard('staff')->user()->id;
@endphp



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
                otp: otp
            },
            success: function(response) {
                if (response.success) {
                    // console.log("the response is :", response);
                 
                     document.getElementById('transferForm').submit();
                } else {
                    $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
                }
            },
            error: function() {
                $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
            }
        });
    }

    transferForm.addEventListener('submit', function(event) {
          
            event.preventDefault();

          
        });



    function hideConfirmationModal() {
        $('#confirmationModal').modal('hide');
    }

</script>