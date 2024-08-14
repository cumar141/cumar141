@extends('admin.layouts.master')

@section('title', __('Add Organization'))

@section('head_style')
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('page_content')
<div class="box box-info" id="organization-create">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('Add Organization') }}</h3>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="box-body">
            <!-- Flash Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
    

        <form action="{{ url(config('adminPrefix').'/organization/store') }}" method="POST" class="form-horizontal" id="organization_form">
            @csrf

            <div class="box-body">
                <!-- Name Field -->
                <div class="form-group row">
                    <label for="name" class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold">{{ __('Name') }}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control f-14" name="name" id="name" placeholder="{{ __('Enter :x', ['x' => __('name')]) }}" value="{{ old('name') }}" required maxlength="30">
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Email Field -->
                <div class="form-group row">
                    <label for="email" class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold">{{ __('Email') }}</label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control f-14" name="email" id="email" placeholder="{{ __('Enter a valid :x.', ['x' => __('email')]) }}" required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Phone Field -->
                <div class="form-group row">
                    <label for="phone" class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold">{{ __('Phone') }}</label>
                    <div class="col-sm-6">
                        <input type="tel" class="form-control f-14" name="phone" id="phone" placeholder="+252">
                    </div>
                </div>

                <!-- Address Field -->
                <div class="form-group row mt-4">
                    <label for="address" class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold">{{ __('Address') }}</label>
                    <div class="col-sm-6">
                        <textarea class="form-control f-14" name="address" id="address" rows="3" placeholder="{{ __('Enter :x', ['x' => __('address')]) }}">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="is_white_list">{{ __('White list') }}</label>
                    <div class="col-sm-6">
                        <select class="form-control f-14" name="is_white_list" id="is_white_list">
                            <option value="0">False</option>
                            <option value="1">True</option>
                        </select>
    
                    </div>
                </div>
            </div>
    
            <!-- White List -->
           


             

                <!-- Submit and Cancel Buttons -->
                <div class="form-group row">
                    <div class="col-sm-6 offset-sm-3">
                        <a href="{{ url(config('adminPrefix').'/merchants') }}" class="btn btn-theme-danger f-14 me-1">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-theme f-14" id="organizationCreateSubmitBtn">
                            <i class="fa fa-spinner fa-spin d-none"></i> <span id="organizationCreateSubmitBtnText">{{ __('Create Organization') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/js/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('public/dist/js/isValidPhoneNumber.min.js') }}"></script>
<script>
    'use strict';

    var searchUserError = '{{ __("Please enter user mobile number") }}';
    var searchingText = '{{ __("Searching...") }}';
    var validPhoneNumberErrorText = '{{ __("Please enter a valid international phone number.") }}';
</script>
<script src="{{ asset('public/admin/customs/js/user/user.min.js') }}"></script>
@endpush
