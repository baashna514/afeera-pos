@extends('layouts.app', ['title' => 'Expense Categories'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-folder-tree text-brand-600"></i>
                <span>Expense Categories</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Define and manage categories for organizing business expenses.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-money-bill-wave text-brand-600"></i>
                <span>View Expenses</span>
            </a>
            <button onclick="document.getElementById('createCategoryModal').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    <!-- Expense Categories Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form action="{{ route('expense-categories.index') }}" method="GET" class="flex-1 max-w-sm flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search category..."
                           class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                </div>
                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                    Search
                </button>
            </form>
            <span class="text-xs text-slate-500 font-medium">Total: {{ $categories->total() }} Categories</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="p-4">#</th>
                        <th class="p-4">Category Name</th>
                        <th class="p-4">Description</th>
                        <th class="p-4">Associated Expenses</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $index => $category)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-mono text-slate-400">{{ $categories->firstItem() + $index }}</td>
                            <td class="p-4 font-bold text-slate-800">
                                <span class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center text-xs font-bold">
                                        <i class="fa-solid fa-tag"></i>
                                    </span>
                                    <span>{{ $category->name }}</span>
                                </span>
                            </td>
                            <td class="p-4 text-slate-500 max-w-xs truncate">{{ $category->description ?? '-' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $category->expenses_count }} expenses
                                </span>
                            </td>
                            <td class="p-4">
                                @if($category->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">Inactive</span>
                                @endif
                            </td>
                            <td class="p-4 text-right space-x-1">
                                <button onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->is_active ? 'true' : 'false' }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete category {{ $category->name }}?');">
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
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                No expense categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $categories->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createCategoryModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-plus text-brand-600"></i> Add Expense Category
            </h3>
            <button onclick="document.getElementById('createCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('expense-categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Electricity Bill, Salaries"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description of this category..."
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                <label for="create_is_active" class="text-xs font-bold text-slate-700">Active Category</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('createCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-xs transition">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-600"></i> Edit Expense Category
            </h3>
            <button onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="editCategoryForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Category Name *</label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Description</label>
                <textarea name="description" id="edit_description" rows="2"
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                <label for="edit_is_active" class="text-xs font-bold text-slate-700">Active Category</label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-xs transition">Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, description, isActive) {
        document.getElementById('editCategoryForm').action = '/expense-categories/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_is_active').checked = isActive;
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }
</script>
@endsection
