@extends('layouts.app')

@section('title', ($paymentTerm ? $paymentTerm->description : 'New PaymentTerm') . ' - Payment Term Detail')

@section('content')
<div class="space-y-6" x-data="{ isModified: false }" @input="isModified = true" @change="isModified = true">
    <!-- Top Header: Back Link, Title, Subtitle, Remove & Save Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <div>
            <a href="{{ route('settings.paymentTerms') }}" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-colors select-none">
                <span class="material-icons-outlined text-base">arrow_back</span>
                <span>Payment Terms</span>
            </a>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 mt-1.5 tracking-tight">
                {{ $paymentTerm ? $paymentTerm->description : 'New PaymentTerm' }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5 font-normal">Payment Term Detail</div>
        </div>

        <div class="flex items-center gap-3">
            @fullAccess('SYSTEM_SETTINGS')
            @if($paymentTerm)
            <!-- Remove Button with Trash Icon Matching Screenshot -->
            <form action="{{ route('settings.paymentTerms.destroy', $paymentTerm->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-2 rounded-full text-xs font-semibold shadow-xs transition cursor-pointer select-none flex items-center gap-1.5">
                    <span class="material-icons-outlined text-base leading-none">delete</span>
                    <span>Remove</span>
                </button>
            </form>
            @endif

            <!-- Save Button -->
            <button type="submit" 
                    form="paymentTermForm" 
                    :disabled="!isModified"
                    :class="isModified ? 'bg-[#4f46e5] hover:bg-[#4338ca] text-white shadow-xs cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="px-6 py-2 rounded-full text-xs font-semibold transition select-none">
                Save
            </button>
            @else
            <span class="px-5 py-2 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">
                Read Only
            </span>
            @endfullAccess
        </div>
    </div>

    <!-- FusePageCarded Card Container -->
    <div class="bg-white rounded-t-2xl shadow-xs border border-slate-200/80 min-h-[calc(100vh-220px)]">
        <!-- Navigation Tabs: 'Basic Info' -->
        <div class="border-b border-slate-200 px-6 pt-3 flex items-center">
            <div class="border-b-2 border-indigo-600 text-indigo-600 font-semibold text-xs pb-3 inline-block select-none cursor-pointer">
                Basic Info
            </div>
        </div>

        <!-- Form Content -->
        <div class="p-6 sm:p-8 max-w-4xl">
            <form id="paymentTermForm" 
                  method="POST" 
                  action="{{ $paymentTerm ? route('settings.paymentTerms.update', $paymentTerm->id) : route('settings.paymentTerms.store') }}" 
                  class="space-y-6">
                @csrf
                @if($paymentTerm)
                    @method('PUT')
                @endif

                <!-- Field: Description * -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                        Description *
                    </label>
                    <input type="text" 
                           name="description" 
                           value="{{ old('description', $paymentTerm->description ?? '') }}" 
                           required 
                           class="w-full text-sm text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
