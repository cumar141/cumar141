@include('staff.layouts.header')
@include('staff.layouts.sidebar')
@php

    //$loggedUserRoles = auth()->guard('staff')->user()->role->name;
    $userRole = optional($transactionDetails->user)->role->name;
    $endUserRole = optional($transactionDetails->end_user)->role->name;
    $transactionTypeId = $transactionDetails->transaction_type_id;

    $reportTitle = str_replace('_', ' ', $transactionDetails->transaction_type->name) ?? 'Transaction Report';

   $reportTitles = [
    1 => [
        'Treasurer' => 'Self Deposit',
        'Manager' => 'Manager To Treasurer',
        'Teller' => 'Manager To Teller',
    ],
    2 => [
        'Treasurer' => 'Treasurer To Manager',
        'Manager' => 'Manager To Treasurer',
        'Teller' => 'Manager To Teller',
    ],
    3 => [
        'Treasurer' => 'Treasurer To Manager',
        'Manager' => 'Manager To Teller',
        'Teller' => 'Teller To Manager',
    ],
    4 => [
         'Treasurer' => 'Manager To Treasurer',
        'Manager' => 'Teller To Manager',
        'Teller' => 'Manager To Teller',
    ],
    5 => [
        'Treasurer' => 'Treasurer Type 5', 
        'Manager' => 'Manager Type 5',
        'Teller' => 'Teller Type 5',
    ],
    6 => [
        'Treasurer' => 'Treasurer Type 6',
        'Manager' => 'Manager Type 6',
        'Teller' => 'Teller Type 6',
    ],
    7 => [
        'Treasurer' => 'Manager To Treasurer',
        'Manager' => 'Treasurer To Manager',
        'Teller' => 'Manager To Teller',
    ],
    8 => [
        'Treasurer' => 'Treasurer To Manager',
        'Manager' => 'Manager To Teller',
        'Teller' => 'Teller To Manager',
    ],
    9 => [
        'Treasurer' => 'Treasurer Type 9',
        'Manager' => 'Manager Type 9',
        'Teller' => 'Teller Type 9',
    ],
    10 => [
        'Treasurer' => 'Treasurer Type 10',
        'Manager' => 'Manager Type 10',
        'Teller' => 'Teller Type 10',
    ],
    11 => [
        'Treasurer' => 'Treasurer Type 11',
        'Manager' => 'Manager Type 11',
        'Teller' => 'Teller Type 11',
    ],
    12 => [
        'Treasurer' => 'Treasurer Type 12',
        'Manager' => 'Manager Type 12',
        'Teller' => 'Teller Type 12',
    ],
    13 => [
        'Treasurer' => 'Treasurer Type 13',
        'Manager' => 'Manager Type 13',
        'Teller' => 'Teller Type 13',
    ],
];

// Set the default report title
//$reportTitle = 'Receipt';

// Check if the transaction type and user role are defined in the array
//if (isset($reportTitles[$transactionTypeId])) {
   // if (isset($reportTitles[$transactionTypeId][$loggedUserRoles])) {
    //    $reportTitle = $reportTitles[$transactionTypeId][$loggedUserRoles];
    //}
//}
@endphp



<style>
    /* Add this CSS to hide table borders */
    table.table-bordered {
        border: none;
    }

    /* Optional: If you want to remove borders from table cells as well */
    table.table-bordered td,
    table.table-bordered th {
        border: none;
    }

    .myDIV {
        width: 100%;
        border: 2px solid;
        border-style: dashed;
    }
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="rounded  p-2 ">
                <div class="d-flex flex-row justify-content-between">
                    <h4 class=""> {{ isset($reportTitle) ? $reportTitle : 'Transaction' }} </h4>
                    <h5
                        class="@if ($transactionDetails->status == 'Success') text-success 
           @elseif($transactionDetails->status == 'Pending') text-warning 
           @elseif($transactionDetails->status == 'Blocked') text-danger @endif">
                        {{ $transactionDetails->status }}
                    </h5>
                </div>
                <div class="row ms-5">
                    Date: [{{ $transactionDetails->created_at->format('d/m/Y H:i:s') }} ]
                </div>
                <div class="d-flex flex-row justify-content-between">
                    <div class="">
                        Transaction Id: &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; [{{ $transactionDetails->uuid }}]
                    </div>
                    <div class="col-span-1">
                        Ref: [{{ $transactionDetails->uuid }}]
                    </div>
                </div>
                <div class="row">
                    <h5 class="">Sender Information</h5>
                </div>
                <div class="row">
                    @if (in_array($transactionDetails->transaction_type_id, [Deposit, Received]) && !empty($transactionDetails->end_user))
                    <div class="col-span-1">
                        Sender Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->end_user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Sender Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->end_user->formattedPhone }}
                    </div>
                    @else
                    <div class="col-span-1">
                        Sender Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Sender Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->formattedPhone }}
                    </div>
                    @endif
                </div>
                <div class="row">
                    <h5 class="">Receiver Information</h5>
                </div>
                <div class="row">
                    @if (in_array($transactionDetails->transaction_type_id, [Deposit, Received]) && !empty($transactionDetails->end_user))
                    <div class="col-span-1">
                        Receiver Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Receiver Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->formattedPhone }}
                    </div>
                    @else
                    <div class="col-span-1">
                        Receiver Name: &nbsp; &nbsp;
                        &nbsp;&nbsp;{{ optional($transactionDetails->end_user)->full_name }}
                    </div>
                    <div class="col-span-1">
                        Receiver Phone: &nbsp; &nbsp;
                        &nbsp;&nbsp;{{ optional($transactionDetails->end_user)->formattedPhone }}
                    </div>
                    @endif
                </div>

                <div class="d-flex flex-row justify-content-between">
                    <div>Amount:</div>
                    <div>Total Amount:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->subtotal), 2) }}
                    </div>
                    <div>Comm:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->charge_fixed), 2) }}
                    </div>
                    <div>Net Amount:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->total), 2) }}
                    </div>
                </div>
                <div class="row">
                <div>
                    Description: {{ $transactionDetails->note }}
                </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex flex-row justify-content-between justify-items-center">
                        <div class="flex-col">
                            <div>
                                <p>_______________</p>
                            </div>
                            <div>
                                <p> Sender Signature</p>
                            </div>
                        </div>
                        <div> &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</div>
                        <div class="flex-col">
                            <div>
                                <p>__________________</p>
                            </div>
                            <div>
                                <p> Receiver Signature</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2 myDIV">
                    <h3 class=" text-center">Terms & Conditions</h3>
                    <p class=" text-center">1. {{ settings('name') }} Will not take any responsibilty for delays or
                        delivery failure as a result of mistake in Information.</p>
                    <p class=" text-center">2. In case of cancellation will be refunded at the market rate with
                        cancellation fee.</p>
                </div>
                <div id="myDIV" class="mb-4 mt-4 myDIV h-1 w-100"> </div>
                <div class="d-flex flex-row justify-content-between">
                    <h4 class=""> {{ isset($reportTitle) ? $reportTitle : 'Transaction' }} </h4>
                    <h5
                        class="@if ($transactionDetails->status == 'Success') text-success 
           @elseif($transactionDetails->status == 'Pending') text-warning 
           @elseif($transactionDetails->status == 'Blocked') text-danger @endif">
                        {{ $transactionDetails->status }}
                    </h5>
                </div>
                <div class="row ms-5">
                    Date: [{{ date('d/m/Y') }} ]
                </div>
                <div class="d-flex flex-row justify-content-between">
                    <div class="">
                        Transaction Id: &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; [{{ $transactionDetails->uuid }}]
                    </div>
                    <div class="col-span-1">
                        Ref: [{{ $transactionDetails->uuid }}]
                    </div>
                </div>
                <div class="row">
                    <h5 class="">Sender Information</h5>
                </div>
                <div class="row">
                    @if (in_array($transactionDetails->transaction_type_id, [Deposit, Received]) && !empty($transactionDetails->end_user))
                    <div class="col-span-1">
                        Sender Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->end_user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Sender Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->end_user->formattedPhone }}
                    </div>
                    @else
                    <div class="col-span-1">
                        Sender Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Sender Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->formattedPhone }}
                    </div>
                    @endif
                </div>
                <div class="row">
                    <h5 class="">Receiver Information</h5>
                </div>
                <div class="row">
                    @if (in_array($transactionDetails->transaction_type_id, [Deposit, Received]) && !empty($transactionDetails->end_user))
                    <div class="col-span-1">
                        Receiver Name: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->full_name }}
                    </div>
                    <div class="col-span-1">
                        Receiver Phone: &nbsp; &nbsp; &nbsp;&nbsp;{{ $transactionDetails->user->formattedPhone }}
                    </div>
                    @else
                    <div class="col-span-1">
                        Receiver Name: &nbsp; &nbsp;
                        &nbsp;&nbsp;{{ optional($transactionDetails->end_user)->full_name }}
                    </div>
                    <div class="col-span-1">
                        Receiver Phone: &nbsp; &nbsp;
                        &nbsp;&nbsp;{{ optional($transactionDetails->end_user)->formattedPhone }}
                    </div>
                    @endif
                </div>
                <div class="d-flex flex-row justify-content-between">
                    <div>Amount:</div>
                    <div>Total Amount:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->subtotal), 2) }}
                    </div>
                    <div>Comm:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->charge_fixed), 2) }}
                    </div>
                    <div>Net Amount:
                        {{ $transactionDetails->currency->symbol ?? '$' }}{{ number_format(abs($transactionDetails->total), 2) }}
                    </div>
                </div>
                <div class="row">
                    <div>
                    Description: {{ $transactionDetails->note }}
                </div>
                </div>
                <div class="row mt-3">
                    <div class="d-flex flex-row justify-content-between ">
                        <div class="flex-col">
                            <div>
                                <p>_______________</p>
                            </div>
                            <div>
                                <p> Sender Signature</p>
                            </div>
                        </div>
                        <div> &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</div>
                        <div class="flex-col">
                            <div>
                                <p>__________________</p>
                            </div>
                            <div>
                                <p> Receiver Signature</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2 myDIV">
                    <h3 class=" text-center">Terms & Conditions</h3>
                    <p class=" text-center">1. {{ settings('name') }} Will not take any responsibilty for delays or
                        delivery failure as a result of mistake in Information.</p>
                    <p class=" text-center">2. In case of cancellation will be refunded at the market rate with
                        cancellation fee.</p>
                </div>
            </div>

            <div class="d-print-none mt-4">
                <button class="btn btn-primary" onclick="window.print()">Print</button>
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
