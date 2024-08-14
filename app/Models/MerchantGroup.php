<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantGroup extends Model
{
    protected $table = 'merchant_groups';

    protected $fillable = ['name', 'description', 'icon', 'fee', 'is_default', 'is_active'];

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'merchant_group_id');
    }
    
    public function getIconAttribute($value)
    {
       return "https://pay.somxchange.com/public/uploads/merchant/icons/{$value}";
    }
}
