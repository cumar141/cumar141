<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link rel="stylesheet" href="{{ asset('public/staff/assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{ asset('public/staff/assets/css/app.min.css')}}">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            width: 400px;
            margin: 0 auto;
            margin-top: 10%;
            display: flex;
            flex-direction: column;
        }

        .login-form {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 5px 5px 10px rgb(49 48 48 / 50%);
            flex: 1;
        }
        .img-div
        {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 5px 5px 10px rgb(49 48 48 / 50%);
            margin-bottom: 20px;
            flex: 2;
            text-align: center;
        }

        .login-title {
            text-align: center;
            color: #00487a;

        }

        .form-control {
            margin-bottom: 15px;
        }

        .btn-login {
            background-color: #00487a !important;
            border-color: #00487a !important;
            color: #ffffff;
            width: 100%;
        }

        .btn-login:hover {
            background-color: #003152 !important;
            border-color: #003152 !important;
            color: #e6dddd;
        }

        .forgot-password {
            text-align: right;
        }

        .logo {
            max-width: 100%;
            max-height: 100%;
            
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-form">
            <h3 class="login-title">STAFF LOGIN</h3>
            <div class="img-div logo">
                <img src="public/logo.png" alt="Logo" class="logo">
            </div>
            
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        

            <form action="{{ route('staff.authenticate') }}" method="post" class="login-form">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Enter Email">

                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Enter password">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember-check">
                    <label class="form-check-label" for="remember-check">Remember me</label>
                </div>

                <div class="mb-3 forgot-password">
                    <a href="ForgetPassword.php" class="text-muted">Forgot password?</a>
                </div>

                <button class="btn btn-login" type="submit">Log In</button>
            </form>
        </div>
    </div>

        <!-- JAVASCRIPT -->'
    
 
        <script src="{{ asset('public/staff/assets/libs/jquery/jquery.min.js')}}"></script>
        <script src="{{ asset('public/staff/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{ asset('public/staff/assets/libs/metismenu/metisMenu.min.js')}}"></script>
        <script src="{{ asset('public/staff/assets/libs/simplebar/simplebar.min.js')}}"></script>
        <script src="{{ asset('public/staff/assets/libs/node-waves/waves.min.js')}}"></script>
        <!-- owl.carousel js -->
        <script src="{{ asset('public/staff/assets/libs/owl.carousel/owl.carousel.min.js')}}"></script>
    
        <!-- auth-2-carousel init -->
        <script src="{{ asset('public/staff/assets/js/pages/auth-2-carousel.init.js')}}"></script>
    
        <!-- App js -->
        <script src="{{ asset('public/staff/assets/js/app.js')}}"></script>
</body>

</html>
