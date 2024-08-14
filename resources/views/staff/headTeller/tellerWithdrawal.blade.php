@include('staff.layouts.header')
@include('staff.layouts.sidebar')


<!-- Add modal dialog -->
<div class="modal fade" id="confirmationModal"  data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blend-darken">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Modal body to display the form data -->
                <p>Teller UUID: <span id="modalTellerUUID"></span></p>
                <p>User Name: <span id="modalUserName"></span></p>
                <p>Withdrawal Amount: <span id="modalWithdrawalAmount"></span></p>

            </div>
            <div class="modal-footer">
                <!-- Close modal button -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- Submit form button -->
                <button type="button" class="btn btn-primary" id="submitFormBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- Page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Close Teller Account</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">manager</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @include('staff.layouts.miniNav')
            <br>
            <div class="text-center">
                <span class="text-danger" id="error"></span>
            </div>
            <br>

            <!-- Flash messages -->
            <div class="flash-container">
                @if(isset($errorMessage) && !session('hideErrorMessage'))
                <div id="errorMessageContainer" class="alert mt-20 f-14 text-danger text-center mb-0" role="alert">
                    {{ $errorMessage }}
                    <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
                </div>
                <script>
                    setTimeout(function() {
                            document.getElementById('errorMessageContainer').style.display = 'none';
                            sessionStorage.setItem('hideErrorMessage', true);
                            {{ session()->forget('errorMessage') }}
                        }, 5000);
                </script>
                @endif

                <div class="alert alert-success f-14 text-center mb-0 d-none" id="success_message_div" role="alert">
                    <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
                    <p id="success_message"></p>
                </div>

                <div class="alert alert-danger f-14 text-center mb-0 d-none" id="error_message_div" role="alert">
                    <p><a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a></p>
                    <p id="error_message"></p>
                </div>
            </div>

            <!-- Close Teller Account Form -->
            <div class="card mt-4" id="cardFoam">
                <div class="card-body">
                    <form id="withdrawForm" method="POST" action="{{ route('withdrawFromTeller') }}">
                        @csrf
                        <input type="hidden" id="userID" name="userID" value="{{$user_id}}" class="form-control" />

                        <div class="mb-3">
                            <label for="tellerUUID" class="form-label">Teller UUID</label>
                            <input required type="text" id="tellerUUID" name="tellerUUID" value="{{ $tellerUuid }}"
                                class="form-control" disabled="true" readonly/>
                            <span class="error-message text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="userName" class="form-label">User Name</label>
                            <input required type="text" id="userName" value="{{$username}}" name="userName"
                                class="form-control" placeholder="User Name" disabled="true" readonly/>
                            <span class="error-message text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Wallets</label>
                            <select required id="currency" name="currency" class="form-control">
                                <option value="">Select Wallet</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                @endforeach
                            </select>
                            <span class="error-message text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="withdrawalAmount" class="form-label">Withdrawal Amount</label>
                            <input required type="number" id="withdrawalAmount" name="withdrawalAmount"
                                class="form-control" placeholder="Withdrawal Amount" />
                            <span class="error-message text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <input required type="text" id="note" name="note" class="form-control"
                                placeholder="Note.." />
                            <span class="error-message text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Password</label>
                            <input required type="password" id="password" name="password" class="form-control password"
                                placeholder="******" />
                            <span class="error-message text-danger"></span>
                        </div>

                        <button type="button" class="btn btn-primary"
                            onclick="showConfirmationModal()">Withdraw</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('staff.layouts.footer')

<script>
    function showConfirmationModal() {
    if (validateForm() && parseFloat($('#withdrawalAmount').val()) > 0) {
        var tellerUUID = $('#tellerUUID').val();
        var userName = $('#userName').val();
        var withdrawalAmount = $('#withdrawalAmount').val();

        $('#modalTellerUUID').text(tellerUUID);
        $('#modalUserName').text(userName);
        $('#modalWithdrawalAmount').text(withdrawalAmount);

        $('#confirmationModal').modal('show');
    } else {
        $('#error').text('Withdrawal amount must be greater than zero.');
    }
}

    // Function to submit the form
    function submitForm() {
        $('#withdrawForm').submit();
    }

    // Bind click event to submit button
    $('#submitFormBtn').click(function() {
        submitForm();
    });

    function validateForm() {
        var isValid = true;

        $('#error').empty(); // Clear previous errors

        var withdrawalAmount = $('#withdrawalAmount').val();
        var note = $('#note').val();
        var password = $('#password').val();

        if (!withdrawalAmount) {
            $('#withdrawalAmount').next('.error-message').text('Withdrawal amount is required');
            isValid = false;
        } else {
            $('#withdrawalAmount').next('.error-message').text('');
        }

        if (!note) {
            $('#note').next('.error-message').text('Note is required');
            isValid = false;
        } else {
            $('#note').next('.error-message').text('');
        }

        if (!password) {
            $('#password').next('.error-message').text('Password is required');
            isValid = false;
        } else {
            $('#password').next('.error-message').text('');
        }

        return isValid;
    }

        function displayErrors(errors) {
            $('#error').empty();
            $.each(errors, function(field, message) {
                $('#error').append('<p>' + message + '</p>');
            });
        }

        

        // Populate currency dropdown
        $(document).ready(function() {
            // hide the card form

           

            $('#currency').change(function() {
                var userId = $('#userID').val(); 
                var currencyId = $(this).val(); 

                // Make an AJAX request to get the wallet balance
                $.ajax({
                    type: 'GET',
                    url: "{{ route('getWalletBalance') }}",
                    data: { user_id: userId, currency_id: currencyId },
                    success: function(response) {
                    
                        if (response.hasOwnProperty('balance')) {
                            // Update the withdrawal amount input field with the wallet balance
                            $('#withdrawalAmount').val(response.balance);
                            $('#error').text(''); 
                        } else if (response.hasOwnProperty('error')) {
                            $('#error').text(response.error);
                            $('#withdrawalAmount').val(00);
                        } 
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText);
                        var errorMessage = "An error occurred while retrieving record";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        $('#error').text(errorMessage);
                    }
                });
            });
        });
</script>