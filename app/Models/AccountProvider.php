<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationPayment;

class AccountProvider extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function organizationPayments()
    {
        return $this->hasMany(OrganizationPayment::class);
    }
}
