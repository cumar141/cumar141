<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVerification extends Model
{
    protected $table = 'payment_verifications';
    protected $fillable = ["platform", "transaction_id", "uuid", "reference_id", "being_processed", "created_at", "paid_at", "expires_at"];
}
