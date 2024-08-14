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

    
</style>

{{-- @include('staff.spinner') --}}
<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Bulk Deposit </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Tellers</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @include('staff.layouts.miniNav')
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
            @include('staff.otp2')
            <!-- HTML -->

            <!-- Add a modal dialog -->
            <div class="modal fade" id="confirmDepositModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="confirmDepositModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDepositModalLabel">Confirm Deposit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="depositInfo"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmDepositBtn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Bulk Deposit</h4>
                            {{-- number of tellers --}}
                            <h5>Number of Users: {{count($users)}}</h5>
                        </div>
                        <div class="card-body">
                            <form id="actionForm" action="{{ route('bulk.deposit.submit') }}" method="POST">
                                <div class="text-danger error-message"></div>
                                @csrf
                                @foreach($currencies as $currency)
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input currency-checkbox" id="{{ $currency->code }}" name="currencies[]" value="{{ $currency->id }}"
                                        @if($currency->code === 'USD') checked @endif>
                                    <label class="form-check-label" for="{{ $currency->code }}">{{ $currency->name }}
                                        ({{ $currency->code }})</label>
                                    <div class="mt-2 deposit-input" @if($currency->code !== 'USD') style="display: none;" @endif>
                                        <label for="{{ $currency->code }}_amount">Amount for {{ $currency->code }}:</label>
                                        <input type="number" class="form-control" id="{{ $currency->code }}_amount" name="amounts[{{ $currency->id }}]" step="0.01" min="0" @if($currency->code === 'USD') required @endif>
                                         
                                    </div>
                                </div>
                                @endforeach
                                {{-- note --}}
                                <div class="mb-3">
                                    <label for="note" class="form-label">Note</label>
                                    <textarea class="form-control" required id="note" name="note" rows="3"></textarea>
                                  
                                </div>
                                {{-- password --}}
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" required class="form-control" id="password" name="password"required>
                                  
                                </div>
                                <button type="button" id="submitForm" onclick="verifyInput()"
                                    class="btn btn-primary">Submit Bulk Deposits</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('staff.layouts.footer')


<script>
    $(document).ready(function() {
        // hide spinner
        // $('#spinner').hide();
        // Find the modal and confirm button elements
        var confirmDepositModal = $('#confirmDepositModal');
        var confirmDepositBtn = $('#confirmDepositBtn');
        
        // Add event listener to checkboxes to toggle deposit input
        $('.currency-checkbox').change(function() {
            var depositInput = $(this).parent().find('.deposit-input');
            if ($(this).prop('checked')) {
                depositInput.show();
                // Add required attribute to the input field when the checkbox is checked
                depositInput.find('input').prop('required', true); 
            } else {
                depositInput.hide();
                // Remove required attribute from the input field when the checkbox is unchecked
                depositInput.find('input').removeAttr('required');
                // remove input value when unchecked
                depositInput.find('input').val('');
            }
        });

        // confirm bulk deposit submission before submitting form show modal with information about the deposit
        // Add event listener to the submit button
       
        confirmDepositBtn.click(function() {
                // hide the modal and show spinner untill opt modal is shown
                confirmDepositModal.modal('hide');
                // show spinner
                // $('#spinner').show();
                

                // send otp
                sendOtp();


            });
    });
    


    function sendOtp() {

        // show spinner
        $('#spinner').show();
        $.ajax({
            type: 'get',
            url: "{{ route('sendOtp') }}",
            data: {
                user_id: {{ auth()->guard('staff')->user()->id }}
            },
            success: function(response) {
                console.log(response);
                if (response.success === true) {
                    // hide spinner
                    $('#spinner').hide();
                    $("#confirmDepositModal").modal('hide');
                    clearOTPInputs()
                    // showOtpModal();
                    $("#otpModal").modal('show');
                    
                } else {
                    $("#confirmDepositModal").modal('hide');
                    alert('Failed to generate OTP. Please try again.');
                    clearOTPInputs()
                }
            },
            error: function() {
                
                //  hideSpinner();
                clearOTPInputs()
                alert('Error occurred while generating OTP. Please try again.');
            }
        });
    }

    function clearOTPInputs() {
        $('.otp-input').val('');
    }

        function verifyInput() 
    {
        var confirmDepositModal = $('#confirmDepositModal');
        var confirmDepositBtn = $('#confirmDepositBtn');

        // Prevent form submission
        event.preventDefault();

        var selectedCurrencies = $('.currency-checkbox:checked');
        var amounts = $('.deposit-input input');
        var note = $('#note').val();
        var password = $('#password').val();
        var isValid = true;
        var error = '';

        // Check if at least one currency is selected
        if (selectedCurrencies.length === 0) {
            $('.currency-checkbox').each(function() {
                if (!$(this).prop('checked')) {
                   error = 'All fields are required';
                    isValid = false;
                } else {
                    $('.error-message').text('');
                }
            });
        }

        // Check if amounts are entered for all selected currencies
        var invalidAmount = false;
        amounts.each(function() {
            var currencyId = $(this).attr('id').split('_')[0]; // Extract currency id from input id
            var currencyCheckbox = $('input[value="' + currencyId + '"]');
            if (currencyCheckbox.prop('checked') && currencyCheckbox.length > 0) {
                if ($(this).prop('required') && $(this).val() === '') {
                    // $('.error-message').text('Please enter a valid amount');
                    error += 'Please enter a valid amount';
                    isValid = false;
                    invalidAmount = true;
                } else {
                    $('.error-message').text('');
                }
            }
        });

        // Check if note and password are entered
        if (note === '') {
            error = error + '\n Please enter a valid note';
            isValid = false;
        } else {
            $('.error-message').text('');
        }

        if (password === '') {
            error += 'Please enter a valid password';
            isValid = false;
        } else {
            $('.error-message').text('');
        }

        // If the form is valid, proceed with verification
        if (isValid) {
            // Populate modal with deposit information
            var depositInfo = '<strong>Note:</strong> ' + note + '<br><br>';
            depositInfo += '<strong>Selected Currencies:</strong><br>';
            selectedCurrencies.each(function() {
                var currencyId = $(this).val();
                var currencyName = $(this).next().text();
                var currencyCode = $(this).attr('id');
                var amount = $('input[name="amounts[' + currencyId + ']"]').val();
                depositInfo += '<strong>' + currencyName + ' (' + currencyCode + '):</strong> $' + amount + '<br>';
            });

            // Populate modal with deposit information
            $('#depositInfo').html(depositInfo);

            // Show the modal
            confirmDepositModal.modal('show');
        }
        else {
            $('.error-message').text(error);
        }
    }


   
</script>