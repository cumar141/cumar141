@extends('admin.layouts.master')

@section('title', __('Add Branch'))

@section('head_style')
<!-- custom-checkbox -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/custom-checkbox.min.css') }}">
@endsection

@section('page_content')
<div class="row">
    <div class="col-md-9">
        <!-- Horizontal Form -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Add Branch') }}</h3>
            </div>

            <!-- form start -->
            <form method="POST" action="{{ route('branch.store')}}" class="form-horizontal"
                id="branch_add_form">
                {{ csrf_field() }}

                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <div class="box-body">
                    <div class="form-group row">
                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control f-14" value="{{ old('name') }}" placeholder="{{ __('Name') }}" id="name">
                            @if($errors->has('name'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                
                    <div class="form-group row">
                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="address">{{ __('Address') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="address" class="form-control f-14" value="{{ old('address') }}" placeholder="{{ __('Address') }}" id="address">
                            @if($errors->has('address'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('address') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                
                    <div class="form-group row">
                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="email">{{ __('Email') }}</label>
                        <div class="col-sm-6">
                            <input type="email" name="email" class="form-control f-14" value="{{ old('email') }}" placeholder="{{ __('Email') }}" id="email">
                            @if($errors->has('email'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                
                    <div class="form-group row">
                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="phone">{{ __('Phone') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="phone" class="form-control f-14" value="{{ old('phone') }}" placeholder="{{ __('Phone') }}" id="phone">
                            @if($errors->has('phone'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('phone') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                
                
                    <div class="form-group row">
                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="status">{{ __('Status') }}</label>
                        <div class="col-sm-6">
                            <select name="status" id="status" class="form-control f-14">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @if($errors->has('status'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('status') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/branch') }}">{{
                        __('Cancel') }}</a>
                    <button type="submit" class="btn btn-theme pull-right f-14">{{ __('Add') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}"
    type="text/javascript"></script>

    <script type="text/javascript">
        $(document).on('change','.other_checkbox',function()
        {
            var tr_id        = $(this).closest('tr').attr('data-rel');
            var fieldInputId = 'view_'+tr_id;
            $("#"+fieldInputId).prop("checked",true);
        });
    
        jQuery.validator.addMethod("letters_with_spaces", function(value, element)
        {
            return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
        }, "Please enter letters only!");
    
        $.validator.setDefaults({
            highlight: function(element) {
                $(element).parent('div').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).parent('div').removeClass('has-error');
            },
            errorPlacement: function (error, element) {
                if (element.prop('type') === 'checkbox') {
                    $('#error-message').html(error);
                } else {
                    error.insertAfter(element);
                }
            }
        });
    
        $('#branch_add_form').validate({
            rules: {
                name: {
                    required: true,
                    letters_with_spaces: true,
                },
                address: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true,
                },
                phone: {
                    required: true,
                    digits: true,
                },
                code: {
                    required: true,
                    letters_with_spaces: true,
                },
                status: {
                    required: true,
                },
            },
        });
    </script>
    

@endpush