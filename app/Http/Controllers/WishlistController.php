<?php

namespace App\Http\Controllers;

use App\Models\UserWishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WishlistController extends Controller
{
    // -----------------------------
    // 1. Show all active wishlist items for logged in user
    // GET /wishlist
    // -----------------------------
    public function index()
    {
        try {
            // Only fetch items not soft deleted
            $wishlist = UserWishlist::where('user_id', Auth::id())
                ->whereNull('deleted_at') // soft delete check
                ->get();

            return response()->json([
                'success' => true,
                'data' => $wishlist
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 2. Add a stock to wishlist
    // POST /wishlist
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'stock_symbol' => 'required|string',
            ]);

            $userId = Auth::id();

            // Check if soft deleted record exists
            $wishlist = UserWishlist::withTrashed()
                ->where('user_id', $userId)
                ->where('stock_symbol', $request->stock_symbol)
                ->first();

            if ($wishlist) {
                // If soft deleted, restore it
                if ($wishlist->trashed()) {
                    $wishlist->restore();
                }
            } else {
                // Create new
                $wishlist = UserWishlist::create([
                    'user_id' => $userId,
                    'stock_symbol' => $request->stock_symbol
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock added to wishlist',
                'data' => $wishlist
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 3. Remove stock from wishlist (soft delete)
    // DELETE /wishlist/{id}
    // -----------------------------
    public function destroy($id)
    {
        try {
            $wishlist = UserWishlist::where('user_id', Auth::id())->findOrFail($id);

            // Soft delete
            $wishlist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Stock removed from wishlist'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
