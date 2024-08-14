@extends('admin.layouts.master')

@section('title', __('Add Permission'))

@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Add Permission') }}</h3>
                </div>
                <form action="{{ url(config('adminPrefix').'/permissions/store') }}" class="form-horizontal" id="permission_form" method="POST">
                    @csrf
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
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="group">{{ __('Group') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter Group') }}" name="group" type="text" id="group">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter Name') }}" name="name" type="text" id="name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="display_name">{{ __('Display Name') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter Display Name') }}" name="display_name" type="text" id="display_name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="user_type">{{ __('User Type') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter User Type') }}" name="user_type" type="text" id="user_type">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="description">{{ __('Description') }}</label>
                            <div class="col-sm-6">
                                <textarea class="form-control f-14" placeholder="{{ __('Enter Description') }}" name="description" id="description"></textarea>
                            </div>
                        </div>

                        <!-- box-footer -->
                        <div class="row">
                            <div class="col-sm-6 offset-md-3">
                                <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/permissions') }}" id="permissions_cancel">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-theme" id="permissions_create"><i class="fa fa-spinner fa-spin d-none"></i> <span class="f-14" id="permissions_create_text">{{ __('Create') }}</span></button>
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

    $('#permission_form').validate({
        rules: {
            group: {
                required: true,
            },
            name: {
                required: true,
            },
            display_name: {
                required: true,
            },
            user_type: {
                required: true,
            },
            description: {
                required: true,
            }
        },
        submitHandler: function (form) {
            $("#permissions_create").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#permissions_create_text").text('Creating...');
            $('#permissions_cancel').attr("disabled", "disabled");
            form.submit();
        }
    });

</script>
@endpush
