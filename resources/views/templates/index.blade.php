@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="space-y-4" x-data="{ selectedItems: [], selectAll: false, search: '{{ request('search', '') }}' }">
    <!-- Top Action Bar: Title & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">Templates</h1>
        </div>

        <!-- Search Input Pill Matching Screenshot -->
        <form method="GET" action="{{ route('templates.index') }}" class="relative w-full sm:w-80">
            <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search template name" 
                   class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/90 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition">
        </form>
    </div>

    <!-- Templates Data Table Matching Screenshot -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden min-h-[calc(100vh-210px)] flex flex-col justify-between">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th scope="col" class="py-4 px-6 w-14 text-left">
                            <input type="checkbox" 
                                   x-model="selectAll"
                                   @change="selectedItems = selectAll ? [{{ $templates->pluck('id')->join(',') }}] : []"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            <span class="inline-flex items-center gap-1 cursor-pointer">
                                <span>Name</span>
                                <span class="text-slate-500 text-xs font-bold leading-none">↑</span>
                            </span>
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Code
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Language
                        </th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($templates as $template)
                    <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        :class="{ 'bg-slate-50/90': selectedItems.includes({{ $template->id }}) }"
                        onclick="if(!event.target.closest('input')) window.location='{{ route('templates.show', $template->id) }}'">
                        <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $template->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            <a href="{{ route('templates.show', $template->id) }}" class="hover:text-indigo-600 transition">
                                {{ $template->name }}
                            </a>
                        </td>
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            {{ $template->type }}
                        </td>
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            {{ $template->language?->name ?? 'English (United Kingdom)' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center text-slate-400 text-sm">
                            No templates found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->total() > 0)
            <div class="px-6 py-3.5 border-t border-slate-100">
                @include('partials.pagination', ['paginator' => $templates])
            </div>
        @endif
    </div>
</div>
@endsection
