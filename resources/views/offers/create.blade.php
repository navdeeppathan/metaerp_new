@extends('layouts.app')

@section('title', $offer->exists ? ($offer->subject ?? 'Offer Detail') : 'New Offer')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto pb-12">
    <!-- Back Link & Header -->
    <div>
        <a href="{{ route('offers.index') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors mb-3">
            <span class="material-icons-outlined text-base">arrow_back</span>
            <span>Offers</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">
                    {{ $offer->exists ? ($offer->subject ?? 'Offer Detail') : 'New Offer' }}
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">Offer Detail</p>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-3">
                @if($offer->exists)
                    <!-- Remove Button with Trash Icon -->
                    <form id="delete-form" method="POST" action="{{ route('offers.destroy', $offer->id) }}" onsubmit="return confirm('Are you sure you want to remove this offer?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-sm font-semibold rounded-full shadow-xs transition-colors"
                        >
                            <span class="material-icons-outlined text-base">delete</span>
                            <span>Remove</span>
                        </button>
                    </form>
                @endif

                <!-- Save Button -->
                <button
                    type="submit"
                    form="offer-form"
                    class="inline-flex items-center px-6 py-2 {{ $offer->exists ? 'bg-slate-200 text-slate-700 hover:bg-[#4f46e5] hover:text-white' : 'bg-slate-200 text-slate-400 hover:bg-[#4f46e5] hover:text-white' }} text-sm font-semibold rounded-full shadow-xs transition-colors"
                >
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs font-medium">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Container -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
        <!-- Tabs Row -->
        <div class="border-b border-slate-200 flex items-center gap-6 text-xs font-medium pb-px">
            <button
                type="button"
                class="text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px] focus:outline-none"
            >
                Basic Info
            </button>
        </div>

        <!-- Form Elements -->
        <form
            id="offer-form"
            method="POST"
            action="{{ $offer->exists ? route('offers.update', $offer->id) : route('offers.store') }}"
            class="pt-6 space-y-6 max-w-4xl"
        >
            @csrf
            @if($offer->exists)
                @method('PUT')
            @endif

            <!-- Subheader Row: Left Title + Right Offer Date & Action Tiles (Image 2) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
                <div class="text-sm font-semibold text-slate-700">
                    {{ $offer->exists ? ($offer->code ?? 'Offer') : 'New Offer' }} &gt;
                </div>

                <div class="flex items-center gap-3">
                    <!-- Offer date * with Calendar Icon -->
                    <div class="relative w-48">
                        <input
                            type="text"
                            name="offer_date"
                            id="offer_date"
                            value="{{ old('offer_date', $offer->offer_date ? \Carbon\Carbon::parse($offer->offer_date)->format('d/m/Y') : date('d/m/Y')) }}"
                            class="peer w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded focus:border-indigo-600 bg-white text-slate-800 focus:outline-none transition-colors"
                            placeholder="DD/MM/YYYY"
                        />
                        <label
                            for="offer_date"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Offer date <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">calendar_today</span>
                    </div>

                    <!-- Right Side Floating Icon Tiles -->
                    <div class="flex flex-col bg-slate-100/90 rounded-xl p-1 gap-1 border border-slate-200">
                        <button type="button" class="w-8 h-8 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-white flex items-center justify-center transition-colors" title="Calendar">
                            <span class="material-icons-outlined text-lg">calendar_month</span>
                        </button>
                        <button type="button" class="w-8 h-8 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-white flex items-center justify-center transition-colors" title="Attachment">
                            <span class="material-icons-outlined text-lg">attach_file</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Field 1: Type * (Dropdown) -->
            <div class="relative">
                <select
                    name="type"
                    id="type"
                    required
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                >
                    <option value="">Type</option>
                    @foreach(['SELL', 'BUY'] as $t)
                        <option value="{{ $t }}" {{ old('type', $offer->type ?? 'SELL') === $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
                <label
                    for="type"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Type <span class="text-slate-400">*</span>
                </label>
                <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Field 2: Subject * -->
            <div class="relative">
                <input
                    type="text"
                    name="subject"
                    id="subject"
                    required
                    value="{{ old('subject', $offer->subject) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Subject"
                />
                <label
                    for="subject"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Subject <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 3: Description -->
            <div class="relative">
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors resize-y"
                    placeholder="Description"
                >{{ old('description', $offer->description) }}</textarea>
                <label
                    for="description"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Description
                </label>
            </div>

            <!-- Field 4: Internal notes -->
            <div class="relative">
                <textarea
                    name="internal_notes"
                    id="internal_notes"
                    rows="3"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors resize-y"
                    placeholder="Internal notes"
                >{{ old('internal_notes', $offer->internal_notes) }}</textarea>
                <label
                    for="internal_notes"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Internal notes
                </label>
            </div>

            <!-- Field 5: Delivery Plan * (Image 3) -->
            <div class="relative">
                <input
                    type="text"
                    name="delivery_plan"
                    id="delivery_plan"
                    value="{{ old('delivery_plan', $offer->delivery_plan) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Delivery Plan"
                />
                <label
                    for="delivery_plan"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Delivery Plan <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 6: Quotational Period -->
            <div class="relative">
                <input
                    type="text"
                    name="quotational_period"
                    id="quotational_period"
                    value="{{ old('quotational_period', $offer->quotational_period) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Quotational Period"
                />
                <label
                    for="quotational_period"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Quotational Period
                </label>
            </div>

            <!-- Field 7: Weights * -->
            <div class="relative">
                <input
                    type="text"
                    name="weights"
                    id="weights"
                    value="{{ old('weights', $offer->weights) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Weights"
                />
                <label
                    for="weights"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Weights <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 8: Payment description * -->
            <div class="relative">
                <input
                    type="text"
                    name="payment_description"
                    id="payment_description"
                    value="{{ old('payment_description', $offer->payment_description) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Payment description"
                />
                <label
                    for="payment_description"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Payment description <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 9: Terms * -->
            <div class="relative">
                <input
                    type="text"
                    name="terms"
                    id="terms"
                    value="{{ old('terms', $offer->terms) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Terms"
                />
                <label
                    for="terms"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Terms <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 10: Extra information -->
            <div class="relative">
                <input
                    type="text"
                    name="extra_information"
                    id="extra_information"
                    value="{{ old('extra_information', $offer->extra_information) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder="Extra information"
                />
                <label
                    for="extra_information"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Extra information
                </label>
            </div>

            <!-- Divider (Image 4) -->
            <div class="border-b border-slate-200 pt-4"></div>

            <!-- Activities Section Header (Image 4) -->
            <div class="pt-2">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-icons-outlined text-slate-700 text-lg">assignment</span>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Activities</h3>
                </div>

                @if($offer->exists)
                    <div class="space-y-4 max-w-4xl pt-2">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                                <span class="material-icons text-base">person</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $offer->created_at ? $offer->created_at->format('d M y H:i:s') : '11 Feb 26 10:00:00' }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-sm font-semibold text-slate-800">Doga</span>
                                    <span class="text-xs font-medium text-slate-500">CREATED</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
