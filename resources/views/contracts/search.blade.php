@extends('layouts.app')

@section('title', 'Find Contracts')

@section('content')
<div class="space-y-6">
    <!-- Header with Title & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Find Contracts</h1>

        <!-- Filter Bar -->
        <form action="{{ route('contracts.search') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Date range pill -->
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-full px-4 py-1.5 shadow-xs text-xs text-slate-600">
                <span>05/09/2026</span>
                <span class="material-icons text-slate-400 text-sm">calendar_today</span>
                <span class="text-slate-300">-</span>
                <span>04/11/2026</span>
                <span class="material-icons text-slate-400 text-sm">calendar_today</span>
            </div>

            <!-- Status Dropdown Pill -->
            <div class="relative">
                <select name="status" onchange="this.form.submit()" class="appearance-none bg-white border border-slate-200 rounded-full pl-4 pr-8 py-1.5 shadow-xs text-xs font-normal text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer">
                    <option value="OPEN" {{ ($status ?? 'OPEN') == 'OPEN' ? 'selected' : '' }}>Status: OPEN</option>
                    <option value="ALL" {{ ($status ?? '') == 'ALL' ? 'selected' : '' }}>Status: ALL</option>
                    <option value="CLOSED" {{ ($status ?? '') == 'CLOSED' ? 'selected' : '' }}>Status: CLOSED</option>
                    <option value="CANCELLED" {{ ($status ?? '') == 'CANCELLED' ? 'selected' : '' }}>Status: CANCELLED</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Type Dropdown Pill -->
            <div class="relative">
                <select name="type" onchange="this.form.submit()" class="appearance-none bg-white border border-slate-200 rounded-full pl-4 pr-8 py-1.5 shadow-xs text-xs font-normal text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer">
                    <option value="ALL" {{ ($type ?? 'ALL') == 'ALL' ? 'selected' : '' }}>Type: ALL</option>
                    <option value="BUY_AND_SELL" {{ ($type ?? '') == 'BUY_AND_SELL' ? 'selected' : '' }}>Type: BUY_AND_SELL</option>
                    <option value="BUY" {{ ($type ?? '') == 'BUY' ? 'selected' : '' }}>Type: BUY</option>
                    <option value="SELL" {{ ($type ?? '') == 'SELL' ? 'selected' : '' }}>Type: SELL</option>
                </select>
                <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Purple Search Button -->
            <button type="submit" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-6 py-1.5 rounded-full font-medium text-xs shadow-xs transition">
                Search
            </button>
        </form>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg overflow-x-auto min-h-[380px]">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-slate-200 text-slate-900 font-bold text-[13px]">
                    <th class="py-3 px-4 font-bold">Order Code</th>
                    <th class="py-3 px-4 font-bold">Status</th>
                    <th class="py-3 px-4 font-bold">Type</th>
                    <th class="py-3 px-4 font-bold">Date</th>
                    <th class="py-3 px-4 font-bold">Purchase</th>
                    <th class="py-3 px-4 font-bold">Sale</th>
                    <th class="py-3 px-4 font-bold">Finish Date</th>
                    <th class="py-3 px-4 font-bold">Cancellation Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                @forelse($contracts as $c)
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="py-4 px-4">
                        <a href="{{ route('contracts.show', $c->id) }}" class="text-blue-600 hover:underline">
                            {{ $c->orderCode }}
                        </a>
                    </td>
                    <td class="py-4 px-4">{{ ($c->contractStatus == 'ACTIVE' || $c->contractStatus == 'OPEN') ? 'OPEN' : $c->contractStatus }}</td>
                    <td class="py-4 px-4">{{ $c->type }}</td>
                    <td class="py-4 px-4">{{ $c->date ? $c->date->format('d M y') : '' }}</td>
                    <td class="py-4 px-4 text-slate-800">{{ $c->purchase?->contact?->name ?? '' }}</td>
                    <td class="py-4 px-4 text-slate-800">{{ $c->sale?->contact?->name ?? '' }}</td>
                    <td class="py-4 px-4">{{ $c->finishDate ? \Carbon\Carbon::parse($c->finishDate)->format('d M y') : '' }}</td>
                    <td class="py-4 px-4">{{ $c->cancellationDate ? \Carbon\Carbon::parse($c->cancellationDate)->format('d M y') : '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        No contracts found matching criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="flex items-center justify-end gap-6 text-xs text-slate-600 pt-4 pb-2">
        <div class="flex items-center gap-2">
            <span>Rows per page:</span>
            <div class="flex items-center gap-0.5 cursor-pointer">
                <span>10</span>
                <span class="material-icons text-sm text-slate-400">arrow_drop_down</span>
            </div>
        </div>
        <div>
            1-{{ $contracts->count() }} of {{ $contracts->total() }}
        </div>
        <div class="flex items-center gap-2 text-slate-400">
            <button class="hover:text-slate-600 disabled:opacity-40" {{ $contracts->onFirstPage() ? 'disabled' : '' }}>
                <span class="material-icons text-sm">chevron_left</span>
            </button>
            <button class="hover:text-slate-600 disabled:opacity-40" {{ !$contracts->hasMorePages() ? 'disabled' : '' }}>
                <span class="material-icons text-sm">chevron_right</span>
            </button>
        </div>
    </div>
</div>
@endsection
