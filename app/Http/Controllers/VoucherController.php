<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Voucher::with(['customer', 'vendor', 'creator'])->latest('voucher_date');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('voucher_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('voucher_date', '<=', $dateTo);
        }

        $vouchers = $query->paginate(15)->withQueryString();

        return view('vouchers.index', compact('vouchers'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('vouchers.create', compact('customers', 'vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:receipt,payment'],
            'customer_id' => ['required_if:type,receipt', 'nullable', 'exists:customers,id'],
            'vendor_id' => ['required_if:type,payment', 'nullable', 'exists:vendors,id'],
            'voucher_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,online'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $prefix = $validated['type'] === 'receipt' ? 'RV' : 'PV';
        $voucherNumber = $prefix.'-'.date('Ymd', strtotime($validated['voucher_date'])).'-'.strtoupper(Str::random(4));

        $voucher = Voucher::create([
            'company_id' => Auth::user()?->company_id,
            'voucher_number' => $voucherNumber,
            'type' => $validated['type'],
            'customer_id' => $validated['type'] === 'receipt' ? $validated['customer_id'] : null,
            'vendor_id' => $validated['type'] === 'payment' ? $validated['vendor_id'] : null,
            'voucher_date' => $validated['voucher_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', 'Payment Voucher '.$voucher->voucher_number.' recorded successfully.');
    }

    public function show(Voucher $voucher): View
    {
        $voucher->load(['customer', 'vendor', 'creator']);

        return view('vouchers.show', compact('voucher'));
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $vNum = $voucher->voucher_number;
        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher '.$vNum.' deleted successfully.');
    }
}
