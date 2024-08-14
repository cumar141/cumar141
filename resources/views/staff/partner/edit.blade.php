@include('staff.layouts.header')
@include('staff.layouts.sidebar')
@section('title', __('Edit Partner Balance'))


<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .card-header {
        background-color: #f8f9fa;
        /* Add consistent background color for card headers */
        border-bottom: 1px solid #dee2e6;
        /* Add border for card headers */
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container">

            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Update </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active"> Partner Balance</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            {{-- error handling --}}
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- success message --}}
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            <div class="card">
                <div class="card-header">Edit Partner Balance</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('staff.partner-balance.update', ['id' => $partnerBalance->id]) }}">
                        @csrf
                        

                        <!-- Partner Name -->
                        <div class="form-group">
                            <label for="partner">Partner Name</label>
                            <input type="text" class="form-control" id="partner" name="partner"
                                value="{{ $partnerBalance->partner }}" required>
                        </div>

                        <!-- Type -->
                        <div class="form-group">
                            <label for="type">Type</label>
                            <input type="text" class="form-control" id="type" name="type"
                                value="{{ $partnerBalance->type }}" required>
                        </div>

                        <!-- Balance -->
                        <div class="form-group">
                            <label for="balance">Balance</label>
                            <input type="text" class="form-control" id="balance" name="balance"
                                value="{{ $partnerBalance->balance }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>