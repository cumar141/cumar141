<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organization;



class OrganizationWallet extends Model
{
    protected $table = 'organization_wallets';

    protected $fillable = [
        'organization_id',
        'balance',
    ];
    protected $guarded =['id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }


}
