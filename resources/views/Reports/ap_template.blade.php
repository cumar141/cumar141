<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ settings('name') }}  Auto Payout Statements</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            justify-content: space-evenly;
            margin: 20px;
        }

        .left-content {
            max-width: 48%;
        }

        .right-content {
            max-width: 48%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
        
        .rtl{
            text-align: right;
        }
                
        @media print {
            @page {
                margin-top: 0;
                margin-bottom: 0;
            }
            body {
                padding-top: 72px;
                padding-bottom: 72px ;
            }
        }
    </style>
</head>

<body>
    <center>
        <img src="{{ image(settings('logo'),'logo') }}" style="width: 196px;">
        <h1 class="text-3xl font-bold mb-4">{{ settings('name') }}  Auto Payout Statements</h1>
        <span class="block mb-4">Statements From {{ $startDate }} To {{ $endDate }}</span>
        <hr class="mb-4">
        <div class="mb-4">
            <div class="mb-4">
                @if (auth()->check())
                <span class="mb-2">Printed By: {{ auth()->user()->full_name }} </span>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Session</th>
                    <th>Reference</th>
                    <th>Trx Reference</th>
                    <th>Receipt</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Cleared Amount</th>
                    <th>Amount</th>
                    <th>Rate</th>
                    <th>Fee</th>
                    <th>Platform</th>
                    <th>Payment Method</th>
                    <th>Partner</th>
                    <th>Misc</th>
                    <th>Being Processed</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Received At</th>
                    <th>Sent At</th>

                </tr>
            </thead> 
            <tbody>
                @foreach($ussdPayments as $ussdPayment)
                <tr>
                    <td>{{ $ussdPayment->session }}</td>
                    <td>{{ $ussdPayment->reference }}</td>
                    <td>{{ $ussdPayment->trx_reference }}</td>
                    <td>{{ $ussdPayment->receipt }}</td>
                    <td>{{ $ussdPayment->sender }}</td>
                    <td>{{ $ussdPayment->receiver }}</td>
                    <td class="rtl">{{ $ussdPayment->cleared_amount }}</td>
                    <td class="rtl">{{ $ussdPayment->amount }}</td>
                    <td class="rtl">{{ $ussdPayment->rate }}</td>
                    <td class="rtl">{{ $ussdPayment->fee }}</td>
                    <td>{{ $ussdPayment->platform }}</td>
                    <td>{{ $ussdPayment->payment_method }}</td>
                    <td>{{ $ussdPayment->partner }}</td>
                    <td>{{ $ussdPayment->misc }}</td>
                    <td>{{ $ussdPayment->being_processed }}</td>
                    <td>{{ $ussdPayment->status }}</td>
                    <td>{{ $ussdPayment->attempts }}</td>
                    <td>{{ $ussdPayment->created_at }}</td>
                    <td>{{ $ussdPayment->updated_at }}</td>
                    <td>{{ $ussdPayment->received_at }}</td>
                    <td>{{ $ussdPayment->sent_at }}</td>
                </tr>
                @endforeach

                @if(!isset($ussdPayments) || count($ussdPayments) == 0)
                <tr>
                    <td colspan="21" class="text-center">No records found</td>
                </tr>
                @endif

            </tbody>
        </table>
    </center>
</body>
</html>