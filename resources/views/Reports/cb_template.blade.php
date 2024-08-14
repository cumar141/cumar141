<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ settings('name') }} Statement of Account</title>
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
        @if ($isAll == false)
            <img src="{{ image(settings('logo'), 'logo') }}" style="width: 196px;">
            <h1 class="text-3xl font-bold mb-4">{{ settings('name') }} Customer Balance Statement</h1>
            <span class="block mb-4">Statement of Account From {{ $startDate }} To {{ $endDate }} </span>
            <hr class="mb-4">
            <div class="container">
                <div class="left-content">
                    <!-- Left side content -->
                    <div class="mb-4">
                        <p class="mb-2">Account Number: {{ $user->full_name }}</p>
                        <p class="mb-2">Account Name: {{ $user->formattedPhone }}</p>
                    </div>
                </div>

                <div class="right-content">
                    <!-- Right side content -->
                    <div class="mb-4">
                        @if (auth()->check())
                            <span class="mb-2">User: {{ auth()->user()->full_name }}</span>
                        @endif

                    </div>
                </div>
            </div>
            <table>
                <thead>
                    <th>Customer name</th>
                    <th>Account Number</th>
                    @foreach ($wallets->unique() as $wallet)
                        <th> {{ $wallet->currency->code }} </th>
                    @endforeach
                </thead>
                <tbody>
                    <td>{{ $user->full_name }}</td>
                    <td> {{ $user->formattedPhone }} </td>
                    @foreach ($userDetails as $user)
                        @if (isset($user['balances']))
                            @foreach ($wallets->unique('currency_id') as $wallet)
                                <td class="rtl">
                                    @if ($user['balances'][$wallet->currency->id])
                                        {{ number_format($user['balances'][$wallet->currency->id], 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td>
                            @endforeach
                        @else
                            <td class="rtl">0.00</td>
                            <td class="rtl">0.00</td>
                        @endif
                    @endforeach

                </tbody>
            </table>
        @endif
        @if ($isAll == true)
            {{-- {{dd($userDetails)}} --}}
            <img src="https://pay.somxchange.com/public/uploads/logos/1703528117_logo.png" style="width: 196px;">
            <h1 class="text-3xl font-bold mb-4">Somxchange Customers Balance Statement</h1>
            <span class="block mb-4">Statement of Account From {{ $startDate }} To {{ $endDate }} </span>

            <div class="right-content">

                <div class="mb-4">
                    @if (auth()->check())
                        <span class="mb-2">User: {{ auth()->user()->username }} </span>
                    @endif
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Account Number</th>
                        @foreach ($wallets->unique('currency_id') as $wallet)
                            <th>{{ $wallet->currency ? $wallet->currency->code : 'N/A' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>



                    @foreach ($userDetails as $user)
                        @if (isset($user['balances']))
                            <tr>
                                <td>{{ $user['name'] }}</td>
                                <td>
                                    @if ($user['phone'])
                                        {{ $user['phone'] }}
                                    @else
                                        {{ 'No Phone Found' }}
                                    @endif
                                </td>
                                @foreach ($wallets->unique('currency_id') as $wallet)
                                    <td class="rtl">
                                        @if ($user['balances'][$wallet->currency->id] > 0)
                                            {{ number_format($user['balances'][$wallet->currency->id], 2) }}
                                        @else
                                            0.00
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach

                    <tr style="font-weight: bold;">
                        <td colspan="2" class="rtl">Grand Total</td>
                        @foreach ($wallets->unique('currency_id') as $wallet)
                            <td class="rtl">
                                {{ number_format(
                                    collect($userDetails)->sum(function ($user) use ($wallet) {
                                        return $user['balances'][$wallet->currency->id];
                                    }),
                                
                                    2,
                                ) }}
                            </td>
                        @endforeach
                    </tr>

                </tbody>
            </table>

        @endif

    </center>
</body>

</html>
