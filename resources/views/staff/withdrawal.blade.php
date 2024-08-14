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


    .otp-input {
        width: 40px;
        /* Adjust the width of the OTP input fields */
        text-align: center;
        margin: 0 5px;
        border: 2px solid #ced4da;
        border-radius: 5px;
        font-size: 20px;
    }

    .otp-input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .btn-submit {
        font-size: 18px;
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


</style>
<!-- Include CSRF Token Meta Tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <div class="page-content">
        <div class="container">
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">verify OTP</h1>
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

            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Withdrawal Form </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                                <li class="breadcrumb-item active">user</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <div class="d-flex justify-content-center">
                <div class="form-outline mt-5">
                    <input type="search" id="search" name="search_query" class="form-control"
                        placeholder="Type phone name" aria-label="Search" style="width: 500px;" />

                    <button type="button" class="btn  btn-primary" onclick="searchUser()">
                        <i class="fas fa-search"></i> <!-- Assuming you have Font Awesome loaded -->
                    </button>
                </div>

            </div>
            <br>
            <div class="text-center">
                <span class="text-danger" id="error"></span>
            </div>

            <br>
            <!-- Flash Message  -->
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


            <!-- /.Flash Message  -->


            <div class="card mt-4" id="cardFoam">
                <div class="card-header">
                    Withdrawal Form
                </div>
                <div class="card-body">
                    <form id="withdrawForm" action="{{ route('CreateWithdraw') }}" method="POST">
                        
                        <div id="errorMessage" style="display: none; color: red;"></div>
                        @csrf
                        <input type="hidden" id="userID" name="userID" class="form-control" />

                        <div class="mb-3">
                            <label for="userName" class="form-label">User Name</label>
                            <input required type="text" id="userName" name="userName" class="form-control"
                                placeholder="User Name" />
                        </div>
                        <div class="mb-3">
                            <label for="userType" class="form-label">Wallets</label>
                            <select required id="currency" name="currency" class="form-control">
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="withdrawalAmount" class="form-label">Withdrawal Amount</label>
                            <input required type="decimal" id="withdrawalAmount" name="withdrawalAmount"
                                class="form-control" placeholder="Withdrawal Amount" />
                        </div>

                        <div class="mb-3">
                            <label for="userName" class="form-label">Note</label>
                            <input required type="text" id="note" name="note" class="form-control"
                                placeholder="Note.." />
                        </div>

                        <button type="button" onclick="validateForm()" class="btn btn-primary">Withdraw</button>
                    </form>
                </div>
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
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    // Define user_id variable


    function searchUser() {
        var searchQuery = $('#search').val();

        if (!searchQuery) {
            $('#error').text('This field is required');
            return;
        }

        $.ajax({
            type: 'GET',
            url: "{{ route('search-user') }}",
            data: { searchQuery: searchQuery },
            success: function(response) {
                console.log(response);

                if (response && response.user && response.user.username) {
                    $('#userName').val(response.user.username);
                    $('#userID').val(response.user.id);
                    $('#cardFoam').show();
                    $('#error').text('');
                    var user_id=$('#userID').val(response.user.id);
            
                } else {
                    $('#error').text('User not found or missing username');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                if (xhr.status == 404) {
                    $('#error').text('User not found');
                } else {
                    $('#error').text('An error occurred while processing the request');
                }
            }
        });
    }

    function validateForm() {
        var currency = $('#currency').val();
        var amount = $('#withdrawalAmount').val();
        var note = $('#note').val();
        var userName = $('#userName').val();
      
        if (!currency || !amount || !note || !userName) { 
            var errorMessageDiv = $('#errorMessage');
            errorMessageDiv.text("Please fill in all fields.");
            errorMessageDiv.css('display', 'block');
            return false;
        }

        showConfirmationModal();
        return false;
    }

    function showConfirmationModal() {
        var currency = $('#currency option:selected').text();
        var amount = $('#withdrawalAmount').val();
        var description = $('#note').val();
       
        $('#modalCurrency').text(currency);
        $('#modalAmount').text(amount);
        $('#modalDescription').text(description);

        $('#confirmationModal').modal('show'); // Ensure Bootstrap modal is initialized
    }

    function verify() {
        var otp = getOTP();
        var user_id = $('#userID').val();
        $.ajax({
            type: 'GET',
            url: '{{ route("verifyOtps") }}',
            data: {
                otp: otp,
                user_id: user_id
            },
            success: function(response) {
                if (response.success) {
                    console.log("the response is :", response);
                    document.getElementById('withdrawForm').submit();
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

    function showModal() {
        var user_id = $('#userID').val();


        $('#spinner').show();
        $.ajax({
            type: 'get',
            url: "{{ route('sendOtp') }}",
            data: {
                user_id: user_id
            },
            
            success: function(response) {
                console.log(response);
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

    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            })
        });

        // Initialize Bootstrap modal
        var otpModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    });

    function getOTP() {
        var otpInput1 = $('#otpInput1').val();
        var otpInput2 = $('#otpInput2').val();
        var otpInput3 = $('#otpInput3').val();
        var otpInput4 = $('#otpInput4').val();
        var otpInput5 = $('#otpInput5').val();
        var otpInput6 = $('#otpInput6').val();

        var otp = otpInput1 + otpInput2 + otpInput3 + otpInput4 + otpInput5 + otpInput6;

        return otp;
    }

    function showOtpModal() {
        var modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        modal.show();
    }

    function clearOTPInputs() {
        $('.otp-input').val('');
    }

    function hideConfirmationModal() {
        $('#confirmationModal').modal('hide');
    }

    $(document).ready(function() {
        $('#cardFoam').hide();
     
    });
</script>
