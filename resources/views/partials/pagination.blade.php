@props(['paginator', 'perPageOptions' => [10, 25, 50, 100]])

@php
    $total = $paginator->total();
    $perPage = $paginator->perPage();
    $currentPage = $paginator->currentPage();
    $from = $total > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
    $to = min($currentPage * $perPage, $total);
@endphp

<div class="mt-8 pt-4 flex items-center justify-end text-xs text-slate-700 select-none">
    <div class="flex items-center gap-6 sm:gap-8">
        <!-- Rows per page -->
        <div class="flex items-center gap-2">
            <span class="text-slate-700 font-normal">Rows per page:</span>
            <div class="relative inline-flex items-center">
                <select
                    onchange="const url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.toString();"
                    class="appearance-none bg-transparent pr-4 py-0.5 text-xs font-medium text-slate-800 focus:outline-none cursor-pointer border-none"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <svg class="w-3 h-3 text-slate-500 absolute right-0 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        <!-- Range indicator e.g. 1–10 of 26 -->
        <div class="text-slate-700 font-normal">
            {{ $from }}–{{ $to }} of {{ $total }}
        </div>

        <!-- Chevrons -->
        <div class="flex items-center gap-5">
            @if ($paginator->onFirstPage())
                <span class="text-slate-300 cursor-default" aria-disabled="true">
                    <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="text-slate-700 hover:text-slate-900 transition-colors">
                    <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="text-slate-700 hover:text-slate-900 transition-colors">
                    <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="text-slate-300 cursor-default" aria-disabled="true">
                    <svg class="w-3.5 h-3.5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </div>
</div>
