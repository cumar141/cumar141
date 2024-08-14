
<br>
<label class="f-14 fw-bold mb-1" for="currency">{{ __('Select Currency') }}</label><br>
<select id="currency" name="currency" class="form-control f-14"> 
    <option value="">{{ __('Select Currency') }}</option>
    @foreach($currencies as $currency)
        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
    @endforeach
</select>
<hr>
<div>
<label class="f-14 fw-bold mb-1" for="_status">{{ __('Transaction Status') }}</label><br>
<select name="_status" id="_status" class="form-control f-14">
    <option value="">{{ __('Select Status') }}</option>
    @foreach ($statuses as $status)
        <option value="{{ $status }}">{{ $status }}</option>
    @endforeach
</select>
<hr>
</div>

