@include('staff.layouts.header')
@include('staff.layouts.sidebar')
@section('title', __('Add Partner Balance'))


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
            <!-- start page title -->
            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Create </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">New Partner Balance</li>
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
                <div class="card-header">
                    <h4 class="card-title">New Partner Balance</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.partner-balance.store') }}" class="form-horizontal"
                        id="partner_balance_form" method="POST">
                        @csrf
                        <!-- Partner -->
                        <div class="mb-3">
                            <label for="partner" class="form-label">Partner</label>
                            <input class="form-control font-size-14" placeholder="Enter partner name" name="partner"
                                type="text" id="partner" value="{{ old('partner') }}" required>
                            <!-- Error message -->
                            @error('partner')
                            <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <input class="form-control font-size-14" placeholder="Enter type" name="type" type="text"
                                id="type" value="{{ old('type') }}" required>
                            <!-- Error message -->
                            @error('type')
                            <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Balance -->
                        <div class="mb-3">
                            <label for="balance" class="form-label">Balance</label>
                            <input class="form-control font-size-14" placeholder="Enter balance" name="balance"
                                type="number" step="0.01" id="balance" value="{{ old('balance') }}" required>
                            <!-- Error message -->
                            @error('balance')
                            <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-sm-6 offset-md-3">
                                <button type="submit" class="btn btn-primary font-size-14">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('staff.layouts.footer')


<script>
    $(document).ready(function() {
        // Form validation
        $('#partner_balance_form').validate({
            rules: {
                partner: {
                    required: true,
                    maxlength: 100
                },
                type: {
                    required: true,
                    maxlength: 100
                },
                balance: {
                    required: true,
                    number: true,
                    min: 0
                }
            },
            messages: {
                partner: {
                    required: "Partner name is required.",
                    maxlength: "Partner name should not exceed 100 characters."
                },
                type: {
                    required: "Type is required.",
                    maxlength: "Type should not exceed 100 characters."
                },
                balance: {
                    required: "Balance is required.",
                    number: "Please enter a valid number for the balance.",
                    min: "Balance should be a non-negative number."
                }
            }
        });
    });
</script>