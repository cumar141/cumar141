<?php

namespace App\Models;

use App\Models\AccountProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\OrganizationBatch;

class OrganizationPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'organization_batch_id',
        'organization_user_id',
        'account_provider_id',
        'account_name',
        'account_number',
        'amount',
        'description',
        'payment_date',
        'status',
        'is_recurring',
    ];

    // cast the is_recurring attribute to boolean
    protected $casts = [
        'is_recurring' => 'boolean',
        'payment_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizationBatch()
    {
        return $this->belongsTo(OrganizationBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(OrganizationUser::class, 'organization_user_id');
    }

    public function getAmountAttribute($value)
    {
        return number_format($value, 2);
    }

    public function accountProvider()
    {
        return $this->hasOne(AccountProvider::class, 'id', 'account_provider_id');
    }
}
