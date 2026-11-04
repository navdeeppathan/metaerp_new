@extends('layouts.app')

@section('title', 'Access Control')

@section('content')
<div class="space-y-4" x-data="{ searchQuery: '{{ request('search', request('q', '')) }}', selectedAll: false, selectedItems: [] }">
    <!-- Header with Title, Search and + Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">Access Control</h1>

        <div class="flex items-center gap-3">
            <!-- Search Pill -->
            <form action="{{ route('accessgroups.index') }}" method="GET" class="relative w-full sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search', request('q')) }}"
                    placeholder="Search"
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/90 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </form>

            <!-- + Add Button -->
            <a href="{{ route('accessgroups.create') }}" class="px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors shrink-0 cursor-pointer">
                <span class="material-icons text-sm font-bold leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Access Control Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden min-h-[calc(100vh-210px)] flex flex-col justify-between">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th class="py-4 px-6 w-14 text-left">
                            <input type="checkbox" 
                                   @click="selectedAll = !selectedAll; selectedItems = selectedAll ? {{ json_encode($groups->pluck('id')) }} : []"
                                   :checked="selectedAll"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">Name</th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm text-center w-28">Active</th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($groups as $group)
                    <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        :class="{ 'bg-slate-50/90': selectedItems.includes({{ $group->id }}) }"
                        onclick="if(!event.target.closest('input')) window.location='{{ route('accessgroups.show', $group->id) }}'">
                        <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $group->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            <a href="{{ route('accessgroups.show', $group->id) }}" class="hover:text-indigo-600 transition">
                                {{ $group->name }}
                            </a>
                        </td>
                        <td class="py-5 px-6 text-center">
                            @if($group->active)
                                <span class="material-icons text-emerald-500 text-lg leading-none inline-block align-middle" title="Active">
                                    check_circle_outline
                                </span>
                            @else
                                <span class="material-icons text-red-500 text-lg leading-none inline-block align-middle" title="Inactive">
                                    remove_circle_outline
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-16 text-center text-slate-400 text-sm">
                            No access groups found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($groups->total() > 0)
            <div class="px-6 py-3.5 border-t border-slate-100">
                @include('partials.pagination', ['paginator' => $groups])
            </div>
        @endif
    </div>
</div>
@endsection
