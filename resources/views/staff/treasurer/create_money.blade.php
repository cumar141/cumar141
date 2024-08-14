<!-- resources/views/staff/create_money.blade.php -->

@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
.card {
    border-radius: 10px;
    transition: transform 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
}

.card:hover {
    transform: translateY(-3px);
}
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid mt-5">
            <div class="row justify-content-center">
                <!-- Form -->
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                        <h3 class="card-title mb-1">Deposit Money</h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Error message display -->
                            @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                            @endif
                            
                            <!-- Money creation form -->
                            <form id="moneyCreationForm" action="{{ route('staff.treasurer.create_money') }}" method="post">
                                @csrf
                                <div id="errorMessage"> </div>
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Currency</label>
                                    <select class="form-select" required name="currency" id="currency">
                                        <option value="">Select Currency Type</option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
            
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input required type="number" id="amount" class="form-control" name="amount" placeholder="Enter amount"/>
                                </div>
                                <div class="mb-3">
                                    <label for="note" class="form-label">Description</label>
                                    <input required type="text" id="note" class="form-control" name="note" placeholder="Enter information"/>
                                </div>
            
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input required type="text" id="password" class="form-control" name="password" placeholder="xxxxxxxxxxxx" />
                                </div>
            
                                <div class="text-center">
                                    <button type="button" onclick="validateForm()" id="submitBtn" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction Details -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                        <h3 class="card-title mb-1">Transaction Details</h3>
                        </div>
                        <div class="card-body">
                            
                            <div class="table-responsive">
                                @if (isset($transactions))
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Transaction Date</th>
                                            <th scope="col">Transaction ID</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Currency</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at }}</td>
                                            <td>{{ $transaction->uuid }}</td>
                                            <td>{{ $transaction->note }}</td>
                                            <td>{{ $transaction->currency->code }}</td>
                                            <td>{{ number_format($transaction->total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="confirmationModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Confirm</h5>
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
                        <button type="button" onclick="showModal()" id="confirmTransferBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="otp-container">
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Verify OTP</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="card-body">
                                <form id="otpForm">
                                    @csrf
                                    <div id="otpMessage"></div>
                                    <div class="d-flex justify-content-center">
                                        <input type="text" class="form-control otp-input" name="otpInput1" id="otpInput1"
                                            maxlength="1" required>
                                        <input type="text" class="form-control otp-input" name="otpInput2" id="otpInput2"
                                            maxlength="1" required>
                                        <input type="text" class="form-control otp-input" name="otpInput3" id="otpInput3"
                                            maxlength="1" required>
                                        <input type="text" class="form-control otp-input" name="otpInput4" id="otpInput4"
                                            maxlength="1" required>
                                        <input type="text" class="form-control otp-input" name="otpInput5" id="otpInput5"
                                            maxlength="1" required>
                                        <input type="text" class="form-control otp-input" name="otpInput6" id="otpInput6"
                                            maxlength="1" required>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" onclick="verify()"
                                                class="btn btn-primary btn-submit">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    

        @include('staff.layouts.footer')

@php
$user_id = auth()->guard('staff')->user()->id;
@endphp

<style>
    otp-container .card {
    width: 550px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    background-color: #ffffff;
}

.otp-container .modal-header {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.otp-container .modal-title {
    font-size: 24px;
}

.otp-container .modal-body {
    padding: 0;
}

.otp-container .otp-input {
    width: 40px;
    text-align: center;
    margin: 0 5px;
    border: 2px solid #ced4da;
    border-radius: 5px;
    font-size: 20px;
}

.otp-container .otp-input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.otp-container .btn-submit {
    font-size: 18px;
    width: 100%;
    margin-top: 20px;
}
       .card {
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .card-title {
        color: #333;
        font-size: 24px;
        font-weight: 600;
    }

    .form-label {
        font-weight: bold;
    }

    .btn-primary:hover {
        background-color: #4582a0;
        border-color: #45a0a0;
    }
    
    #errorMessage {
            color: red;
            font-weight: bold;
        }
</style>


<script>

function showModal() {
    $.ajax({
        type: 'GET',
        url: "{{ route('sendOtp') }}",
        data: {
            user_id: {{ $user_id }}
        },
        success: function(response) {
            // console.log(response);
            if (response.success === true) {
                hideConfirmationModal();
                clearOTPInputs();
                showOtpModal();
            } else {
                hideConfirmationModal();
                alert('Failed to generate OTP. Please try again.');
                clearOTPInputs();
            }
        },
        error: function() {
            clearOTPInputs();
            alert('Error occurred while generating OTP. Please try again.');
        }
    });
}

function validateForm() {
    var currency = $('#currency').val();
    var amount = $('#amount').val();
    var note = $('#note').val();
    var password = $('#password').val();

    if (!currency || !amount || !note || !password) {
        $('#errorMessage').text("Please fill in all fields.").show();
        return false;
    }

    showConfirmationModal();
    return false; 
}

function showConfirmationModal() {
    var currency = $('#currency option:selected').text();
    var amount = $('#amount').val();
    var description = $('#note').val();

    $('#modalCurrency').text(currency);
    $('#modalAmount').text(amount);
    $('#modalDescription').text(description);

    $('#confirmationModal').modal('show');
}

function verify() {
    var otp = getOTP();

    $.ajax({
        type: 'GET',
        url: "{{ route('verifyOtps') }}",
        data: {
            otp: otp,
            user_id: {{ $user_id }},
            otp: otp
        },
        success: function(response) {
            if (response.success) {
                clearOTPInputs();
                hideConfirmationModal();
                document.getElementById('moneyCreationForm').submit();
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

function showOtpModal() {
    var otpModal = document.getElementById('staticBackdrop');
    var modal = new bootstrap.Modal(otpModal);
    modal.show();
}

function clearOTPInputs() {
    $('.otp-input').val('');
}

function getOTP() {
    var otp = '';
    $('.otp-input').each(function() {
        otp += $(this).val();
    });
    return otp;
}

function hideConfirmationModal() {
    $('#confirmationModal').modal('hide');
}

function hideSpinner() { 
    $('#spinner').hide(); 
} 

$(document).ready(function() {
    $('.otp-input').on('input', function() {
        var index = $('.otp-input').index(this);
        if ($(this).val().length === 1 && index < $('.otp-input').length - 1) {
            $('.otp-input').eq(index + 1).focus();
        }
    });
});
</script>
