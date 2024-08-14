<?php

namespace App\Models; // Adjust namespace as per your directory structure

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes; 
class PartnerBalance extends Model
{
    public $timestamps = false;
    use HasFactory, SoftDeletes;
    protected $table = 'partner_balance';
    
    protected $fillable = [
        'partner',
        'type',
        'balance',
      
    ];

}
