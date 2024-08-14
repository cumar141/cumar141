@extends('admin.layouts.master')

@section('title', __('Add Organization Transaction'))

@section('head_style')
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')
<div class="box box-info" id="user-create">
    <div class="box-header with-border">
        <h3 class="box-title f-18">{{ __('Link Merchant To') }} {{ $organizations->name }}</h3>
    </div>

    <div class="box-body">
        <form action="{{ url(config('adminPrefix').'/organization/assign-merchant') }}" class="form-horizontal" id="user_form" method="POST">
            @csrf
            <input type="hidden" name="organization_id" value="{{ $organizations->id }}" id="organizations_id">
            <input type="hidden" name="merchant_user_id" id="merchant_user_id">
            <input type="hidden" name="uuid" id="uuid">

            <!-- Organization Name -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="name">{{ __('Organization Name') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14" type="text" name="name" id="name" value="{{ $organizations->name }}" disabled placeholder="{{ __('Enter :x', ['x' => __('name')]) }}" maxlength="30" readonly required>
                    @error('name')
                    <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Search User -->
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="search_user">{{ __('Search User') }}</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="text" class="form-control f-14" name="search_user" id="search_user" placeholder="{{ __('Enter Merchant code ') }}" value="{{ old('search_user') }}">
                        <button type="button" class="btn btn-theme f-14" id="searchUserBtn">
                            <span id="searchUserBtnText">{{ __('Search') }}</span>
                        </button>
                    </div>
                    <span class="text-danger" id="search_user_error"></span>
                    <i class="fa fa-spinner fa-spin d-none"></i>
                </div>
            </div>

          


            <!-- User Name Display -->
            <div class="form-group row" id="userNameContainer" style="display: none;">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="user_name">{{ __('Merchant Account') }}</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control f-14" name="user_name" id="user_name" placeholder="{{ __('Merchant Account') }}" value="{{ old('user_name') }}" readonly>
                    <span class="text-danger p-3" id="user_name_error"></span>
                </div>
        
            </div>
            <div class="form-group row">
                <div class="col-sm-6 offset-sm-3">
                    <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/organization') }}" id="users_cancel">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-theme f-14" id="users_create">
                        <i class="fa fa-spinner fa-spin d-none"></i> <span id="users_create_text">{{ __('Create') }}</span>
                    </button>
                    <button type="button" class="btn btn-danger f-14" id="clearBtn" style="display: none;">{{ __('Clear') }}</button>
          
            </div>
        </div>
  
        </form>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    'use strict';

    $(document).ready(function() {
        $('#searchUserBtn').click(function() {
    var searchUserBtn = $(this);
    var searchUserBtnText = $('#searchUserBtnText');
    var searchUser = $('#search_user').val().trim();
    var searchUserError = $('#search_user_error');
    var userId = $('#merchant_user_id');
    var userName = $('#user_name');
    var uuid = $('#uuid');
    var clearBtn = $('#clearBtn');

    if (searchUser === '') {
        searchUserError.text("{{ __('Please enter Merchant code') }}").show();
        return false;
    } else {
        searchUserError.hide();
    }

    searchUserBtn.attr('disabled', true);
    searchUserBtnText.html('<i class="fa fa-spinner fa-spin"></i> {{ __('Searching...') }}');

    $.ajax({
        url: "{{ url(config('adminPrefix').'/merchant/search-merchant-user') }}",
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { search: searchUser },
        success: function(response) {
            if (response.status === 'success') {
                var user = response.data;
         
                userId.val(user.user_id);
                uuid.val(user.merchant_uuid);
           
                userName.val(user.full_name + ' - ' + user.business_name);

                $('#userNameContainer').show();
                $('#search_user').prop('disabled', true);
                // $('#user_name').after('<i class="fas fa-check-circle text-success ml-2"></i>');
                clearBtn.show();
            } else {
                searchUserError.text(response.message).show();
                // $('#search_user').before('<i class="fa fa-times-circle text-danger ml-2"></i>');
            }
            clearBtn.show();
        },
        error: function() {
            searchUserError.text("{{ __('No user found or error occurred') }}").show();
        },
        complete: function() {
            searchUserBtn.attr('disabled', false);
            searchUserBtnText.html('{{ __('Search') }}');
        }
    });
});


        $('#clearBtn').click(function() {
            $('#search_user').val('').prop('disabled', false);
            $('#user_name').val('');
            $('#userNameContainer').hide();
            $('#merchantCreateForm').hide();
            $('#search_user_error').hide();
            $('#search_user + i').remove();
            $('#user_name + i').remove();
            $(this).hide();
        });
    });
</script>
@endpush
