<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    // -----------------------------
// 1. Show all expenses (with date filter & pagination)
// -----------------------------
public function index(Request $request)
{
    try {
        $query = Expense::where('user_id', Auth::id());

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
        $expenses = $query->orderBy('date', 'desc')->paginate($perPage);

        // Check if empty after filter
        if ($expenses->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No expenses found for the selected date(s)',
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
            'data' => $expenses->items(),
            'pagination' => [
                'total' => $expenses->total(),
                'per_page' => $expenses->perPage(),
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch expenses',
            'error' => $e->getMessage()
        ], 500);
    }
}



    // -----------------------------
    // 2. Create a new expense
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'amount' => 'required|numeric',
                'date' => 'required|date',
            ]);

            $expense = Expense::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'amount' => $request->amount,
                'date' => $request->date,
            ]);

            return response()->json([
                'message'=>'Expense created',
                'expense'=>$expense
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message'=>'Failed to create expense',
                'error'=>$e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 3. Show single expense
    // -----------------------------
    public function show($id)
    {
        try {
            $expense = Expense::where('id', $id)
                              ->where('user_id', Auth::id())
                              ->firstOrFail();

            return response()->json($expense);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message'=>'Expense not found'],404);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>'Failed to fetch expense',
                'error'=>$e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 4. Update existing expense
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $expense = Expense::where('id', $id)
                              ->where('user_id', Auth::id())
                              ->firstOrFail();

            $request->validate([
                'title' => 'sometimes|required|string',
                'amount' => 'sometimes|required|numeric',
                'date' => 'sometimes|required|date',
            ]);

            $expense->update($request->only(['title','amount','date']));

            return response()->json([
                'message'=>'Expense updated',
                'expense'=>$expense
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message'=>'Expense not found'],404);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>'Failed to update expense',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    // -----------------------------
    // 5. Soft delete expense
    // -----------------------------
    public function destroy($id)
    {
        try {
            $expense = Expense::where('id', $id)
                              ->where('user_id', Auth::id())
                              ->firstOrFail();

            $expense->delete(); // soft delete

            return response()->json(['message'=>'Expense deleted']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message'=>'Expense not found'],404);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>'Failed to delete expense',
                'error'=>$e->getMessage()
            ],500);
        }
    }
}
