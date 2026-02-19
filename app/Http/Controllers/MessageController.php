<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MessageNotification;


class MessageController extends Controller
{


// Fetch all users (id + name)
// Admin: see all messages with sender & receiver names
public function allMessages()
{
    try {
        

        // Fetch all messages with sender & receiver name
        $messages = Message::with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $messages
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Send message
    public function send(Request $request)
{
    try {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string',
        ]);

        // 1. Create message
        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
        ]);

        // 2. Get all admin users (role = Admin)
        $admins = User::whereHas('roles', function ($q) {
            $q->where('name', 'Admin');
        })->get();

        // 3. Create notification for each admin
        foreach ($admins as $admin) {
            MessageNotification::create([
                'user_id'    => $admin->id,   // admin will receive notification
                'message_id' => $message->id, // which message triggered it
                'is_read'    => 0,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Message sent & admin notified',
            'data' => $message
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'error'  => $e->getMessage()
        ], 500);
    }
}

    // Fetch chat history with a user
    public function chatWithUser($userId)
   
        {
            try {
                $messages = Message::with(['sender:id,name', 'receiver:id,name'])
                    ->where(function($q) use($userId){
                        $q->where('sender_id', Auth::id())
                        ->where('receiver_id', $userId);
                    })->orWhere(function($q) use($userId){
                        $q->where('sender_id', $userId)
                        ->where('receiver_id', Auth::id());
                    })->orderBy('created_at', 'asc')
                    ->get();

                return response()->json([
                    'status' => true,
                    'data' => $messages
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => false,
                    'error'  => $e->getMessage()
                ], 500);
            }
        }

    // Soft delete a message (sender only)
    public function delete($id)
    {
        try {
            $message = Message::where('id', $id)
                              ->where('sender_id', Auth::id())
                              ->firstOrFail();

            $message->delete();

            return response()->json([
                'status' => true,
                'message' => 'Message deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Restore a soft deleted message
    public function restore($id)
    {
        try {
            $message = Message::withTrashed()
                              ->where('id', $id)
                              ->where('sender_id', Auth::id())
                              ->firstOrFail();

            $message->restore();

            return response()->json([
                'status' => true,
                'message' => 'Message restored successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // -----------------------------
// All Message Notifications (Read + Unread) - Full Page
// -----------------------------
public function adminMessageNotifications()
{
    try {
        $adminId = auth()->id();

        // Latest 5 notifications (bell dropdown)
        $notifications = MessageNotification::with([
                'message:id,sender_id,message,created_at',
                'message.sender:id,name'
            ])
            ->where('user_id', $adminId)
            ->latest()
            ->take(5)
            ->get();

        // Only send message + sender name
        $result = $notifications->map(function($n) {
            return [
                'id' => $n->id,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,
                'message' => [
                    'id' => $n->message->id,
                    'text' => $n->message->message,
                    'sender' => [
                        'id' => $n->message->sender->id,
                        'name' => $n->message->sender->name,
                    ]
                ]
            ];
        });

        // Unread count (badge number)
        $unreadCount = MessageNotification::where('user_id', $adminId)
                            ->where('is_read', 0)
                            ->count();

        return response()->json([
            'success' => true,
            'count' => $unreadCount,
            'data' => $result
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch message notifications',
            'error' => $e->getMessage()
        ], 500);
    }
}




// -----------------------------
// Mark Single Message Notification as Read
// -----------------------------
public function markMessageNotificationAsRead($id)
{
    try {
        // Only logged-in admin can mark their own notification as read
        $notification = MessageNotification::where('id', $id)
            ->where('user_id', auth()->id()) // security check (very important)
            ->first();

        // যদি notification না পাওয়া যায়
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or unauthorized'
            ], 404);
        }

        // Already read না হলে read mark করবে
        if ($notification->is_read == 0) {
            $notification->update([
                'is_read' => 1
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message notification marked as read successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as read'
        ], 500);
    }
}

}
