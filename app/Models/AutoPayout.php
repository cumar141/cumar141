<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoPayout extends Model
{
    public $incrementing = false;
    
    protected $table = 'ussd_payment';
    protected $primaryKey = 'session';
    protected $keyType = 'string';
    protected $casts = [
        "created_at" => 'date:h:m:s d-m-Y'
    ];
}
