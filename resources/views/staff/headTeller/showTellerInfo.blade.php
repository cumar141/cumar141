<!-- resources/views/users/index.blade.php -->
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
                        <h4 class="mb-sm-0 font-size-18"> Tellers </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">information</li>
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
            @include('staff.layouts.miniNav')

            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Tellers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="bg-info text-white">
                                            <tr>
                                                <th>TellerUUID</th>
                                                <th>Phone</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tellers as $user)
                                            <tr>
                                                <td>{{ $user->teller_uuid }}</td>
                                                <td>{{ $user->formattedPhone }}</td>
                                                <td>{{ $user->first_name." ".$user->last_name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td class="d-flex">
                                                    <form method="get" action="{{ route('showTellerDepositForm') }}"
                                                        class="me-4">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="name"
                                                            value="{{ $user->first_name."".$user->last_name }}">
                                                        <input type="hidden" name="teller_uuid"
                                                            value="{{ $user->teller_uuid }}">
                                                        <input type="hidden" name="formattedPhone"
                                                            value="{{ $user->formattedPhone }}">
                                                        <button type="submit" name="action" value="deposit"
                                                            class="btn btn-primary mr-2">Deposit</button>
                                                    </form>
                                                    <form method="get" action="{{ route('tellerWithdrawal') }}">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="name"
                                                            value="{{ $user->first_name."".$user->last_name }}">
                                                        <input type="hidden" name="teller_uuid"
                                                            value="{{ $user->teller_uuid }}">
                                                        <button type="submit" name="action" value="closeAccount"
                                                            class="btn btn-danger mr-2">Close Account</button>
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