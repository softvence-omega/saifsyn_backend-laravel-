<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{




   /**
     * Admin Dashboard Stats
     */
    public function dashboardStats()
    {
         try {

            // -------------------------
            // USER STATS
            // -------------------------
            $totalUsers = User::count();
            $totalActiveUsers = User::where('status', 1)->count();
            $totalInactiveUsers = User::where('status', 0)->count();

            // -------------------------
            // PLAN STATS
            // -------------------------
            $totalPlans = DB::table('subscription_plans')->count();
            $activePlans = DB::table('subscription_plans')->where('status', 1)->count();
            $popularPlans = DB::table('subscription_plans')->where('is_popular', 1)->count();

            // -------------------------
            // REVENUE STATS (Payments only)
            // -------------------------
            $totalRevenue = DB::table('payments')->where('status', 'paid')->sum('amount');

            $monthlyRevenue = DB::table('payments')
                ->where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');

            // -------------------------
            // PLAN WISE REVENUE
            // -------------------------
            $planRevenue = DB::table('payments')
                ->join('subscription_plans', 'payments.plan_id', '=', 'subscription_plans.id')
                ->select(
                    'subscription_plans.title',
                    DB::raw('SUM(payments.amount) as total_revenue'),
                    DB::raw('COUNT(payments.id) as total_users')
                )
                ->where('payments.status', 'paid')
                ->groupBy('subscription_plans.title')
                ->get();

            // -------------------------
            // RESPONSE
            // -------------------------
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => [
                        'total_users' => $totalUsers,
                        'active_users' => $totalActiveUsers,
                        'inactive_users' => $totalInactiveUsers,
                    ],

                    'plans' => [
                        'total_plans' => $totalPlans,
                        'active_plans' => $activePlans,
                        'popular_plans' => $popularPlans,
                    ],

                    'revenue' => [
                        'total_revenue' => $totalRevenue,
                        'monthly_revenue' => $monthlyRevenue,
                        'plan_revenue' => $planRevenue,
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //all user show
   public function index(Request $request)
{
    try {
        $query = User::query();

        // Filter by status if param exists
        if ($request->has('status')) {
            $status = strtolower($request->status);
            if ($status === 'active') {
                $query->where('status', true);
            } elseif ($status === 'inactive') {
                $query->where('status', false);
            }
        }

        // Only paid subscriptions
        $query->with(['payments' => function($q) {
            $q->where('status', 'paid')->with('plan');
        }]);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        // Transform data
        $users->getCollection()->transform(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->getRoleNames()->first() ?? null,
                'purchases' => $user->payments->map(function($payment){
                    return [
                        'plan_title' => $payment->plan->title,
                        'amount' => $payment->amount,
                        'paid_at' => $payment->created_at->toDateTimeString(),
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch users',
            'error' => $e->getMessage()
        ], 500);
    }
}



//admin manually can changes user active inactive
public function toggleUserStatus($userId)
{
    $user = User::findOrFail($userId);

    // status1 → 0, 0 → 1
    $user->status = !$user->status;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'User status updated successfully',
        'status' => $user->status ? 'active' : 'inactive',
    ]);
}


    // -----------------------------
    // User: Show own profile
    // -----------------------------
    public function profile()
    {
        try {
            $user = Auth::user();
             $user->role = $user->getRoleNames()->first() ?? null;
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
