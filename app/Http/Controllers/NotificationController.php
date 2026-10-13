<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::query();

        // Check if user_id column exists before filtering
        if (\Schema::hasColumn('notifications', 'user_id')) {
            // Only show notifications assigned to this specific user
            $query->where('user_id', auth()->id());
        }

        // Only show notifications created since the user registered (clean slate on new registration)
        $query->where('created_at', '>=', auth()->user()->created_at);

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(fn($notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'data' => $notification->data,
                'type' => $notification->type,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'is_read' => !is_null($notification->read_at),
            ]);

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $query = Notification::whereNull('read_at');

        // Filter by user if user_id column exists
        if (\Schema::hasColumn('notifications', 'user_id')) {
            // Only mark as read notifications assigned to this specific user
            $query->where('user_id', auth()->id());
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
