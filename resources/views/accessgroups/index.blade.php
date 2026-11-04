@extends('layouts.app')

@section('title', 'Access Control')

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '{{ request('search', '') }}', selectedAll: false, selectedItems: [] }">
    <!-- Header with Title, Search and + Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Access Control</h1>

        <div class="flex items-center gap-3">
            <!-- Search Pill -->
            <form action="{{ route('accessgroups.index') }}" method="GET" class="relative w-64 sm:w-72">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search" 
                       class="w-full bg-white border border-slate-200 rounded-full pl-9 pr-4 py-1.5 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-2xs transition">
            </form>

            <!-- + Add Button -->
            <a href="{{ route('accessgroups.create') }}" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-1.5 rounded-full font-semibold text-xs shadow-2xs transition whitespace-nowrap">
                + Add
            </a>
        </div>
    </div>

    <!-- Access Control Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 overflow-hidden min-h-[460px] flex flex-col justify-between">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 text-slate-900 font-bold text-[13px] bg-slate-50/50">
                        <th class="py-3.5 px-4 w-12 text-center">
                            <input type="checkbox" 
                                   @click="selectedAll = !selectedAll; selectedItems = selectedAll ? {{ json_encode($groups->pluck('id')) }} : []"
                                   :checked="selectedAll"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th class="py-3.5 px-4 font-bold">Name</th>
                        <th class="py-3.5 px-6 font-bold text-center w-28">Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($groups as $group)
                    <tr class="hover:bg-slate-50/70 transition cursor-pointer group" onclick="if(!event.target.closest('input')) window.location='{{ route('accessgroups.show', $group->id) }}'">
                        <td class="py-4 px-4 text-center" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $group->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-4 px-4 text-slate-800 tracking-wide text-xs">
                            <a href="{{ route('accessgroups.show', $group->id) }}" class="hover:text-indigo-600 transition">
                                {{ $group->name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($group->active)
                                <span class="material-icons text-emerald-500 text-lg leading-none" title="Active">check_circle</span>
                            @else
                                <span class="material-icons text-slate-300 text-lg leading-none" title="Inactive">radio_button_unchecked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center text-slate-400 text-xs">
                            No access groups found.
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
                1-{{ $groups->count() }} of {{ $groups->count() }}
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
