@extends('layouts.app')

@section('title', 'PaymentTypes')

@section('content')
<div class="space-y-4" x-data="{ selectedItems: [], selectAll: false }">
    <!-- Top Action Bar: Title, Search, and Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">PaymentTypes</h1>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('settings.paymentTypes') }}" class="relative w-full sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search payment type" 
                       class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/90 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </form>

            <a href="{{ route('settings.paymentTypes.create') }}" 
               class="px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors shrink-0 cursor-pointer">
                <span class="material-icons text-sm font-bold leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden min-h-[calc(100vh-210px)] flex flex-col justify-between">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th scope="col" class="py-4 px-6 w-14 text-left">
                            <input type="checkbox" 
                                   x-model="selectAll"
                                   @change="selectedItems = selectAll ? [{{ $paymentTypes->pluck('id')->join(',') }}] : []"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Description
                        </th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($paymentTypes as $type)
                    <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        :class="{ 'bg-slate-50/90': selectedItems.includes({{ $type->id }}) }"
                        onclick="if(!event.target.closest('input')) window.location='{{ route('settings.paymentTypes.show', $type->id) }}'">
                        <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   :value="{{ $type->id }}" 
                                   x-model="selectedItems"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                        </td>
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            <a href="{{ route('settings.paymentTypes.show', $type->id) }}" class="hover:text-indigo-600 transition">
                                {{ $type->description }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-16 text-center text-slate-400 text-sm">
                            No payment types found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paymentTypes->total() > 0)
            <div class="px-6 py-3.5 border-t border-slate-100">
                @include('partials.pagination', ['paginator' => $paymentTypes])
            </div>
        @endif
    </div>
</div>
@endsection
