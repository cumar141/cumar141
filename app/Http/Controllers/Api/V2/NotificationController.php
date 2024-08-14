<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;
        $user_id = auth()->user()->id;
        // $notifications = NotificationResource::collection(
        //     Notification::active()->where('receiver', 'customers')->orWhere('receiver', 'all')->where('status', 1)
        //     ->select('id', 'title', 'description', 'image', 'type', 'created_at')->latest()
        //     ->paginate($limit, ['*'], 'page', $offset)
        // );
        
        $notifications = NotificationResource::collection(
            Notification::active()
                ->where(function($query) {
                    $query->where('receiver', 'customers')
                          ->orWhere('receiver', 'all');
                })
                ->where(function($query) use ($user_id) {
                    $query->where('status', 1)
                          ->where(function($query) use ($user_id) {
                              $query->where('user_id',  $user_id)
                                    ->orWhereNull('user_id');
                          });
                })
                ->select('id', 'title', 'description', 'image', 'type', 'created_at')
                ->latest()
                ->paginate($limit, ['*'], 'page', $offset)
        );

        return response()->json([
            'total_size' => $notifications->total(),
            'limit' => $limit,
            'offset' => $offset,
            'notifications' => $notifications->items()
        ]);
    }
}
