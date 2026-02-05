<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function updateFCMToken(Request $request)
{
    try {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user(); // Sanctum authenticated user

        // Update or create token
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'message' => 'FCM token updated successfully',
            'fcm_token' => $user->fcm_token
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update FCM token',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
