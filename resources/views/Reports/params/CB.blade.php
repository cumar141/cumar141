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

<br>

<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
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