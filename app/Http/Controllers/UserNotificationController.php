<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function index()
    {
        $notifications = UserNotification::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($notifications);
    }

    public function markRead(Request $request)
    {
        $query = UserNotification::where('user_id', Auth::user()->id);

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $query->update(['is_read' => 1]);

        return response()->json(['success' => true]);
    }

    public function getCount()
    {
        $count = UserNotification::where('user_id', Auth::user()->id)
            ->where('is_read', 0)
            ->count();

        return response()->json(['count' => $count]);
    }
    public function getLatestUnreadNotification()
    {
        $notification = UserNotification::where('user_id', Auth::user()->id)
            ->where('is_read', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($notification) {
            $notification->created_at_formatted = $notification->created_at->diffForHumans();
        }

        return response()->json($notification);
    }
}
