<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    // -----------------------------
    // 1. Show all expenses for authenticated user
    // -----------------------------
    public function index()
    {
        try {
            $expenses = Expense::where('user_id', Auth::id())->get();
            return response()->json($expenses);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>'Failed to fetch expenses',
                'error'=>$e->getMessage()
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
