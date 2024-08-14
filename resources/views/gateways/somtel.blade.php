
@extends('gateways.layouts.master')

@section('content')


    <div class="row">
        <div class="col-12">
            <div class="d-grid mt-3p">
                <button type="submit" class="btn btn-lg btn-primary" type="submit" id="somtel-button">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="somtelSubmitBtnText" class="px-1">{{ __('Pay with :x', ['x' => ucfirst($gateway)]) }}</span>
                </button>
            </div>
        </div>
    </div>


@endsection

@section('js')

<script src="{{ asset('public/dist/libraries/jquery-3.6.1/jquery-3.6.1.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<script>
    "use strict";
    var submitText = "{{ __('Submitting...') }}";
    var paymentUrl = "{{ route('gateway.confirm_payment')}}";
    var token = "{{ csrf_token() }}";
    var uuid = "{{ $uuid }}";
    var requiredText = "{{ __('This field is required.') }}";
    var redirect_url = "{{ $redirectUrl }}";
    var amount ="{{ $total }}";
    var currency_id = "{{ $currency_id }}";
    var payment_type = "{{ $payment_type }}";
    var payment_method_id ="{{ $payment_method }}";
    var transaction_type = "{{ $transaction_type }}";
    var params = '{{ $params }}';
    
    var data = { amount: {{$total}}, phone: {{auth()->user()->formattedPhone}}, uuid: "{{$uuid}}", _token: token};
    
    $("#somtel-button").on("click", function() {
        $.post("{{ route('payment-gateway.waafi-withdraw') }}", data,function(status, response) {
            if (status.success) {window.location = "{{ route('gateway.payment_verify', $gateway) }}"};
            window.location = "{{ route('gateway.payment_verify', $gateway) }}";
        });
    });

</script>



@endsection









