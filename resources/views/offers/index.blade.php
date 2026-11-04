@extends('layouts.app')

@section('title', 'Offers')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto">
    <!-- Header with Title & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1 pb-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">Offers</h1>

        <div class="flex items-center gap-3">
            <!-- Search Pill -->
            <form method="GET" action="{{ route('offers.index') }}" class="relative w-64 sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search offer"
                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-600 shadow-xs transition"
                >
            </form>

            <!-- + Add Button -->
            <a
                href="{{ route('offers.create') }}"
                class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-sm font-semibold rounded-full shadow-xs transition-colors shrink-0"
            >
                <span class="material-icons text-base">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Offers Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-800 text-sm font-bold bg-white">
                        <th class="w-12 px-6 py-4">
                            <input
                                type="checkbox"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">id</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Status</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Type</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Subject</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Code</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Contract Code</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($offers as $offer)
                        <tr
                            onclick="window.location='{{ route('offers.show', $offer->id) }}'"
                            class="hover:bg-slate-50/80 cursor-pointer transition-colors"
                        >
                            <td class="w-12 px-6 py-4.5" onclick="event.stopPropagation()">
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                >
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $offer->id }}
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="font-medium text-slate-900 leading-tight">
                                    {{ $offer->status }}
                                </div>
                                @if($offer->status === 'ACCEPTED' || $offer->accepted_at)
                                    <div class="text-xs text-slate-500 font-normal leading-tight mt-0.5">
                                        {{ $offer->accepted_at ? \Carbon\Carbon::parse($offer->accepted_at)->format('d M y') : '11 Feb 26' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $offer->type }}
                            </td>
                            <td class="px-6 py-4.5 font-medium text-slate-900">
                                {{ $offer->subject }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $offer->code ?? $offer->orderCode }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $offer->contract_code ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No offers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar Matching Screenshot -->
        <div class="px-6 py-3 border-t border-slate-100 flex items-center justify-end gap-6 text-xs text-slate-600">
            <div class="flex items-center gap-1.5">
                <span>Rows per page:</span>
                <span class="font-medium text-slate-800">10</span>
                <span class="material-icons text-sm text-slate-500">arrow_drop_down</span>
            </div>
            <div>
                {{ $offers->firstItem() ?? 1 }}-{{ $offers->lastItem() ?? $offers->count() }} of {{ $offers->total() }}
            </div>
            <div class="flex items-center gap-2 text-slate-400">
                <button type="button" class="hover:text-slate-600 disabled:opacity-40" {{ $offers->onFirstPage() ? 'disabled' : '' }}>
                    <span class="material-icons text-base">chevron_left</span>
                </button>
                <button type="button" class="hover:text-slate-600 disabled:opacity-40" {{ !$offers->hasMorePages() ? 'disabled' : '' }}>
                    <span class="material-icons text-base">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
