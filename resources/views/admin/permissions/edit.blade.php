@extends('admin.layouts.master')

@section('title', __('Edit Permission'))

@section('page_content')
    <div id="permission-edit">
        <div class="box">
            <div class="panel-body ml-20">
                <!-- Navigation tabs -->
                <ul class="nav nav-tabs cus f-14" role="tablist">
                    <!-- Include tabs for permission editing if needed -->
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>

        <!-- Page content -->
        <div class="box mt-20">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Permission edit form -->
                        <form action="{{  url(config('adminPrefix').'/permissions/update') }}" class="form-horizontal" id="permission_form" method="POST">
                            @csrf
                            <input type="hidden" value="{{ $permission->id }}" name="id" id="id" />
                            <div class="box-body">
                                <!-- Group -->
                                <div class="row form-group">
                                    <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="group">{{ __('Group') }}</label>
                                    <div class="col-sm-6">
                                        <input name="group" value="{{ $permission->group }}" type="text" id="group" class="form-control f-14" placeholder="{{ __('Enter Group') }}">
                                    </div>
                                </div>

                                <!-- Name -->
                                <div class="row form-group">
                                    <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="name">{{ __('Name') }}</label>
                                    <div class="col-sm-6">
                                        <input name="name" value="{{ $permission->name }}" type="text" id="name" class="form-control f-14" placeholder="{{ __('Enter Name') }}">
                                    </div>
                                </div>

                                <!-- Display Name -->
                                <div class="row form-group">
                                    <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="display_name">{{ __('Display Name') }}</label>
                                    <div class="col-sm-6">
                                        <input name="display_name" value="{{ $permission->display_name }}" type="text" id="display_name" class="form-control f-14" placeholder="{{ __('Enter Display Name') }}">
                                    </div>
                                </div>

                                <!-- User Type -->
                                <div class="row form-group">
                                    <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="user_type">{{ __('User Type') }}</label>
                                    <div class="col-sm-6">
                                        <input name="user_type" value="{{ $permission->user_type }}" type="text" id="user_type" class="form-control f-14" placeholder="{{ __('Enter User Type') }}">
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="row form-group">
                                    <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="description">{{ __('Description') }}</label>
                                    <div class="col-sm-6">
                                        <textarea name="description" id="description" class="form-control f-14" placeholder="{{ __('Enter Description') }}">{{ $permission->description }}</textarea>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="row form-group align-items-center">
                                    <div class="col-sm-6 offset-md-3">
                                        <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/permissions') }}" id="permissions_cancel">{{ __('Cancel') }}</a>
                                        <button type="submit" class="btn btn-theme f-14" id="permissions_edit">
                                            <i class="fa fa-spinner fa-spin f-14 d-none"></i> <span id="permissions_edit_text">{{ __('Update') }}</span>
                                        </button>
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
    <!-- Include any necessary scripts here -->
@endpush
