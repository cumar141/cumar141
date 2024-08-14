@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">
            
            <section >
                <div class="container py-5">
                    <div class="row justify-content-center align-items-stretch">
                        <div class="col-lg-8">
                            <div class="card mb-4 h-100">
                                <div class="card-body rounded shadow">
                                    <div class="row ">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Full Name</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->full_name}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Email</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->email}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Branch</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->branch->name}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Phone</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->formattedPhone}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Mobile</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->phone}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <p class="mb-0">Status</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->status}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 ">
                            <div class="card mb-4 rounded shadow h-100">
                                <div class="card-body text-center">
                                    <img src="https://www.gstatic.com/webp/gallery/1.jpg" alt="Nature picture" class="rounded-circle img-fluid" style="width: 150px;">
                                    <h5 class="my-3">{{$user->first_name}}</h5>
                                    <p class="text-muted mb-1">Somexchange staff</p>
                                    <button class="btn btn-primary" id="change-password">Change password</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- end main content-->
    </div>
    <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" role="document">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Change password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="change-pass-form">
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="otp" class="col-form-label">OTP:</label>
                            <input type="text" class="form-control" id="otp" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label for="old_password" class="col-form-label">Current password:</label>
                            <input type="text" class="form-control" id="old_password" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="new_password" class="col-form-label">New Password:</label>
                            <input type="text" class="form-control" id="new_password" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label for="new_password_" class="col-form-label">Repeat Password:</label>
                            <input type="text" class="form-control" id="new_password_" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="save">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('staff.layouts.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    function processRequest(url, _data, callback) {
        $.post(url, _data)
        .done(function(data) {
            callback(data);
        }).fail(function() {
            showToastr("error", "Error", "There's an error");
        });
    }

    // Function to show toastr
    function showToastr(icon, title, body, confirmation = false) {
        if (confirmation) {
            return Swal.fire({
                title: title,
                icon: icon,
                html: body,
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Confirm',
                allowOutsideClick: false
            });
        } else {
            Swal.fire({
                icon: icon,
                text: body,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }
    }

    // Event delegation for dynamically generated buttons
    $('#change-password').on('click', function() {
        let url = `{{ route('sendOtp') }}`;
        let request = "You're about to change your password";
        let data = { user_id: {{$user->id}}, _token: '{{@csrf_token()}}' };
        showToastr("warning", "Are you sure?", request, true)
        .then((result) => {
            if (result.isConfirmed) {
                processRequest(url, data, (function(data) {
                    $("#modal").modal('show');
                    showToastr("success", "Success", "OTP was sent to your phone");
                })
                );
            }
        });
    });
    
    $(".btn-close[data-bs-dismiss='modal']").on('click', function() {$("#change-pass-form").trigger("reset")});
    
    $("#change-pass-form").submit(function(e) {
        e.preventDefault();
        let pass = $("#new_password").val();
        let pass_ = $("#new_password_").val();
        if(pass != pass_) {
            showToastr("error", "Error", "Passwords don't match")
            return false;
        }
        let otp = $("#otp").val();
        let olp = $("#old_password").val();
        
        let url = `{{ route('staff.change-password') }}`;
        let data = { old_password: olp, otp: otp, password: pass, _token: '{{@csrf_token()}}' };
        processRequest(url, data, (function(data) {
            showToastr(data.status, "Success", data.message);
            window.location = "{{ route('staff.logout') }}";
        })
        );
    });
});
</script>