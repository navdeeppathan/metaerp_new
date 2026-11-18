@extends('layouts.app')

@section('title', ($country ? $country->name : 'New Country') . ' - Country Detail')

@section('content')
<div class="space-y-6" x-data="{ 
    isModified: false,
    init() {
        this.$watch('$el', () => {});
    }
}" @input="isModified = true" @change="isModified = true">

    <!-- Top Header: Back Link, Title, Subtitle, Remove & Save Buttons (Matching Screenshot) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <div>
            <a href="{{ route('settings.countries') }}" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-colors select-none">
                <span class="material-icons-outlined text-base">arrow_back</span>
                <span>Countries</span>
            </a>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 mt-1.5 tracking-tight">
                {{ $country ? $country->name : 'New Country' }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5 font-normal">Country Detail</div>
        </div>

        <div class="flex items-center gap-3">
            @if($country)
            <!-- Remove Button with Trash Icon Matching Screenshot -->
            <form action="{{ route('settings.countries.destroy', $country->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-2 rounded-full text-xs font-semibold shadow-xs transition cursor-pointer select-none flex items-center gap-1.5">
                    <span class="material-icons-outlined text-base leading-none">delete</span>
                    <span>Remove</span>
                </button>
            </form>
            @endif

            <!-- Save Button Matching Screenshot -->
            <button type="submit" 
                    form="countryForm" 
                    :disabled="!isModified"
                    :class="isModified ? 'bg-[#4f46e5] hover:bg-[#4338ca] text-white shadow-xs cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="px-6 py-2 rounded-full text-xs font-semibold transition select-none">
                Save
            </button>
        </div>
    </div>

    <!-- FusePageCarded Card Container Matching Screenshot -->
    <div class="bg-white rounded-t-2xl shadow-xs border border-slate-200/80 min-h-[calc(100vh-220px)]">
        <!-- Navigation Tabs: 'Basic Info' -->
        <div class="border-b border-slate-200 px-6 pt-3 flex items-center">
            <div class="border-b-2 border-indigo-600 text-indigo-600 font-semibold text-xs pb-3 inline-block select-none cursor-pointer">
                Basic Info
            </div>
        </div>

        <!-- Form Content Inside Card -->
        <div class="p-6 sm:p-8 max-w-4xl">
            <form id="countryForm" 
                  method="POST" 
                  action="{{ $country ? route('settings.countries.update', $country->id) : route('settings.countries.store') }}" 
                  class="space-y-6">
                @csrf
                @if($country)
                    @method('PUT')
                @endif

                <!-- Field 1: Name * -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        Name *
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $country->name ?? '') }}" 
                           required 
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>

                <!-- Field 2: Abbreviation 2 -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        Abbreviation 2
                    </label>
                    <input type="text" 
                           name="alpha2" 
                           value="{{ old('alpha2', $country->alpha2 ?? '') }}" 
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>

                <!-- Field 3: Abbreviation 3 -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        Abbreviation 3
                    </label>
                    <input type="text" 
                           name="alpha3" 
                           value="{{ old('alpha3', $country->alpha3 ?? '') }}" 
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>

                <!-- Field 4: Region * -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        Region *
                    </label>
                    <input type="text" 
                           name="region" 
                           value="{{ old('region', $country->region ?? '') }}" 
                           required
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>

                <!-- Field 5: VAT Tax * -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        VAT Tax *
                    </label>
                    <input type="number" 
                           step="any"
                           name="vat" 
                           value="{{ old('vat', $country->vat ?? '0') }}" 
                           required
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
