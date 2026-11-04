@extends('layouts.app')

@section('header_title', 'Operational Overview')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">description</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $activeContractsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Contracts</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">request_quote</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $openOffersCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open Offers</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">category</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $productsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Commodities</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">contacts</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $contactsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Counterparties</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">corporate_fare</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $companiesCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Group Companies</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Welcome back, {{ Auth::user()->displayName ?? Auth::user()->name }}</h2>
            <p class="text-sm text-blue-100 mt-1">Trading pipelines and contracts are synchronized and active.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('contracts.board') }}" class="px-4 py-2.5 rounded-xl bg-white text-blue-800 font-semibold text-sm hover:bg-blue-50 shadow transition-all flex items-center gap-2">
                <span class="material-icons-outlined text-lg">view_kanban</span>
                <span>Open Kanban Board</span>
            </a>
            <a href="{{ route('contracts.search') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-500 shadow transition-all flex items-center gap-2">
                <span class="material-icons-outlined text-lg">search</span>
                <span>Search Contracts</span>
            </a>
        </div>
    </div>

    <!-- Recent Contracts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600">history</span>
                <span>Recent Contracts Pipeline Activity</span>
            </h3>
            <a href="{{ route('contracts.board') }}" class="text-xs text-blue-600 font-semibold hover:underline">View All &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-5">Order Code</th>
                        <th class="py-3 px-5">Type</th>
                        <th class="py-3 px-5">Counterparty</th>
                        <th class="py-3 px-5">Stage</th>
                        <th class="py-3 px-5">Date</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentContracts as $c)
                        @php
                            $deal = $c->purchase ?: $c->sale;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-5 font-semibold text-slate-800">
                                {{ $c->orderCode ?: "Contract #{$c->id}" }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $c->type === 'BUY' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $c->type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-medium text-slate-700">
                                {{ $deal?->contact?->name ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $c->stage?->name ?? 'Draft' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-xs text-slate-400">
                                {{ $c->date ? $c->date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('contracts.board', ['boardId' => $c->stage?->model_id ?? 2]) }}" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold hover:underline">
                                    <span>Board</span>
                                    <span class="material-icons-outlined text-sm">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">No recent contracts recorded</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
