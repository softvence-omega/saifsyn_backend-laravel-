<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use Illuminate\Http\Request;

class FinancialManagerController extends Controller
{
    public function index($userId)
    {
        $totalIncome = Income::where('user_id',$userId)->sum('amount');
        $totalExpense = Expense::where('user_id',$userId)->sum('amount');
        $totalLoan = Loan::where('user_id',$userId)->sum('amount');

        return response()->json([
            'totalIncome'=>$totalIncome,
            'totalExpense'=>$totalExpense,
            'totalLoan'=>$totalLoan,
            'netBalance'=>$totalIncome - $totalExpense,
        ]);
    }
}
