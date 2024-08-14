<!-- resources/views/users/index.blade.php -->
@include('../staff.layouts.header')
@include('../staff.layouts.sidebar')

<style>
    .otp-input {
        width: 30;
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
<div class="main-content">
    <div class="page-content">
        <div class="container">
            {{-- @include('staff.miniNav') --}}
            
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

            <div class="container">
                <div class="row">
                    <div class="col-12">
                        @if(isset($user))
                        <form id="RequestForm" action="{{ route('staff.treasurer.request_money') }}" method="post">
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
                                <input required type="number" id="amount" required class="form-control" name="amount" />
                            </div>
                            <div class="form-outline">
                                <label class="form-label" for="note">Description</label>
                                <input required type="text" id="note" required class="form-control" name="note" />
                            </div>
                            <input type="hidden" name="type" value="transfer">
                            <input type="hidden" name="manager_id" required value="{{ $user->id }}">

                            {{-- password field --}}
                            <div class="form-outline  mt-2">
                                <label class="form-label" for="password">Password</label>
                                <input required type="password" id="password" required class="form-control"
                                    name="password" />
                            </div>
                            <button type="button" onclick="validateForm()" class="btn btn-primary mt-3">Request</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@php

$user_id = auth()->guard('staff')->user()->id;
@endphp


@include('staff.treasurer.otp')
@include('staff.layouts.footer')
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

   
        showModal();
        return false; 
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
                    // console.log("the response is :", response);
                   
                     document.getElementById('RequestForm').submit();
                } else {
                    $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
                }
            },
            error: function() {
                $('#otpMessage').html('<div class="alert alert-danger" role="alert">Failed to verify OTP. Please try again.</div>');
            }
        });
    }

  
</script>