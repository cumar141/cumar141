<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationMessages extends Model
{
    protected $table = "notification_messages";
    protected $fillable = [
        "type",
        "key",
        "value",
        "status",
        "created_at",
        "updated_at" 	
    ];
}
