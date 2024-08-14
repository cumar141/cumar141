<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>{{ settings('name') }} Customer Statement</title>
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
        
        .rtl {
            text-align: right;
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
        <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
        <h1 class="text-3xl font-bold mb-4">{{ settings('name') }} Customer Statement</h1>
        <span class="block mb-4">Statement of Account From {{ $sdate }} To {{ $edate }}</span>
        <hr class="mb-4">
        <div class="container">
            <div class="left-content">
                <!-- Left side content -->
                <div class="mb-4">
                    <p class="mb-2">Account Number: {{ $user->formattedPhone }}</p>
                    <p class="mb-2">Account Name: {{ $user->full_name }}</p>
                </div>
            </div>
            <div class="right-content">
                <!-- Right side content -->
                <div class="mb-4">
                    @if (auth()->check())
                        <span class="mb-2">User: {{ auth()->user()->full_name }}}}
                        </span>
                    @endif
                    <p class="mb-2">Available Balance: {{ number_format($walletBalance, 2) }}</p>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Particulars</th>
                    <th>Deposit</th>
                    <th>Withdrawal</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <p class="mb-2">Opening Balance: {{ number_format($openingBalance, 2) }}</p>
            <tbody>
                @php

                    $totalDeposit = 0;
                    $totalWithdrawal = 0;
                @endphp
                {{-- {{dd($transactions)}} --}}
                @foreach ($transactions as $transaction)
                    @php
                        // Increment total deposit if transaction is positive
                        if ($transaction->total > 0) {
                            $totalDeposit += $transaction->total;
                        }

                        // Increment total withdrawal if transaction is negative
                        if ($transaction->total < 0) {
                            $totalWithdrawal += $transaction->total;
                        }

                    @endphp

                    <tr>
                        <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                        <td>{{ $transaction->uuid }}</td>
                        <td>
                            @if ($transaction->transaction_type_id == 1)
                                @if (isset($transaction->end_user) && !empty($transaction->end_user->teller_uuid)  && $transaction->payment_method_id == 1)
                                  
                                    {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} from {{ settings('name') }} Teller:
                                    {{ $transaction->end_user->teller_uuid }}
                                   
                                @else
                                    {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} from {{ $transaction->payment_method->name }}
                                    {{ $transaction->reference_number }}
                                @endif
                            @elseif ($transaction->transaction_type_id == 2)
                                @if (isset($transaction->end_user) && !empty($transaction->end_user->teller_uuid)  && $transaction->payment_method_id == 1)
                                   
                                        {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                        {{ $transaction->currency->name }} via {{ settings('name') }} Teller:
                                        {{ $transaction->end_user->teller_uuid }}
                                   
                                    
                                @else
                                     {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} via {{ $transaction->payment_method->name }}
                                    {{ $transaction->reference_number }}
                                @endif
                            @elseif ($transaction->transaction_type_id == 3)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} To
                                {{ optional($transaction->end_user)->formattedPhone }}
                            @elseif ($transaction->transaction_type_id == 4)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 5)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 6)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 7)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} From
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 8)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} From
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 9)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} From
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 10)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} From
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->code }}
                            @elseif ($transaction->transaction_type_id == 12)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }} US Dollar
                            @elseif ($transaction->transaction_type_id == 13)
                                    {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} for {{ $transaction->payment_method->name }}
                                    {{ $transaction->reference_number }}
                            @endif
                        </td>
                        <td class="p-2 rtl">
                            {{ $transaction->total > 0 ? str_replace('-', '', number_format($transaction->total, 2)) : '0.00' }}
                        </td>
                        <td class="p-2 rtl">
                            {{ $transaction->total < 0 ? str_replace('-', '', number_format($transaction->total, 2)) : '0.00' }}
                        </td>
                        <td class="p-2 rtl">
                            {{ str_replace('-', '', number_format($transaction->balance, 2)) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="rtl">Total</td>
                    <td class="rtl">{{ number_format(abs($totalDeposit), 2) }}</td>
                    <td class="rtl">{{ number_format(abs($totalWithdrawal), 2) }}</td>
                    <td class="rtl">{{ number_format(abs($transaction->balance), 2) }}</td>
                </tr>
            </tbody>
        </table>

    </center>
</body>

</html>
