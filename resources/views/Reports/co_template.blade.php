<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ settings('name') }} Commission Statement</title>
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
                padding-bottom: 72px ;
            }
        }
    </style>
</head>

<body>
    <center>
        <img src="{{ image(settings('logo'),'logo') }}" style="width: 196px;">
        <h1 class="text-3xl font-bold mb-4">{{ settings('name') }} Commission Statement</h1>
        <span class="block mb-4">Statement of Account From {{$startDate}} To {{$endDate}} </span>
        <hr class="mb-4">
        <div class="container">
            @if (auth()->check())
            <div class="right-content">
                <!-- Right side content -->
                <div class="mb-4">
                    <span class="mb-2">Printed By: {{ auth()->user()->username }}</span>
                </div>
            </div>
            @endif
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Account Info</th>
                    <th>Transaction Particulars</th>
                    <th>Commission Amount</th>
                </tr>
            </thead>
            <tbody>
                @php 
                $totalCommission = 0;
                @endphp
                
                @foreach ($transactions as $transaction)
                @php
                $commission = round($transaction->charge_percentage, 2);
                @endphp
                @if ($commission > 0)
                <tr>
                    <td>{{ $transaction->created_at }}</td>
                    <td>{{ $transaction->uuid }}</td>
                    <td>{{ $transaction->user->full_name }} {{ $transaction->user->formattedPhone }}</td>
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
                                    {{ $transaction->currency->name }} from {{ $transaction->payment_method->name }}
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
                               @if (isset($transaction->end_user) && !empty($transaction->end_user->teller_uuid)  && $transaction->payment_method_id == 1)
                                  
                                    {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} from {{ settings('name') }} Teller:
                                    {{ $transaction->end_user->teller_uuid }}
                                   
                                @else
                                    {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                    {{ $transaction->currency->name }} from {{ $transaction->payment_method->name }}
                                    {{ $transaction->reference_number }}
                                @endif
                            @endif
                        </td>
                    <td class="rtl">{{ $commission }}</td>
                </tr>
                @endif
                
                @php
                $totalCommission += $commission;
                @endphp
                
                @endforeach
                <tr style="font-weight: bold;">
                    <td colspan="4" class="rtl">Total Commission</td>
                    <td class="rtl">{{ $totalCommission }}</td>
                </tr>
            </tbody>
        </table>

    </center>
</body>

</html>
