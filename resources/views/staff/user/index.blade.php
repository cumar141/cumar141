@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
        background-color: rgb(229, 237, 241);
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

    .table th,
    .table td {
        padding: 8px 12px;
        /* Adjust padding for better spacing */
    }
</style>
{{-- include datatable links --}}


{{-- list users in card and data table --}}
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="col-xl-12 ">
                <div style="display: flex; justify-content: space-between;" class="mb-5 mt-5">
                    <h1 id="message">Users</h1>
                    {{-- add users button --}}
                    <a href="{{ route('staff.user.create') }}" class="btn btn-primary">Add User</a>
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
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                {{-- success message --}}
             
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Users</h5>
                        
                    </div>
                    <div class="card-body bg-slate-500">
                        <div class="table-responsive">
                            {!! $dataTable->table(['class' => 'table table-striped table-hover f-14 dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                        </div>
                           
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('staff.layouts.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
