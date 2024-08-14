<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ settings('name') }} Withdrawal Statement</title>
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
        <h1 class="text-3xl font-bold mb-4">{{ settings('name') }} Withdrawal Statement</h1>
        <span class="block mb-4">Statement of Account From {{ $sdate }} To {{ $edate }}</span>
        <hr class="mb-4">
        @if(!$allCustomer)
        <div class="container">
            <div class="left-content">
                <div class="mb-4">
                    <p class="mb-2">Account Number: {{ $user->formattedPhone }}</p>
                    <p class="mb-2">Account Name: {{ $user->full_name }}</p>
                </div>
            </div>
            <div class="right-content">
                <div class="mb-4">
                    @if (auth()->check())
                    <div class="mb-4">
                        <span class="mb-2">Printed By: {{ auth()->user()->full_name }}</span>
                    </div>
                    @endif
                    <p class="mb-2">Available Balance: {{ $walletBalance }}</p>
                </div>
            </div>
        </div>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Particulars</th>
                    @if($allCustomer == true)
                    <th>Account Info</th>
                    @endif
                    
                    <th>Amount</th>
                </tr>
            </thead> 
            <tbody>
                @php
                $totalAmount = 0;
                @endphp
                
                @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                    <td>{{ $transaction->uuid}}</td>
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
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} {{ $transaction->currency->name }} To
                                {{ optional($transaction->end_user)->formattedPhone }}
                            @elseif ($transaction->transaction_type_id == 4)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 5)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 6)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 7)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 8)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 9)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 10)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} from
                                {{ optional($transaction->end_user)->formattedPhone }}
                                {{ $transaction->currency->name }}
                            @elseif ($transaction->transaction_type_id == 12)
                                {{ str_replace('_', ' ', $transaction->transaction_type->name) }} {{ $transaction->currency->name }} from
                                {{ optional($transaction->end_user)->formattedPhone }} 
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
                        
                    @if($allCustomer == true)
                    <td>{{ $transaction->user->full_name }} - ({{ $transaction->user->formattedPhone ? $transaction->user->formattedPhone : 'Phone not found' }})</td>
                    @endif
                    <td class="rtl">{{ number_format(abs($transaction->total), 2) }}</td>
                </tr>
                
                @php
                $totalAmount += $transaction->total;
                @endphp
                
                @endforeach
                <!-- Total row -->
                <tr>
                    <td colspan="{{ $allCustomer ? 4 : 3 }}" class="rtl"><strong>Total</strong></td>
                    <td class="rtl"><strong>{{ number_format(abs($totalAmount), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

    </center>
</body>

</html>
