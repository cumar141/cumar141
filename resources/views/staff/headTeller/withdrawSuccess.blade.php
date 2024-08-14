@include('staff.layouts.header')
@include('staff.layouts.sidebar')

<div class="main-content">
    <div class="page-content">
        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-8">
                    <!-- Logo for printing -->
                    <img src="https://pay.somxchange.com/public/uploads/logos/1703528117_logo.png"
                        style="width: 196px; display: none;" class="print-logo">

                    <div class="card">
                        <div class="card-body">
                            <table class="table table-borderless table-padding">
                                <tbody>
                                    <!-- Transaction details -->
                                    <tr>
                                        <td>
                                            <span class="text-sm">{{ __('Withdrawal With') }}</span>
                                            <h2 class="text-lg">{{ optional($transactionDetails->payment_method)->name
                                                == 'Mts' ? settings('name') :
                                                optional($transactionDetails->payment_method)->name }}</h2>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-sm">{{ __('Transaction ID') }}</span>
                                            <h2 class="text-lg">{{ $transactionDetails->uuid }}</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="text-sm">{{ __('Currency') }}</span>
                                            <h2 class="text-lg">{{ $transactionDetails->currency?->code }}</h2>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-sm">{{ __('Transaction Date') }}</span>
                                            <h2 class="text-lg">{{ dateFormat($transactionDetails->created_at) }}</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="text-sm">{{ __('Status') }}</span>
                                            <h2 class="text-lg {{ getColor($transactionDetails->status) }}">{{
                                                $transactionDetails->status }}</h2>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-sm">{{ __('Withdrawal Amount') }}</span>
                                            <h2 class="text-lg">{{
                                                moneyFormat(optional($transactionDetails->currency)->symbol,
                                                formatNumber($transactionDetails->subtotal,
                                                $transactionDetails->currency_id)) }}</h2>
                                        </td>
                                    </tr>

                                    <!-- Transaction amount -->
                                    <tr>
                                        <td colspan="2">
                                            <p class="desc-title">{{ __('Description') }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">
                                            <p class="text-md">{{ __('Sub Total') }}</p>
                                            <p class="text-md">{{
                                                moneyFormat(optional($transactionDetails->currency)->symbol,
                                                formatNumber($transactionDetails->subtotal,
                                                $transactionDetails->currency_id)) }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-md">{{ __('Fees') }}</p>
                                            <p class="text-md">{{ getmoneyFormatFee($transactionDetails) }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <hr class="hr-style">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <p class="text-md">{{ __('Total') }}</p>
                                            <p class="text-md">{{
                                                moneyFormat(optional($transactionDetails->currency)->symbol,
                                                formatNumber( str_replace('-', '', $transactionDetails->total),
                                                $transactionDetails->currency_id)) }}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-primary " onclick="window.print()">Print</button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@include('staff.layouts.footer')
<style>
    /* Show logo only when printing */
    @media print {
        .print-logo {
            display: block !important;
            position: relative;
            margin: 20px auto;
        }

        /* Hide print button when printing */
        .d-print-none {
            display: none !important;
        }

        @page {
            margin-top: 0;
            margin-bottom: 0;
        }

        body {
            padding-top: 72px;
            padding-bottom: 72px;
        }
    }

    /* Style for the hr element */
    .hr-style {
        border-top: 1px solid #000000;
    }

    /* Add padding to the table */
    .table-padding {
        padding: 20px;
    }

    /* Center the entire page when printing */
    body {
        margin: 0 auto;
    }

    /* Hide URL and page header */
    .page-header,
    .page-url {
        display: none !important;
    }
</style>