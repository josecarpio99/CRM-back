<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class NotificationController extends ApiController
{
    public function markAsRead(Request $request)
    {
        auth()->user()
            ->unreadNotifications
            ->when($request->id, function($query) use($request) {
                return $query->where('id', $request->id);
            })
            ->markAsRead();

        return response()->noContent();
    }

}
