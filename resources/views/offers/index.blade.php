@extends('layouts.app')

@section('title', 'Offers')

@section('content')
<div class="space-y-4"
     x-data="{
         selectedItems: [],
         selectAll: false,
         toggleSelectAll() {
             this.selectAll = !this.selectAll;
             this.selectedItems = this.selectAll ? [{{ $offers->pluck('id')->join(',') }}] : [];
         }
     }">

    <!-- Top Action Bar (On Page Background): Title, Search Pill, and Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">Offers</h1>
        </div>

        <div class="flex items-center gap-3">
            <!-- Search Pill Matching Exact Screenshot -->
            <form method="GET" action="{{ route('offers.index') }}" class="relative w-full sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q', request('search')) }}"
                    placeholder="Search offer"
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/90 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </form>

            <!-- + Add Button Matching Screenshot -->
            <a
                href="{{ route('offers.create') }}"
                class="px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors shrink-0 cursor-pointer"
            >
                <span class="material-icons text-sm font-bold leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alert Messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Standalone White Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden min-h-[calc(100vh-210px)] flex flex-col justify-between">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th scope="col" class="py-4 px-6 w-14 text-left">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                @change="toggleSelectAll()"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                            >
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            id
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Status
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Type
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Subject
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Code
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Contract Code
                        </th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($offers as $offer)
                        <tr
                            onclick="if(!event.target.closest('input')) window.location='{{ route('offers.show', $offer->id) }}'"
                            :class="{ 'bg-slate-50/90': selectedItems.includes({{ $offer->id }}) }"
                            class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        >
                            <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                                <input
                                    type="checkbox"
                                    :value="{{ $offer->id }}"
                                    x-model="selectedItems"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                                >
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                {{ $offer->id }}
                            </td>
                            <td class="py-5 px-6 font-normal">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $offer->status === 'ACCEPTED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $offer->status }}
                                </span>
                            </td>
                            <td class="py-5 px-6 font-normal">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $offer->type === 'BUY' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $offer->type }}
                                </span>
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                <a href="{{ route('offers.show', $offer->id) }}" class="hover:text-indigo-600 transition font-medium">
                                    {{ $offer->subject }}
                                </a>
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                {{ $offer->code ?? '' }}
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                {{ $offer->contract_code ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400 text-sm">
                                There are no offers!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bottom Pagination Matching Material UI DataGrid -->
        @if($offers->total() > 0)
            <div class="px-6 py-3.5 border-t border-slate-100">
                @include('partials.pagination', ['paginator' => $offers])
            </div>
        @endif
    </div>

</div>
@endsection
