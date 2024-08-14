@extends('admin.layouts.master')

@section('title', __('Staff Deposit'))

@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <form action="{{ url(config('adminPrefix').'/staff/depositMoney') }}" class="form-horizontal" id="user_form" method="POST">

                    <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
                    <div class="box-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group row">

                            <input type="hidden" value="{{$user->id}}" name="userid" id="userid">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="last_name">{{ __('First Name') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control f-14" readonly value="{{ __($user->first_name) }}" name="first_name" type="text" id="first_name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="last_name">{{ __('Last Name') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control f-14" readonly value="{{ __($user->last_name) }}" name="first_name" type="text" id="last_name">
                                </div>
                            </div>
                        </div>


                        {{-- currency --}}
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="currency">{{ __('Wallet') }}</label>
                            <div class="col-sm-6">
                                <select class="select2" name="currency" id="currency">
                                    @foreach ($currency as $curr)
                                        <option value='{{ $curr->id }}'> {{ $curr->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="amount">{{ __('Amount') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="0.00"  name="amount" type="text" id="amount">
                            </div>
                        </div>


                        <!-- box-footer -->
                        <div class="row">
                            <div class="col-sm-6 offset-md-3">
                                <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/staff') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-theme" id="users_create"><i class="fa fa-spinner fa-spin d-none"></i> <span class="f-14" id="users_create_text">{{ __('Deposit') }}</span></button>
                            </div>
                        </div>
                        <!-- /.box-footer -->
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

    $(function () {
        $(".select2").select2({});
    })

    $.validator.setDefaults({
        highlight: function (element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#user_form').validate({
        rules: {
            amount: {
                required: true,
            },
            currency: {
                required: true,
            }
        },
        messages: {
            amount: {
                required: "Please enter amount",
            },
            currency: {
                required: "Please select wallet",
            }
        },
        submitHandler: function (form) {
            $("#users_create").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#users_create_text").text('Depositing...');
            $('#users_cancel').attr("disabled", "disabled");
            form.submit();
        }
    });




</script>
@endpush


