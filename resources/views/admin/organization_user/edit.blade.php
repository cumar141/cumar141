@extends('admin.layouts.master')

@section('title', __('Edit Organization User'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')

<div class="box box-info" id="user-edit">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('Edit Organization User') }}</h3>
    </div>

    {{-- Handle error --}}
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    {{-- Edit form --}}
    <form action="{{ route('organization.user.update') }}" class="form-horizontal" id="user_form" method="POST">
        @csrf
       

        <div class="box-body">
            <!-- Organization ID -->
            <input type="hidden" class="form-control" name="organization_id" id="organization_id" value="{{ $organizationUser->organization_id }}">

            <!-- User ID -->
            <input type="hidden" class="form-control" name="id" id="id" value="{{ $organizationUser->id }}">

            <!-- Org. Name -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="name">{{ __('Org. Name') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" disabled value="{{ $organizationUser->organization->name }}" placeholder="{{ __('Enter :x', ['x' => __('name')]) }}" type="text" id="name" required>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="email">{{ __('Email') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" value="{{ $organizationUser->email }}" placeholder="{{ __('Enter a valid :x.', ['x' => __('email')] )}}" name="email" type="email" id="email" required>
                    @error('email')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Username -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="username">{{ __('Username') }}</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control f-14" value="{{ $organizationUser->username }}" placeholder="Enter Username" id="username" name="username">
                    @error('username')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="password">{{ __('Password') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" placeholder="{{ __('Enter new Password') }}" name="password" type="password" id="password" >
                    @error('password')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="password_confirmation">{{ __('Confirm Password') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" placeholder="{{ __('Confirm password') }}" name="password_confirmation" type="password" id="password_confirmation" >
                    @error('password_confirmation')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 offset-md-3">
                    <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/organization-user') }}" id="users_cancel">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-theme f-14" id="users_create"><i class="fa fa-spinner fa-spin d-none"></i> <span id="users_create_text">{{ __('Update') }}</span></button>
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
<script src="{{ asset('public/admin/customs/js/user/user.min.js') }}" type="text/javascript"></script>
@endpush
