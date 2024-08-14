@include('admin2.nav')

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
                                            <p class="text-muted mb-0">{{$user->first_name." ".$user->last_name}}</p>
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
                                            <p class="mb-0">Phone</p>
                                        </div>
                                        <div class="col-sm-9">
                                            <p class="text-muted mb-0">{{$user->phone}}</p>
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
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
              
           
        </div>
        <!-- end main content-->
    </div>
</div>


@include('admin2.footer')


<script>
    $("#profile").css("border-bottom", '1px solid blue');
</script>