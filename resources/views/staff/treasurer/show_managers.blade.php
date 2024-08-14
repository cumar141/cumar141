@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container">
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Show Managers </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                                <li class="breadcrumb-item active">show</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @include('staff.layouts.miniNav')
            {{-- error handling --}}
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Managers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        <tbody>
                                            @foreach($managers as $user)
                                            <tr>
                                                <td>
                                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}"
                                                        alt="user-image" class="img-fluid rounded-circle mb-2"
                                                        style="width: 50px; height: 50px;">
                                                </td>
                                                <td><strong></strong> {{ $user->first_name." ".$user->last_name }}
                                                </td>
                                                <td><strong></strong> {{ $user->formattedPhone }}</td>
                                                <td><strong></strong> {{ $user->email }}</td>
                                                <td  class="d-flex">
                                                    <form method="get" action="{{ route('show.money.form') }}"  class="me-4">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="type" value="transfer">
                                                        <button type="submit" name="action" value="deposit"
                                                            class="btn btn-primary">Transfer Money</button>
                                                    </form>
                                                
                                                    <form method="get" action="{{ route('show.money.form') }}">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="type" value="request">
                                                        <button type="submit" name="action" value="closeAccount"
                                                            class="btn btn-danger">Withdraw Money</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


@include('staff.layouts.footer')