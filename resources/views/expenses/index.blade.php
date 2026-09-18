@extends('layouts.app', ['title' => 'Business Expenses'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-brand-600"></i>
                <span>Business Expenses</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Log, manage, and monitor company operating expenses.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('expense-categories.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-folder-tree text-indigo-600"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Record Expense</span>
            </a>
        </div>
    </div>

    <!-- Filter & Summary Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-4">
        <form action="{{ route('expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['category_id', 'payment_method', 'date_from', 'date_to', 'search']))
                    <a href="{{ route('expenses.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">Showing {{ $expenses->total() }} recorded expenses</span>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold">
                <span>Total Filtered Expense:</span>
                <span class="text-sm font-black text-amber-900">Rs. {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="p-4">Date</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Reference</th>
                        <th class="p-4">Payment Method</th>
                        <th class="p-4">Note / Reason</th>
                        <th class="p-4 text-right">Amount</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-mono font-medium text-slate-600">
                                {{ $expense->expense_date->format('d M Y') }}
                            </td>
                            <td class="p-4 font-bold text-slate-800">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $expense->category->name ?? 'General' }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-500">
                                {{ $expense->reference_no ?? '-' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $expense->payment_method) }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-500 max-w-xs truncate">
                                {{ $expense->note ?? '-' }}
                            </td>
                            <td class="p-4 text-right font-black text-rose-600">
                                Rs. {{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="p-4 text-right space-x-1">
                                <a href="{{ route('expenses.edit', $expense) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition inline-block">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this expense record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-2xl mb-2 block"></i>
                                No expenses recorded yet for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection
