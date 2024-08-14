<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table   = 'activity_logs';

    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'device_id',
        'os',
        'device_model',
        'browser_agent',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /**
     * Store activity log
     *
     * @param int $user_id
     * @param string $type
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    
    public static function createActivityLog($user_id = null, $type = 'User', $ipAddress, $deviceid, $os, $devicemodel, $userAgent)
    {
        $userExists = self::where('user_id', $user_id)->where('is_active', 1)->exists();
        if (!$userExists) {
            $log = new self();
            $log->user_id = (int) $user_id;
            $log->type = $type;
            $log->ip_address = $ipAddress;
            $log->device_id = $deviceid;
            $log->os = $os;
            $log->device_model = $devicemodel;
            $log->browser_agent = $userAgent;
            $log->is_active = 1;
            $log->save();
            return true;
        }
    
        $existingLog = self::where('user_id', $user_id)->where('is_active', 1)->first();
        if ($existingLog && $existingLog->device_id != $deviceid) {
            return 'device verify';
        } else {
            $log = new self();
            $log->user_id = (int) $user_id;
            $log->type = $type;
            $log->ip_address = $ipAddress;
            $log->device_id = $deviceid;
            $log->os = $os;
            $log->device_model = $devicemodel;
            $log->browser_agent = $userAgent;
            $log->is_active = 0;
    
            try {
                $log->save();
                return true;
            } catch (\Exception $e) {
                \Log::error('Error creating activity log: ' . $e->getMessage());
                return false;
            }
        }
    }

    
     public static function verifyDevicelogs($user_id = null, $type = 'User', $ipAddress, $deviceid, $os, $devicemodel, $userAgent)
    {
        $existingLog = self::where('user_id', $user_id)->where('is_active', 1)->first();
    
        if ($existingLog && $existingLog->device_id != $deviceid) {
            $existingLog->is_active = 0;
            $existingLog->save();
    
            $log = new self();
            $log->user_id = (int) $user_id;
            $log->type = $type;
            $log->ip_address = $ipAddress;
            $log->device_id = $deviceid;
            $log->os = $os;
            $log->device_model = $devicemodel;
            $log->browser_agent = $userAgent;
            $log->is_active = 1;
            $log->save();
            return true;
        } else {
            return false;
        }
    }




}
