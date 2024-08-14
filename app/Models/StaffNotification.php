<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class StaffNotification extends Model
{
    use HasFactory;

    protected $table = 'staff_notification';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'payload',
        'status',
        'read',
    ];

    protected $casts = [
        'payload' => 'array',
        'read' => 'boolean',
    ];

    /**
     * Create a new notification.
     *
     * @param array $data
     * @return \App\Models\StaffNotification
     */
    public function createNotification($data)
    {

        return self::create([
            'sender_id' => $data['user_id'],
            'receiver_id' => $data['end_user_id'],
            'message' => $data['note'],
            'payload' => $data['payload'],
            'status' => 'Pending',
            'is_read' => false
        ]);
    }
    

 
}
