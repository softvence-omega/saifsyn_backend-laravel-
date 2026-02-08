<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    // -----------------------------
    // 1. Show all incomes (with date filter & pagination)
    // -----------------------------
   public function index(Request $request)
{
    try {
        $query = Income::where('user_id', Auth::id());

        // Validate from_date & to_date formats
        if ($request->filled('from_date')) {
            $from = $request->from_date;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid from_date format. Use YYYY-MM-DD'
                ], 422);
            }
            $query->whereDate('date', '>=', $from);
        }

        if ($request->filled('to_date')) {
            $to = $request->to_date;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid to_date format. Use YYYY-MM-DD'
                ], 422);
            }
            $query->whereDate('date', '<=', $to);
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $incomes = $query->orderBy('date', 'desc')->paginate($perPage);

        // Check if empty after filter
        if ($incomes->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No incomes found for the selected date(s)',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $request->input('page', 1),
                    'last_page' => 0,
                ]
            ]);
        }

        // Normal response with pagination
        return response()->json([
            'success' => true,
            'data' => $incomes->items(),
            'pagination' => [
                'total' => $incomes->total(),
                'per_page' => $incomes->perPage(),
                'current_page' => $incomes->currentPage(),
                'last_page' => $incomes->lastPage(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch incomes',
            'error' => $e->getMessage()
        ], 500);
    }
}





    // -----------------------------
// 2. Create a new income
// -----------------------------
public function store(Request $request)
{
    try {
        $request->validate([
            'title' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'date' => 'nullable|date',
        ]);

        // Use authenticated user's ID
        $income = Income::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);

        return response()->json([
            'message' => 'Income created successfully',
            'income' => $income
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create income',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // -----------------------------
    // 3. Show single income
    // -----------------------------
    public function show($id)
    {
        try {
            $income = Income::findOrFail($id);
            return response()->json($income);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Income not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch income',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    // -----------------------------
// 4. Update existing income
// -----------------------------
public function update(Request $request, $id)
{
    try {
        $income = Income::findOrFail($id);

        // Ensure the authenticated user owns this income
        if ($income->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to update this income'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric',
            'date' => 'sometimes|required|date',
        ]);

        // Update only provided fields
        $income->update($request->only(['title','amount','date']));

        return response()->json([
            'message' => 'Income updated successfully',
            'income' => $income
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'Income not found'], 404);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update income',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // -----------------------------
    // 5. Soft delete income
    // -----------------------------
    public function destroy($id)
    {
        try {
            $income = Income::findOrFail($id);
            $income->delete(); // soft delete
            return response()->json(['message' => 'Income deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Income not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete income',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
