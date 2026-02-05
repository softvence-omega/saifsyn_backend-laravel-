<?php

namespace App\Http\Controllers;

use App\Models\UserWishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // -----------------------------
    // 1. Show all wishlist items for logged in user
    // GET /wishlist
    // -----------------------------
    public function index()
    {
        try {
            $wishlist = UserWishlist::where('user_id', Auth::id())->get();
            return response()->json($wishlist);
        } catch (\Exception $e) {
            return response()->json([
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

            $wishlist = UserWishlist::updateOrCreate(
                ['user_id' => Auth::id(), 'stock_symbol' => $request->stock_symbol]
            );

            return response()->json([
                'message' => 'Stock added to wishlist',
                'wishlist' => $wishlist
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
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
            $wishlist->delete();

            return response()->json([
                'message' => 'Stock removed from wishlist'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Wishlist item not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
