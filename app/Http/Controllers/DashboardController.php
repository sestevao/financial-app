<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $now = Carbon::now();

        // 1. Total Balance
        $totalIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpense;

        // 2. Goals (for Chart)
        $goals = Goal::where('user_id', $userId)->get();

        // 3. Upcoming Bills
        $upcomingBills = Bill::where('user_id', $userId)
            ->where('status', 'unpaid')
            ->where('due_date', '>=', $now->toDateString())
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Activity
        $recentTransactions = Transaction::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 5. Weekly Statistics (Income vs Expense)
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endOfLastWeek = $now->copy()->subWeek()->endOfWeek();

        $thisWeekIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('amount');

        $thisWeekExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('amount');

        $lastWeekIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfLastWeek, $endOfLastWeek])
            ->sum('amount');

        $lastWeekExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfLastWeek, $endOfLastWeek])
            ->sum('amount');

        // 6. Monthly Expenses Breakdown
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $thisMonthExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->get();

        $lastMonthTotalExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $thisMonthTotalExpense = $thisMonthExpenses->sum('total');

        return Inertia::render('Dashboard', [
            'totalBalance' => $totalBalance,
            'goals' => $goals,
            'upcomingBills' => $upcomingBills,
            'recentTransactions' => $recentTransactions,
            'weeklyStats' => [
                'thisWeek' => ['income' => $thisWeekIncome, 'expense' => $thisWeekExpense],
                'lastWeek' => ['income' => $lastWeekIncome, 'expense' => $lastWeekExpense],
            ],
            'monthlyStats' => [
                'breakdown' => $thisMonthExpenses,
                'thisMonthTotal' => $thisMonthTotalExpense,
                'lastMonthTotal' => $lastMonthTotalExpense,
            ]
        ]);
    }
}
