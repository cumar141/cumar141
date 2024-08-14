<!-- OTP Modal -->
<div class="modal fade" id="otpModal"  data-bs-backdrop="static" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otpModalLabel">OTP Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please enter the OTP sent to your mobile number.</p>
                <!-- OTP Input Fields -->
                <div id="otpMessage"></div>
                <div class="mb-3 d-flex justify-content-between">
                    <input type="text" class="form-control otp-input m-1" name="otpInput1" id="otpInput1" maxlength="1"
                        required>
                    <input type="text" class="form-control otp-input m-1" name="otpInput2" id="otpInput2" maxlength="1"
                        required>
                    <input type="text" class="form-control otp-input m-1" name="otpInput3" id="otpInput3" maxlength="1"
                        required>
                    <input type="text" class="form-control otp-input m-1" name="otpInput4" id="otpInput4" maxlength="1"
                        required>
                    <input type="text" class="form-control otp-input m-1" name="otpInput5" id="otpInput5" maxlength="1"
                        required>
                    <input type="text" class="form-control otp-input m-1" name="otpInput6" id="otpInput6" maxlength="1"
                        required>
                    <!-- Add more input fields if needed -->
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyOTPBtn">Verify OTP</button>
            </div>
        </div>
    </div>
</div>

<!-- Add custom CSS -->
<style>
    .otp-input {
        width: 20%;
        /* Adjust the width as needed */
        text-align: center;
        margin: 0;
        outline: none;
        /* Remove default outline */
    }

    .otp-input:focus {
        outline: 2px solid blue;
        /* Add custom outline when focused */
    }
</style>

<script>
    $(document).ready(function () {
        $('.otp-input').keyup(function () {
            if (this.value.length == this.maxLength) {
                $(this).next('.otp-input').focus();
            }
        });

        $('.otp-input').keydown(function (e) {
            if (e.keyCode == 8 && this.value.length == 0) {
                $(this).prev('.otp-input').focus();
            }
        });
    });

    $("#verifyOTPBtn").click(function () {
        var otp = '';
        $('.otp-input').each(function () {
            otp += $(this).val();
        });
        // Send the OTP for verification
        console.log(otp);
       
    // AJAX request
    $("#spinner").show();
    $.ajax({
        type: 'GET',
        url: '{{ route('verifyOtps') }}',
        data: {
            otp: otp,
            user_id: {{ auth()->guard('staff')->user()->id }}
        },
        success: function(response) {
            if (response.success) {
                $("#spinner").hide();
                console.log("the response is :", response);
                $('#actionForm').submit();
                // clsoe modal
                $('#otpModal').modal('hide');
            } else {
                // Display error message with animation
                $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
                // hide spinner
                $("#spinner").hide();
                // close modal
                
            }
        },
        error: function() {
            $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
        }
    });
    });
</script>