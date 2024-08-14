<?php
use App\Http\Helpers\Common;
use Illuminate\Support\Facades\Route;

//login Controller
Route::get('/logout', 'LoginController@logout')->name('staff.logout');
Route::post('authenticate', 'LoginController@login')->name('staff.authenticate');
Route::get('/', 'StaffController@showLoginForm')->name('staff.login');

Route::group(['middleware' => ['auth:staff']], function ()
{
    Route::get('/search-user', 'StaffController@searchUser')->name('search-user');
    // DashboardController
    Route::get('dashboard', 'DashboardController@dashboard')->name('staff.dashboard');
    Route::get('receipt', 'DashboardController@showReceipt')->name('staff.receipts');
    Route::get('SearchReceipt', 'DashboardController@searchReceipt')->name('SearchReceipt');
    //print the receipt Route 
    Route::get('printRoute/{id}', 'DashboardController@printRoute')->name('printRoute');
    Route::match(['GET', 'POST'], '/GetWalletTransactions', 'DashboardController@GetWalletTransactions')->name('GetWalletTransactions');
    // Route to display the dashboard selection form
    Route::get('/dashboard/select', 'DashboardController@showSelectionForm')->name('dashboard.select');
    
    // Route to handle form submission
    Route::post('/dashboard/select', 'DashboardController@selectDashboard');
    
    // ProfileController
    Route::get('/profile', 'profileController@showProfile')->name('profile');
    Route::get('wallets', 'profileController@wallets')->name('staff.myWallets');
    Route::get('/get-pending-deposits', 'profileController@getPendingDeposits')->name('get-pending-deposits');
    Route::post('change-password', 'profileController@changePassword')->name('staff.change-password');
    
    // DepositController
    Route::get('/showDeposit', 'DepositController@showDeposit')->name('showDeposit');
    Route::post('/createDeposit', 'DepositController@createDeposit')->name('createDeposit');
    
    // WithdrawalController
    Route::get('/Withdrawal', 'WithdrawalController@ShowWithdrawal')->name('Withdrawal');
    Route::post('/Withdraw/create', 'WithdrawalController@eachUserWithdrawSuccess')->name('CreateWithdraw');
    
    /*
    |--------------------------------------------------------------------------
    | Teller Withdrawal Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('teller')->group(function () {
        Route::get('/information', 'TellerDepositController@showTellerInfo')->name('showTellerInfo');
        Route::get('/show-deposit', 'TellerDepositController@showTellerDepositForm')->name('showTellerDepositForm');
        Route::post('/deposit', 'TellerDepositController@createDeposit')->name('createTellerDeposit');
        Route::get('/search', 'TellerDepositController@getTellerUser')->name('searchTellerDeposit');
        
        // TellerWithdraw Controller
        Route::get('/Withdrawal', 'TellerWithdrawController@index')->name('tellerWithdrawal');
        Route::post('/withdraw-teller', 'TellerWithdrawController@withdrawFromTeller')->name('withdrawFromTeller');
        Route::get('/search', 'TellerWithdrawController@searchTeller')->name('searchTeller');
        Route::get('/get-wallet', 'TellerWithdrawController@getWalletBalance')->name('getWalletBalance');
    });
    
    
    
    /*
    |--------------------------------------------------------------------------
    | Treasurer Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('treasure')->group(function () {
        // Create Money Form Route
        Route::get('/index', 'TreasurerController@showTreasurerCreateMoney')
            ->name('staff.treasurer.create_money_form');
    
        // Create Money Route
        Route::post('/create', 'TreasurerController@createMoney')
            ->name('staff.treasurer.create_money');
    
        // Show Managers Route
        Route::get('/managers', 'TreasurerController@showManagers')
            ->name('staff.treasurer.show_managers');
    
        // Show Branches Route
        Route::get('/branches', 'TreasurerController@showBranches')
            ->name('staff.treasurer.show_branches');
    
        // Show Money Form Route
        Route::get('/show', 'TreasurerController@showMoneyForm')
            ->name('show.money.form');
    });
    
    
    
    // TransferController 
    Route::post('/transfer', 'TransferController@transferMoney')->name('staff.treasurer.transfer_money');
    
    //Notifications Routes
    Route::get('/get-notifications', 'profileController@getNotifications')->name('get-notifications');
    
    
    //view More transaction when user Receiver notification
    Route::get('/requests', 'profileController@ViewMoreTransactions')->name('ViewMoreTransactions');
    //request
    
    /*
    |--------------------------------------------------------------------------
    | Request Routes
    |--------------------------------------------------------------------------
    */
    Route::controller(RequestController::class)->group(function () {
        // Get Pending Payment Count
        Route::get('/pending-payment', 'getPendingPaymentCount')->name('pending.payment.count');
    
        // Teller Request Money Routes
        Route::get('/teller-request', 'showTellerRequestForm')->name('tellerRequestMoney');
        Route::post('/teller-request', 'TellerRequestHandler')->name('TellerRequestHandler');
    
        // Manager Request Money Routes
        Route::get('/manager-request', 'showManagerRequestForm')->name('managerRequestMoney');
        Route::post('/manager-request', 'managerRequestHandler')->name('managerRequestHandler');
    
        // Handle Money Requests
        Route::post('/request', 'requestMoney')->name('staff.treasurer.request_money');
        Route::get('/pending-requests', 'PendingRequests')->name('PendingRequests');
        Route::post('/reject-request', 'rejectRequest')->name('rejectRequest');
        Route::post('/approve-request', 'approveRequest')->name('approveRequest');
    });
    
    /*
    |--------------------------------------------------------------------------
    | OTP Routes
    |--------------------------------------------------------------------------
    */
    Route::controller(OtpController::class)->group(function () {
        // Send OTP Route
        Route::match(['GET', 'POST'], '/send-otp', 'sendOtp')
            ->name('sendOtp');
    
        // Verify OTP Route
        Route::get('/verify-otp', 'verifyOtps')
            ->name('verifyOtps');
    
        // Single Print Route
        Route::get('/single-print', 'print')
            ->name('staff.print.singlePrint');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Manager Control Panel Routes
    |--------------------------------------------------------------------------
    */
    Route::controller(ManagerControlPanel::class)->group(function () {
        // Manager Control Panel Index Route
        Route::get('/withdrawal-all', 'index')->name('managerControlPanel');
        // Handle Transaction Route
        Route::post('/transactions', 'handleTransaction')->name('handleTransaction');
    });
    
    // Display all chosen managers' dashboards
    Route::get('/dashboard/manager/{id}', 'ManagerDashboardController@dashboard')->name('managerDashboard');
    
    //local admin dashboard
    Route::get('/admin', 'AdminController@index')->name('adminDashboard');
    
    // BulkDepositController
    Route::get('/deposit-all', 'BulkDepositController@showBulkDeposit')->name('bulkDeposit');
    // bulk.deposit.submit
    Route::post('/deposit-submit', 'BulkDepositController@bulkDepositSubmit')->name('bulk.deposit.submit');
    
    // Autopayout
    Route::prefix('autopayout')->group(function () {
        Route::get('failed', 'AutoPayoutController@failed')->name('staff.autopayout.failed');
        Route::get('retry', 'AutoPayoutController@retry')->name('staff.autopayout.retry');
        Route::get('approve', 'AutoPayoutController@approve')->name('staff.autopayout.approve');
        Route::get('block', 'AutoPayoutController@block')->name('staff.autopayout.block');
    });
    
    // Grouping routes under the 'users' prefix
    Route::prefix('users')->group(function () {
        Route::get('/', 'UserController@index')->name('staff.user.index');
        Route::get('/create', 'UserController@create')->name('staff.user.create');
        Route::post('/store', 'UserController@store')->name('staff.user.store');
        Route::get('/edit/{id}', 'UserController@edit')->name('staff.user.edit');
        Route::post('/update', 'UserController@update')->name('staff.user.update');
        Route::post('/checkPhone', 'UserController@checkPhone')->name('staff.user.checkPhone');
        Route::post('/checkEmail', 'UserController@checkEmail')->name('staff.user.checkEmail');
        Route::get('/tabs', 'UserController@displayTab')->name('displayTab');
        Route::get('/wallets/{id}', 'UserController@wallets')->name('staff.user.wallets');
        Route::get('/transactions/{id}', 'UserController@transactions')->name('staff.user.transactions');
    });
    
    // Grouping routes under the 'partner-balance' prefix
    Route::prefix('partner-balance')->group(function () {
        Route::get('/', 'PartnerBalanceController@index')->name('staff.partner-balance.index');
        Route::get('/create', 'PartnerBalanceController@create')->name('staff.partner-balance.create');
        Route::post('/store', 'PartnerBalanceController@store')->name('staff.partner-balance.store');
        Route::get('/edit/{id}', 'PartnerBalanceController@edit')->name('staff.partner-balance.edit');
        Route::post('/update/{id}', 'PartnerBalanceController@update')->name('staff.partner-balance.update');
        Route::get('/delete/{id}', 'PartnerBalanceController@destroy')->name('staff.partner-balance.delete');
    });
    
    // ReportsController
    Route::prefix('reports')->group(function () {
        Route::get('/', 'ReportController@myReports')->name('myReports');
        Route::get('/reports', 'ReportController@mr')->name('staff.reports.mr');
        Route::get('/treasurers', 'ReportController@treasurer')->name('staff.treasurer.accountant_treasurer');
        Route::get('/tellers', 'ReportController@managerTellerReport')->name('teller-report');
        Route::get('/teller', 'ReportController@generateTeller')->name('staff.reports.teller');
        Route::get('/treasurer', 'ReportController@generateTreasurer')->name('staff.reports.treasurer');
        Route::get('/manager', 'ReportController@generateManager')->name('staff.reports.manager');
        Route::get('/managers', 'ReportController@managerReport')->name('staff.manager');
        Route::get('/admin-reports', 'ReportController@index')->name('staff.admin_reports');
        Route::get('reports/params', 'ReportController@params')->name('staff.report.params');
        Route::get('reports/generate', 'ReportController@generate')->name('staff.report.generate');
    });
    
    //All transactions
    Route::prefix('transactions')->group(function () {
        
        // Transactions routes
        Route::get('/', 'TransactionController@allTransactions')->name('staff.transactions.all');
        Route::get('/edit/{id}', 'TransactionController@edit')->name('transactions.edit');
        Route::post('/update/{id}', 'TransactionController@update')->name('staff.transactions.update');
        
        
        //routes for dowloading pdf and excell for transactions
        Route::get('/csv', 'TransactionController@transactionCsv')->name('transactions.csv');
        Route::get('/pdf', 'TransactionController@transactionPdf')->name('transactions.pdf');
    });
});