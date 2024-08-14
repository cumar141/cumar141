<?php

namespace App\Models;

use App\Models\OrganizationWallet;
use App\Models\OrganizationUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use softDeletes;
    // Define the table associated with the model
    protected $table = 'organizations';
    protected $guarded = ['id'];

    public function organizationWallet()
    {
       return $this->hasOne(OrganizationWallet::class);
    }
    
    public function organizationUser()
    {
       return $this->hasMany(OrganizationUser::class);
    }

}
