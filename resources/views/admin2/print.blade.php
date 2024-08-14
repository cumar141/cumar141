@include('admin2.nav')
<style>
    
    table.table-bordered {
        border: none;
    }

  
    table.table-bordered td,
    table.table-bordered th {
        border: none;
    }
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row justify-content-center">

                <div class="col-lg-10"> 

                    <div class="card">
                        <div class="card-body">

                            <!-- Transaction details start -->
                            <table class="table ">
                                <tbody>
                                    <tr>
                                        <td class="px-30">
                                            <span class="text-sm">{{ __('Name') }}</span>
                                            <h2 class="text-lg">{{ getColumnValue($transactionDetails->user) }}</h2>
                                        </td>
                                        <td class="px-30 align-right">
                                            <span class="text-sm">{{ __('Transaction ID') }}</span>
                                            <h2 class="text-lg">{{ $transactionDetails->uuid }}</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-24">
                                            <span class="text-sm">{{ __('Currency') }}</span>
                                            <h2 class="text-lg">{{ getColumnValue($transactionDetails->currency, 'code', '') }}</h2>
                                        </td>
                                        <td class="py-24 align-right">
                                            <span class="text-sm">{{ __('Transaction Date') }}</span>
                                            <h2 class="text-lg">{{ dateFormat($transactionDetails->created_at) }}</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pxy-36 align-left">
                                            <span class="text-sm">{{ __('Status') }}</span>
                                            <h2 class="text-lg {{ getColor($transactionDetails->status) }}">{{ $transactionDetails->status }}</h2>
                                        </td>
                                        <td class="pxy-36 align-rigt">
                                            <span class="text-sm">{{ __('Deposited Amount') }}</span>
                                            <h2 class="text-lg">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</h2>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Transaction details end -->

                            <!-- Transaction amount start -->
                            <table class="table ">
                                <tbody>
                                    <tr>
                                        <td class="px-desc">
                                            <p class="desc-title">{{ __('Description') }}</p>
                                            <h2 class="text-lg {{ getColor($transactionDetails->note) }}">{{ $transactionDetails->note }}</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pt-10 align-center">
                                            <p class="text-md">{{ __('Sub Total') }}</p>
                                        </td>
                                        <td class="pt-10 align-center">
                                            <p class="text-md">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pb-5 align-center">
                                            <p class="text-md">{{ __('Fees') }}</p>
                                        </td>
                                        <td class="pb-5 align-center">
                                            <p class="text-md">{{ getmoneyFormatFee($transactionDetails) }}</p>
                                        </td>
                                    </tr>
                                   
                                </tbody>
                            </table>
                            <!-- Transaction amount end -->

                            <!-- Signatures section -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h4>Signatures:</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>Signature of: {{ $transactionDetails->user->first_name }} <br> _______________________</p>
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <p>Signature of: {{ $transactionDetails->end_user->first_name }}<br> _______________________</p>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Print button -->
                            <div class="text-center mt-3">
                                <button class="btn btn-primary d-print-none" onclick="window.print()">Print</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


@include('admin2.footer')


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

