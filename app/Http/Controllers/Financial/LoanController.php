<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    // -----------------------------
    // 1. Show all loans
    // -----------------------------
    public function index()
    {
        try {
            $loans = Loan::all();
            return response()->json($loans);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch loans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 2. Create a new loan
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'amount' => 'required|numeric',
                'interest_rate' => 'required|numeric',
                'repayment_period' => 'required|integer', // in months
                'start_date' => 'required|date',
            ]);

            $loan = Loan::create($request->only([
                'user_id','title','amount','interest_rate','repayment_period','start_date'
            ]));

            return response()->json([
                'message' => 'Loan created successfully',
                'loan' => $loan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 3. Show single loan
    // -----------------------------
    public function show($id)
    {
        try {
            $loan = Loan::findOrFail($id);
            return response()->json($loan);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 4. Update existing loan
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|required|string',
                'amount' => 'sometimes|required|numeric',
                'interest_rate' => 'sometimes|required|numeric',
                'repayment_period' => 'sometimes|required|integer',
                'start_date' => 'sometimes|required|date',
            ]);

            $loan->update($request->only([
                'title','amount','interest_rate','repayment_period','start_date'
            ]));

            return response()->json([
                'message' => 'Loan updated successfully',
                'loan' => $loan
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 5. Soft delete loan
    // -----------------------------
    public function destroy($id)
    {
        try {
            $loan = Loan::findOrFail($id);
            $loan->delete(); // soft delete
            return response()->json(['message' => 'Loan deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete loan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
