<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for current user
     */
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $notifications = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notifications
     */
    public function getUnread(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->unread()
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(Request $request)
    {
        $count = $request->user()
            ->notifications()
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully'
        ]);
    }
}
