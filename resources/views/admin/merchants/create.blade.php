@extends('admin.layouts.master')

@section('title', __('Add Merchant'))

@section('head_style')
<link rel="stylesheet" type="text/css"
    href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

@endsection

@section('page_content')

<div class="box box-info" id="user-create">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('Add Merchant') }}</h3>
    </div>
      @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                       
    {{-- A place to search the user --}}
    <div class="box-body">
        <div class="form-group row">
            <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="search_user">{{ __('Search
                User')}}</label>
            <div class="col-sm-6">
                <input type="text" class="form-control f-14" name="search_user" id="search_user"
                    placeholder="{{ __('Enter user mobile number') }}" value="{{ old('search_user') }}">
                <span class="text-danger" id="search_user_error"></span>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-6 offset-md-3">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-theme f-14" id="searchUserBtn">
                        <i class="fa fa-spinner fa-spin d-none"></i> <span id="searchUserBtnText">{{ __('Search')
                            }}</span>
                    </button>
                    <button type="button" class="btn btn-danger f-14" id="clearBtn" style="display: none;">{{ __('Clear') }}</button>
                </span>
            </div>
            <div class="col-sm-6 offset-md-3">
                <button type="button" class="btn btn-danger f-14" id="clearBtn" style="display: none;">{{ __('Clear')}}</button>
            </div>
        </div>
        {{-- A text for displaying the user name after search --}}
        <div class="form-group row">
            <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="user_name">{{ __('UserName')}}</label>
            <div class="col-sm-6">
                <input type="text" class="form-control f-14" name="user_name" id="user_name" placeholder="{{ __('User Name') }}" value="{{ old('user_name') }}" readonly>
                <span class="text-danger p-3" id="user_name_error"></span>
            </div>
        </div>
    </div>
    <form action="{{ url(config('adminPrefix').'/merchant/save') }}" method="post" enctype="multipart/form-data"
        id="merchantCreateForm" class="form-horizontal">
        <input type="hidden" class="form-control f-14" name="user_id" id="user_id" value="{{ old('user_id') }}">
        @csrf
        <div class="box-body">
            <!-- Business Name -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="business_name">{{ __('Business
                    Name') }} <span class="text-warning">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control f-14" name="business_name" id="business_name"
                        placeholder="{{ __('Enter your business name.') }}" value="{{ old('business_name') }}" required
                        data-value-missing="{{ __('This field is required.') }}">
                    @error('business_name')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <!-- Site URL -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="site_url">{{ __('Site URL') }}
                    <span class="text-warning">*</span></label>
                <div class="col-sm-6">
                    <input type="url" class="form-control f-14" name="site_url" id="site_url"
                        placeholder="https://example.com" value="{{ old('site_url') }}" required
                        data-value-missing="{{ __('This field is required.') }}">
                    @error('site_url')
                    <span class="error">{{ $message }}</span>
                    @enderror
                    <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2"><em>* {{ __('Make sure to add
                            http://') }}</em></p>
                </div>
            </div>
            <!-- Currency & Merchant Type -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="currency_id">{{
                    __('Currency')}}<span class="text-warning">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control select2 f-14" data-minimum-results-for-search="Infinity"
                        name="currency_id" id="currency_id">
                        @foreach($activeCurrencies as $activeCurrency)
                        <option value="{{ $activeCurrency->id }}">{{ $activeCurrency->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="type">{{ __('Merchant
                    Type')}}<span class="text-warning">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control select2 f-14" data-minimum-results-for-search="Infinity" name="type"
                        id="type">
                        <option value="standard" {{ old('type')=='standard' ? 'selected' : '' }}>{{ __('Standard') }}
                        </option>
                        <option value="express" {{ old('type')=='express' ? 'selected' : '' }}>{{ __('Express') }}
                        </option>
                    </select>
                </div>
            </div>
            <br>
            <!-- Withdrawal Approval -->
            @if (module('WithdrawalApi') && isActive('WithdrawalApi'))
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold"
                    for="withdrawal_approval">{{__('Withdrawal Approval') }}<span class="text-warning">*</span></label>
                <div class="col-sm-6">
                    <input type="checkbox" class="form-check-input" name="withdrawal_approval" id="withdrawal_approval">
                </div>
            </div>
            @endif
            <!-- Message for administration -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="note">{{ __('Message for
                    administration') }}</label>
                <div class="col-sm-6">
                    <textarea class="form-control f-14" name="note" id="note"
                        placeholder="{{ __('Enter your message here.') }}" required
                        data-value-missing="{{ __('This field is required.') }}">{{ old('note') }}</textarea>
                    @error('note')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <!-- Business Logo -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="logo">{{ __('BusinessLogo')
                    }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" type="file" name="logo" id="logo">
                    @error('logo')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-sm-3">
                    <img src="{{ image(null, 'merchant') }}" width="100" height="80" id="merchantLogoPreview">
                    <p class="mb-0 f-12 leading-15 gilroy-regular text-gray-100">{{ __('Recommended size') }}:
                        <strong class="text-dark">{{ __('100px * 100px') }}</strong>
                    </p>
                    <p class="mb-0 f-12 leading-15 gilroy-regular text-gray-100 mt-10">{{ __('Supported format')}}:<span
                            class="text-dark">{{ __('jpeg, png, bmp, gif or svg') }}</span></p>
                </div>
            </div>
            <!-- Create and Cancel buttons -->
            <div class="form-group row">
                <div class="col-sm-6 offset-md-3">
                    <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/merchants') }}">{{
                        __('Cancel') }}</a>
                    <button type="submit" class="btn btn-theme f-14" id="merchantCreateSubmitBtn">
                        <i class="fa fa-spinner fa-spin d-none"></i> <span id="merchantCreateSubmitBtnText">{{
                            __('Create Merchant') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@endsection
@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script type="text/javascript">
    'use strict';
        var csrfToken = $('[name="_token"]').val();
        var merchantDefaultLogo = "{{ image(null, 'merchant') }}";
        var submitButtonText = "{{ __('Submitting...') }}";
</script>

<script src="{{ asset('public/user/customs/js/merchant.min.js') }}"></script>
@endpush
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<script>
    // document ready function hide the merchant create form
    $(document).ready(function() {
    // Initially hide the merchant create form
    $('#merchantCreateForm').hide();

    $('#searchUserBtn').click(function() {
        var searchUserBtn = $(this);
        var searchUserBtnText = $('#searchUserBtnText');
        var searchUser = $('#search_user').val().trim();
        var searchUserError = $('#search_user_error');
        var userId = $('#user_id');
        var userName = $('#user_name');
        var clearBtn = $('#clearBtn'); // New: Clear button

        if (searchUser === '') {
            searchUserError.text("{{ __('Please enter user mobile number') }}");
            searchUserError.show();
            return false;
        } else {
            searchUserError.hide();
        }

        searchUserBtn.attr('disabled', true);
        searchUserBtnText.html('<i class="fa fa-spinner fa-spin"></i> {{ __('Searching...') }}');

        $.ajax({
            url: "{{ url(config('adminPrefix').'/merchant/search-user') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                search_user: searchUser
            },
            success: function(response) {
                console.log(response);
                var status = response.status;
                if(status === 200){
                    var user = response.user;
                    userId.val(user.id);
                    userName.val(user.first_name + ' ' + user.last_name);
                    $('#merchantCreateForm').show();
                    $('#search_user').prop('disabled', true);
                    $('#user_name').after('<i class="fas fa-check-circle text-success ml-2"></i>');
                    clearBtn.show();
                }
                if(status === 404){
                    console.log(response.message);
                    $('#search_user').after('<i class="fa fa-times-circle text-danger ml-2"></i>');
                    var message = response.message;
                    searchUserError.show();
                    searchUserError.text(message);
                }
                clearBtn.show();
                
            },
            error: function(xhr, status, error) {
                // console.log('Error occurred: ' + error);
                $("#search_user_error").text("No user found or error occurred");
            },
            complete: function() {
                searchUserBtn.attr('disabled', true);
                searchUserBtnText.html('{{ __('Search') }}');
            }
        });
    });

    // Clear button functionality
    $('#clearBtn').click(function() {
        $('#search_user').val('').prop('disabled', false);
        $('#searchUserBtn').attr('disabled', false);
        $('#user_name').val(''); 
        $('#merchantCreateForm').hide(); 
        $('#user_name + i').remove(); 
        $('#search_user + i').remove(); 
        $('#search_user_error').hide();
        $('#search_user_error').text('');
        $(this).hide(); 
    });
});

</script>