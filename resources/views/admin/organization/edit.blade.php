@extends('admin.layouts.master')

@section('title', __('Edit Organization'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')
<div id="user-edit">

    <div class="box mt-20">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- form start -->
                    <form action="{{ url(config('adminPrefix').'/organization/update') }}" class="form-horizontal" id="user_form" method="POST">
                        {{ csrf_field() }}

                        <input type="hidden" value="{{ $organization->id }}" name="id" id="id" />

                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">

                                     <!-- FirstName --> 
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="name">{{ __('Name') }}</label>
                                        <div class="col-sm-6">
                                            <input name="name" value="{{ $organization->name }}" type="text" id="name" class="form-control f-14" placeholder="{{ __('Enter :x', ['x' => __('name')]) }}" data-value-missing="{{ __('This field is required.') }}" maxlength="30" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('name'), 'y' => __('30')]) }}">
                                            @if($errors->has('name'))
                                                <span class="error">
                                                    {{ $errors->first('name') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14 require" for="email">{{ __('Email') }}</label>
                                        <div class="col-sm-6">
                                            <input name="email" value="{{ $organization->email }}" type="email" id="email" class="form-control f-14" placeholder="{{ __('Enter a valid :x.', ['x' => __('email')] )}}" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" data-type-mismatch="{{ __('Enter a valid :x.', [ 'x' => strtolower(__('email'))]) }}">
                                            @if($errors->has('email'))
                                                <span class="error">{{ $errors->first('email') }}</span>
                                            @endif
                                            <span id="emailError"></span>
                                            <span id="email-ok" class="text-success"></span>
                                        </div>
                                    </div>

                                     <!-- Phone -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="phone">{{ __('Phone') }}</label>
                                        <div class="col-sm-6">
                                            <input type="tel" value={{ $organization->phone}} class="form-control f-14" id="phone" name="phone">
                                            <span id="duplicate-phone-error"></span>
                                            <span id="tel-error"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Address -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="address">{{ __('Address') }}</label>
                                        <div class="col-sm-6">
                                            <textarea name="address" id="address" value="{{$organization->address}}" class="form-control f-14" placeholder="{{ __('Enter :x', ['x' => __('address')]) }}" data-value-missing="{{ __('This field is required.') }}">{{ $organization->address }}</textarea>
                                            @if($errors->has('address'))
                                                <span class="error">
                                                    {{ $errors->first('address') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                   

                                    <div class="row form-group align-items-center">
                                        <div class="col-sm-6 offset-md-3">
                                            <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/organization') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                            <button type="submit" class="btn btn-theme f-14" id="users_edit">
                                                <i class="fa fa-spinner fa-spin f-14 d-none"></i> <span id="users_edit_text">{{ __('Update') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/js/intlTelInput-jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/js/isValidPhoneNumber.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    'use strict';
    var userNameError = '{{ __("Please enter only alphabet and spaces.") }}';
    var userNameLengthError = '{{ __("Name length can not be more than 30 characters") }}';
    var utilsScriptLoadingPath = '{{ asset("public/dist/plugins/intl-tel-input-17.0.19/js/utils.min.js") }}';
    var validPhoneNumberErrorText = '{{ __("Please enter a valid international phone number.") }}';
    var inactiveWarning = '{!! __("Warning! User would not be able to login.") !!}';
    var suspendWarning = '{!! __("Warning! User would not be able to do any transaction.") !!}';
    var passwordMatchErrorText = '{{ __("Please enter same value as the password field.") }}';
    var updatingText = '{{ __("Updating...") }}';
</script>
<script src="{{ asset('public/admin/customs/js/user/user.min.js') }}" type="text/javascript"></script>
@endpush
