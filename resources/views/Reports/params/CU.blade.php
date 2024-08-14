<label class="f-14 fw-bold mb-1" for="phone">{{ __('Customer Phone') }}</label><br>
<input id="phone" type="text" name="phone" class="form-control f-14" placeholder="+252XXXXXXXX"><br>
<label class="f-14 fw-bold mb-1" for="phone">{{ __('Wallet') }}</label><br>
<select id="currency" name="currency" class="form-control f-14"> 
    <option value="">{{ __('Select Currency') }}</option>
    @foreach($currencies as $currency)
        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
    @endforeach
</select>
<br>
<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>
<script>
    // Update data attribute on form submission
    $("#transaction_form").on("submit", function () {
        $('#generate_report').data("phone", $("#customer_phone").val());
        // Add data for the currency if needed
        $('#generate_report').data("currency", $("#currency").val());
    });
</script>