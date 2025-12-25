<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Bill::where('user_id', Auth::id());

        if ($year) {
            $query->whereYear('due_date', $year);
        }

        if ($month && $month !== 'all') {
            $query->whereMonth('due_date', $month);
        }

        $bills = $query->orderBy('due_date', 'asc')->get();

        return Inertia::render('Bills', [
            'bills' => $bills,
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
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid',
        ]);

        $request->user()->bills()->create($validated);

        return redirect()->route('bills')->with('success', 'Bill created successfully.');
    }

    public function update(Request $request, Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid',
        ]);

        $bill->update($validated);

        return back()->with('success', 'Bill updated successfully.')->with('updated_id', $bill->id);
    }

    public function destroy(Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        $bill->delete();

        return redirect()->route('bills')->with('success', 'Bill deleted successfully.');
    }

    public function pay(Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            abort(403);
        }

        // Create a transaction for this bill payment
        Transaction::create([
            'user_id' => Auth::id(),
            'name' => 'Bill Payment: ' . $bill->name,
            'amount' => $bill->amount,
            'date' => now(),
            'type' => 'expense',
            'category' => 'Bills',
            'status' => 'Completed'
        ]);

        // Mark the bill as paid
        $bill->update(['status' => 'paid']);

        return redirect()->route('bills')->with('success', 'Bill paid and transaction created successfully.');
    }
}
