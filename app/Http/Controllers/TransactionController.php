<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Transaction::where('user_id', Auth::id());

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($month && $month !== 'all') {
            $query->whereMonth('date', $month);
        }

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Transactions', [
            'transactions' => $transactions,
            'filters' => [
                'year' => $year,
                'month' => $month,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string',
            'status' => 'required|in:Completed,Pending'
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('transactions')->with('success', 'Transaction created successfully.');
    }

    /**
     * Import transactions from PDF.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();
            
            Log::info("PDF Import Started for user " . Auth::id());
            Log::info("Extracted Text Sample: " . substr($text, 0, 500));

            // Split text into lines
            $lines = explode("\n", $text);
            $importedCount = 0;

            // State machine for multi-line parsing (Lloyds Bank style)
            $currentTx = [];
            $state = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                Log::info("Processing line: " . $line);

                // --- STRATEGY 1: Lloyds Bank Multi-line Parsing ---
                
                // Detect field headers to change state
                if ($line === 'Date') {
                    $state = 'date';
                    // If we were building a transaction and hit a new Date without finishing, 
                    // it might mean the previous one was incomplete or this is a fresh start.
                    // But typically 'Balance (£)' ends the previous one.
                    $currentTx = []; // Reset for new potential transaction
                    continue;
                }
                if ($line === 'Description') {
                    $state = 'description';
                    continue;
                }
                if ($line === 'Type') {
                    $state = 'type';
                    continue;
                }
                if ($line === 'Money In (£)') {
                    $state = 'money_in';
                    continue;
                }
                if ($line === 'Money Out (£)') {
                    $state = 'money_out';
                    continue;
                }
                if ($line === 'Balance (£)') {
                    $state = 'balance';
                    continue;
                }

                // Process values based on state
                if ($state === 'date') {
                    // Format: 01 Dec 25.
                    if (preg_match('/(\d{2})\s([A-Za-z]{3})\s(\d{2})\.?/', $line, $matches)) {
                        $currentTx['date'] = $matches[1] . ' ' . $matches[2] . ' 20' . $matches[3]; // 01 Dec 2025
                    }
                    $state = null; // Reset state after consuming value
                }
                elseif ($state === 'description') {
                    // Take the whole line, remove trailing dot if present
                    $currentTx['description'] = rtrim($line, '.');
                    $state = null;
                }
                elseif ($state === 'money_in') {
                    if ($line !== 'blank.' && $line !== 'blank') {
                        // Parse amount: 26.05. -> 26.05
                        $amountStr = str_replace([',', '£'], '', rtrim($line, '.'));
                        $currentTx['amount'] = floatval($amountStr);
                        $currentTx['type'] = 'income';
                    }
                    $state = null;
                }
                elseif ($state === 'money_out') {
                    if ($line !== 'blank.' && $line !== 'blank') {
                        $amountStr = str_replace([',', '£'], '', rtrim($line, '.'));
                        $currentTx['amount'] = floatval($amountStr);
                        $currentTx['type'] = 'expense';
                    }
                    $state = null;
                }
                elseif ($state === 'balance') {
                    // End of transaction block. Save if valid.
                    if (isset($currentTx['date']) && isset($currentTx['amount']) && isset($currentTx['description'])) {
                        Log::info("Saving Lloyds Transaction: " . json_encode($currentTx));
                        
                        Transaction::create([
                            'user_id' => Auth::id(),
                            'name' => $currentTx['description'],
                            'amount' => abs($currentTx['amount']),
                            'date' => date('Y-m-d', strtotime($currentTx['date'])),
                            'type' => $currentTx['type'],
                            'status' => 'Completed',
                            'category' => 'Imported',
                        ]);
                        $importedCount++;
                    }
                    $currentTx = []; // Clear
                    $state = null;
                }

                // --- STRATEGY 2: Single-line Parsing (Fallback) ---
                // Only run if we aren't in the middle of a multi-line parse
                if (empty($currentTx)) {
                    // Expanded regex to catch more date formats
                    // YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY
                    if (preg_match('/(\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}|\d{2}-\d{2}-\d{4})/', $line, $dateMatch)) {
                        $dateStr = $dateMatch[0];
                        
                        // Expanded amount regex to be more flexible
                        // Catches: 1,234.56 | 1234.56 | -1,234.56 | -1234.56
                        if (preg_match('/(-?[\d,]+\.\d{2})/', $line, $amountMatch)) {
                            $amountStr = str_replace(',', '', $amountMatch[0]);
                            $amount = floatval($amountStr);
                            
                            // The description is everything else
                            $description = trim(str_replace([$dateStr, $amountMatch[0]], '', $line));
                            $description = preg_replace('/\s+/', ' ', $description); // Clean extra spaces
                            
                            // Basic filtering to skip header/footer lines that might match coincidence
                            if (strlen($description) < 3) continue;

                            Log::info("Match found (Single Line)! Date: $dateStr, Amount: $amount, Desc: $description");

                            // Normalize date to YYYY-MM-DD
                            $date = date('Y-m-d', strtotime(str_replace('/', '-', $dateStr)));

                            $type = $amount < 0 ? 'expense' : 'income';
                            
                            Transaction::create([
                                'user_id' => Auth::id(),
                                'name' => $description ?: 'Imported Transaction',
                                'amount' => abs($amount),
                                'date' => $date,
                                'type' => $type,
                                'status' => 'Completed',
                                'category' => 'Imported',
                            ]);
                            
                            $importedCount++;
                        }
                    }
                }
            }

            if ($importedCount === 0) {
                Log::warning("No transactions found in PDF import.");
                return redirect()->route('transactions')->with('error', 'No transactions found. The PDF format might not be supported yet. Check logs for details.');
            }

            return redirect()->route('transactions')->with('success', "$importedCount transactions imported successfully.");

        } catch (\Exception $e) {
            Log::error("PDF Import Error: " . $e->getMessage());
            return redirect()->route('transactions')->with('error', 'An error occurred while parsing the PDF: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure the user owns the transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string',
            'status' => 'required|in:Completed,Pending'
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transaction updated successfully.')->with('updated_id', $transaction->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        // Ensure the user owns the transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('transactions')->with('success', 'Transaction deleted successfully.');
    }
}
