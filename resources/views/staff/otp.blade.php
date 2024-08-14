@include('staff.header')
@include('staff.sidebar')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter OTP</title>
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


  </style>
  
</head>
<body>
    @if(session('errorMessage'))
    <div class="alert alert-danger">
        {{ session('errorMessage') }}
    </div>
@endif

<div id="otpErrorMessage" class="error-message"></div>

    <div class="container ">
        <div class="card">
         <div class="card-header">Enter OTP</div>
      
            <div class="card-body">
                <form id="otpForm" action="{{ route('CreateWithdraw') }}" method="POST">
                    @csrf
                    <div id="otpMessage"></div>
                    <!-- Hidden fields to store data from the main form -->
                    <input type="hidden" id="userIDModal" name="userIDModal" value="{{$userId}}" />
                    <input type="hidden" id="userNameModal" name="userNameModal" value="{{$username}}"/>
                    <input type="hidden" id="currencyModal" name="currencyModal" value="{{$currency}}" />
                    <input type="hidden" id="withdrawalAmountModal" name="withdrawalAmountModal" value="{{$amount}}" />
                    <input type="hidden" id="noteModal" name="noteModal"  value="{{$note}}" />
                
                    <div class="row">
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput1" id="otpInput1" maxlength="1" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput2" id="otpInput2" maxlength="1" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput3" id="otpInput3" maxlength="1" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput4" id="otpInput4" maxlength="1" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput5" id="otpInput5" maxlength="1" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" class="form-control otp-input" name="otpInput6" id="otpInput6" maxlength="1" required>
                        </div>
                    </div>
                    <div class="row justify-content-center mt-4">
                        <div class="col-auto">
                            <button type="button" onclick="verify()" class="btn btn-primary btn-submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>



@include('staff.footer')


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>

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
function verify() {
    // Get OTP inputs
    var otpInputs = document.querySelectorAll('.otp-input');
    var otp = '';

    otpInputs.forEach(input => {
        otp += input.value;
    });

    // AJAX request
    $.ajax({
        type: 'GET',
        url: '{{ route('verifyOtp') }}',
        data: {
            otp: otp,
            user_id: '{{ $userId }}'
        },
        success: function(response) {
            if (response.success) {
                console.log("the response is :", response);
                document.getElementById('otpForm').submit();
            } else {
                // Display error message with animation
                $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
            }
        },
        error: function() {
            $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
        }
    });
}



</script>
