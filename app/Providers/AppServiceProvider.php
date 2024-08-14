<?php

namespace App\Providers;

use App\Models\{TransactionType, PaymentMethod};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use TechVill\Theme\Facades\Theme;
use Config, View, Schema;

class AppServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        header('x-powered-by:');
        Schema::defaultStringLength(191);

        if (!defined('BLOCKIO_API_VERSION')) define('BLOCKIO_API_VERSION', 2);

        if (env('APP_INSTALL') == true) {
            
            $transactionTypes = TransactionType::all()->toArray();
            foreach ($transactionTypes as $transactionType) {
                if (!defined($transactionType['name'])) define($transactionType['name'], $transactionType['id']);
            }

            $paymentMethods = PaymentMethod::all()->toArray();
            foreach( $paymentMethods as $paymentMethod) {
                if (!defined($paymentMethod['name'])) define($paymentMethod['name'], $paymentMethod['id']);
            }

            $adminUrlPrefix = preference('admin_url_prefix');
            if (!empty($adminUrlPrefix)) {
                Config::set(['adminPrefix' => $adminUrlPrefix]);
                View::share('adminPrefix', $adminUrlPrefix);
            }
        }
        
        Validator::extend('custom_phone_number', function ($attribute, $value, $parameters, $validator) {
            // Check if the phone number starts with +252 and has 13 digits
            if (preg_match('/^\+252\d{11}$/', $value)) {
                // Check if the next two digits are one of the specified values
                $prefix = substr($value, 4, 2);
                $allowedPrefixes = ['61', '63', '62', '65', '66', '67', '68', '69', '64', '77', '70'];
                return in_array($prefix, $allowedPrefixes);
            } elseif (preg_match('/^\d{7}$/', $value)) {
                // Check if the phone number without +252 has 7 digits
                return true;
            }
            return false;
        });
    }

    public function register()
    {
    }
}
