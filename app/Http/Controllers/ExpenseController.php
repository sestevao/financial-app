<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $baseQuery = Transaction::where('user_id', $userId)->where('type', 'expense');

        if ($year) {
            $baseQuery->whereYear('date', $year);
        }

        if ($month && $month !== 'all') {
            $baseQuery->whereMonth('date', $month);
        }

        $expensesByCategory = (clone $baseQuery)
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpense = $baseQuery->sum('amount');

        return Inertia::render('Expenses', [
            'expensesByCategory' => $expensesByCategory,
            'totalExpense' => $totalExpense,
            'filters' => [
                'year' => $year,
                'month' => $month
            ]
        ]);
    }
}
