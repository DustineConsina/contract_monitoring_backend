<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get conversations list for current user
     */
    public function getConversations(Request $request)
    {
        $user = $request->user();
        
        // Get unique users who have exchanged messages with current user
        $sentTo = ChatMessage::where('sender_id', $user->id)
            ->distinct()
            ->pluck('receiver_id');
            
        $receivedFrom = ChatMessage::where('receiver_id', $user->id)
            ->distinct()
            ->pluck('sender_id');
            
        $conversationUserIds = $sentTo->merge($receivedFrom)->unique();
        
        $conversations = User::whereIn('id', $conversationUserIds)
            ->with(['tenant'])
            ->get()
            ->map(function ($conversationUser) use ($user) {
                $lastMessage = ChatMessage::betweenUsers($user->id, $conversationUser->id)
                    ->latest()
                    ->first();
                    
                $unreadCount = ChatMessage::where('sender_id', $conversationUser->id)
                    ->where('receiver_id', $user->id)
                    ->unread()
                    ->count();
                    
                return [
                    'user' => $conversationUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc(function ($conversation) {
                return $conversation['last_message']->created_at ?? null;
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Get messages between two users
     */
    public function getMessages(Request $request, $userId)
    {
        $currentUser = $request->user();
        $otherUser = User::with('tenant')->findOrFail($userId);

        $messages = ChatMessage::betweenUsers($currentUser->id, $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->unread()
            ->each(function ($message) {
                $message->markAsRead();
            });

        return response()->json([
            'success' => true,
            'data' => [
                'other_user' => $otherUser,
                'messages' => $messages
            ]
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sender = $request->user();

        if ($sender->id == $request->receiver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send message to yourself'
            ], 422);
        }

        $message = ChatMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // Create notification for receiver
        \App\Models\Notification::create([
            'user_id' => $request->receiver_id,
            'type' => 'new_message',
            'title' => 'New Message',
            'message' => "{$sender->name} sent you a message",
            'data' => ['message_id' => $message->id, 'sender_id' => $sender->id],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender', 'receiver'])
        ], 201);
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        $message = ChatMessage::findOrFail($id);

        if ($message->receiver_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(Request $request)
    {
        $count = ChatMessage::where('receiver_id', $request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get available users to chat with (for staff/admin)
     */
    public function getAvailableUsers(Request $request)
    {
        $user = $request->user();
        
        $query = User::where('id', '!=', $user->id)
            ->where('status', 'active');

        // If tenant, can only chat with admin/staff
        if ($user->isTenant()) {
            $query->whereIn('role', ['admin', 'staff']);
        }

        $users = $query->with('tenant')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage($id)
    {
        $message = ChatMessage::findOrFail($id);

        if ($message->sender_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }
}
