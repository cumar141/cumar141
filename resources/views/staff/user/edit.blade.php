@include('../staff.layouts.header')
@include('../staff.layouts.sidebar')
@section('title', __('Edit User'))


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<link rel="stylesheet" href={{ asset('public/admin/customs/css/phone.css') }}>

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

    .intl-tel-input {
        width: 100%;
    }

    .iti {
        width: 100%;
    }

    #phone:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Update </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active"> User</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
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
            {{-- this page is for creating new users --}}

            @include('staff.user.tab')
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <h3 class="f-24 mb-0">{{ getColumnValue($users) }}</h3>
                    <p
                        class="badge bg-{{ $users->status == 'Active' ? 'success' : ($users->status == 'Inactive' ? 'danger' : 'warning') }} mb-0 ms-1">
                        {{ $users->status == 'Active' ? __('Active') : ($users->status == 'Inactive' ? __('Inactive') :
                        __('Suspended')) }}</p>
                </div>
            </div>

            <br>

            <div class="col-md-3"></div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">New User</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('staff.user.update') }}" class="form-horizontal" id="user_form"
                        method="POST">
                        <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
                        <input type="hidden" value="{{ $users->id }}" name="id" id="id" />
                        <input type="hidden" value="{{ $users->defaultCountry }}" name="user_defaultCountry"
                            id="user_defaultCountry" />
                        <input type="hidden" value="{{ $users->carrierCode }}" name="user_carrierCode"
                            id="user_carrierCode" />
                        <input type="hidden" value="{{ $users->formattedPhone }}" name="formattedPhone"
                            id="formattedPhone">

                        <!-- FirstName -->
                        <div class="mb-3">
                            <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                            <input class="form-control font-size-14"
                                placeholder="{{ __('Enter :x', ['x' => __('first name')]) }}" name="first_name"
                                type="text" id="first_name" value="{{ $users->first_name }}" required
                                data-value-missing="{{ __('This field is required.') }}" maxlength="30"
                                data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('First name'), 'y' => __('30')]) }}">
                            @if($errors->has('first_name'))
                            <span class="error">{{ $errors->first('first_name') }}</span>
                            @endif
                        </div>

                        <!-- LastName -->
                        <div class="mb-3">
                            <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                            <input class="form-control font-size-14"
                                placeholder="{{ __('Enter :x', ['x' => __('last name')]) }}" name="last_name"
                                type="text" id="last_name" value="{{ $users->last_name }}" required
                                data-value-missing="{{ __('This field is required.') }}" maxlength="30"
                                data-max-length="{{ __(':x length should be maximum :y character.', ['x' => __('Last name'), 'y' => __('30')]) }}">
                            @if($errors->has('last_name'))
                            <span class="error">{{ $errors->first('last_name') }}</span>
                            @endif
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="tel" class="form-control font-size-14" id="phone" name="phone"
                                value="{{ $users->phone }}">
                            <span id="duplicate-phone-error" class="text-danger"></span>
                            <span id="tel-error"></span>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label require">{{ __('Email') }}</label>
                            <input class="form-control font-size-14" value="{{ $users->email }}"
                                placeholder="{{ __('Enter a valid :x.', ['x' => __('email')] )}}" name="email"
                                type="email" id="email" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')"
                                data-type-mismatch="{{ __('Enter a valid :x.', [ 'x' => strtolower(__('email'))]) }}">
                            @if($errors->has('email'))
                            <span class="error">{{ $errors->first('email') }}</span>
                            @endif
                            <span id="email_error" class="text-danger"></span>
                            <span id="email_ok" class="text-success"></span>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label for="role" class="form-label require">{{ __('Group') }}</label>
                            <select class="select2 font-size-14" name="role" id="role" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                @foreach ($roles as $role)
                                <option value='{{ $role->id }}' {{ $role->id == $users->role_id ? 'selected' : "" }}> {{
                                    $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Branch -->
                        <div class="mb-3">
                            <label for="branch_id" class="form-label require">{{ __('Branch') }}</label>
                            <select class="select2 font-size-14" name="branch_id" id="branch_id" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                @foreach ($branch as $role)
                                <option value='{{ $role->id }}' {{ $role->id == $users->branch_id ? 'selected' : "" }}>
                                    {{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label require">{{ __('Password') }}</label>
                            <input class="form-control font-size-14" placeholder="{{ __('Enter new Password') }}"
                                name="password" type="password" id="password" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" minlength="4"
                                data-min-length="{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '4']) }}">
                            @if($errors->has('password'))
                            <span class="error">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label require">{{ __('Confirm Password')
                                }}</label>
                            <input class="form-control font-size-14" placeholder="{{ __('Confirm password') }}"
                                name="password_confirmation" type="password" id="password_confirmation" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" minlength="4"
                                data-min-length="{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '4']) }}">
                            @if($errors->has('password_confirmation'))
                            <span class="error">{{ $errors->first('password_confirmation') }}</span>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label require">{{ __('Status') }}</label>
                            <select class="select2 font-size-14" name="status" id="status" required
                                oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                <option value='Active' {{ $users->status == 'Active' ? 'selected' : '' }}>{{
                                    __('Active') }}</option>
                                <option value='Inactive' {{ $users->status == 'Inactive' ? 'selected' : '' }}>{{
                                    __('Inactive') }}</option>
                                <option value='Suspended' {{ $users->status == 'Suspended' ? 'selected' : '' }}>{{
                                    __('Suspended') }}</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 offset-md-3">
                                <a class="btn btn-danger font-size-14 me-1" href="{{ route('staff.user.index') }}"
                                    id="users_cancel">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary font-size-14" id="users_create"><i
                                        class="fa fa-spinner fa-spin d-none"></i> <span id="users_create_text">{{
                                        __('Update') }}</span></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@include('../staff.layouts.footer')


{{-- <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript">
</script> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<script src="{{ asset('public/dist/js/isValidPhoneNumber.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    'use strict';
var countryShortCode = '{{ getDefaultCountry() }}';
var userNameError = '{{ __("Please enter only alphabet and spaces") }}';
var userNameLengthError = '{{ __("Name length can not be more than 30 characters") }}';
var passwordMatchErrorText = '{{ __("Please enter same value as the password field.") }}';
var creatingText = '{{ __("Updating...") }}';
var utilsScriptLoadingPath = '{{ asset("public/dist/plugins/intl-tel-input-17.0.19/js/utils.min.js") }}';
var validPhoneNumberErrorText = '{{ __("Please enter a valid international phone number.") }}';
</script>


{{-- select 2 --}}
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // select 2 width 100%
        $('.select2-container').css('width', '100%');
        // select 2 height 100%
        $('.select2-container').css('height', '100%');
        // $("#phone").css('height', '100%');
    });
</script>
<script>
    const phoneInputField = document.querySelector("#phone");
    const phoneInput = window.intlTelInput(phoneInputField, {
      utilsScript:
        "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });

    // Listen for the input event on the phone input field
    phoneInputField.addEventListener("input", function() {
        // Get the selected country data
        const countryData = phoneInput.getSelectedCountryData();
        // Get the full international phone number
        const fullPhoneNumber = phoneInput.getNumber();
        
        // Parse the full phone number to extract the carrier code
        const phoneNumber = phoneInputField.value;


        // Set the values of the hidden fields
        document.getElementById('defaultCountry').value = countryData.iso2;
        document.getElementById('carrierCode').value =countryData.dialCode;
        document.getElementById('formattedPhone').value = fullPhoneNumber;

        console.log('defaultCountry: ', document.getElementById('defaultCountry').value);
        console.log('carrierCode: ', document.getElementById('carrierCode').value);
        console.log('formattedPhone: ', document.getElementById('formattedPhone').value);

        console.log('carrierCode: ', countryData.dialCode);
    });

    // Set default country (e.g., Somalia)
    phoneInput.setCountry("so");
</script>


<script>
    // document ready function
    $(document).ready(function() {
        // form validation
        $('#user_form').validate({
            rules: {
                first_name: {
                    required: true,
                    maxlength: 30,
                    lettersonly: true
                },
                last_name: {
                    required: true,
                    maxlength: 30,
                    lettersonly: true
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 4
                },
                password_confirmation: {
                    required: true,
                    minlength: 4,
                    equalTo: "#password"
                },
                phone: {
                    required: true,
                    isValidPhoneNumber: true
                }
            },
            messages: {
                first_name: {
                    required: "{{ __('This field is required.') }}",
                    maxlength: "{{ __(':x length should be maximum :y characters.', ['x' => __('First name'), 'y' => '30']) }}",
                    lettersonly: "{{ __('Please enter only alphabet and spaces') }}"
                },
                last_name: {
                    required: "{{ __('This field is required.') }}",
                    maxlength: "{{ __(':x length should be maximum :y characters.', ['x' => __('Last name'), 'y' => '30']) }}",
                    lettersonly: "{{ __('Please enter only alphabet and spaces') }}"
                },
                email: {
                    required: "{{ __('This field is required.') }}",
                    email: "{{ __('Please enter a valid email address.') }}"
                },
                password: {
                    required: "{{ __('This field is required.') }}",
                    minlength: "{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '4']) }}"
                },
                password_confirmation: {
                    required: "{{ __('This field is required.') }}",
                    minlength: "{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '4']) }}",
                    equalTo: "{{ __('Please enter same value as the password field.') }}"
                },
                phone: {
                    required: "{{ __('This field is required.') }}",
                    isValidPhoneNumber: "{{ __('Please enter a valid international phone number.') }}"
                }
            }
        });


        function checkIfPhoneAlreadyExists(phone) {
            $.ajax({
                url: "{{ route('staff.user.checkPhone') }}",
                type: "POST",
                data: {
                    _token: $('#token').val(),
                    phone: phone,
                    user_id: $('#id').val()
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == false) {
                        $('#duplicate-phone-error').html('');
                    } else if (response.status == true){
                        // alert('response.message: ' + response.message)
                        console.log('response.message: ', response.message);
                        $('#duplicate-phone-error').html(response.message);
                    }
                    else {
                        $('#duplicate-phone-error').html('');
                    }
                }
            });
        }


        function checkIfEmailAlreadyExists(email) {
            $.ajax({
                url: "{{ route('staff.user.checkEmail') }}",
                type: "POST",
                data: {
                    _token: $('#token').val(),
                    email: email,
                    user_id: $('#id').val()
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == false) {
                        $('#email_error').html('');
                        // $('#email_ok').html(response.message);
                    } else if (response.status == true){
                        // alert('response.message: ' + response.message)
                        console.log('response.message: ', response.message);
                        $('#email_error').html(response.message);
                        $('#email_ok').html('');
                    }
                    else {
                        $('#email_error').html('');
                        $('#email_ok').html('');
                    }
                }
            });
        }

        //on chanage of phone input field check if phone already exists
        $('#phone').on('change', function() {
            var phone = $('#phone').val();
            checkIfPhoneAlreadyExists(phone);
        });

        //on chanage of email input field check if email already exists
        $('#email').on('change', function() {
            var email = $('#email').val();
            checkIfEmailAlreadyExists(email);
        });
    });
</script>