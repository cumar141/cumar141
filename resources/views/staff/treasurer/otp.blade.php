<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification Modal</title>
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            overflow: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            /* margin-top: -40%;  */
        }

        .card {
            width: 550px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .card-header {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .otp-input {
            width: 40px;
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
            width: 100%;
            margin-top: 20px;
        }
        #spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }
    </style>
</head>

<body>
    
    {{-- <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
        <span class="visually-hidden">Loading...</span>
    </div>
     --}}
    <!--OTP MODAL-->
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

</body>

</html>
@php
$user_id=auth()->guard('staff')->user()->id;
@endphp

<script>
    function showModal() {

        $('#spinner').show();
        $.ajax({
            type: 'get',
            url: "{{ route('sendOtp') }}",
            data: {
                user_id: {{ $user_id }}
            },
            success: function(response) {
                // console.log(response);
                if (response.success === true) {
                    hideConfirmationModal()
                    clearOTPInputs()
                 showOtpModal();
                } else {
                    hideConfirmationModal()
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

    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
    
        // Add event listener to each input field
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus(); // Move focus to the next input field
                }
            })
        });
    });
   
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
    
        function showOtpModal(){
          
            var otpModal = document.getElementById('staticBackdrop');
                        var confirmationModal = document.getElementById('otpModal');
                        var modal = new bootstrap.Modal(otpModal);
                      
                        modal.show();
        }
    
    
        function clearOTPInputs() {
        document.getElementById('otpInput1').value = '';
        document.getElementById('otpInput2').value = '';
        document.getElementById('otpInput3').value = '';
        document.getElementById('otpInput4').value = '';
        document.getElementById('otpInput5').value = '';
        document.getElementById('otpInput6').value = '';
    }
    
    function hideConfirmationModal() {
        $('#confirmationModal').modal('hide');
    }

    // Function to hide the Spinner 
function hideSpinner() { 
    document.getElementById('spinner') 
            .style.display = 'none'; 
} 
  
</script>