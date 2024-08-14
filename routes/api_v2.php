<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', 'LoginController@login');
Route::post('device-verify', 'LoginController@verifyDevice'); //<------THIS IS VERIFYING DEVICE
Route::post('registration', 'RegistrationController@registration');
Route::post('duplicate-email-check', 'RegistrationController@checkDuplicateEmail');
Route::post('duplicate-phone-number-check', 'RegistrationController@checkDuplicatePhoneNumber');
Route::post('phone-check', 'RegistrationController@phoneCheck');
Route::get('default-country-short-name', 'CountryController@getDefaultCountryShortName');
Route::get('countries', 'CountryController@list');
Route::get('user-types', 'PreferenceController@userRoles');

Route::post('forget-password', 'ForgotPasswordController@forgetPassword');
Route::post('forget-password/verify', 'ForgotPasswordController@verifyResetCode');
Route::post('forget-password/store', 'ForgotPasswordController@confirmNewPassword'); //FG forget-password/confirm ayaa ka belay appka waa shaqeyn wayey logs marki aan eegay wan argay in u wacaayo endpoint forget-password/store hada waa shaqeynaa

Route::get('get-banner', 'BannerController@get_customer_banner');   //<------THIS ROUTE MOVED FROM DEV.SOMXCHANGE.COM
Route::get('linked-website', 'BannerController@linked_website');    //<------THIS ROUTE MOVED FROM DEV.SOMXCHANGE.COM

Route::post('bulk-pay', "BulkPaymentController@process");

Route::post('ussd3', "ussd3Controller@ussd");
/**
 * OTP routes
 */
Route::group(['prefix' => 'otp'], function() {
    Route::post('send', 'OTPController@send'); //<------THIS IS ROUTE FOR SMS OTP
    Route::post('verify', 'OTPController@verify'); //<------THIS IS ROUTE FOR OTP VERIFICATION
});

/**
 * USSD routes 
 */
Route::group(['prefix' => 'ussd'], function () { 
    Route::post('register-customer', 'USSDController@register_customer'); 
    Route::post('check-balance', 'USSDController@check_balance');
    Route::post('get-service-charge', 'USSDController@get_service_charge');
    Route::post('change-pin', 'USSDController@change_pin');
    Route::post('withdraw', 'USSDController@withdraw');
    Route::post('transfer', 'USSDController@transfer');
    Route::post('merchant-payment', 'USSDController@merchant_payment');
    Route::post('waafi-show', 'USSDController@waafi_show');
    Route::post('send-sms', 'USSDController@send_sms');
});

/**
 * Preference routes
 */
Route::group(['prefix' => 'preference'], function () {
    Route::get('/', 'PreferenceController@preferenceSettings');
    Route::get('custom', 'PreferenceController@customSetting');
    Route::get('check-login-via', 'PreferenceController@checkLoginVia');
    Route::get('check-processed-by', 'PreferenceController@checkProcessedByApi');
    Route::get('faq', 'PreferenceController@faq');  //<------ADDED THIS LINE FOR FAQ
    Route::get('pages', 'PreferenceController@pages');  //<------ADDED THIS LINE FOR ABOUT, TERMS AND CONDITION, PRIVANCY POLICY
    
});

Route::group(['middleware' => ['auth:api-v2', 'check-user-inactive']], function () {
    Route::get('check-user-status', 'ProfileController@checkUserStatus');
    
    /**
     * Profile routes
     */
    Route::group(['middleware' => ['permission:manage_setting']], function ()
    {
        Route::group(['prefix' => 'profile'], function () {
            Route::post('logout', 'ProfileController@logout'); //<------THIS IS LINE IS FOR LOGOUT
            Route::get('/summary', 'ProfileController@summary');
            Route::get('/details', 'ProfileController@details');
            Route::put('/update', 'ProfileController@update');
            Route::put('update-fcm-token', 'ProfileController@update_fcm_token');   //<------ADDED THIS LINE FOR FCM-TOKEN
            Route::post('verify-kyc', 'ProfileController@updatePersonalId');
            Route::post('verify-kyc-address', 'ProfileController@updatePersonalAddress');
            Route::post('/upload-image', 'ProfileController@uploadImage');
            Route::post('/duplicate-phone-number-check', 'ProfileController@checkDuplicatePhoneNumber');
            Route::get('linked-phones', 'ProfileController@LinkedPhones'); //<------THIS IS ROUTE FOR LINKED PHONES
            Route::post('link-phone', 'ProfileController@LinkPhone');   //<------THIS IS ROUTE FOR LINKED PHONES
            Route::post('unlink-phone', 'ProfileController@UnlinkPhone');   //<------THIS IS ROUTE FOR LINKED PHONES
            Route::post('update-web-access', 'ProfileController@updateWebAccessState'); //<------THIS IS ROUTE FOR WEB ACCESS
            Route::post('update-biometric-login', 'ProfileController@updateBiometricLoginState');   //<------THIS IS ROUTE FOR LINKED BIOMETRIC
            Route::get('exchange-rates', 'ProfileController@exchangeRate');
        });
        Route::post('/change-password', 'ProfileController@changePassword');
        Route::get('/default-wallet-balance', 'ProfileController@getDefaultWalletBalance');
        Route::get('/available-balances', 'ProfileController@getUserAvailableWalletsBalance');
    });
    
    /**
     * Notification Routes
     */
    Route::group(['prefix' => 'notification'], function () {
        Route::get('all', 'NotificationController@index');
    });
    
     /**
     * Push Notification Routes
     */
    Route::group(['prefix' => 'pushnotification'], function () {
        Route::post('send-to-device', 'FirebaseController@sendToDevice');
    });
    
    /**
     * Transaction routes
     */
    Route::group(['prefix' => 'transaction', 'middleware' => ['permission:manage_transaction']], function () {
        Route::get('types', 'TransactionController@types');
        Route::post('activityall', 'TransactionController@list'); //<------THIS WAS GET METHOD THEN CHANGED TO POST METHOD
        Route::post('details', 'TransactionController@details');
    });

    /**
     * Send money routes
     */
    Route::group(['name' => 'send-money.', 'prefix' => 'send-money', 'middleware' => ['permission:manage_transfer', 'check-user-suspended']], function () {
        Route::post('/email-check', 'SendMoneyController@emailValidate')->name('validate-email');
        Route::post('/phone-check', 'SendMoneyController@phoneValidate')->name('validate-phone');
        Route::get('/get-currencies', 'SendMoneyController@getCurrencies')->name('get-currencies');
        Route::post('/check-amount-limit', 'SendMoneyController@amountLimitCheck')->name('check-amount-limit');
        Route::post('/confirm', 'SendMoneyController@sendMoneyConfirm')->name('confirm');
    });

    /**
     * Accept Money routes
     */
     Route::group(['prefix' => 'accept-money', 'middleware' => ['permission:manage_request_payment', 'check-user-suspended']], function () {
        Route::get('details', 'AcceptCancelRequestMoneyController@details');
        Route::post('amount-limit-check', 'AcceptCancelRequestMoneyController@checkAmountLimit');
    });

    /**
     * Exchange money routes
     */
    Route::group(['prefix' => 'exchange-money', 'middleware' => ['permission:manage_exchange', 'check-user-suspended']], function () {
        Route::get('get-currencies', 'ExchangeMoneyController@getCurrencies');
        Route::post('amount-limit-check', 'ExchangeMoneyController@exchangeLimitCheck');
        Route::post('get-wallets-balance', 'ExchangeMoneyController@getExchangeWalletsBalance');
        Route::post('get-destination-wallets', 'ExchangeMoneyController@getExchangableDestinations');
        Route::post('get-exchange-rate', 'ExchangeMoneyController@getCurrenciesExchangeRate');
        Route::post('confirm-details', 'ExchangeMoneyController@reviewExchangeDetails');
        Route::post('complete', 'ExchangeMoneyController@exchangeMoneyComplete');
    });
    
    /**
     * Topup Routes
     */
    
    Route::group(['prefix' => 'topup'], function () {
        Route::get('operators', 'TopupController@operators');   //<------ADD THIS LINE FOR TOPUP
        Route::post('products', 'TopupController@products');     //<------ADD THIS LINE FOR TOPUP
        Route::post('packages', 'TopupController@packages');     //<------ADD THIS LINE FOR TOPUP
        Route::post('purchase', 'TopupController@purchase');     //<------ADD THIS LINE FOR TOPUP
    });

    /**
     * Deposit money rotue
     */
    Route::group(['prefix' => 'deposit-money', 'middleware' => ['permission:manage_deposit', 'check-user-suspended']], function () {
        Route::get('get-currencies', 'DepositMoneyController@getCurrencies');
        Route::post('amount-limit-check', 'DepositMoneyController@validateDepositData');
        Route::post('payment-methods', 'DepositMoneyController@getPaymentMethod');
        Route::post('get-bank-list', 'DepositMoneyController@getBankList');
        Route::post('get-bank-detail', 'DepositMoneyController@getBankDetails');
        Route::post('stripe-make-payment', 'DepositMoneyController@stripePaymentInitiate');
        Route::post('payment-confirm', 'DepositMoneyController@paymentConfirm');
        Route::post('get-paypal-info', 'DepositMoneyController@getPaypalInfo');
        Route::post('mobile-make-payment', 'PaymentGatewaysController@mobilePaymentWithdraw');  //<------ADD THIS LINE FOR LOCAL PAYMENT API
        Route::post('verify-Payment', 'PaymentGatewaysController@verifyPayment');  //<------ADD THIS verified payment
        Route::post('confirm-Payment', 'PaymentGatewaysController@confirmPayment');  //<------ADD THIS verified payment
    });

    // Request Money routes
    Route::group(['prefix' => 'request-money', 'middleware' => ['permission:manage_request_payment', 'check-user-suspended']], function () {
        Route::post('email-check', 'RequestMoneyController@checkEmail');
        Route::post('phone-check', 'RequestMoneyController@checkPhone');
        Route::get('currencies', 'RequestMoneyController@getCurrency');
        Route::post('confirm', 'RequestMoneyController@store');
        Route::post('accept', 'AcceptCancelRequestMoneyController@store');
        Route::post('cancel-by-creator', 'AcceptCancelRequestMoneyController@cancelByCreator');
        Route::post('cancel-by-receiver', 'AcceptCancelRequestMoneyController@cancelByReceiver');

    });

    /**
     * Withdrawal setting routes
     */
    Route::group(['prefix' => 'withdrawal-setting', 'middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {
        Route::get('/payment-methods', 'WithdrawalSettingController@paymentMethods');
        Route::get('/crypto-currencies', 'WithdrawalSettingController@cryptoCurrencies');
    });
    Route::resource('/withdrawal-settings', WithdrawalSettingController::class)->middleware('permission:manage_withdrawal', 'check-user-suspended');

    /**
     * Withdrawal routes
     */
    Route::group(['prefix' => 'withdrawal', 'middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {
        Route::post('payment-methods', 'WithdrawalController@getPaymentMethod');
        Route::post('get-currencies', 'WithdrawalController@getCurrencies');
        Route::post('amount-limit-check', 'WithdrawalController@amountLimitCheck');
        Route::post('confirm', 'WithdrawalController@Confirm');
    });
    
     /**
     * Payout for Agent or Tellers routes
     */
    Route::group(['prefix' => 'payout', 'middleware' => ['permission:manage_withdrawal', 'check-user-suspended']], function () {   
        Route::post('check-agent', 'PayoutMoneyController@checkAgent'); //<------ADD THIS LINE FOR AGENT
        Route::post('amount-limit-check', 'PayoutMoneyController@amountLimitCheck');    //<------ADD THIS LINE FOR AGENT
        Route::post('confirm', 'PayoutMoneyController@Confirm');    //<------ADD THIS LINE FOR AGENT
    });
    
    /**
     * Merchant Routes
     */
    Route::group(['prefix' => 'merchants'], function () {
        Route::get('groups', 'MerchantController@groups');
        Route::get('operators', 'MerchantController@getOperators');
        Route::post('get-group', 'MerchantController@getGroup');
        Route::post('get-merchant', 'MerchantController@getMerchantByNumber');
        Route::post('merchant-confirm', 'MerchantController@MerchantPaymentConfirm');
        Route::post('merchant-complete', 'MerchantController@MerchantPaymentComplete');
    });
    
     /**
     * Bank api Routes
     */
    Route::group(['prefix' => 'banks'], function () {
        Route::get('get-bank-names', 'BanksController@getBankname');
        Route::get('bank-list', 'BanksController@getBankList');
        Route::post('verify-bank-pin', 'BanksController@bankpin');
        Route::post('add-bank-account', 'BanksController@addBank');
        Route::post('del-bank-account', 'BanksController@delBank');
    });
    
    /**
     * QrCode
     */
    Route::group(['prefix' => 'qr-code', 'middleware' => ['check-user-suspended']], function () {
        Route::get('get-qr-code', 'QrCodeController@getQrCode');
        Route::get('get-qr-image', 'QrCodeController@getQrImage');  //<------ADD THIS LINE FOR GET QRCCODE IMAGE
        Route::post('add-update-qr-code', 'QrCodeController@addOrUpdateQrCode');
        Route::post('send-request-qr-operation', 'QrCodeController@sendRequestQrOperation');
        Route::post('merchant-qr-operation', 'QrCodeController@merchantQrOperation');
        Route::post('merchant-number', 'QrCodeController@merchantQrOperationByNumber');
        Route::post('merchant-payment-review', 'QrCodeController@merchantPaymentReview');
        Route::post('merchant-payment-submit', 'QrCodeController@merchantPaymentSubmit');
    });
    
    /**
     * Crypto api Routes
     */
    Route::group(['prefix' => 'crypto'], function()
    {
        Route::post('exchange-direction', 'CryptoExchangeApiController@exchangeDirection');
        Route::post('direction-to-currencies', 'CryptoExchangeApiController@directionToCurrencies');
        Route::post('direction-amount', 'CryptoExchangeApiController@exchangeAmount');
        Route::post('confirm-exchange', 'CryptoExchangeApiController@confirmExchange');
        Route::post('process-exchange', 'CryptoExchangeApiController@cryptoExchangeProcess');
    
    });
    
    //cashout api routes
    Route::group(['prefix' => 'cash-out', 'middleware' => ['permission:manage_transfer', 'check-user-suspended']], function () {
        Route::post('verify-teller', 'CashoutController@verifyTeller');
        Route::post('amount-limit-check', 'CashoutController@amountLimitCheck'); 
        Route::post('complete', 'CashoutController@cashOut'); 
    });


});
