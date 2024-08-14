@include('admin2.nav')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-muted">Dashboard</h3>
        </div>



        {{-- display all pending transaction --}}
        <div class="col-md-12">
            <h5>Pending Treasurer Transaction</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Transaction Date</th>
                        <th>Transaction ID</th>
                        <th>Account Info</th>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($transactions) && count($transactions) > 0)
                    @foreach($transactions as $transaction)
                    <tr>
                        <form id="approve-form-{{ $transaction->id }}"
                            action="{{ route('admin2.transaction.approve', ['id' => $transaction->id]) }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                        <form id="reject-form-{{ $transaction->id }}"
                            action="{{ route('admin2.transaction.reject', ['id' => $transaction->id]) }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                        <td>{{ $transaction->created_at }}</td>
                        <td>{{ $transaction->uuid }}</td>
                        <td>
                            {{ $transaction->user->first_name }} {{ $transaction->user->last_name }} -
                            {{ $transaction->user->formattedPhone }}
                        </td>
                        <td>{{ $transaction->currency->code}}</td>
                        <td>{{ number_format($transaction->total, 2) }}</td>

                        <td>{{ $transaction->status }}</td>
                        <td>
                            <a href="#" class="btn btn-primary"
                                onclick="if(confirmApprove()) {event.preventDefault(); document.getElementById('approve-form-{{ $transaction->id }}').submit();}">Approve</a>
                            <a href="#" class="btn btn-danger"
                                onclick="if(confirmReject()) {event.preventDefault(); document.getElementById('reject-form-{{ $transaction->id }}').submit();}">Reject</a>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7">No pending transaction</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

@include('admin2.footer')
{{-- confirm form before submit --}}
<script>
    function confirmApprove() {
        return confirm('Are you sure you want to approve this transaction?');
    }

    function confirmReject() {
        return confirm('Are you sure you want to reject this transaction?');
    }
    $('#dashboard').css("border-bottom", '1px solid blue');
</script>