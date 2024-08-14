@extends('admin.layouts.master')

@section('title', __('Deposit Success'))

@section('page_content')

<div class="text-center"> 
    <h3 class="f-24">{{ $name }}</h3>

    <div class="box mt-20"> 
        <div class="box-body">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="confirm-btns"><i class="fa fa-check f-14"></i></div>
                    <div class="f-24 text-success mt-2">{{ __('Success') }}!</div>
                    <div class="f-14 mt-2"><p class="mb-0"><strong>{{ __('Deposit Completed Successfully') }}</strong></p></div>
                    <h5 class="f-14 mt-1">{{ __('Deposit Amount') }} : {{ moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['subtotal'], $transInfo['currency_id'])) }}</h5>
                </div>
            </div>

            <div>
                <a href="{{ url(config('adminPrefix')."/users/deposit/print/".$transInfo['id'])}}" target="_blank" class="btn button-secondary"><strong class="f-14">{{ __('Print') }}</strong></a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')
<script type="text/javascript">
</script>
@endpush
