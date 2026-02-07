<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    // -----------------------------
    // 1. Show all loans of authenticated user
    // -----------------------------
    public function index()
    {
        try {
            $loans = Loan::where('user_id', Auth::id())->get();
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
                'title' => 'nullable|string',
                'amount' => 'nullable|numeric',
                'interest_rate' => 'nullable|numeric',
                'repayment_period' => 'nullable|integer',
                'start_date' => 'nullable|date',
            ]);


            $loan = Loan::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'amount' => $request->amount,
                'interest_rate' => $request->interest_rate,
                'repayment_period' => $request->repayment_period,
                'start_date' => $request->start_date,
            ]);

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
            $loan = Loan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

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
            $loan = Loan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

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
            $loan = Loan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

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
