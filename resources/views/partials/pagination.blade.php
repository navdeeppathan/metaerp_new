@props(['paginator', 'perPageOptions' => [10, 25, 50, 100]])

@php
    $total = $paginator->total();
    $perPage = $paginator->perPage();
    $currentPage = $paginator->currentPage();
    $from = $total > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
    $to = min($currentPage * $perPage, $total);
@endphp

<div class="flex items-center justify-end text-xs text-slate-700 select-none py-1">
    <div class="flex items-center gap-6 sm:gap-8">
        <!-- Rows per page -->
        <div class="flex items-center gap-2">
            <span class="text-slate-600 sm:text-slate-700 font-normal">Rows per page:</span>
            <div class="relative inline-flex items-center">
                <select
                    onchange="changeTablePerPage(this.value)"
                    class="appearance-none bg-transparent pr-5 py-1 text-xs font-medium text-slate-800 focus:outline-none cursor-pointer border-none"
                    aria-label="Rows per page"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <!-- Solid Downward Caret matching MUI TablePagination: M7 10l5 5 5-5z -->
                <svg class="w-4 h-4 text-slate-500 absolute right-0 pointer-events-none fill-current" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </div>
        </div>

        <!-- Range indicator e.g. 1–10 of 27 (en-dash matching MUI TablePagination) -->
        <div class="text-slate-600 sm:text-slate-700 font-normal tracking-wide">
            {{ $from }}–{{ $to }} of {{ $total }}
        </div>

        <!-- Chevrons (< and > matching MUI TablePagination) -->
        <div class="flex items-center gap-1 sm:gap-2">
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-default" aria-disabled="true" title="Previous Page">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-full transition-colors cursor-pointer" title="Previous Page">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-full transition-colors cursor-pointer" title="Next Page">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </a>
            @else
                <span class="w-8 h-8 flex items-center justify-center text-slate-300 cursor-default" aria-disabled="true" title="Next Page">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </span>
            @endif
        </div>
    </div>
</div>

<script>
    if (typeof window.changeTablePerPage !== 'function') {
        window.changeTablePerPage = function(val) {
            const filterForm = document.getElementById('searchFilterForm') || document.querySelector('form[data-filter-form="true"]');
            if (filterForm) {
                let perPageInput = filterForm.querySelector('input[name="per_page"]');
                if (!perPageInput) {
                    perPageInput = document.createElement('input');
                    perPageInput.type = 'hidden';
                    perPageInput.name = 'per_page';
                    filterForm.appendChild(perPageInput);
                }
                perPageInput.value = val;
                
                let pageInput = filterForm.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.value = 1;
                }
                filterForm.submit();
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        };
    }
</script>
