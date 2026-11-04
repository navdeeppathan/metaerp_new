@extends('layouts.app')

@section('title', ($currency ? ($currency->name ?? $currency->description) : 'New Currency') . ' - Currency Detail')

@section('content')
<div class="space-y-6">
    <!-- Top Header: Back Link, Title, Subtitle, Save Button -->
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('settings.currencies') }}" class="text-xs font-semibold text-slate-700 hover:text-indigo-600 flex items-center gap-1 transition select-none">
                <span>←</span>
                <span>Currencies</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">
                {{ $currency ? ($currency->name ?? $currency->description) : 'New Currency' }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5">Currency Detail</div>
        </div>

        <div>
            <button type="submit" 
                    form="currencyForm" 
                    class="px-6 py-2 rounded-full text-xs font-medium text-slate-700 bg-slate-200 hover:bg-indigo-600 hover:text-white transition shadow-2xs cursor-pointer">
                Save
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Matching Screenshot -->
    <div class="border-b border-slate-200">
        <div class="border-b-2 border-indigo-600 text-indigo-600 font-semibold text-xs pb-2.5 inline-block">
            Basic Info
        </div>
    </div>

    <!-- Form Content Matching Screenshot -->
    <form id="currencyForm" 
          method="POST" 
          action="{{ $currency ? route('settings.currencies.update', $currency->id) : route('settings.currencies.store') }}" 
          class="space-y-5 max-w-4xl">
        @csrf
        @if($currency)
            @method('PUT')
        @endif

        <!-- Field 1: Name * -->
        <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
            <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                Name *
            </label>
            <input type="text" 
                   name="name" 
                   value="{{ old('name', $currency->name ?? '') }}" 
                   placeholder="Pound"
                   required 
                   class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
        </div>

        <!-- Field 2: Abbreviation * -->
        <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
            <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                Abbreviation *
            </label>
            <input type="text" 
                   name="abbreviation" 
                   value="{{ old('abbreviation', $currency->abbreviation ?? $currency->description ?? '') }}" 
                   placeholder="GBP"
                   required
                   class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
        </div>

        <!-- Field 3: Symbol * -->
        <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
            <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                Symbol *
            </label>
            <input type="text" 
                   name="symbol" 
                   value="{{ old('symbol', $currency->symbol ?? '') }}" 
                   placeholder="£"
                   class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
        </div>

        <!-- Field 4: HTML encode Symbol * (Active focused border matching screenshot 4) -->
        <div class="relative border border-indigo-600 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs">
            <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-indigo-600 font-medium leading-none select-none">
                HTML encode Symbol *
            </label>
            <input type="text" 
                   name="htmlEncodeSymbol" 
                   value="{{ old('htmlEncodeSymbol', $currency->htmlEncodeSymbol ?? '') }}" 
                   placeholder="HTML encode Symbol"
                   autofocus
                   class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
        </div>
    </form>
</div>
@endsection
