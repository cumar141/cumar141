{{-- for displaying all the users wallets --}}
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
            <div class="col-xl-12">
                <h1 id="message">Wallets Balances</h1>
                @if(empty($balances))
                    <p class="text-center">No wallet balances available.</p>
                @else
                    <div class="row">
                        @php $walletCount = 0; @endphp
                        @foreach($balances as $currencyCode => $balance)
                        <div class="col-md-6">
                            <div class="card mini-stats-wid wallet-card" data-currency-code="{{ $currencyCode }}">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">{{$currencyCode}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <h4 class="mb-0">{{number_format($balance,2)}}</h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                                <span class="avatar-title">
                                                    @switch($currencyCode)
                                                    @case('USD')
                                                    <i class="fa fa-usd font-size-24"></i>
                                                    @break
                                                    @case('GBP')
                                                    <i class="fa fa-gbp font-size-24"></i>

                                                    @break
                                                    @case('EUR')
                                                    <i class="fa fa-eur font-size-24"></i>

                                                    @break
                                                    @case('BTC')
                                                    <i class="fa fa-btc font-size-24"></i>

                                                    @break
                                                    
                                                    @default
                                                    <i class="fas fa-money-bill font-size-24"></i>

                                                    @endswitch
                                                    <input type="hidden" class="currency-code" value="{{$currencyCode}}">
                                                    
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php $walletCount++; @endphp
                        @if ($walletCount % 2 == 0)
                    </div>
                    <div class="row">
                        @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>




<!-- Popup Modal -->
<div id="walletDetailsModal" class="modal fade"  data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="walletDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walletDetailsModalLabel">Wallet Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>End User</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody">
                       
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id='modalClose' data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printWalletDetails()">Print</button>
            </div>
        </div>
    </div>
</div>
<!-- Print Content -->
<div id="printContent" style="display: none;">
    <div style="text-align: center;">
        <img src="https://pay.somxchange.com/public/uploads/logos/1703528117_logo.png" style="max-width: 200px; margin-bottom: 20px;">
    </div>
    <div style="text-align: right; margin-bottom: 20px;">Date: <span id="currentDate"></span></div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>End User</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody id="transactionsTableBodyPrint">
           
        </tbody>
    </table>
</div>

<!-- End Popup Modal -->

@include('staff.layouts.footer')

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<!-- Bootstrap JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>



<script>
$(document).ready(function() {
    $('.wallet-card').hover(function() {
        $(this).css('cursor', 'pointer');
    });

    $('.wallet-card').click(function() {
        var currencyCode = $(this).data('currency-code');
        $('#transactionsTableBody').empty(); 
        fetchAndDisplayTransactionDetails(currencyCode);
    });
});

function printWalletDetails() {
    // Populate print content
    var currentDate = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    $('#currentDate').text(currentDate);
    $('#transactionsTableBodyPrint').empty();
    $('#transactionsTableBody tr').each(function() {
        $('#transactionsTableBodyPrint').append($(this).clone());
    });

    // Print the content
    var printContents = document.getElementById('printContent').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}



function fetchAndDisplayTransactionDetails(currencyCode) {
    $.ajax({
        url: "{{ route('GetWalletTransactions') }}",
        data: {
            currencyCode: currencyCode
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (response.length > 0) {
              
                $.each(response, function(index, transaction) {
                    $('#transactionsTableBody').append('<tr>' +
                        '<td>' + transaction?.id + '</td>' +
                        '<td>' + transaction?.user?.first_name + '</td>' +
                        '<td>' + transaction?.end_user?.first_name + '</td>' +
                        '<td>' + transaction?.transaction_type?.name + '</td>' +
                        '<td>' + transaction?.subtotal + '</td>' +
                        '</tr>');
                });
                $('#walletDetailsModal').modal('show');
            } else {
            
                $('#transactionsTableBody').html('<tr><td colspan="4">No transactions found</td></tr>');
                $('#walletDetailsModal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

</script>