<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Goal::where('user_id', Auth::id());

        if ($year) {
            $query->whereYear('deadline', $year);
        }

        if ($month && $month !== 'all') {
            $query->whereMonth('deadline', $month);
        }

        $goals = $query->orderBy('deadline', 'asc')->get();

        return Inertia::render('Goals', [
            'goals' => $goals,
            'filters' => [
                'year' => $year,
                'month' => $month
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
        ]);

        $request->user()->goals()->create($validated);

        return redirect()->route('goals')->with('success', 'Goal created successfully.');
    }

    public function update(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
        ]);

        $goal->update($validated);

        return back()->with('success', 'Goal updated successfully.')->with('updated_id', $goal->id);
    }

    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->delete();

        return redirect()->route('goals')->with('success', 'Goal deleted successfully.');
    }

    public function deposit(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $amount = $request->input('amount');

        // Create transaction
        Transaction::create([
            'user_id' => Auth::id(),
            'name' => 'Deposit to Goal: ' . $goal->name,
            'amount' => $amount,
            'date' => now(),
            'type' => 'expense', // Money leaving "checking" to go to savings
            'category' => 'Savings',
            'status' => 'Completed'
        ]);

        // Update goal
        $goal->increment('current_amount', $amount);

        return redirect()->route('goals')->with('success', 'Deposit added successfully.');
    }
}
