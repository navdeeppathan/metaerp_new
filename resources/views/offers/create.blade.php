@extends('layouts.app')

@section('title', $offer->exists ? ($offer->subject ?? 'Offer Detail') : 'New Offer')

@section('content')
@php
    $effectiveMetaCompanyId = $offer->effective_meta_company_id;
    $effectiveContactId = $offer->effective_contact_id;
    $effectiveAddressId = $offer->effective_address_id;
    $effectiveContactRepresentativeId = $offer->effective_contact_representative_id;
    $effectiveCurrencyId = $offer->effective_currency_id;
    $effectiveIncoterms = $offer->effective_incoterms ?? 'CFR';
    $effectivePaymentTypeId = $offer->effective_payment_type_id;
    $effectivePaymentTermsTypeId = $offer->effective_payment_terms_type_id;
    $effectivePaymentTerms = $offer->effective_payment_terms ?? $offer->payment_description ?? '45 Days';
    $effectiveAgreeDate = $offer->effective_agree_date ? \Carbon\Carbon::parse($offer->effective_agree_date)->format('d/m/Y') : '';
    $initialProducts = $offer->effective_products_list->values()->all();

    $currencyObj = $currencies->firstWhere('id', $effectiveCurrencyId) ?? $currencies->firstWhere('description', 'USD') ?? $currencies->first();
    $currencyCode = $currencyObj ? ($currencyObj->description ?? 'USD') : 'USD';
@endphp

<div
    class="space-y-4 max-w-7xl mx-auto pb-16"
    x-data="{
        contactsData: {{ json_encode($contactsPayload) }},
        selectedContactId: '{{ $effectiveContactId ?? '' }}',
        selectedAddressId: '{{ $effectiveAddressId ?? '' }}',
        selectedRepId: '{{ $effectiveContactRepresentativeId ?? '' }}',
        currencyCode: '{{ $currencyCode }}',
        
        // Products table state
        products: {{ json_encode($initialProducts) }},
        showProductModal: false,
        editingIndex: null,
        productForm: {
            id: null,
            product_id: '',
            item_name: '',
            quantity: 1,
            uom: 'MT',
            unit_price: 0,
            total: 0,
            pricing_type: 'CUSTOM'
        },

        // Dropdown menus state
        showOptionsMenu: false,
        showDocActions: false,
        showDuePopover: false,
        showEditDocModal: false,
        docTitle: 'OFFER_{{ $offer->code ?? $offer->orderCode ?? 'OFFER-00021' }}',
        docContent: '<html><body><h2>Offer {{ $offer->code ?? $offer->orderCode ?? 'OFFER-00021' }}</h2><p>Subject: {{ addslashes($offer->subject ?? '') }}</p><p>Delivery: {{ addslashes($offer->delivery_plan ?? '') }}</p></body></html>',

        get currentContact() {
            return this.contactsData[this.selectedContactId] || null;
        },

        get currentAddresses() {
            return this.currentContact ? (this.currentContact.addresses || []) : [];
        },

        get currentAddress() {
            if (!this.selectedAddressId && this.currentAddresses.length > 0) {
                return this.currentAddresses[0];
            }
            return this.currentAddresses.find(a => String(a.id) === String(this.selectedAddressId)) || null;
        },

        get currentRepresentatives() {
            return this.currentContact ? (this.currentContact.representatives || []) : [];
        },

        get currentRepresentative() {
            if (!this.selectedRepId && this.currentRepresentatives.length > 0) {
                return this.currentRepresentatives[0];
            }
            return this.currentRepresentatives.find(r => String(r.id) === String(this.selectedRepId)) || null;
        },

        get totalQty() {
            return this.products.reduce((acc, p) => acc + (parseFloat(p.quantity) || 0), 0);
        },

        get totalAmount() {
            return this.products.reduce((acc, p) => acc + (parseFloat(p.total) || (parseFloat(p.quantity || 0) * parseFloat(p.unit_price || 0))), 0);
        },

        formatMoney(val) {
            return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        onContactChange() {
            if (this.currentAddresses.length > 0) {
                this.selectedAddressId = this.currentAddresses[0].id;
            } else {
                this.selectedAddressId = '';
            }

            if (this.currentRepresentatives.length > 0) {
                this.selectedRepId = this.currentRepresentatives[0].id;
            } else {
                this.selectedRepId = '';
            }
        },

        openAddProduct() {
            this.editingIndex = null;
            this.productForm = {
                id: Date.now(),
                product_id: '',
                item_name: '',
                quantity: 1,
                uom: 'MT',
                unit_price: 0,
                total: 0,
                pricing_type: 'CUSTOM'
            };
            this.showProductModal = true;
        },

        openEditProduct(index) {
            this.editingIndex = index;
            const p = this.products[index];
            this.productForm = {
                id: p.id || Date.now(),
                product_id: p.product_id || '',
                item_name: p.item_name || '',
                quantity: p.quantity || 1,
                uom: p.uom || 'MT',
                unit_price: p.unit_price || 0,
                total: p.total || 0,
                pricing_type: p.pricing_type || 'CUSTOM'
            };
            this.showProductModal = true;
        },

        saveProduct() {
            if (!this.productForm.item_name && !this.productForm.product_id) {
                alert('Please select or enter an Item name');
                return;
            }
            this.productForm.total = (parseFloat(this.productForm.quantity) || 0) * (parseFloat(this.productForm.unit_price) || 0);
            
            if (this.editingIndex !== null) {
                this.products[this.editingIndex] = Object.assign({}, this.productForm);
            } else {
                this.products.push(Object.assign({}, this.productForm));
            }
            this.showProductModal = false;
        },

        deleteProduct(index) {
            if (confirm('Do you want to delete this product?')) {
                this.products.splice(index, 1);
            }
        }
    }"
    @click.away="showOptionsMenu = false; showDocActions = false; showDuePopover = false"
>
    <!-- Top Back Link & Action Bar Matching Screenshot 1 -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <div>
            <a href="{{ route('offers.index') }}" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 text-sm font-semibold transition-colors mb-1">
                <span class="material-icons-outlined text-base">arrow_back</span>
                <span>Offers</span>
            </a>
            <p class="text-xs text-slate-500 font-normal pl-5">Offer Detail</p>
        </div>

        <!-- Top Actions: Remove & Save Pill Buttons -->
        <div class="flex items-center gap-3">
            @if($offer->exists)
                <!-- Remove Button Matching Screenshot -->
                <form id="delete-form" method="POST" action="{{ route('offers.destroy', $offer->id) }}" onsubmit="return confirm('Are you sure you want to remove this offer?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-xs font-semibold rounded-full shadow-xs transition-colors cursor-pointer"
                    >
                        <span class="material-icons-outlined text-sm">delete</span>
                        <span>Remove</span>
                    </button>
                </form>
            @endif

            <!-- Save Button Matching Screenshot -->
            <button
                type="submit"
                form="offer-form"
                class="inline-flex items-center px-6 py-2 bg-[#e2e8f0] text-slate-700 hover:bg-[#4f46e5] hover:text-white text-xs font-semibold rounded-full shadow-xs transition-colors cursor-pointer"
            >
                Save
            </button>
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

    <!-- Standalone White Content Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-8 relative">
        <!-- Tabs Row: Basic Info -->
        <div class="border-b border-slate-200 flex items-center gap-6 text-xs font-medium pb-px">
            <button
                type="button"
                class="text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px] focus:outline-none cursor-default"
            >
                Basic Info
            </button>
        </div>

        <!-- Main Form -->
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

            <!-- Hidden input for synchronized products JSON array -->
            <input type="hidden" name="products_json" :value="JSON.stringify(products)">

            <!-- Subheader Row: Left Code + Right Offer Date & Vertical Action Strip (Image 1) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
                <div class="flex items-center gap-2 text-slate-800 text-sm font-semibold">
                    <span class="text-slate-400 font-bold">&gt;</span>
                    <span>{{ $offer->exists ? ($offer->code ?? $offer->orderCode ?? 'OFFER-' . str_pad($offer->id, 5, '0', STR_PAD_LEFT)) : 'New Offer' }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Offer date * with Calendar Icon -->
                    <div class="relative w-48">
                        <input
                            type="text"
                            name="offer_date"
                            id="offer_date"
                            value="{{ old('offer_date', $offer->offer_date ? \Carbon\Carbon::parse($offer->offer_date)->format('d/m/Y') : date('d/m/Y')) }}"
                            class="peer w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-md focus:border-indigo-600 bg-white text-slate-800 focus:outline-none transition-colors"
                            placeholder="DD/MM/YYYY"
                        />
                        <label
                            for="offer_date"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Offer date <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">calendar_today</span>
                    </div>

                    <!-- Right Side Vertical Action Strip Matching Screenshot 1 -->
                    <div class="relative flex flex-col bg-slate-50/90 rounded-xl p-1 gap-1 border border-slate-200/90 shadow-2xs">
                        <!-- 1. Due date menu -->
                        <button
                            type="button"
                            @click="showDuePopover = !showDuePopover"
                            class="w-8 h-8 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-white flex items-center justify-center transition-colors"
                            title="Due Date"
                        >
                            <span class="material-icons-outlined text-base">calendar_month</span>
                        </button>

                        <!-- 2. Attachment button -->
                        <label
                            for="file-upload"
                            class="w-8 h-8 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-white flex items-center justify-center transition-colors cursor-pointer"
                            title="Attach File"
                        >
                            <span class="material-icons-outlined text-base">attach_file</span>
                        </label>
                        <input type="file" id="file-upload" class="hidden" multiple @change="alert('File attached successfully')">

                        <!-- 3. Three dots Options menu -->
                        <button
                            type="button"
                            @click="showOptionsMenu = !showOptionsMenu"
                            class="w-8 h-8 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-white flex items-center justify-center transition-colors"
                            title="Options"
                        >
                            <span class="material-icons text-base">more_horiz</span>
                        </button>

                        <!-- Options Popover Menu -->
                        <div
                            x-show="showOptionsMenu"
                            x-transition
                            class="absolute right-10 top-0 w-44 bg-white border border-slate-200 rounded-lg shadow-lg z-30 py-1 text-xs text-slate-700"
                        >
                            <a href="#" @click.prevent="alert('Duplicate offer copied')" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50">
                                <span class="material-icons-outlined text-sm">content_copy</span>
                                <span>Duplicate Offer</span>
                            </a>
                            <a href="#" @click.prevent="alert('Offer cancelled')" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 text-red-600">
                                <span class="material-icons-outlined text-sm">cancel</span>
                                <span>Cancel Offer</span>
                            </a>
                            <a href="#" @click.prevent="alert('Promoted to Contract')" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 text-emerald-600">
                                <span class="material-icons-outlined text-sm">check_circle</span>
                                <span>Agree / Promote</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field 1: Type * (Dropdown) -->
            <div class="relative">
                <select
                    name="type"
                    id="type"
                    required
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                >
                    <option value="SELL" {{ old('type', $offer->type ?? 'SELL') === 'SELL' ? 'selected' : '' }}>Sales</option>
                    <option value="BUY" {{ old('type', $offer->type ?? 'SELL') === 'BUY' ? 'selected' : '' }}>Purchase</option>
                </select>
                <label
                    for="type"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    value="{{ old('subject', $offer->subject ?? '125 MT ZINC SHG 99.995%') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Subject"
                />
                <label
                    for="subject"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors resize-y"
                    placeholder="Description"
                >{{ old('description', $offer->description ?? 'ZINC SHG 99.995%') }}</textarea>
                <label
                    for="description"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors resize-y"
                    placeholder="Internal notes"
                >{{ old('internal_notes', $offer->internal_notes ?? 'Internal notes') }}</textarea>
                <label
                    for="internal_notes"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Internal notes
                </label>
            </div>

            <!-- Field 5: Delivery Plan * (Image 2) -->
            <div class="relative">
                <input
                    type="text"
                    name="delivery_plan"
                    id="delivery_plan"
                    value="{{ old('delivery_plan', $offer->delivery_plan ?? 'Rades Port') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Delivery Plan"
                />
                <label
                    for="delivery_plan"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    value="{{ old('quotational_period', $offer->quotational_period ?? 'Quotational Period') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Quotational Period"
                />
                <label
                    for="quotational_period"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    value="{{ old('weights', $offer->weights ?? '125MT') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Weights"
                />
                <label
                    for="weights"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    value="{{ old('payment_description', $offer->payment_description ?? '45 DAYS') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Payment description"
                />
                <label
                    for="payment_description"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
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
                    value="{{ old('terms', $offer->terms ?? 'CFR Rades Port') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Terms"
                />
                <label
                    for="terms"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Terms <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 10: Extra information (Image 3) -->
            <div class="relative">
                <input
                    type="text"
                    name="extra_information"
                    id="extra_information"
                    value="{{ old('extra_information', $offer->extra_information ?? 'Extra information') }}"
                    class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                    placeholder="Extra information"
                />
                <label
                    for="extra_information"
                    class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Extra information
                </label>
            </div>

            <!-- Section: Documents (Image 3) -->
            <div class="pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-icons-outlined text-slate-700 text-lg">attach_file</span>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Documents</h3>
                </div>

                <div class="flex flex-wrap gap-4">
                    <!-- Document Card Matching Screenshot 3 -->
                    <div class="bg-slate-50/90 border border-slate-200/90 rounded-xl p-4 w-60 relative shadow-2xs">
                        <div class="text-xs sm:text-sm font-semibold text-slate-800 truncate mb-3" :title="docTitle">
                            <span x-text="docTitle">OFFER_{{ $offer->code ?? $offer->orderCode ?? 'OFFER-00021' }}</span>
                        </div>

                        <div class="relative inline-block">
                            <button
                                type="button"
                                @click="showDocActions = !showDocActions"
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-300 hover:border-slate-400 rounded-md text-xs font-semibold text-slate-700 shadow-2xs transition-colors"
                            >
                                <span>Actions</span>
                                <span class="material-icons-outlined text-xs">expand_more</span>
                            </button>

                            <!-- Document Actions Dropdown Menu -->
                            <div
                                x-show="showDocActions"
                                x-transition
                                class="absolute left-0 mt-1 w-36 bg-white border border-slate-200 rounded-lg shadow-lg z-30 py-1 text-xs text-slate-700"
                            >
                                <a href="#" @click.prevent="showEditDocModal = true; showDocActions = false" class="block px-3 py-1.5 hover:bg-slate-50">
                                    Edit
                                </a>
                                <a href="#" @click.prevent="alert('Download document'); showDocActions = false" class="block px-3 py-1.5 hover:bg-slate-50">
                                    Download
                                </a>
                                <a href="#" @click.prevent="alert('Document regenerated'); showDocActions = false" class="block px-3 py-1.5 hover:bg-slate-50">
                                    Regenerate
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divider Line (Image 3) -->
            <div class="border-b border-slate-200 pt-6"></div>

            <!-- Deal Details Section: Meta Company & Agree/Disagree Date (Image 3 & 4) -->
            <div class="space-y-6 pt-2">
                <!-- Row 1: Meta Company * & Agree or Disagree Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Meta Company * -->
                    <div class="relative">
                        <select
                            name="meta_company_id"
                            id="meta_company_id"
                            required
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Meta Company</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ (string)$c->id === (string)$effectiveMetaCompanyId ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="meta_company_id"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Meta Company <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Agree or Disagree Date -->
                    <div class="relative">
                        <input
                            type="text"
                            name="agree_date"
                            id="agree_date"
                            value="{{ $effectiveAgreeDate }}"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                            placeholder="DD/MM/YYYY"
                        />
                        <label
                            for="agree_date"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Agree or Disagree Date
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">calendar_today</span>
                    </div>
                </div>

                <!-- Row 2 (3 Columns): Name *, Contact Address *, Contact Person * (Image 4) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Column 1: Name * (Contact) -->
                    <div>
                        <div class="relative">
                            <select
                                name="contact_id"
                                id="contact_id"
                                x-model="selectedContactId"
                                @change="onContactChange()"
                                required
                                class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                            >
                                <option value="">Select a name</option>
                                @foreach($contacts as $cnt)
                                    <option value="{{ $cnt->id }}">{{ $cnt->name }}</option>
                                @endforeach
                            </select>
                            <label
                                for="contact_id"
                                class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Name <span class="text-slate-400">*</span>
                            </label>
                            <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                        </div>

                        <!-- Card under Contact Name: VAT Number -->
                        <div class="mt-2.5 p-3 bg-slate-50/90 border border-slate-200/80 rounded-lg min-h-[58px] text-xs text-slate-700">
                            <div class="font-bold text-slate-800 mb-0.5">VAT Number</div>
                            <div x-text="currentContact ? (currentContact.vat || '-') : '-'"></div>
                        </div>
                    </div>

                    <!-- Column 2: Contact Address * -->
                    <div>
                        <div class="relative">
                            <select
                                name="address_id"
                                id="address_id"
                                x-model="selectedAddressId"
                                class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                            >
                                <option value="">Select an address</option>
                                <template x-for="addr in currentAddresses" :key="addr.id">
                                    <option :value="addr.id" x-text="addr.name || 'Address #' + addr.id"></option>
                                </template>
                            </select>
                            <label
                                for="address_id"
                                class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Contact Address <span class="text-slate-400">*</span>
                            </label>
                            <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                        </div>

                        <!-- Card under Contact Address: Address lines -->
                        <div class="mt-2.5 p-3 bg-slate-50/90 border border-slate-200/80 rounded-lg min-h-[58px] text-xs text-slate-700 space-y-0.5">
                            <div class="font-bold text-slate-800" x-text="currentAddress ? (currentAddress.line1 || currentAddress.name || '-') : '-'"></div>
                            <div x-text="currentAddress ? (currentAddress.postal_code || '.') : '.'"></div>
                            <div x-text="currentAddress ? (currentAddress.city || '-') : '-'"></div>
                        </div>
                    </div>

                    <!-- Column 3: Contact Person * -->
                    <div>
                        <div class="relative">
                            <select
                                name="contact_representative_id"
                                id="contact_representative_id"
                                x-model="selectedRepId"
                                class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                            >
                                <option value="">Select a contact person</option>
                                <template x-for="rep in currentRepresentatives" :key="rep.id">
                                    <option :value="rep.id" x-text="rep.name"></option>
                                </template>
                            </select>
                            <label
                                for="contact_representative_id"
                                class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Contact Person <span class="text-slate-400">*</span>
                            </label>
                            <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                        </div>

                        <!-- Card under Contact Person: Representative lines -->
                        <div class="mt-2.5 p-3 bg-slate-50/90 border border-slate-200/80 rounded-lg min-h-[58px] text-xs text-slate-700 space-y-0.5">
                            <div class="font-bold text-slate-800" x-text="currentRepresentative ? currentRepresentative.name : '-'"></div>
                            <div x-text="currentRepresentative ? (currentRepresentative.email || '') : ''"></div>
                            <div x-text="currentRepresentative ? (currentRepresentative.phone || '') : ''"></div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Currency * & Incoterms * (Image 4) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Currency * -->
                    <div class="relative">
                        <select
                            name="currency_id"
                            id="currency_id"
                            required
                            @change="const opt = $event.target.options[$event.target.selectedIndex]; currencyCode = opt.text;"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Currency</option>
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}" {{ (string)$curr->id === (string)$effectiveCurrencyId ? 'selected' : '' }}>
                                    {{ $curr->description ?? $curr->name }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="currency_id"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Currency <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Incoterms * -->
                    <div class="relative">
                        <input
                            type="text"
                            name="incoterms"
                            id="incoterms"
                            value="{{ old('incoterms', $effectiveIncoterms) }}"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                            placeholder="Incoterms"
                        />
                        <label
                            for="incoterms"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Incoterms <span class="text-slate-400">*</span>
                        </label>
                    </div>
                </div>

                <!-- Row 4: Payment type, Payment Terms type, Payment Terms (Image 4) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Payment type -->
                    <div class="relative">
                        <select
                            name="payment_type_id"
                            id="payment_type_id"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Payment Type</option>
                            @foreach($paymentTypes as $pt)
                                <option value="{{ $pt->id }}" {{ (string)$pt->id === (string)$effectivePaymentTypeId ? 'selected' : '' }}>
                                    {{ $pt->description }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="payment_type_id"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Payment type
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Payment Terms type -->
                    <div class="relative">
                        <select
                            name="payment_terms_type_id"
                            id="payment_terms_type_id"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Payment Terms Type</option>
                            @foreach($paymentTermsTypes as $ptt)
                                <option value="{{ $ptt->id }}" {{ (string)$ptt->id === (string)$effectivePaymentTermsTypeId ? 'selected' : '' }}>
                                    {{ $ptt->description }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="payment_terms_type_id"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Payment Terms type
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Payment Terms -->
                    <div class="relative">
                        <input
                            type="text"
                            name="payment_terms"
                            id="payment_terms"
                            value="{{ old('payment_terms', $effectivePaymentTerms) }}"
                            class="peer w-full px-3.5 py-3 rounded-md border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-xs sm:text-sm focus:outline-none transition-colors"
                            placeholder="Payment Terms"
                        />
                        <label
                            for="payment_terms"
                            class="absolute left-2.5 -top-2 bg-white px-1 text-[11px] font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Payment Terms
                        </label>
                    </div>
                </div>

                <!-- Products Table Section Matching Screenshot 4 & 5 -->
                <div class="pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-icons-outlined text-slate-700 text-lg">table_chart</span>
                        <h3 class="font-bold text-slate-800 text-sm tracking-wide">Products <span class="text-slate-400">*</span></h3>
                    </div>

                    <div class="bg-white rounded-lg border border-slate-200/90 overflow-hidden shadow-2xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-50/70 border-b border-slate-200/80 text-slate-800 font-semibold select-none">
                                        <th class="py-3 px-4">Item</th>
                                        <th class="py-3 px-4">Qty</th>
                                        <th class="py-3 px-4">UOM</th>
                                        <th class="py-3 px-4">U. Price</th>
                                        <th class="py-3 px-4">Total</th>
                                        <th class="py-3 px-4">Pricing type</th>
                                        <th class="py-3 px-4 text-center w-20"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(prod, idx) in products" :key="idx">
                                        <tr class="border-b border-slate-100 hover:bg-slate-50/60 transition-colors">
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="prod.item_name"></td>
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="prod.quantity"></td>
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="prod.uom || 'MT'"></td>
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="currencyCode + ' ' + formatMoney(prod.unit_price)"></td>
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="currencyCode + ' ' + formatMoney(prod.total)"></td>
                                            <td class="py-3.5 px-4 font-normal text-slate-800" x-text="prod.pricing_type || 'CUSTOM'"></td>
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5 text-slate-500">
                                                    <button type="button" @click="openEditProduct(idx)" class="hover:text-indigo-600 transition p-1">
                                                        <span class="material-icons-outlined text-sm">edit</span>
                                                    </button>
                                                    <button type="button" @click="deleteProduct(idx)" class="hover:text-red-600 transition p-1">
                                                        <span class="material-icons-outlined text-sm">delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="products.length === 0">
                                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                            No products added yet. Click "+ Add Product" below.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Footer Totals Matching Screenshot 4 & 5 -->
                        <div class="bg-slate-50/80 px-6 py-3 border-t border-slate-200/80 flex flex-wrap items-center justify-between text-xs sm:text-sm font-semibold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="font-bold">Total Qty:</span>
                                <span x-text="totalQty"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold">Total:</span>
                                <span x-text="currencyCode + ' ' + formatMoney(totalAmount)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- + Add Product Button (Screenshot 5) -->
                    <div class="mt-4">
                        <button
                            type="button"
                            @click="openAddProduct()"
                            class="px-5 py-2 bg-[#0284c7] hover:bg-[#0369a1] text-white rounded-full text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                        >
                            <span>Add Product</span>
                        </button>
                    </div>
                </div>

                <!-- Activities Timeline Section Matching Screenshot 5 -->
                <div class="pt-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons-outlined text-slate-700 text-lg">assignment</span>
                        <h3 class="font-bold text-slate-800 text-sm tracking-wide">Activities</h3>
                    </div>

                    <div class="space-y-4 max-w-4xl pt-1">
                        @if($logs->isNotEmpty())
                            @foreach($logs as $log)
                                <div class="flex items-start gap-4">
                                    <div class="w-24 text-[11px] text-slate-400 font-mono text-right shrink-0 pt-0.5">
                                        <div>{{ \Carbon\Carbon::parse($log->date_time ?? $log->dateTime)->format('d M y') }}</div>
                                        <div>{{ \Carbon\Carbon::parse($log->date_time ?? $log->dateTime)->format('H:i:s') }}</div>
                                    </div>

                                    <div class="flex flex-col items-center shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                            <span class="material-icons text-base">person</span>
                                        </div>
                                        <div class="w-0.5 h-8 bg-slate-200 my-1"></div>
                                    </div>

                                    <div class="flex-1 min-w-0 pt-1">
                                        <div class="text-xs font-bold text-slate-800 tracking-wide uppercase">
                                            {{ $log->user_display_name ?? 'KARIMA' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            {{ $log->action ?? 'CREATED' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Default Activity from Screenshot 5 -->
                            <div class="flex items-start gap-4">
                                <div class="w-24 text-[11px] text-slate-400 font-mono text-right shrink-0 pt-0.5">
                                    <div>13 Feb 26</div>
                                    <div>14:45:43</div>
                                </div>

                                <div class="flex flex-col items-center shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        <span class="material-icons text-base">person</span>
                                    </div>
                                    <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                                </div>

                                <div class="flex-1 min-w-0 pt-1">
                                    <div class="text-xs font-bold text-slate-800 tracking-wide uppercase">
                                        KARIMA
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                        CREATED
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Add / Edit Product Modal -->
    <div
        x-show="showProductModal"
        x-transition
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4" @click.away="showProductModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="text-base font-bold text-slate-900" x-text="editingIndex !== null ? 'Edit product' : 'Add product'"></h3>
                <button type="button" @click="showProductModal = false" class="text-slate-400 hover:text-slate-600">
                    <span class="material-icons-outlined text-lg">close</span>
                </button>
            </div>

            <div class="space-y-4 text-xs sm:text-sm">
                <!-- Product select -->
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Item *</label>
                    <select
                        x-model="productForm.product_id"
                        @change="
                            const sel = $event.target.options[$event.target.selectedIndex];
                            productForm.item_name = sel.text;
                            productForm.uom = sel.getAttribute('data-uom') || 'MT';
                        "
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                    >
                        <option value="">Select a product</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-uom="{{ $prod->unitOfMeasurement ?? $prod->uom ?? 'MT' }}">
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Custom Item Name fallback -->
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Item Name Display *</label>
                    <input
                        type="text"
                        x-model="productForm.item_name"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                        placeholder="e.g. Special High Grade Zinc Ingots (SHG)"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                        <input
                            type="number"
                            step="any"
                            x-model="productForm.quantity"
                            @input="productForm.total = (parseFloat(productForm.quantity) || 0) * (parseFloat(productForm.unit_price) || 0)"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">UOM</label>
                        <input
                            type="text"
                            x-model="productForm.uom"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pricing Type</label>
                        <select
                            x-model="productForm.pricing_type"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                        >
                            <option value="CUSTOM">CUSTOM</option>
                            <option value="LME">LME</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price (<span x-text="currencyCode"></span>) *</label>
                        <input
                            type="number"
                            step="any"
                            x-model="productForm.unit_price"
                            @input="productForm.total = (parseFloat(productForm.quantity) || 0) * (parseFloat(productForm.unit_price) || 0)"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:border-indigo-600 text-xs sm:text-sm focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Total</label>
                    <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-md font-bold text-slate-800">
                        <span x-text="currencyCode + ' ' + formatMoney(productForm.total)"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                <button
                    type="button"
                    @click="showProductModal = false"
                    class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-xs font-semibold"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="saveProduct()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold"
                >
                    Save Product
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div
        x-show="showEditDocModal"
        x-transition
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 space-y-4" @click.away="showEditDocModal = false">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="text-base font-bold text-slate-900" x-text="'Edit ' + docTitle"></h3>
                <button type="button" @click="showEditDocModal = false" class="text-slate-400 hover:text-slate-600">
                    <span class="material-icons-outlined text-lg">close</span>
                </button>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-semibold text-slate-700">Document HTML / Content</label>
                <textarea
                    x-model="docContent"
                    rows="8"
                    class="w-full font-mono text-xs p-3 border border-slate-300 rounded-md focus:border-indigo-600 focus:outline-none"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button
                    type="button"
                    @click="showEditDocModal = false"
                    class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-xs font-semibold"
                >
                    Close
                </button>
                <button
                    type="button"
                    @click="alert('Document saved'); showEditDocModal = false"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold"
                >
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
