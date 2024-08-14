
{{-- drop down for selecting transaction Status --}}
<label class="f-14 fw-bold mb-1" for="_status">{{ __('Select Status') }}</label><br>
<select name="_status" id="_status" class="form-control f-14">
    <option value="">{{ __('Select Status') }}</option>
    <option value="all">{{ __('All') }}</option>
    <option value="-1">{{ __('Waiting Waafi Push') }}</option>
    <option value="1">{{ __('Payment Received') }}</option>
    <option value="2">{{ __('Payment Sent') }}</option>
    <option value="3">{{ __('Payment Failed') }}</option>
    <option value="4">{{ __('Payment Requires Attention') }}</option>
</select>

<hr>
<label class="f-14 fw-bold mb-1" for="partner">{{ __('Select Partner') }}</label><br>
<select name="partner" id="partner" class="form-control f-14">
    <option value="">{{ __('Select Partter') }}</option>
    <option value="all">{{ __('All') }}</option>
    {{-- 'SomXchange Wallet' --}}
    <option value="SomXchange Wallet">{{ __('SomXchange Wallet') }}</option>
    <option value="Hormuud (EVC Plus)">{{ __('Hormuud (EVC Plus)') }}</option>
    <option value="Hormuud (Reseller)">{{ __('Hormuud (Reseller)') }}</option>
    <option value="Premier Wallet">{{ __('Premier Wallet') }}</option>
    <option value="Somnet (Reseller)">{{ __('Somnet (Reseller)') }}</option>
    <option value="Somtel (E-Dahab)">{{ __('Somtel (E-Dahab)') }}</option>
    <option value="Yeel Wallet">{{ __('Yeel Wallet') }}</option>
    <option value="Amtel (MyCash)">{{ __('Amtel (MyCash)') }}</option>
    <option value="Somtel (Reseller)">{{ __('Somtel (Reseller)') }}</option>
    <option value="Kaafiye Plus">{{ __('Kaafiye Plus') }}</option>
    <option value="Anfac Plus">{{ __('Anfac Plus') }}</option>
    <option value="Anfac">{{ __('Anfac') }}</option>
    <option value="Telesom (Zaad)">{{ __('Telesom (Zaad)') }}</option>
    <option value="Mybank Wallet">{{ __('Mybank Wallet') }}</option>
    <option value="Paypal">{{ __('Paypal') }}</option>
</select>
<hr>

<label class="f-14 fw-bold mb-1" for="payment_method">{{ __('Select payment method') }}</label><br>
<select name="payment_method" id="payment_method" class="form-control f-14">
    <option disabled selected>Select payment method</option>
    <option value="all">All payment method</option>
    <option value="TOPUP">TOPUP</option>
    <option value="MOBILE">MOBILE</option>
    <option value="WALLET">WALLET</option>
</select>
<hr>
{{-- drop down for selecting currency --}}
<label class="f-14 fw-bold mb-1" for="platform">{{ __('Select platform') }}</label><br>
<select id="platform" name="platform" class="form-control f-14">
    <option value="">{{ __('Select platform') }}</option>
    <option value="all">{{ __('All') }}</option>
    <option value="EVC Plus">{{ __('EVC Plus') }}</option>
    <option value="eDahab">{{ __('eDahab') }}</option>
    <option value="PaySomX">{{ __('PaySomX') }}</option>
</select>
<br>
<hr>



<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>
