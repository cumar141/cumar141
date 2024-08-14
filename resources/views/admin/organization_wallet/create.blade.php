@extends('admin.layouts.master')

@section('title', __('Add Organization'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')

    <div class="box box-info" id="user-create">
        <div class="box-header with-border">
            <h3 class="box-title">{{ __('Add Organization') }}</h3>
        </div>
        <form action="{{ url(config('adminPrefix').'/organization/wallet/store') }}" method="POST" class="form-horizontal" id="org_wallet_form">

            <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
            <input type="hidden" value="{{ $organizations->id}}" name="organizations_id" id="organizations_id">

            <div class="box-body">

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

                <!-- Amount -->
                <div class="form-group row">
                    <label class="col-sm-3 mt-11 control-label require text-sm-end f-14 fw-bold" for="email">{{ __('Amount') }}</label>
                    <div class="col-sm-6">
                        <input class="form-control f-14" placeholder="{{ __('Enter a valid :x.', ['x' => __('Amount')] )}}" name="balance" type="text" id="balance" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                        @if($errors->has('balance'))
                            <span class="error">{{ $errors->first('balance') }}</span>
                        @endif
                        <span id="balance"></span>
                        <span id="balance" class="text-success"></span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-6 offset-md-3">
                        <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/organization/wallet') }}" id="users_cancel">{{ __('Cancel') }}</a>
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
<script src="{{ asset('public/admin/customs/js/user/user.min.js') }}" type="text/javascript"></script>
@endpush


