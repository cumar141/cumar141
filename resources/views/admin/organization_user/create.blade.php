
@extends('admin.layouts.master')

@section('title', __('Add Organization User'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')

    <div class="box box-info" id="user-create">
        <div class="box-header with-border">
            <h3 class="box-title">{{ __('Add Organization User') }}</h3>
        </div>

       {{-- handle error --}}
        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        {{-- a search form  --}}

        <form action="{{ url(config('adminPrefix').'/organization/user/store') }}" class="form-horizontal" id="user_form" method="POST">
            <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">

            <div class="box-body">
                {{-- hidden  id input --}}
                <input type="hidden" class="form-control " name="organization_id" id="organization_id" value="{{ $organizations->id }}">

                <!-- FirstName -->
                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="name">{{ __('Org. Name') }}</label>
                    <div class="col-sm-6">
                        <input class="form-control f-14" disabled placeholder="{{ __('Enter :x', ['x' => __('name')]) }}" name="name" type="text" id="name" value="{{ $organizations->name }}" required data-value-missing="{{ __('This field is required.') }}" maxlength="30" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('name'), 'y' => __('30')]) }}">
                        @if($errors->has('name'))
                            <span class="error">
                                {{ $errors->first('name') }}
                            </span>
                        @endif
                    </div> 
                </div>

                
                <!-- Email -->
                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="email">{{ __('Email') }}</label>
                    <div class="col-sm-6">
                        <input class="form-control f-14" placeholder="{{ __('Enter a valid :x.', ['x' => __('email')] )}}" name="email" type="email" id="email" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" data-type-mismatch="{{ __('Enter a valid :x.', [ 'x' => strtolower(__('email'))]) }}">
                        @if($errors->has('email'))
                            <span class="error">{{ $errors->first('email') }}</span>
                        @endif
                        <span id="email_error"></span>
                        <span id="email_ok" class="text-success"></span>
                    </div>
                </div>


                <!-- Phone -->
                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="username">{{ __('Username') }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control f-14" placeholder="Enter Username" id="username" name="username">
                        <span id="duplicate-phone-error"></span>
                        <span id="tel-error"></span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="password">{{ __('Password') }}</label>
                    <div class="col-sm-6">
                        <input class="form-control f-14" placeholder="{{ __('Enter new Password') }}" name="password" type="password" id="password" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" minlength="6" data-min-length="{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '6']) }}">
                        @if($errors->has('password'))
                            <span class="error">
                                {{ $errors->first('password') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="password_confirmation">{{ __('Confirm Password') }}</label>
                    <div class="col-sm-6">
                        <input class="form-control f-14" placeholder="{{ __('Confirm password') }}" name="password_confirmation" type="password" id="password_confirmation" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" minlength="6" data-min-length="{{ __(':x should contain at least :y characters.', ['x' => __('Password'), 'y' => '6']) }}">
                        @if($errors->has('password_confirmation'))
                            <span class="error">
                                {{ $errors->first('password_confirmation') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 offset-md-3">
                        <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/organization-user') }}" id="users_cancel">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-theme f-14" id="users_create"><i class="fa fa-spinner fa-spin d-none"></i> <span id="users_create_text">{{ __('Create') }}</span></button>
                    </div>
                </div>

            </div>
        </form>
    </div>

@endsection
@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/js/intlTelInput-jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/js/isValidPhoneNumber.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    'use strict';
   
    var userNameError = '{{ __("Please enter only alphabet and spaces") }}';
    var userNameLengthError = '{{ __("Name length can not be more than 30 characters") }}';
  
    var creatingText = '{{ __("Creating...") }}';
    var utilsScriptLoadingPath = '{{ asset("public/dist/plugins/intl-tel-input-17.0.19/js/utils.min.js") }}';
    var validPhoneNumberErrorText = '{{ __("Please enter a valid international phone number.") }}';
</script>
@endpush
