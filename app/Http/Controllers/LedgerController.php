<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Vendor;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    /**
     * Display Customer Ledger (Customer Khata statement).
     */
    public function customerLedger(Request $request): View
    {
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        $ledgerEntries = collect();
        $totalDebit = 0;
        $totalCredit = 0;
        $closingBalance = 0;

        if ($selectedCustomer) {
            // 1. Sales (Invoices)
            $sales = Sale::where('customer_id', $selectedCustomer->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->flatMap(function ($sale) {
                    $records = [];
                    // Billed amount (Customer owes this -> Debit)
                    $records[] = [
                        'timestamp' => $sale->created_at,
                        'sort_order' => 1,
                        'id' => $sale->id,
                        'date' => $sale->created_at,
                        'type' => 'Sale Invoice',
                        'type_badge' => 'bg-emerald-100 text-emerald-800',
                        'reference' => $sale->invoice_number,
                        'url' => route('sales.show', $sale),
                        'description' => 'Sale Invoice #'.$sale->invoice_number.' ('.$sale->payment_status_label.')',
                        'debit' => (float) $sale->total_amount,
                        'credit' => 0.0,
                    ];

                    // Payment received at sale (Payment reduces debt -> Credit)
                    if ($sale->paid_amount > 0) {
                        $records[] = [
                            'timestamp' => $sale->created_at,
                            'sort_order' => 2,
                            'id' => $sale->id,
                            'date' => $sale->created_at,
                            'type' => 'Payment Received',
                            'type_badge' => 'bg-blue-100 text-blue-800',
                            'reference' => $sale->invoice_number,
                            'url' => route('sales.show', $sale),
                            'description' => 'Payment received via '.ucfirst($sale->payment_method),
                            'debit' => 0.0,
                            'credit' => (float) $sale->paid_amount,
                        ];
                    }

                    return $records;
                });

            // 2. Sale Returns (Goods returned -> Credit)
            $returns = SaleReturn::where('customer_id', $selectedCustomer->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(function ($ret) {
                    return [
                        'timestamp' => $ret->created_at ?? Carbon::parse($ret->return_date),
                        'sort_order' => 3,
                        'id' => $ret->id,
                        'date' => $ret->created_at ?? $ret->return_date,
                        'type' => 'Sale Return',
                        'type_badge' => 'bg-amber-100 text-amber-800',
                        'reference' => $ret->return_number,
                        'url' => route('sale-returns.show', $ret),
                        'description' => 'Return items for '.($ret->sale ? 'Inv #'.$ret->sale->invoice_number : 'Customer return'),
                        'debit' => 0.0,
                        'credit' => (float) $ret->total_amount,
                    ];
                });

            // 3. Receipt Vouchers (Payment received from customer -> Credit)
            $vouchers = Voucher::where('customer_id', $selectedCustomer->id)
                ->where('type', 'receipt')
                ->when($dateFrom, fn ($q) => $q->whereDate('voucher_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('voucher_date', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(function ($vch) {
                    return [
                        'timestamp' => $vch->created_at ?? Carbon::parse($vch->voucher_date),
                        'sort_order' => 4,
                        'id' => $vch->id,
                        'date' => $vch->created_at ?? $vch->voucher_date,
                        'type' => 'Receipt Voucher',
                        'type_badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                        'reference' => $vch->voucher_number,
                        'url' => route('vouchers.show', $vch),
                        'description' => 'Payment received via '.ucfirst($vch->payment_method).($vch->notes ? ' - '.$vch->notes : ''),
                        'debit' => 0.0,
                        'credit' => (float) $vch->amount,
                    ];
                });

            // Merge & sort strictly chronologically ascending (oldest first, newest last)
            $ledgerEntries = $sales->concat($returns)->concat($vouchers)->sortBy(function ($entry) {
                $ts = $entry['timestamp'] instanceof Carbon ? $entry['timestamp']->getTimestamp() : strtotime((string) $entry['timestamp']);

                return sprintf('%012d_%02d_%010d', $ts, $entry['sort_order'], $entry['id'] ?? 0);
            })->values();

            // Calculate running balance row by row
            $runningBalance = 0;
            $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
                $totalDebit += $entry['debit'];
                $totalCredit += $entry['credit'];
                $runningBalance += ($entry['debit'] - $entry['credit']);
                $entry['running_balance'] = $runningBalance;

                return $entry;
            });

            $closingBalance = $runningBalance;
        }

        return view('ledgers.customer', compact('customers', 'selectedCustomer', 'customerId', 'dateFrom', 'dateTo', 'ledgerEntries', 'totalDebit', 'totalCredit', 'closingBalance'));
    }

    /**
     * Display Vendor Ledger (Supplier Khata statement).
     */
    public function vendorLedger(Request $request): View
    {
        $vendorId = $request->query('vendor_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $vendors = Vendor::orderBy('name')->get();
        $selectedVendor = $vendorId ? Vendor::find($vendorId) : null;

        $ledgerEntries = collect();
        $totalDebit = 0;
        $totalCredit = 0;
        $closingBalance = 0;

        if ($selectedVendor) {
            // 1. Purchases (Invoices -> Credit, we owe vendor)
            $purchases = Purchase::where('vendor_id', $selectedVendor->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->flatMap(function ($purchase) {
                    $records = [];
                    // Billed amount (We owe vendor -> Credit)
                    $records[] = [
                        'timestamp' => $purchase->created_at ?? Carbon::parse($purchase->purchase_date),
                        'sort_order' => 1,
                        'id' => $purchase->id,
                        'date' => $purchase->created_at ?? $purchase->purchase_date,
                        'type' => 'Purchase Invoice',
                        'type_badge' => 'bg-emerald-100 text-emerald-800',
                        'reference' => $purchase->reference_no,
                        'url' => route('purchases.show', $purchase),
                        'description' => 'Purchase Invoice #'.$purchase->reference_no.' ('.$purchase->payment_status_label.')',
                        'debit' => 0.0,
                        'credit' => (float) $purchase->total_amount,
                    ];

                    // Payment made to vendor at purchase (Payment reduces payable -> Debit)
                    if ($purchase->paid_amount > 0) {
                        $records[] = [
                            'timestamp' => $purchase->created_at ?? Carbon::parse($purchase->purchase_date),
                            'sort_order' => 2,
                            'id' => $purchase->id,
                            'date' => $purchase->created_at ?? $purchase->purchase_date,
                            'type' => 'Payment Made',
                            'type_badge' => 'bg-blue-100 text-blue-800',
                            'reference' => $purchase->reference_no,
                            'url' => route('purchases.show', $purchase),
                            'description' => 'Payment made via '.ucfirst($purchase->payment_method ?? 'cash'),
                            'debit' => (float) $purchase->paid_amount,
                            'credit' => 0.0,
                        ];
                    }

                    return $records;
                });

            // 2. Purchase Returns (Goods returned to vendor -> Debit, reduces what we owe)
            $returns = PurchaseReturn::where('vendor_id', $selectedVendor->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(function ($ret) {
                    return [
                        'timestamp' => $ret->created_at ?? Carbon::parse($ret->return_date),
                        'sort_order' => 3,
                        'id' => $ret->id,
                        'date' => $ret->created_at ?? $ret->return_date,
                        'type' => 'Purchase Return',
                        'type_badge' => 'bg-amber-100 text-amber-800',
                        'reference' => $ret->return_number,
                        'url' => route('purchase-returns.show', $ret),
                        'description' => 'Returned items to vendor ('.$ret->return_number.')',
                        'debit' => (float) $ret->total_amount,
                        'credit' => 0.0,
                    ];
                });

            // 3. Payment Vouchers (Payment made to vendor -> Debit)
            $vouchers = Voucher::where('vendor_id', $selectedVendor->id)
                ->where('type', 'payment')
                ->when($dateFrom, fn ($q) => $q->whereDate('voucher_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('voucher_date', '<=', $dateTo))
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(function ($vch) {
                    return [
                        'timestamp' => $vch->created_at ?? Carbon::parse($vch->voucher_date),
                        'sort_order' => 4,
                        'id' => $vch->id,
                        'date' => $vch->created_at ?? $vch->voucher_date,
                        'type' => 'Payment Voucher',
                        'type_badge' => 'bg-blue-100 text-blue-800 border border-blue-300',
                        'reference' => $vch->voucher_number,
                        'url' => route('vouchers.show', $vch),
                        'description' => 'Payment made via '.ucfirst($vch->payment_method).($vch->notes ? ' - '.$vch->notes : ''),
                        'debit' => (float) $vch->amount,
                        'credit' => 0.0,
                    ];
                });

            // Merge & sort strictly chronologically ascending (oldest first, newest last)
            $ledgerEntries = $purchases->concat($returns)->concat($vouchers)->sortBy(function ($entry) {
                $ts = $entry['timestamp'] instanceof Carbon ? $entry['timestamp']->getTimestamp() : strtotime((string) $entry['timestamp']);

                return sprintf('%012d_%02d_%010d', $ts, $entry['sort_order'], $entry['id'] ?? 0);
            })->values();

            // Calculate running balance (Credit is payable, Debit reduces payable)
            $runningBalance = 0;
            $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
                $totalDebit += $entry['debit'];
                $totalCredit += $entry['credit'];
                $runningBalance += ($entry['credit'] - $entry['debit']);
                $entry['running_balance'] = $runningBalance;

                return $entry;
            });

            $closingBalance = $runningBalance;
        }

        return view('ledgers.vendor', compact('vendors', 'selectedVendor', 'vendorId', 'dateFrom', 'dateTo', 'ledgerEntries', 'totalDebit', 'totalCredit', 'closingBalance'));
    }
}
