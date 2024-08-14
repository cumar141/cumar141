<!doctype html>
<html lang="en">
<head> 
        <meta charset="utf-8" />
        <title>Dashboard | Staff</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesbrand" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('public/dist/images/default-favicon.png')}}">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.min.css">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Bootstrap Css -->
        <link rel="stylesheet" href="{{ asset('public/staff/assets/css/bootstrap.min.css')}}" />


        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/fontawesome.min.css" >

        <!-- Icons Css -->
        <link rel="stylesheet" href="{{ asset('public/staff/assets/css/icons.min.css')}}" />
        <!-- App Css-->
        <link rel="stylesheet" href="{{ asset('public/staff/assets/css/app.min.css')}}" />
        <link rel="stylesheet" href="{{ asset('public/staff/toastr/toastr.min.css')}}" />
        
    </head>
    <body data-sidebar="dark">
        
<!-- Flash Message  -->
<div class="flash-container">
    @php
        session_start();
    @endphp
    @if(Session::has('message'))
        <div class="alert mt-20 f-14 {{ Session::get('alert-class') }} text-center mb-0" role="alert">
          {{ Session::get('message') }}
          <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
        </div>
    @endif
    <div class="alert alert-success f-14 text-center mb-0 d-none" id="success_message_div" role="alert">
        <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
        <p id="success_message"></p>
    </div>

    <div class="alert alert-danger f-14 text-center mb-0 d-none" id="error_message_div" role="alert">
        <p><a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a></p>
        <p id="error_message"></p>
    </div>
</div>
<!-- /.Flash Message  -->
    <!-- <body data-layout="horizontal" data-topbar="dark"> -->
        @if (Session::has('user_data'))
        @php
        $userData = Session::get('user_data');
        if(empty($userid)){
        session()->flash('message', __('Session Expired.'));
        session()->flash('alert-class', 'alert-danger');
        
        return redirect()->route('staff.login');
        }
        @endphp
        @endif
