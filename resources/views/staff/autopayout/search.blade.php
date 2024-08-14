@include('staff.layouts.header')
@include('staff.layouts.sidebar')

<style>
    .form-outline {
        display: inline-block;
        width: 100%;
        max-width: 500px;
    }

    .form-outline input {
        width: 100%;
        display: inline-block;
        margin: 0;
    }

    .btn-primary {
        margin-left: 10px;
    }

    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .table-responsive {
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }

    .action-buttons button {
        margin-right: 5px;
    }

    .form-inline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .form-inline .form-outline {
        flex: 1;
    }
</style>

<div class="row p-3">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Search Auto-payout</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Auto-payout</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center">
    <div class="form-outline mt-5">
        <form action="{{ route('search-autopayout') }}" method="post" class="form-inline">
            @csrf
            <input type="search" id="search" name="search_query" class="form-control"
                placeholder="Type Auto-payout Reference" aria-label="Search" />
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<div class="row p-3">
    <div class="col-12">
        <div class="card p-4 mt-5">
            @if($data->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Sender</th>
                                <th>Receiver</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->reference }}</td>
                                    <td>{{ $item->sender }}</td>
                                    <td>{{ $item->receiver }}</td>
                                    <td>{{ $item->amount }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->id }}">View</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>No results found.</p>
            @endif
        </div>
    </div>
</div>

@include('staff.layouts.footer')

<!-- Modals for each action -->
@foreach($data as $item)
    <!-- View Modal -->
    <div class="modal fade" id="viewModal{{ $item->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel{{ $item->id }}">View Auto-payout Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Session:</strong> {{ $item->session }}</p>
                    <p><strong>Reference:</strong> {{ $item->reference }}</p>
                    <p><strong>Trx Reference:</strong> {{ $item->trx_reference }}</p>
                    <p><strong>Receipt:</strong> {{ $item->receipt }}</p>
                    <p><strong>Sender:</strong> {{ $item->sender }}</p>
                    <p><strong>Receiver:</strong> {{ $item->receiver }}</p>
                    <p><strong>Cleared Amount:</strong> {{ $item->cleared_amount }}</p>
                    <p><strong>Amount:</strong> {{ $item->amount }}</p>
                    <p><strong>Rate:</strong> {{ $item->rate }}</p>
                    <p><strong>Fee:</strong> {{ $item->fee }}</p>
                    <p><strong>Platform:</strong> {{ $item->platform }}</p>
                    <p><strong>Payment Method:</strong> {{ $item->payment_method }}</p>
                    <p><strong>Partner:</strong> {{ $item->partner }}</p>
                    <p><strong>Misc:</strong> {{ $item->misc }}</p>
                    <p><strong>Being Processed:</strong> {{ $item->being_processed }}</p>
                    <p><strong>Status:</strong> {{ $item->status }}</p>
                    <p><strong>Attempts:</strong> {{ $item->attempts }}</p>
                    <p><strong>Created At:</strong> {{ $item->created_at }}</p>
                    <p><strong>Updated At:</strong> {{ $item->updated_at }}</p>
                    <p><strong>Received At:</strong> {{ $item->received_at }}</p>
                    <p><strong>Sent At:</strong> {{ $item->sent_at }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

   

@endforeach
