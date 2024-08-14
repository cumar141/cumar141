<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <script>
                    document.write(new Date().getFullYear())
                </script> © SOMXCHANGE | All rights reserved
            </div>
        </div>
    </div>
</footer>
</div>
<!-- end main content-->
</div>
</div>

<!-- JAVASCRIPT -->
<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
       
            // Function to fetch and update pending deposits
            function fetchPendingDeposits() {
                $.ajax({
                    url: "{{ route('get-pending-deposits') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // console.log(data);

                        if (data && data.count !== undefined && data.details !==
                            undefined) {

                                // console.log(data);
                            // Update notification count
                            $('#notification-count').text(data.count);

                            // Populate notification items
                            var dropdown = $(
                                '#page-header-notification-dropdown + .dropdown-menu');
                            dropdown.empty(); // Clear existing items

                            $.each(data.details, function(index, deposit) {
                                // console.log(deposit);
                                // Check if user and enduser properties exist
                                var userData = deposit.user ? deposit.user.first_name :
                                    'N/A';
                                var enduserData = deposit.end_user ? deposit
                                    .end_user.first_name : 'N/A';
                                    

                                    var notes = deposit.note ? deposit
                                    .note : 'N/A';
                                   
                                    var date = deposit.created_at ? new Date(deposit.created_at) : null;
                                    var formattedDate = date ? date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';

                                                                        dropdown.append(`
                                        <a class="dropdown-item show-deposit" href="#" 
                                        data-deposit-id="${deposit.uuid}" 
                                        data-deposit-amount="${deposit.total}"
                                        data-deposit-currency="${deposit.currency.code}"
                                        data-user="${userData}"
                                        data-date="${formattedDate}"
                                        data-enduser="${enduserData}"
                                        data-notes="${notes}">
                                            <i class="bx bx-info-circle font-size-16 align-middle me-1"></i>
                                            <span key="t-notification">Trans ID: ${deposit.uuid}, Amount: ${deposit.total}</span>
                                        </a>
                                    `);


                                // Set the value of currency_id input field inside the loop
                                $('#currency_id').val(deposit.currency_id);
                            });
                        } else {

                            console.warn('Empty or invalid data received from the server.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching pending deposits:', error);
                        $('#dropdown-menu dropdown-menu-end').hide();
                        // Display an error message to the user
                        console.log('Error fetching pending deposits. Please try again. ' +
                            error);
                    }
                });
            }

            // Call the fetchPendingDeposits function initially
            fetchPendingDeposits();

            // Set up an interval to fetch pending deposits periodically
            setInterval(fetchPendingDeposits, 5000); // Fetch every minute (adjust as needed)
        });






    document.getElementById('dropdown-menu-end').addEventListener('click', function() {
        // Replace 'your-route' with the actual route you want to navigate to
        window.location.href = 'ViewMoreTransactions';
    });




</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="{{ asset('public/staff/assets/libs/metismenu/metisMenu.min.js')}}"></script>
<script src="{{ asset('public/staff/assets/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ asset('public/staff/assets/libs/node-waves/waves.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>

<!-- apexcharts -->
<script src="{{ asset('public/staff/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

<!-- dashboard init -->
<!--<script src="{{ asset('public/staff/assets/js/pages/dashboard.init.js')}}"></script>-->

<!-- App js -->
<script src="{{ asset('public/staff/assets/js/app.js')}}"></script>
<script src="{{ asset('public/staff/toastr/toastr.min.js')}}"></script>
</body>


<!-- Mirrored from themesbrand.com/skote-mvc/layouts/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 19 Nov 2022 09:12:05 GMT -->

</html>