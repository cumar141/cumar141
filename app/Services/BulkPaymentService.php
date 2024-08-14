<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Services\PayoutGateway\{
    TrueShilling
};

class BulkPaymentService {
    
    public function process($data) {
        $rules = [
            'amount' => 'required|numeric|min:0',
            'account' => 'required',
            'account_provider' => 'required|integer|exists:account_providers,id',
            'organization' => 'required|integer|exists:organizations,id',
            'description' => 'required|string',
        ];

        // Validation messages
        $messages = [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'account.required' => 'Account is required.',
            'account_provider.required' => 'Account Provider is required',
            'account_provider.integer' => 'Account Provider must be valid',
            'account_provider.exists' => 'Account Provider must be valid',
            'organization.required' => 'Organization is required',
            'organization.integer' => 'Organization must be valid',
            'organization.exists' => 'Organization must be valid',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be valid.',
        ];

        // Validate the request
        $validator = Validator::make($data->all(), $rules, $messages);

        // Check for validation errors
        if ($validator->fails()) return ["status" => "failed", "message" => $validator->errors()->first()];
        
        $provider = new TrueShilling();
        $data = $provider->send($data);
        return $data;
    }
}