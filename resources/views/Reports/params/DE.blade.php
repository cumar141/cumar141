<label class="f-14 fw-bold mb-1" for="singall">{{ __('Select Type') }}</label><br>
<select name="singall" id="singall" class="form-control f-14">
    <option disabled selected>Select Option</option>
    <option value="All">All Customer</option>
    <option value="Single">Single Customer</option>
</select>
<hr>


<div id="phoneSection">
    <label class="f-14 fw-bold mb-1" for="phone">{{ __('Customer Phone') }}</label><br>
    <input id="phone" type="text" name="phone" class="form-control f-14" placeholder="+252XXXXXXXX"><br>
</div>
<hr>
<label class="f-14 fw-bold mb-1" for="phone">{{ __('Wallet') }}</label><br>
<select id="currency" name="currency" class="form-control f-14">
    <option disabled selected>{{ __('Select Currency') }}</option>
    @foreach($currencies as $currency)
    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
    @endforeach
</select>
<hr>

<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>
<script>
    // Update data attribute on form submission
    $("#transaction_form").on("submit", function () {
        $('#generate_report').data("phone", $("#customer_phone").val());
        // Add data for the currency if needed
        $('#generate_report').data("currency", $("#currency").val());
    });
    
    $(document).ready(function() {
        // Initial hide/show based on default value
        togglePhoneSection();

        // Listen for changes on #singall dropdown
        $("#singall").on("change", function() {
            togglePhoneSection();
        });

        // Function to toggle the visibility of the phone section
        function togglePhoneSection() {
            var selectedOption = $("#singall").val();
            if (selectedOption === "All" || selectedOption === null) {
                $("#phoneSection").hide();
                // Clear and set the phone input to null
                $("#phone").val(null);
            } else {
                $("#phoneSection").show();
            }
        }
    });
</script>