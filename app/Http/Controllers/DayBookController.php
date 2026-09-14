<?php

namespace App\Http\Controllers;

use App\Models\DayBook;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DayBookController extends Controller
{
    /**
     * Display Day Book / Cash Book for selected date.
     */
    public function index(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $companyId = Auth::user()?->company_id;

        // 1. Opening Balance Record for selected date
        $dayBook = DayBook::whereDate('date', $date)->first();
        $openingBalance = $dayBook ? (float) $dayBook->opening_balance : 0.00;

        // 2. Cash Inflows (+)
        // A. Cash Sales
        $cashSales = Sale::whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->where('paid_amount', '>', 0)
            ->with('customer')
            ->get()
            ->map(function ($sale) {
                return [
                    'time' => $sale->created_at,
                    'type' => 'Cash Sale',
                    'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'reference' => $sale->invoice_number,
                    'party' => $sale->customer_display_name,
                    'description' => 'Sale Invoice #'.$sale->invoice_number,
                    'cash_in' => (float) $sale->paid_amount,
                    'cash_out' => 0.0,
                    'url' => route('sales.show', $sale),
                ];
            });

        // B. Receipt Vouchers (Cash)
        $receiptVouchers = Voucher::whereDate('voucher_date', $date)
            ->where('type', 'receipt')
            ->where('payment_method', 'cash')
            ->with('customer')
            ->get()
            ->map(function ($vch) {
                return [
                    'time' => $vch->created_at,
                    'type' => 'Receipt Voucher',
                    'badge_class' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'reference' => $vch->voucher_number,
                    'party' => $vch->customer->name ?? 'Customer',
                    'description' => 'Cash received from customer'.($vch->notes ? ' - '.$vch->notes : ''),
                    'cash_in' => (float) $vch->amount,
                    'cash_out' => 0.0,
                    'url' => route('vouchers.show', $vch),
                ];
            });

        // 3. Cash Outflows (-)
        // C. Cash Purchases
        $cashPurchases = Purchase::whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->where('paid_amount', '>', 0)
            ->with('vendor')
            ->get()
            ->map(function ($purchase) {
                return [
                    'time' => $purchase->created_at,
                    'type' => 'Cash Purchase',
                    'badge_class' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'reference' => $purchase->reference_no,
                    'party' => $purchase->vendor->name ?? 'Supplier',
                    'description' => 'Purchase Invoice #'.$purchase->reference_no,
                    'cash_in' => 0.0,
                    'cash_out' => (float) $purchase->paid_amount,
                    'url' => route('purchases.show', $purchase),
                ];
            });

        // D. Payment Vouchers (Cash)
        $paymentVouchers = Voucher::whereDate('voucher_date', $date)
            ->where('type', 'payment')
            ->where('payment_method', 'cash')
            ->with('vendor')
            ->get()
            ->map(function ($vch) {
                return [
                    'time' => $vch->created_at,
                    'type' => 'Payment Voucher',
                    'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'reference' => $vch->voucher_number,
                    'party' => $vch->vendor->name ?? 'Vendor',
                    'description' => 'Cash paid to vendor'.($vch->notes ? ' - '.$vch->notes : ''),
                    'cash_in' => 0.0,
                    'cash_out' => (float) $vch->amount,
                    'url' => route('vouchers.show', $vch),
                ];
            });

        // E. Cash Expenses
        $cashExpenses = Expense::whereDate('expense_date', $date)
            ->where('payment_method', 'cash')
            ->with('category')
            ->get()
            ->map(function ($exp) {
                return [
                    'time' => $exp->created_at,
                    'type' => 'Operating Expense',
                    'badge_class' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'reference' => $exp->reference_no ?? 'EXP-'.$exp->id,
                    'party' => $exp->category->name ?? 'Expense',
                    'description' => 'Expense: '.($exp->category->name ?? 'General').($exp->note ? ' - '.$exp->note : ''),
                    'cash_in' => 0.0,
                    'cash_out' => (float) $exp->amount,
                    'url' => route('expenses.index'),
                ];
            });

        // Combine & sort chronologically
        $transactions = $cashSales->concat($receiptVouchers)
            ->concat($cashPurchases)
            ->concat($paymentVouchers)
            ->concat($cashExpenses)
            ->sortBy('time')
            ->values();

        // Calculate running drawer balance
        $runningBalance = $openingBalance;
        $totalCashIn = 0;
        $totalCashOut = 0;

        $totalSalesCash = $cashSales->sum('cash_in');
        $totalReceiptsCash = $receiptVouchers->sum('cash_in');
        $totalPurchasesCash = $cashPurchases->sum('cash_out');
        $totalPaymentsCash = $paymentVouchers->sum('cash_out');
        $totalExpensesCash = $cashExpenses->sum('cash_out');

        $transactions = $transactions->map(function ($entry) use (&$runningBalance, &$totalCashIn, &$totalCashOut) {
            $totalCashIn += $entry['cash_in'];
            $totalCashOut += $entry['cash_out'];
            $runningBalance += ($entry['cash_in'] - $entry['cash_out']);
            $entry['running_balance'] = $runningBalance;

            return $entry;
        });

        $expectedNetCashInHand = $runningBalance;

        return view('day-book.index', compact(
            'date',
            'dayBook',
            'openingBalance',
            'transactions',
            'totalSalesCash',
            'totalReceiptsCash',
            'totalPurchasesCash',
            'totalPaymentsCash',
            'totalExpensesCash',
            'totalCashIn',
            'totalCashOut',
            'expectedNetCashInHand'
        ));
    }

    /**
     * Store or update Opening Balance for selected date.
     */
    public function storeOpeningBalance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $companyId = Auth::user()?->company_id;

        DayBook::updateOrCreate(
            [
                'company_id' => $companyId,
                'date' => $validated['date'],
            ],
            [
                'opening_balance' => $validated['opening_balance'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]
        );

        return redirect()->route('day-book.index', ['date' => $validated['date']])
            ->with('success', 'Opening Cash Balance updated successfully for '.Carbon::parse($validated['date'])->format('d M Y').'.');
    }
}
