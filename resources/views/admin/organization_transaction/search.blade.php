@extends('admin.layouts.master')

@section('title', __('Add Organization User'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')

    <div class="box box-info" id="user-create">
        <div class="box-header with-border">
            <h3 class="box-title">{{ __('Search  Organization  ') }}</h3>
        </div>
        <br>
        <br>
    <div></div>

               {{-- handle error --}}
               @if(session('error'))
               <div class="alert alert-danger">
                   {{ session('error') }}
               </div>
               @endif

        {{-- a search form  --}}
                {{-- a search form  --}}
        <form action="{{ url(config('adminPrefix').'/organization/transaction/search') }}" class="form-horizontal" id="orgSearchForm" method="POST">
            <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">

            {{-- search field --}}
            <div class="form-group row">
                <label class="col-sm-3 mt-11 control-label text-sm-end f-14 fw-bold" for="name">{{ __('Search') }}</label>
                <div class="col-sm-6">
                    <input class="form-control f-14"  placeholder="Enter Organization name" name="searchInput" type="text" id="searchInput"  required data-value-missing="{{ __('This field is required.') }}" >
                    @if($errors->has('name'))
                        <span class="error">
                            {{ $errors->first('name') }}
                        </span>
                    @endif
                </div> 

                <div class="col-sm-3">
                    <button type="submit" class="btn btn-theme f-14" id="search"><i class="fa fa-spinner fa-spin d-none"></i> <span id="search_text">{{ __('Search') }}</span></button>
                </div>
            </div>


        </form>
        <br>
        <br>
    <div></div>
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