<label class="f-14 fw-bold mb-1" for="phone">{{ __('Select Single or All Customers') }}</label><br>
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

{{-- drop down for selecting transaction Status --}}
<div>
    <label class="f-14 fw-bold mb-1" for="_status">{{ __('Transaction Status') }}</label><br>
    <select name="_status" id="_status" class="form-control f-14">
        <option disabled selected>{{ __('Select Status') }}</option>
        @foreach ($statuses as $status)
            <option value="{{ $status }}">{{ $status }}</option>
        @endforeach
    </select>
</div>
<hr>
{{-- drop down for selecting currency --}}
<label class="f-14 fw-bold mb-1" for="currency">{{ __('Select Currency') }}</label><br>
<select id="currency" name="currency" class="form-control f-14">
    <option disabled selected>{{ __('Select Currency') }}</option>
    @foreach ($currencies as $currency)
        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
    @endforeach
</select>
<br>

<hr>


<script>
    $(document).ready(function() {


        // Initial hide/show based on default value
        $('#phoneSection').hide();

        // Listen for changes on #singall dropdown
        $("#singall").on("change", function() {
            togglePhoneSection();
        });

        // Function to toggle the visibility of the phone section
        function togglePhoneSection() {
            var selectedOption = $("#singall").val();
            if (selectedOption === "All") {
                $("#phoneSection").hide();
                // Clear and set the phone input to null
                $("#phone").val(null);
            } else {
                $("#phoneSection").show();
            }
        }
    });
</script>
