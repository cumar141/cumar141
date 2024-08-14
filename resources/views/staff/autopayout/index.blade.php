@include('staff.layouts.header')
@include('staff.layouts.sidebar')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Transactions</title>
</head>

<body>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Inside your Blade view -->
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <!-- Inside your Blade view -->
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Failed transactions</h1>
                    </div>

                    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payments</h3>
        </div>
        <div class="card-body">
            {!! $dataTable->table(['class' => 'table data-table table-striped table-hover f-14 dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
@include('staff.layouts.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   $(document).ready(function() {
    // Ensure tooltips are activated for dynamically generated content
    function initializeTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Function to handle process request
    function processRequest(url, id, request) {
        showToastr("warning", "Are you sure?", request, true)
            .then((result) => {
                if (result.isConfirmed) {
                    $.get(url, { transaction: id })
                        .done(function(data) {
                            console.log(data);
                            showToastr(data.status, "Success", data.message);
                            $('.data-table').DataTable().ajax.reload(null, false); // false to keep current page
                        }).fail(function() {
                            showToastr("error", "Error", `Request failed, please contact developers! ${id}`);
                        });
                }
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
    $('body').on('click', '.retry', function() {
        let url = `{{ route('staff.autopayout.retry') }}?transaction=${$(this).data('id')}`;
        let request = "You're about to retry transaction, check transactions before confirmation.<br><span class='text-danger'>This could lead to double payment.</span>";
        processRequest(url, $(this).data('id'), request);
    });

    $('body').on('click', '.approve', function() {
        let url = `{{ route('staff.autopayout.approve') }}?transaction=${$(this).data('id')}`;
        let request = "You're about to approve transaction, check transactions before confirmation.<br><span class='text-danger'>This could lead to customer not receiving request payment.</span>";
        processRequest(url, $(this).data('id'), request);
    });

    $('body').on('click', '.block', function() {
        let url = `{{ route('staff.autopayout.block') }}?transaction=${$(this).data('id')}`;
        let request = "You're about to block and refund transaction, check transactions before confirmation.<br><span class='text-danger'>This could lead to customer being refunded and transaction blocked</span>";
        processRequest(url, $(this).data('id'), request);
    });

    // Initialize tooltips for the first time
    initializeTooltips();

    // Ensure tooltips are re-initialized every time the table is redrawn
    $('.data-table').on('draw.dt', function() {
        initializeTooltips();
    });
});
</script>
 {{ $dataTable->scripts() }}