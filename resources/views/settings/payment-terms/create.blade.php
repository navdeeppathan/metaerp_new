@extends('layouts.app')

@section('title', ($paymentTerm ? $paymentTerm->description : 'New PaymentTermsType') . ' - PaymentTermsType Detail')

@section('content')
<div class="space-y-6">
    <!-- Top Header: Back Link, Title, Subtitle, Save Button -->
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('settings.paymentTerms') }}" class="text-xs font-semibold text-slate-700 hover:text-indigo-600 flex items-center gap-1 transition select-none">
                <span>←</span>
                <span>PaymentTermsTypes</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">
                {{ $paymentTerm ? $paymentTerm->description : 'New PaymentTermsType' }}
            </h1>
            <div class="text-xs text-slate-500 mt-0.5">PaymentTermsType Detail</div>
        </div>

        <div>
            <button type="submit" 
                    form="paymentTermForm" 
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
    <form id="paymentTermForm" 
          method="POST" 
          action="{{ $paymentTerm ? route('settings.paymentTerms.update', $paymentTerm->id) : route('settings.paymentTerms.store') }}" 
          class="space-y-5 max-w-5xl">
        @csrf
        @if($paymentTerm)
            @method('PUT')
        @endif

        <!-- Field 1: Description * -->
        <div class="relative border border-slate-300 rounded-md px-3.5 pt-3 pb-2.5 bg-white shadow-2xs focus-within:border-indigo-600 transition">
            <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                Description *
            </label>
            <input type="text" 
                   name="description" 
                   value="{{ old('description', $paymentTerm->description ?? '') }}" 
                   required 
                   class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 outline-none">
        </div>
    </form>
</div>
@endsection
