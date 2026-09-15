@extends('layouts.app', ['title' => 'Record Expense'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-plus text-emerald-600"></i>
                <span>Record New Expense</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Enter operational expense details to keep accounts accurate.</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            &larr; Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
        <form action="{{ route('expenses.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Expense Category -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Expense Category <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" onclick="openQuickExpenseCategoryModal()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition">
                        <i class="fa-solid fa-plus-circle"></i> New Category
                    </button>
                </div>
                <select name="expense_category_id" id="expense_category_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Amount & Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Amount (Rs.) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required placeholder="e.g. 5000"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Expense Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <!-- Payment Method & Reference -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Payment Method <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Reference / Receipt No.
                    </label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. Bill # 98123"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-mono">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Note / Reason
                </label>
                <textarea name="note" rows="3" placeholder="Provide details about this expense..."
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">{{ old('note') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition">
                    Save Expense Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Expense Category Modal -->
<div id="quickExpenseCategoryModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-emerald-600"></i> Quick Add Expense Category
            </h3>
            <button type="button" onclick="closeQuickExpenseCategoryModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickExpenseCategory(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qec_name" required placeholder="e.g. Utility Bills, Rent, Refreshments..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description (Optional)</label>
                <input type="text" id="qec_desc" placeholder="Brief notes..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickExpenseCategoryModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qec_submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickExpenseCategoryModal() {
        const modal = document.getElementById('quickExpenseCategoryModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qec_name').focus();
        }
    }

    function closeQuickExpenseCategoryModal() {
        const modal = document.getElementById('quickExpenseCategoryModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qec_name').value = '';
            document.getElementById('qec_desc').value = '';
        }
    }

    async function saveQuickExpenseCategory(e) {
        e.preventDefault();
        const name = document.getElementById('qec_name').value.trim();
        const description = document.getElementById('qec_desc').value.trim();
        if (!name) return;

        const btn = document.getElementById('qec_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('expense-categories.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, description }),
            });

            const data = await res.json();
            if (data.success && data.category) {
                const select = document.getElementById('expense_category_id');
                const opt = new Option(data.category.name, data.category.id, true, true);
                select.add(opt);
                closeQuickExpenseCategoryModal();
            } else {
                alert(data.message || 'Could not save expense category.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating expense category.');
        } finally {
            btn.disabled = false;
        }
    }
</script>
@endsection
