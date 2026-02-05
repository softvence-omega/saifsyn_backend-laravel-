<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    // -----------------------------
    // 1. Show all incomes
    // -----------------------------
    public function index()
    {
        try {
            $incomes = Income::all();
            return response()->json($incomes);
        } catch (\Exception $e) {
            return response()->json([
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
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'amount' => 'required|numeric',
                'date' => 'required|date',
            ]);

            $income = Income::create($request->only(['user_id','title','amount','date']));

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

            $request->validate([
                'title' => 'sometimes|required|string',
                'amount' => 'sometimes|required|numeric',
                'date' => 'sometimes|required|date',
            ]);

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
