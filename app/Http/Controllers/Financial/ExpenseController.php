<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        try {
            $expenses = Expense::all();
            return response()->json($expenses);
        } catch (\Exception $e) {
            return response()->json(['message'=>'Failed to fetch expenses','error'=>$e->getMessage()],500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id'=>'required|exists:users,id',
                'title'=>'required|string',
                'amount'=>'required|numeric',
                'date'=>'required|date',
                
            ]);

            $expense = Expense::create($request->all());

            return response()->json(['message'=>'Expense created','expense'=>$expense],201);

        } catch (\Exception $e) {
            return response()->json(['message'=>'Failed to create expense','error'=>$e->getMessage()],500);
        }
    }

    public function show($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            return response()->json($expense);
        } catch (\Exception $e) {
            return response()->json(['message'=>'Expense not found'],404);
        }
    }

   public function update(Request $request, $id)
{
    try {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric',
            'date' => 'sometimes|required|date',
        ]);

        $expense->update($request->only(['title','amount','date']));

        return response()->json([
            'message' => 'Expense updated',
            'expense' => $expense
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


    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete(); // soft delete
            return response()->json(['message'=>'Expense deleted']);
        } catch (\Exception $e) {
            return response()->json(['message'=>'Failed to delete expense','error'=>$e->getMessage()],500);
        }
    }
}
