<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $queryIncome = Transaction::where('user_id', $userId)->where('type', 'income');
        $queryExpense = Transaction::where('user_id', $userId)->where('type', 'expense');

        if ($year) {
            $queryIncome->whereYear('date', $year);
            $queryExpense->whereYear('date', $year);
        }

        if ($month && $month !== 'all') {
            $queryIncome->whereMonth('date', $month);
            $queryExpense->whereMonth('date', $month);
        }

        $totalIncome = $queryIncome->sum('amount');
        $totalExpense = $queryExpense->sum('amount');

        $balance = $totalIncome - $totalExpense;

        return Inertia::render('Balances', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'filters' => [
                'year' => $year,
                'month' => $month
            ]
        ]);
    }
}
