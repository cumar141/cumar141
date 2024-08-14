@include('admin2.nav')
<div class="container">
    <div class="row">
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
                        <form id="approve-form-{{ $transaction->id }}" action="{{ route('admin2.transaction.approve', ['id' => $transaction->id]) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <form id="reject-form-{{ $transaction->id }}" action="{{ route('admin2.transaction.reject', ['id' => $transaction->id]) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <td>{{ $transaction->created_at }}</td>
                        <td>{{ $transaction->uuid }}</td>
                        <td>
                            {{ $transaction->user->first_name }} {{ $transaction->user->last_name }} - {{ $transaction->user->formattedPhone }}
                        </td>
                        <td>{{ $transaction->currency->code}}</td>
                        <td>{{ $transaction->total }}</td>

                        <td>{{ $transaction->status }}</td>
                        <td>
                            <a href="{{ route('admin2.transaction.approve', ['id' => $transaction->id]) }}"  class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('approve-form-{{ $transaction->id }}').submit(); class="btn btn-success">Approve</a>
                            <a href="{{ route('admin2.transaction.reject', ['id' => $transaction->id]) }}"  class="btn btn-danger" onclick="event.preventDefault(); document.getElementById('reject-form-{{ $transaction->id }}').submit(); class="btn btn-success">Reject</a>
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
@include('admin2.footer')