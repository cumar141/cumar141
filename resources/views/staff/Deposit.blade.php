<!-- resources/views/users/index.blade.php -->
@include('staff.layouts.header')
@include('staff.layouts.sidebar')
<style>
    .form-outline {
        display: inline-block;
    }

    .form-outline input {
        width: 300px;
        /* Set the width to your desired value */
        display: inline-block;
        margin: 0;
    }

    .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 6px 10px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-3px);
    }
</style>


<div class="main-content">
    <div class="page-content">
        <div class="container">

            <div class="row p-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Deposit Form </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                                <li class="breadcrumb-item active">user</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <div class="d-flex justify-content-center">
                <div class="form-outline mt-5">
                    <input type="search" id="search" name="search_query" class="form-control"
                        placeholder="Type phone" aria-label="Search" style="width: 500px;" />
                    <button type="button" class="btn  btn-primary" onclick="searchUser()">
                        <i class="fas fa-search"></i>
                    </button>


                </div>
            </div>

            <br>
            <div class="text-center text-danger">
                <span class="text-danger" id="error"></span>
            </div>

            <br>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- success message --}}
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

            <div class="alert alert-danger f-14 text-center mb-0 d-none" id="message" role="alert">
                <p><a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a></p>
                <p id="message"></p>
            </div>


            <!-- Depososit form -->

            <div class="card mt-4" id="cardFoam">
                <div class="card-header">
                    Deposit Form
                </div>
                <div class="card-body">
                    <form id="Deposit" action="{{ route('createDeposit')}}" method="post">
                        @csrf
                        <div id="errorMessage" style="display: none; color: red;"></div>
                        <input type="hidden" class="form-control " name="userID" id="userID">
            
                        <label for="currency" class="form-label">Currencies</label>
                        <select class="form-control" name="currency" id="currency">
                            <option value="">Select currency</option>
                            @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                            @endforeach
                        </select>
            
                        <label for="user_id" class="form-label">Customer</label>
                        <input type="text" class="form-control " required name="userName" id="userName">
            
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" required name="amount" placeholder="Amount">
            
                        <div class="mb-3">
                            <label for="userName" class="form-label">Note</label>
                            <input required type="text" id="note" name="note" class="form-control" placeholder="Note.." />
                        </div>
                            
                        <button type="button" onclick="validateForm()" class="btn btn-primary mt-2" id="btnCustomer">Deposit</button>
                    </form>
                </div>
            </div>
            
            
            
        </div>
    </div>
</div>
    <!-- Modal -->
    <div class="modal fade"  data-bs-backdrop="static" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display transfer details here -->
                <p><strong>Currency:</strong> <span id="modalCurrency"></span></p>
                <p><strong>Amount:</strong> <span id="modalAmount"></span></p>
                <p><strong>Description:</strong> <span id="modalDescription"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="submitPage()" id="confirmTransferBtn"
                    class="btn btn-primary">Confirm Transfer</button>
            </div>
        </div>
    </div>
</div>
        <!-- end modal -->

        @include('staff.layouts.footer')
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <script>
            $(document).ready(function() {
      
      
 
       
        $('#cardFoam').hide();
    });
    function searchUser() {
        var searchQuery = $('#search').val();

// If search is empty, return error
if (!searchQuery) {
    $('#error').text('This field is required');
    return;
}


    $.ajax({
        type: 'GET',
        url: "{{ route('search-user') }}",
        data: { searchQuery: searchQuery },
        success: function(response) {
            console.log(response);
  
            if (response && response.user && response.user.username) {
                // Update form field with the username
                $('#userName').val(response.user.username);
                $('#userID').val(response.user.id);

                $('#cardFoam').show();
              
                // Clear the error message
                $('#error').text('');
            } else {
                // Display an error message
                $('#cardFoam').hide()
                $('#error').text('User not found or missing username');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            // Handle errors
            if (xhr.status == 404) {
                $('#cardFoam').hide();
                $('#error').text('User not found');
            } else {
                $('#error').text('An error occurred while processing the request');
            }
        }
    });
}
function validateForm() {
 
        var currency = $('#currency').val();
        var amount = $('#amount').val();
        var note = $('#note').val();
        var userName = $('#userName').val();
      
        if (!currency || !amount || !note || !userName) { 
            var errorMessageDiv = $('#errorMessage');
            errorMessageDiv.text("Please fill in all fields.");
            errorMessageDiv.css('display', 'block');
            return false;
        }

        showConfirmationModal();
        return false;
    }

function showConfirmationModal() {
        var currency = $('#currency option:selected').text();
        var amount = $('#amount').val();
        var description = $('#note').val();
       
        $('#modalCurrency').text(currency);
        $('#modalAmount').text(amount);
        $('#modalDescription').text(description);

        $('#confirmationModal').modal('show'); 
    }
  function submitPage() {
    document.getElementById('Deposit').submit();
}


        </script>