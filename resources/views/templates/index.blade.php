@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="space-y-6" x-data="{ selectedItems: [], selectAll: false, search: '{{ request('search', '') }}' }">
    <!-- Top Action Bar: Title & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Templates</h1>
        </div>

        <!-- Search Input Pill Matching Screenshot -->
        <form method="GET" action="{{ route('templates.index') }}" class="relative w-full sm:w-80">
            <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search template name" 
                   class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition">
        </form>
    </div>

    <!-- Templates Data Table Matching Screenshot -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-white border-b border-slate-200/80 text-[11px] font-semibold text-slate-700 uppercase tracking-wider select-none">
                    <tr>
                        <th scope="col" class="py-3.5 px-4 w-12 text-center">
                            <input type="checkbox" 
                                   x-model="selectAll"
                                   @change="selectedItems = selectAll ? [{{ $templates->pluck('id')->join(',') }}] : []"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th scope="col" class="py-3.5 px-6 font-semibold normal-case text-slate-700 text-xs">
                            <span class="inline-flex items-center gap-1 cursor-pointer">
                                <span>Name</span>
                                <span class="text-slate-500 text-xs font-bold leading-none">↑</span>
                            </span>
                        </th>
                        <th scope="col" class="py-3.5 px-6 font-semibold normal-case text-slate-700 text-xs">
                            Code
                        </th>
                        <th scope="col" class="py-3.5 px-6 font-semibold normal-case text-slate-700 text-xs">
                            Language
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($templates as $template)
                    <tr class="hover:bg-slate-50/70 transition cursor-pointer group" onclick="if(!event.target.closest('input')) window.location='{{ route('templates.show', $template->id) }}'">
                        <td class="py-4 px-4 text-center" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $template->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-4 px-6 text-slate-800 tracking-wide font-normal">
                            <a href="{{ route('templates.show', $template->id) }}" class="hover:text-indigo-600 transition">
                                {{ $template->name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-slate-800 tracking-wide font-normal">
                            {{ $template->type }}
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-normal">
                            {{ $template->language?->name ?? 'English (United Kingdom)' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 text-xs">
                            No templates found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
