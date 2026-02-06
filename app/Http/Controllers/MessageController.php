<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Send message
    public function send(Request $request)
    {
        try {
            $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message'     => 'required|string',
            ]);

            $message = Message::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message'     => $request->message,
            ]);

            return response()->json([
                'status' => true,
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
            $messages = Message::where(function($q) use($userId){
                $q->where('sender_id', Auth::id())->where('receiver_id', $userId);
            })->orWhere(function($q) use($userId){
                $q->where('sender_id', $userId)->where('receiver_id', Auth::id());
            })->orderBy('created_at', 'asc')->get();

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
}
