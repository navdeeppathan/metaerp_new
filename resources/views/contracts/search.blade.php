@extends('layouts.app')

@section('title', 'Find Contracts')

@section('content')
<!-- Flatpickr CDN for interactive date range selection -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="w-full space-y-6">
    <!-- Header with Title & Filter Bar Matching Screenshot -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pt-1">
        <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#0f172a] tracking-tight">Find Contracts</h1>

        <!-- Filter Bar -->
        <form action="{{ route('contracts.search') }}" method="GET" id="searchFilterForm" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="per_page" id="per_page_input" value="{{ $perPage ?? 10 }}">
            <input type="hidden" name="q" value="{{ request('q', request('search', '')) }}">

            <!-- Date Range Picker Pill -->
            <div class="flex items-center bg-white border border-slate-200/90 rounded-full px-5 py-2.5 shadow-2xs text-sm text-slate-800">
                <input
                    type="text"
                    name="from_date"
                    id="from_date"
                    value="{{ $fromDateRaw }}"
                    class="w-24 bg-transparent text-slate-800 text-sm font-normal focus:outline-none cursor-pointer text-center"
                    placeholder="DD/MM/YYYY"
                    readonly
                />
                <button type="button" id="from_calendar_btn" class="text-slate-400 hover:text-slate-600 focus:outline-none ml-1.5 flex items-center">
                    <span class="material-icons-outlined text-lg">calendar_today</span>
                </button>
                <span class="text-slate-400 mx-2 text-sm">-</span>
                <input
                    type="text"
                    name="to_date"
                    id="to_date"
                    value="{{ $toDateRaw }}"
                    class="w-24 bg-transparent text-slate-800 text-sm font-normal focus:outline-none cursor-pointer text-center"
                    placeholder="DD/MM/YYYY"
                    readonly
                />
                <button type="button" id="to_calendar_btn" class="text-slate-400 hover:text-slate-600 focus:outline-none ml-1.5 flex items-center">
                    <span class="material-icons-outlined text-lg">calendar_today</span>
                </button>
            </div>

            <!-- Status Dropdown Pill -->
            <div class="relative flex items-center bg-white border border-slate-200/90 rounded-full pl-5 pr-9 py-2.5 shadow-2xs">
                <select
                    name="status"
                    id="status"
                    class="appearance-none bg-transparent text-sm font-normal text-slate-800 focus:outline-none cursor-pointer w-full"
                >
                    <option value="OPEN" {{ ($status ?? 'OPEN') === 'OPEN' ? 'selected' : '' }}>Status: OPEN</option>
                    <option value="ALL" {{ ($status ?? '') === 'ALL' ? 'selected' : '' }}>Status: ALL</option>
                    <option value="CLOSED" {{ ($status ?? '') === 'CLOSED' ? 'selected' : '' }}>Status: CLOSED</option>
                    <option value="CANCELLED" {{ ($status ?? '') === 'CANCELLED' ? 'selected' : '' }}>Status: CANCELLED</option>
                </select>
                <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Type Dropdown Pill -->
            <div class="relative flex items-center bg-white border border-slate-200/90 rounded-full pl-5 pr-9 py-2.5 shadow-2xs">
                <select
                    name="type"
                    id="type"
                    class="appearance-none bg-transparent text-sm font-normal text-slate-800 focus:outline-none cursor-pointer w-full"
                >
                    <option value="ALL" {{ ($type ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Type: ALL</option>
                    <option value="BUY_AND_SELL" {{ ($type ?? '') === 'BUY_AND_SELL' ? 'selected' : '' }}>Type: BUY & SELL</option>
                    <option value="BUY" {{ ($type ?? '') === 'BUY' ? 'selected' : '' }}>Type: BUY</option>
                    <option value="SELL" {{ ($type ?? '') === 'SELL' ? 'selected' : '' }}>Type: SELL</option>
                </select>
                <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Search Button -->
            <button
                type="submit"
                class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-7 py-2.5 rounded-full font-semibold text-sm shadow-xs transition-colors cursor-pointer whitespace-nowrap flex items-center justify-center"
            >
                Search
            </button>
        </form>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg overflow-x-auto min-h-[380px]">
        <table class="w-full text-left text-xs sm:text-sm border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-800 font-semibold select-none">
                    <th class="py-3 px-4 font-semibold">Order Code</th>
                    <th class="py-3 px-4 font-semibold">Status</th>
                    <th class="py-3 px-4 font-semibold">Type</th>
                    <th class="py-3 px-4 font-semibold">Date</th>
                    <th class="py-3 px-4 font-semibold">Purchase</th>
                    <th class="py-3 px-4 font-semibold">Sale</th>
                    <th class="py-3 px-4 font-semibold">Finish Date</th>
                    <th class="py-3 px-4 font-semibold">Cancellation Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-800 text-xs sm:text-sm">
                @forelse($contracts as $c)
                <tr
                    onclick="window.location='{{ route('contracts.show', $c->id) }}'"
                    class="hover:bg-slate-50/70 transition-colors cursor-pointer"
                >
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        {{ $c->orderCode ?? $c->order_code }}
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        {{ $c->status }}
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        {{ $c->type }}
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        @if($c->formatted_due_date)
                            <div class="leading-tight">
                                <div>{{ $c->formatted_due_date['day_month'] }}</div>
                                <div>{{ $c->formatted_due_date['year'] }}</div>
                            </div>
                        @elseif($c->due_date)
                            <div class="leading-tight">
                                <div>{{ \Carbon\Carbon::parse($c->due_date)->format('d M') }}</div>
                                <div>{{ \Carbon\Carbon::parse($c->due_date)->format('y') }}</div>
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800">
                        {{ $c->purchase?->contact?->name ?? '' }}
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800">
                        {{ $c->sale?->contact?->name ?? '' }}
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        @if($c->formatted_close_date)
                            <div class="leading-tight">
                                <div>{{ $c->formatted_close_date['day_month'] }}</div>
                                <div>{{ $c->formatted_close_date['year'] }}</div>
                            </div>
                        @elseif($c->close_date)
                            <div class="leading-tight">
                                <div>{{ \Carbon\Carbon::parse($c->close_date)->format('d M') }}</div>
                                <div>{{ \Carbon\Carbon::parse($c->close_date)->format('y') }}</div>
                            </div>
                        @endif
                    </td>
                    <td class="py-4 sm:py-5 px-4 font-normal text-slate-800 whitespace-nowrap">
                        @if($c->formatted_cancellation_date)
                            <div class="leading-tight">
                                <div>{{ $c->formatted_cancellation_date['day_month'] }}</div>
                                <div>{{ $c->formatted_cancellation_date['year'] }}</div>
                            </div>
                        @elseif($c->cancellation_date)
                            <div class="leading-tight">
                                <div>{{ \Carbon\Carbon::parse($c->cancellation_date)->format('d M') }}</div>
                                <div>{{ \Carbon\Carbon::parse($c->cancellation_date)->format('y') }}</div>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        There are no contracts!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer Matching Screenshot -->
    <div class="px-6 py-3 border-t border-slate-100">
        @include('partials.pagination', ['paginator' => $contracts])
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fpConfig = {
            dateFormat: "d/m/Y",
            allowInput: true,
            disableMobile: "true"
        };

        const fromPicker = flatpickr("#from_date", fpConfig);
        const toPicker = flatpickr("#to_date", fpConfig);

        document.getElementById('from_calendar_btn')?.addEventListener('click', function() {
            fromPicker.open();
        });

        document.getElementById('to_calendar_btn')?.addEventListener('click', function() {
            toPicker.open();
        });
    });
</script>
@endsection
