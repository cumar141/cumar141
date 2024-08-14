<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Microfinance Statement of Account</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            justify-content: space-between;
            /* margin: 20px; */
        }

        .left-content {
            max-width: 100%;
        }

        .right-content {
            max-width: 100%;
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

        @media print {
            @page {
                margin-top: 0;
                margin-bottom: 0;
            }

            body {
                padding-top: 72px;
                padding-bottom: 72px;
            }
        }
    </style>
</head>

<body>
    <center>
        <img src="https://pay.somxchange.com/public/uploads/logos/1703528117_logo.png" style="width: 196px;">
        <h1 class="text-3xl font-bold mb-4">Somxchange Branch {{ $reportHolder }} Statement</h1>
        {{-- <span class="block mb-4">Statement of Account From {{ $startDate }} To {{ $endDate }}</span> --}}
        <br>
        <br>
        @foreach ($users as $user)
            

        <span class="block mb-4"><strong>{{ $reportHolder }}</strong></span> <br>
        <span class="block mb-4"><strong>Branch Name: {{ $user->branch->name }}</strong></span>
        {{-- <span class="block mb-4">Branch: {{ $branch->name }}</span> --}}
        <hr class="mb-4">
        <div class="container">
            <div class="left-content">
                <!-- Left side content -->
                <div class="mb-4">
                    <p class="mb-2">Account Number: {{ $user->formattedPhone }}</p>
                    <p class="mb-2">Account Name: {{ $user->first_name." ".$user->last_name }}</p>
                </div>
            </div>
            <div class="right-content">
                <!-- Right side content -->
                <div class="mb-4">
                    <div class="mb-4">
                        @if (auth()->check())
                        <span class="mb-2">Printed By: {{ auth()->user()->first_name }} {{auth()->user()->last_name }}</span>
                        @endif
                    </div>
                    {{-- <p class="mb-2">Available Balance: {{ isset($availlableBalance) ?
                        number_format($availlableBalance, 2) : '0' }}</p> --}}
                </div>
                {{-- opening balance --}}
                <div class="mb-4">
                    <p class="mb-2">Availlable Balance: {{ isset($availlableBalance) ? number_format($availlableBalance,
                        2) : '0' }}</p>
                </div>
            </div>
        </div>
        <table>
            @if($reportType == 'transactions')
                @if(isset($transactions) && count($transactions) > 0)
                <thead>
                    <tr>
                        <th>Transaction Date</th>
                        <th>Transaction ID</th>
                        <th>Particulars</th>
                        <th>Currency</th>
                        <th>Deposit</th>
                        <th>Withdrawal</th>
                    </tr>
                </thead> 
                <tbody>
                    @php
                    $totalDeposit = 0;
                    $totalWithdrawal = 0;
    
                    @endphp
                    @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at }}</td>
                        <td>{{ $transaction->uuid }}</td>
                        <td>
                            @if ($transaction->transaction_type_id == 4)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from {{ optional($transaction->end_user)->formattedPhone }} {{$transaction->currency->code}}
                            @elseif ($transaction->transaction_type_id == 7)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} From {{ optional($transaction->end_user)->formattedPhone }} {{$transaction->currency->code}}
                            @elseif ($transaction->transaction_type_id == 5)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}  {{$transaction->currency->code}} 
                            @elseif ($transaction->transaction_type_id == 1)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} US Dollar {{ optional($transaction->end_user)->formattedPhone }}
                            @elseif ($transaction->transaction_type_id == 12)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from {{ optional($transaction->end_user)->formattedPhone }} US Dollar
                            @elseif ($transaction->transaction_type_id == 3)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} To {{ optional($transaction->end_user)->formattedPhone }}
                            @elseif ($transaction->transaction_type_id == 6)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}  {{$transaction->currency->code}} 
                            @elseif ($transaction->transaction_type_id == 8)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}  From {{ optional($transaction->end_user)->formattedPhone }} {{$transaction->currency->code}}
                            @elseif ($transaction->transaction_type_id == 9)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}  From {{ optional($transaction->end_user)->formattedPhone }} {{$transaction->currency->code}}
                            @elseif ($transaction->transaction_type_id == 10)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}  From {{ optional($transaction->end_user)->formattedPhone }} {{$transaction->currency->code}}
                            @elseif ($transaction->transaction_type_id == 2) 
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} US Dollar From {{ optional($transaction->end_user)->formattedPhone }}
                            @endif
                        </td>
                        <td>{{ $transaction->currency->name }}</td>
                        <td>
                            @if ($transaction->total > 0)
                                {{ number_format($transaction->total, 2) }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if ($transaction->total < 0)
                                {{ number_format(abs($transaction->total), 2) }}
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    @php
                    $totalDeposit += $transaction->total > 0 ? $transaction->total : 0;
                    $totalWithdrawal += $transaction->total < 0 ? abs($transaction->total) : 0;
                    @endphp
                    @endforeach
                    <tr style="font-weight: bold;">
                        <td colspan="4">Total</td>
                        <td>{{ number_format($totalDeposit, 2) }}</td>
                        <td>{{ number_format($totalWithdrawal, 2) }}</td>
                    </tr>
                </tbody>
            @endif
           
        </table>
        @endif
        @endforeach
    </center>
</body>

</html>