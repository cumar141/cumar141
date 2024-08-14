@include('staff.layouts.header')
@include('staff.layouts.sidebar')



<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container">
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">
                           Branch
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Managers </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!-- Loop through branches -->
                        @foreach ($branch as $b)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ $b->name }}</h5>
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
                                            <!-- Loop through managers of the current branch -->
                                            @foreach($managers->where('branch_id', $b->id) as $user)
                                            <tr>
                                                <td>
                                                    <img src="{{ asset('public/staff/assets/images/small/img-6.jpg') }}" alt="user-image" class="img-fluid rounded-circle mb-2" style="width: 100px; height: 100px;">
                                                </td>
                                                <td><strong></strong> {{ $user->first_name." ".$user->last_name }}</td>
                                                <td><strong></strong> {{ $user->formattedPhone }}</td>
                                                <td><strong></strong> {{ $user->email }}</td>
                                                <td>
                                                    <form method="post" action="{{ route('dashboard.select') }}" id="managerForm">
                                                        @csrf
                                                        <input type="hidden" name="manager_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="branch_id" value="{{ $user->branch_id }}">
                                                        <input type="hidden" name="type" value="manager">
                                                        <button type="submit" name="action" value="closeAccount" class="btn btn-primary">View Dashboard</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            

        </div>
    </div>
</div>

@include('staff.layouts.footer')