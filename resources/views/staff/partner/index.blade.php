@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
        background-color: rgb(226, 242, 250);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="col-xl-12 ">
                <div style="display: flex; justify-content: space-between;" class="mb-5 mt-5">
                    <h1 id="message">Partner Balance</h1>
                    {{-- add users button --}}
                    <a href="{{ route('staff.partner-balance.create') }}" class="btn btn-primary">Add Partner</a>
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
                        <h5 class="card-title mb-0">Partner Balance</h5>
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
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to attach the SweetAlert confirmation to delete buttons
        function attachDeleteEvent() {
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = button.getAttribute('href');
                        }
                    });
                });
            });
        }

        // Attach the event after the DataTable has been initialized
        $('#dataTableBuilder').on('draw.dt', function () {
            attachDeleteEvent();
        });

        // Initial attachment
        attachDeleteEvent();
    });
</script>
