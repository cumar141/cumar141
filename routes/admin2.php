<?php

use Illuminate\Support\Facades\Route;


// Grouping routes that require 'auth:admin' middleware
Route::middleware('auth:admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', 'Admin2Controller@index')->name('admin2.dashboard');

    // Transactions
    Route::post('/transaction/approve', 'Admin2Controller@approve')->name('admin2.transaction.approve');
    Route::post('/transaction/reject', 'Admin2Controller@reject')->name('admin2.transaction.reject');
    Route::get('/transactions', 'Admin2Controller@transactions')->name('admin2.transactions');

    // Reports
    Route::get('reports/treasurer', 'Admin2Controller@treasury')->name('admin2.treasurer.accountant_treasurer');
    Route::get('reports/approved', 'Admin2Controller@approved')->name('admin2.approved');
    Route::get('/reports/branch/{id}', 'Admin2Controller@reports')->name('admin2.reports');
    Route::get('/reports/treasurers', 'Admin2Controller@generateAdminTreasurer')->name('staff.reports.generateAdminTreasurer');

    // Profile
    Route::get('/profile', 'Admin2Controller@profile')->name('admin2.profile');
});
