<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;



class UserController extends Controller
{


// -----------------------------
    // Admin: List all users (with optional subscription filter)
    // -----------------------------
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Eager load subscriptions
            $query->with('subscriptions');

            // Filter only users who have paid subscription
            if ($request->filled('subscribed') && $request->subscribed) {
                $query->has('subscriptions');
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $users = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // User: Show own profile
    // -----------------------------
    public function profile()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // User: Update own profile (name, phone)
    // -----------------------------
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $user->id,
            ]);

            $user->update($request->only(['name', 'phone']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   

    // -----------------------------
    // User: Change password
    // -----------------------------
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'current_password' => 'required|string',
                'new_password' =>  [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ],// new_password_confirmation field needed
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }



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
