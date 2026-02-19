<?php

namespace App\Http\Controllers;

use App\Models\OurAnalysis;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
// use App\Jobs\SendAnalysisNotification;

class OurAnalysisController extends Controller
{

// -----------------------------
// 1. List all analyses (paginated)
// -----------------------------
public function index(Request $request)
{
    try {
        $perPage = $request->get('per_page', 10); // Optional: allow dynamic pagination
        $analyses = OurAnalysis::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $analyses->items(),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'per_page' => $analyses->perPage(),
                'total' => $analyses->total(),
            ],
        ]);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to fetch analyses', $e);
    }
}

// -----------------------------
// 2. Show single analysis
// -----------------------------
public function show($id)
{
    try {
        $analysis = OurAnalysis::find($id);

        if (!$analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to fetch analysis', $e);
    }
}


    // -----------------------------
    // 3. Create new analysis
    // -----------------------------
    public function store(Request $request)
{
    $data = $request->validate([
        'symbol' => 'nullable|string',
        'name' => 'nullable|string',
        'status' => 'nullable|string',
        'recommendation' => 'nullable|string',
        'note' => 'nullable|string',
    ]);

    $analysis = OurAnalysis::create($data);

    if(!empty($data['note'])){
        // Create notification for all users
        $users = User::all();
        foreach($users as $user){
            Notification::create([
                'user_id' => $user->id,
                'analysis_id' => $analysis->id,
                'is_read' => false
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Analysis created successfully',
        'data' => $analysis
    ], 201);
}

    // -----------------------------
    // 4. Update existing analysis
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);

            $data = $request->validate([
                'symbol' => 'sometimes|required|string',
                'name' => 'sometimes|required|string',
                'status' => 'sometimes|required|string',
               
                'recommendation' => 'nullable|string',
                'note' => 'nullable|string',
            ]);

            $analysis->update($data);

            // Dispatch notification if note updated
            // if(isset($data['note']) && $data['note']) {
            //     SendAnalysisNotification::dispatch($analysis);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Analysis updated successfully',
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update analysis', $e);
        }
    }

    // -----------------------------
    // 5. Soft delete analysis
    // -----------------------------
    public function destroy($id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);
            $analysis->delete();

            return response()->json([
                'success' => true,
                'message' => 'Analysis deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete analysis', $e);
        }
    }




      // -----------------------------
    // 6. Bell notifications (Latest 5 globally)
    // -----------------------------
   public function bellNotifications()
{
    try {
        $userId = auth()->id();

        // Latest unread notifications
        $notifications = \App\Models\Notification::with('analysis:id,symbol,name,note')
            ->where('user_id', $userId)
            ->where('is_read', 0) // only unread
            ->latest()
            ->take(5)
            ->get();

        $unreadCount = \App\Models\Notification::where('user_id', $userId)
                        ->where('is_read', 0)
                        ->count(); // unread count

        // যদি unread notifications না থাকে
        if ($unreadCount == 0) {
            return response()->json([
                'success' => true,
                'count' => 0,
                'data' => [],
                'message' => 'No new notifications'
            ]);
        }

        return response()->json([
            'success' => true,
            'count' => $unreadCount,
            'data' => $notifications
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch notifications'
        ], 500);
    }
}




public function markAsRead($id)
{
    try {
          // Find notification by its primary ID (notifications table id)
        $notification = \App\Models\Notification::where('id', $id)
            ->where('user_id', auth()->id()) // important!
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Already read 
        if ($notification->is_read == 0) {
            $notification->is_read = 1;
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as read',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // -----------------------------
    // 7. Standardized error response
    // -----------------------------
    private function errorResponse($message, \Exception $e, $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $e->getMessage()
        ], $status);
    }
}
