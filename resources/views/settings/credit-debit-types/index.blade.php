@extends('layouts.app')

@section('title', 'CreditDebitTypes')

@section('content')
<div class="space-y-6" x-data="{ selectedItems: [], selectAll: false }">
    <!-- Top Action Bar: Title, Search, and Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">CreditDebitTypes</h1>
        </div>

        <div class="flex items-center gap-3">
            <!-- Search Input Pill Matching Screenshot -->
            <form method="GET" action="{{ route('settings.creditDebitTypes') }}" class="relative w-full sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search payment type" 
                       class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </form>

            <!-- + Add Button Matching Screenshot -->
            <a href="{{ route('settings.creditDebitTypes.create') }}" 
               class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-xs transition shrink-0 cursor-pointer">
                <span class="material-icons text-sm font-bold leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- CreditDebitTypes Data Table Matching Screenshot -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-white border-b border-slate-200/80 text-[11px] font-semibold text-slate-700 select-none">
                    <tr>
                        <th scope="col" class="py-3.5 px-4 w-12 text-center">
                            <input type="checkbox" 
                                   x-model="selectAll"
                                   @change="selectedItems = selectAll ? [{{ $items->pluck('id')->join(',') }}] : []"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th scope="col" class="py-3.5 px-6 font-semibold text-slate-700 text-xs">
                            Description
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/70 transition cursor-pointer group" onclick="if(!event.target.closest('input')) window.location='{{ route('settings.creditDebitTypes.show', $item->id) }}'">
                        <td class="py-4 px-4 text-center" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $item->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-4 px-6 text-slate-800 tracking-wide font-normal">
                            <a href="{{ route('settings.creditDebitTypes.show', $item->id) }}" class="hover:text-indigo-600 transition">
                                {{ $item->description }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-12 text-center text-slate-400 text-xs">
                            No records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination Matching Screenshot -->
        <div class="px-6 py-3 border-t border-slate-100 flex items-center justify-end gap-6 text-xs text-slate-600 select-none bg-white">
            <div class="flex items-center gap-1.5 cursor-pointer">
                <span>Rows per page:</span>
                <span class="font-medium">10</span>
                <span class="material-icons text-sm text-slate-400">arrow_drop_down</span>
            </div>

            <div>
                1-{{ $items->count() }} of {{ $items->count() }}
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-40" disabled>
                    <span class="material-icons text-sm leading-none">chevron_left</span>
                </button>
                <button type="button" class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-40" disabled>
                    <span class="material-icons text-sm leading-none">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
