@extends('layouts.app')

@section('title', $contact->exists ? $contact->name : 'New Contact')

@section('content')
@php
    $initialAddresses = [];
    if ($contact->exists && $contact->addresses && $contact->addresses->count() > 0) {
        foreach ($contact->addresses as $a) {
            $rep = $a->representatives ? $a->representatives->first() : null;
            $initialAddresses[] = [
                'id' => $a->id,
                'addressName' => $a->addressName ?? '',
                'addressLine1' => $a->addressLine1 ?? '',
                'addressLine2' => $a->addressLine2 ?? '',
                'country_id' => $a->country_id ?? '',
                'state' => $a->state ?? '',
                'city' => $a->city ?? '',
                'postalCode' => $a->postalCode ?? '',
                'postalBox' => $a->postalBox ?? '',
                'forGoogleMapsSearch' => $a->forGoogleMapsSearch ?? '',
                'contact_person' => [
                    'name' => $rep->name ?? '',
                    'job_title' => $rep->job_title ?? '',
                    'email' => $rep->email ?? '',
                    'phone' => $rep->phone ?? '',
                    'phone2' => $rep->phone2 ?? '',
                    'mobile' => $rep->mobile ?? '',
                    'department' => $rep->department ?? '',
                ]
            ];
        }
    } else {
        $initialAddresses[] = [
            'id' => null,
            'addressName' => '',
            'addressLine1' => '',
            'addressLine2' => '',
            'country_id' => '',
            'state' => '',
            'city' => '',
            'postalCode' => '',
            'postalBox' => '',
            'forGoogleMapsSearch' => '',
            'contact_person' => [
                'name' => '',
                'job_title' => '',
                'email' => '',
                'phone' => '',
                'phone2' => '',
                'mobile' => '',
                'department' => '',
            ]
        ];
    }

    $initialBankAccounts = [];
    if ($contact->exists && $contact->bankAccounts && $contact->bankAccounts->count() > 0) {
        foreach ($contact->bankAccounts as $b) {
            $initialBankAccounts[] = [
                'id' => $b->id,
                'description' => $b->description ?? '',
                'name' => $b->name ?? '',
                'sortCode' => $b->sortCode ?? '',
                'accountNumber' => $b->accountNumber ?? '',
                'iban' => $b->iban ?? '',
                'swift' => $b->swift ?? '',
            ];
        }
    } else {
        $initialBankAccounts[] = [
            'id' => null,
            'description' => '',
            'name' => '',
            'sortCode' => '',
            'accountNumber' => '',
            'iban' => '',
            'swift' => '',
        ];
    }
@endphp

<div
    class="space-y-4 max-w-7xl mx-auto pb-12"
    x-data="{
        activeTab: 'basic-info',
        addresses: {{ json_encode($initialAddresses) }},
        bankAccounts: {{ json_encode($initialBankAccounts) }},
        addAddress() {
            this.addresses.push({
                id: null,
                addressName: '',
                addressLine1: '',
                addressLine2: '',
                country_id: '',
                state: '',
                city: '',
                postalCode: '',
                postalBox: '',
                forGoogleMapsSearch: '',
                contact_person: { name: '', job_title: '', email: '', phone: '', phone2: '', mobile: '', department: '' }
            });
        },
        removeAddress(index) {
            if (this.addresses.length > 1) {
                this.addresses.splice(index, 1);
            }
        },
        addAccount() {
            this.bankAccounts.push({ id: null, description: '', name: '', sortCode: '', accountNumber: '', iban: '', swift: '' });
        },
        removeAccount(index) {
            if (this.bankAccounts.length > 1) {
                this.bankAccounts.splice(index, 1);
            } else {
                this.bankAccounts = [{ id: null, description: '', name: '', sortCode: '', accountNumber: '', iban: '', swift: '' }];
            }
        }
    }"
>
    <!-- Back Link & Header -->
    <div>
        <a href="{{ route('contacts.index') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors mb-3">
            <span class="material-icons-outlined text-base">arrow_back</span>
            <span>Contacts</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">
                    {{ $contact->exists ? $contact->name : 'New Contact' }}
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">Contact Detail</p>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-3">
                <!-- Permission Warning Pill (Matching Image 2) -->
                <div class="inline-flex items-center gap-1.5 p-2 px-3 bg-red-50 border border-red-200/80 rounded-xl text-red-600 text-xs shadow-2xs">
                    <span class="material-icons-outlined text-base text-red-500">error_outline</span>
                    <span>You don't have permission to edit for this company</span>
                </div>

                @if($contact->exists)
                    <!-- Remove Button with Trash Icon -->
                    <form id="delete-form" method="POST" action="{{ route('contacts.destroy', $contact->id) }}" onsubmit="return confirm('Are you sure you want to remove this contact?');" class="inline">
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
                    form="contact-form"
                    class="inline-flex items-center px-6 py-2 {{ $contact->exists ? 'bg-slate-200 text-slate-700 hover:bg-[#4f46e5] hover:text-white' : 'bg-slate-200 text-slate-400 hover:bg-[#4f46e5] hover:text-white' }} text-sm font-semibold rounded-full shadow-xs transition-colors"
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
                @click="activeTab = 'basic-info'"
                :class="activeTab === 'basic-info' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                class="focus:outline-none"
            >
                Basic Info
            </button>
            <button
                type="button"
                @click="activeTab = 'addresses'"
                :class="activeTab === 'addresses' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                class="focus:outline-none"
            >
                Addresses
            </button>
            <button
                type="button"
                @click="activeTab = 'bank-accounts'"
                :class="activeTab === 'bank-accounts' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                class="focus:outline-none"
            >
                Bank Accounts
            </button>
        </div>

        <!-- Single Form Spanning Tabs -->
        <form
            id="contact-form"
            method="POST"
            action="{{ $contact->exists ? route('contacts.update', $contact->id) : route('contacts.store') }}"
            class="pt-6 max-w-4xl"
        >
            @csrf
            @if($contact->exists)
                @method('PUT')
            @endif

            <!-- TAB 1: BASIC INFO (Images 2 & 3) -->
            <div x-show="activeTab === 'basic-info'" class="space-y-6">
                <!-- Checkbox: Active -->
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="active"
                        id="active"
                        value="1"
                        {{ old('active', $contact->active ?? true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    />
                    <label for="active" class="text-sm font-normal text-slate-800 cursor-pointer select-none">
                        Active
                    </label>
                </div>

                <!-- Row 1: Name * (left) + Meta company (right) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-7 relative">
                        <div class="relative">
                            <span class="material-icons-outlined text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">apartment</span>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                required
                                value="{{ old('name', $contact->name) }}"
                                class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Name"
                            />
                        </div>
                        <label
                            for="name"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Name <span class="text-slate-400">*</span>
                        </label>
                    </div>

                    <div class="md:col-span-5 relative">
                        <select
                            name="companyId"
                            id="companyId"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Meta company</option>
                            @foreach($companies as $cmp)
                                <option value="{{ $cmp->id }}" {{ old('companyId', $contact->companyId) == $cmp->id ? 'selected' : '' }}>
                                    {{ $cmp->name }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="companyId"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Meta company
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>
                </div>

                <!-- Row 2: Types (full-width) -->
                <div class="relative">
                    <select
                        name="types"
                        id="types"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                    >
                        <option value="">Types</option>
                        @foreach(['SUPPLIER', 'CUSTOMER', 'BROKER', 'CARRIER', 'SERVICE_PROVIDER'] as $typeOpt)
                            <option value="{{ $typeOpt }}" {{ old('types', $contact->types) === $typeOpt ? 'selected' : '' }}>
                                {{ $typeOpt }}
                            </option>
                        @endforeach
                    </select>
                    <label
                        for="types"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Types
                    </label>
                    <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                </div>

                <!-- Row 3: Template language * + Country * + Code Meta -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Template language * -->
                    <div class="relative">
                        <select
                            name="template_language"
                            id="template_language"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Template language</option>
                            @foreach(['English', 'Arabic', 'Spanish', 'French', 'German'] as $lang)
                                <option value="{{ $lang }}" {{ old('template_language', $contact->template_language ?? 'English') === $lang ? 'selected' : '' }}>
                                    {{ $lang }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="template_language"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Template language <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Country * -->
                    <div class="relative">
                        <select
                            name="countryId"
                            id="countryId"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">Country</option>
                            @foreach($countries as $cntry)
                                <option value="{{ $cntry->id }}" {{ old('countryId', $contact->countryId) == $cntry->id ? 'selected' : '' }}>
                                    {{ $cntry->name }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            for="countryId"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Country <span class="text-slate-400">*</span>
                        </label>
                        <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                    </div>

                    <!-- Code Meta with Building Icon -->
                    <div class="relative">
                        <div class="relative">
                            <span class="material-icons-outlined text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">apartment</span>
                            <input
                                type="text"
                                name="codeMeta"
                                id="codeMeta"
                                value="{{ old('codeMeta', $contact->codeMeta) }}"
                                class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Code Meta"
                            />
                        </div>
                        <label
                            for="codeMeta"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Code Meta
                        </label>
                    </div>
                </div>

                <!-- Row 4: Registration + VAT + EORI Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Registration with Briefcase Icon -->
                    <div class="relative">
                        <div class="relative">
                            <span class="material-icons-outlined text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">business_center</span>
                            <input
                                type="text"
                                name="registration"
                                id="registration"
                                value="{{ old('registration', $contact->registration) }}"
                                class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Registration"
                            />
                        </div>
                        <label
                            for="registration"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Registration
                        </label>
                    </div>

                    <!-- VAT with Building Icon -->
                    <div class="relative">
                        <div class="relative">
                            <span class="material-icons-outlined text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">apartment</span>
                            <input
                                type="text"
                                name="vat"
                                id="vat"
                                value="{{ old('vat', $contact->vat) }}"
                                class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="VAT"
                            />
                        </div>
                        <label
                            for="vat"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            VAT
                        </label>
                    </div>

                    <!-- EORI Number -->
                    <div class="relative">
                        <input
                            type="text"
                            name="eoriNumber"
                            id="eoriNumber"
                            value="{{ old('eoriNumber', $contact->eoriNumber) }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="EORI Number"
                        />
                        <label
                            for="eoriNumber"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            EORI Number
                        </label>
                    </div>
                </div>

                <!-- Row 5: Website (Image 3) -->
                <div class="relative">
                    <div class="relative">
                        <span class="material-icons-outlined text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">public</span>
                        <input
                            type="text"
                            name="website"
                            id="website"
                            value="{{ old('website', $contact->website) }}"
                            class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="Website"
                        />
                    </div>
                    <label
                        for="website"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Website
                    </label>
                </div>

                <!-- Row 6: Additional Information (Image 3) -->
                <div class="relative">
                    <textarea
                        name="additionalInformation"
                        id="additionalInformation"
                        rows="4"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors resize-y"
                        placeholder="Additional Information"
                    >{{ old('additionalInformation', $contact->additionalInformation) }}</textarea>
                    <label
                        for="additionalInformation"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Additional Information
                    </label>
                </div>

                <!-- Row 7: Associated Product (Image 3) -->
                <div class="relative">
                    <select
                        name="product_id"
                        id="product_id"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                    >
                        <option value="">Associated Product</option>
                        @php
                            $selectedProductId = $contact->products ? $contact->products->pluck('id')->first() : null;
                        @endphp
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ old('product_id', $selectedProductId) == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }}
                            </option>
                        @endforeach
                    </select>
                    <label
                        for="product_id"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Associated Product
                    </label>
                    <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                </div>
            </div>

            <!-- TAB 2: ADDRESSES (Images 4 & 5) -->
            <div x-show="activeTab === 'addresses'" class="space-y-8" style="display: none;">
                <template x-for="(addr, aIdx) in addresses" :key="aIdx">
                    <div class="space-y-6 pb-6 border-b border-slate-200">
                        <input type="hidden" :name="'addresses[' + aIdx + '][id]'" :value="addr.id">

                        <!-- Green Pill Header Banner (Image 4) -->
                        <div class="w-full bg-[#2e7d32] text-white text-xs font-semibold py-1.5 px-4 rounded-full text-center tracking-wide">
                            <span x-text="'Address #' + (aIdx + 1) + ' -'">Address #1 -</span>
                        </div>

                        <!-- Address Name * with Pin Icon -->
                        <div class="relative">
                            <div class="relative">
                                <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">location_on</span>
                                <input
                                    type="text"
                                    :name="'addresses[' + aIdx + '][addressName]'"
                                    :id="'addr_name_' + aIdx"
                                    x-model="addr.addressName"
                                    class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="Address Name"
                                />
                            </div>
                            <label
                                :for="'addr_name_' + aIdx"
                                class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Address Name <span class="text-slate-400">*</span>
                            </label>
                        </div>

                        <!-- Address Line 1 * -->
                        <div class="relative">
                            <input
                                type="text"
                                :name="'addresses[' + aIdx + '][addressLine1]'"
                                :id="'addr_l1_' + aIdx"
                                x-model="addr.addressLine1"
                                class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Address Line 1"
                            />
                            <label
                                :for="'addr_l1_' + aIdx"
                                class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Address Line 1 <span class="text-slate-400">*</span>
                            </label>
                        </div>

                        <!-- Address Line 2 -->
                        <div class="relative">
                            <input
                                type="text"
                                :name="'addresses[' + aIdx + '][addressLine2]'"
                                :id="'addr_l2_' + aIdx"
                                x-model="addr.addressLine2"
                                class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Address Line 2"
                            />
                            <label
                                :for="'addr_l2_' + aIdx"
                                class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                Address Line 2
                            </label>
                        </div>

                        <!-- Country * + State/County -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-4 relative">
                                <select
                                    :name="'addresses[' + aIdx + '][country_id]'"
                                    :id="'addr_cntry_' + aIdx"
                                    x-model="addr.country_id"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                                >
                                    <option value="">Country</option>
                                    @foreach($countries as $cntry)
                                        <option value="{{ $cntry->id }}">{{ $cntry->name }}</option>
                                    @endforeach
                                </select>
                                <label
                                    :for="'addr_cntry_' + aIdx"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Country <span class="text-slate-400">*</span>
                                </label>
                                <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                            </div>

                            <div class="md:col-span-8 relative">
                                <input
                                    type="text"
                                    :name="'addresses[' + aIdx + '][state]'"
                                    :id="'addr_state_' + aIdx"
                                    x-model="addr.state"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="State/County"
                                />
                                <label
                                    :for="'addr_state_' + aIdx"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    State/County
                                </label>
                            </div>
                        </div>

                        <!-- City * + Postal Code + Postal Box -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative">
                                <input
                                    type="text"
                                    :name="'addresses[' + aIdx + '][city]'"
                                    :id="'addr_city_' + aIdx"
                                    x-model="addr.city"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="City"
                                />
                                <label
                                    :for="'addr_city_' + aIdx"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    City <span class="text-slate-400">*</span>
                                </label>
                            </div>

                            <div class="relative">
                                <input
                                    type="text"
                                    :name="'addresses[' + aIdx + '][postalCode]'"
                                    :id="'addr_pc_' + aIdx"
                                    x-model="addr.postalCode"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="Postal Code"
                                />
                                <label
                                    :for="'addr_pc_' + aIdx"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Postal Code
                                </label>
                            </div>

                            <div class="relative">
                                <input
                                    type="text"
                                    :name="'addresses[' + aIdx + '][postalBox]'"
                                    :id="'addr_pb_' + aIdx"
                                    x-model="addr.postalBox"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="Postal Box"
                                />
                                <label
                                    :for="'addr_pb_' + aIdx"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Postal Box
                                </label>
                            </div>
                        </div>

                        <!-- For google maps search -->
                        <div class="relative">
                            <input
                                type="text"
                                :name="'addresses[' + aIdx + '][forGoogleMapsSearch]'"
                                :id="'addr_map_' + aIdx"
                                x-model="addr.forGoogleMapsSearch"
                                class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                placeholder="Help to search the address in google maps"
                            />
                            <label
                                :for="'addr_map_' + aIdx"
                                class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                            >
                                For google maps search
                            </label>
                        </div>

                        <!-- Inner Contacts Card (Image 5) -->
                        <div class="border border-slate-200 rounded-xl p-5 bg-white space-y-4 shadow-2xs">
                            <h4 class="font-bold text-slate-800 text-sm">Contacts</h4>

                            <!-- Row 1: Name * + Job title -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <div class="relative">
                                        <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">location_on</span>
                                        <input
                                            type="text"
                                            :name="'addresses[' + aIdx + '][contact_person][name]'"
                                            x-model="addr.contact_person.name"
                                            class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                            placeholder="Name"
                                        />
                                    </div>
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Name <span class="text-slate-400">*</span>
                                    </label>
                                </div>

                                <div class="relative">
                                    <input
                                        type="text"
                                        :name="'addresses[' + aIdx + '][contact_person][job_title]'"
                                        x-model="addr.contact_person.job_title"
                                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Job title"
                                    />
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Job title
                                    </label>
                                </div>
                            </div>

                            <!-- Row 2: e-mail -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">mail</span>
                                    <input
                                        type="email"
                                        :name="'addresses[' + aIdx + '][contact_person][email]'"
                                        x-model="addr.contact_person.email"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="e-mail"
                                    />
                                </div>
                                <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                    e-mail
                                </label>
                            </div>

                            <!-- Row 3: Phone number * + Phone number 2 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <input
                                        type="text"
                                        :name="'addresses[' + aIdx + '][contact_person][phone]'"
                                        x-model="addr.contact_person.phone"
                                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Phone number"
                                    />
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Phone number <span class="text-slate-400">*</span>
                                    </label>
                                </div>

                                <div class="relative">
                                    <input
                                        type="text"
                                        :name="'addresses[' + aIdx + '][contact_person][phone2]'"
                                        x-model="addr.contact_person.phone2"
                                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Phone number 2"
                                    />
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Phone number 2
                                    </label>
                                </div>
                            </div>

                            <!-- Row 4: Mobile + Department -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <input
                                        type="text"
                                        :name="'addresses[' + aIdx + '][contact_person][mobile]'"
                                        x-model="addr.contact_person.mobile"
                                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Mobile"
                                    />
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Mobile
                                    </label>
                                </div>

                                <div class="relative">
                                    <select
                                        :name="'addresses[' + aIdx + '][contact_person][department]'"
                                        x-model="addr.contact_person.department"
                                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                                    >
                                        <option value="">Department</option>
                                        @foreach(['Sales', 'Procurement', 'Finance', 'Logistics', 'Operations', 'Management'] as $dept)
                                            <option value="{{ $dept }}">{{ $dept }}</option>
                                        @endforeach
                                    </select>
                                    <label class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none">
                                        Department
                                    </label>
                                    <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                                </div>
                            </div>

                            <!-- + Add a contact inside this card -->
                            <div class="flex justify-end pt-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 text-slate-900 hover:text-indigo-600 font-semibold text-xs transition-colors focus:outline-none"
                                >
                                    <span class="material-icons text-base text-slate-800">add_circle</span>
                                    <span>Add a contact</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- + Add an address button (Image 5) -->
                <button
                    type="button"
                    @click="addAddress()"
                    class="inline-flex items-center gap-2 text-slate-900 hover:text-indigo-600 font-semibold text-sm transition-colors pt-1 focus:outline-none"
                >
                    <span class="material-icons text-lg text-slate-800">add_circle</span>
                    <span>Add an address</span>
                </button>
            </div>

            <!-- TAB 3: BANK ACCOUNTS -->
            <div x-show="activeTab === 'bank-accounts'" class="space-y-6" style="display: none;">
                <template x-for="(account, index) in bankAccounts" :key="index">
                    <div class="space-y-6 pb-6">
                        <input type="hidden" :name="'bank_accounts[' + index + '][id]'" :value="account.id">

                        <!-- Row 1: Description + Bank Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Description -->
                            <div class="relative">
                                <input
                                    type="text"
                                    :name="'bank_accounts[' + index + '][description]'"
                                    :id="'c_ba_desc_' + index"
                                    x-model="account.description"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="Description"
                                />
                                <label
                                    :for="'c_ba_desc_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Description
                                </label>
                            </div>

                            <!-- Bank Name with Briefcase Icon -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">business_center</span>
                                    <input
                                        type="text"
                                        :name="'bank_accounts[' + index + '][name]'"
                                        :id="'c_ba_name_' + index"
                                        x-model="account.name"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Bank Name"
                                    />
                                </div>
                                <label
                                    :for="'c_ba_name_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Bank Name
                                </label>
                            </div>
                        </div>

                        <!-- Row 2: Sort Code + Account Number with Column/Bank Icon -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Sort Code -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">account_balance</span>
                                    <input
                                        type="text"
                                        :name="'bank_accounts[' + index + '][sortCode]'"
                                        :id="'c_ba_sort_' + index"
                                        x-model="account.sortCode"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Sort Code"
                                    />
                                </div>
                                <label
                                    :for="'c_ba_sort_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Sort Code
                                </label>
                            </div>

                            <!-- Account Number -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">account_balance</span>
                                    <input
                                        type="text"
                                        :name="'bank_accounts[' + index + '][accountNumber]'"
                                        :id="'c_ba_acc_' + index"
                                        x-model="account.accountNumber"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Account Number"
                                    />
                                </div>
                                <label
                                    :for="'c_ba_acc_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    Account Number
                                </label>
                            </div>
                        </div>

                        <!-- Row 3: IBAN + SWIFT -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- IBAN with Briefcase Icon -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">business_center</span>
                                    <input
                                        type="text"
                                        :name="'bank_accounts[' + index + '][iban]'"
                                        :id="'c_ba_iban_' + index"
                                        x-model="account.iban"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="IBAN"
                                    />
                                </div>
                                <label
                                    :for="'c_ba_iban_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    IBAN
                                </label>
                            </div>

                            <!-- SWIFT with Bank/Column Icon -->
                            <div class="relative">
                                <div class="relative">
                                    <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">account_balance</span>
                                    <input
                                        type="text"
                                        :name="'bank_accounts[' + index + '][swift]'"
                                        :id="'c_ba_swift_' + index"
                                        x-model="account.swift"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="SWIFT"
                                    />
                                </div>
                                <label
                                    :for="'c_ba_swift_' + index"
                                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                                >
                                    SWIFT
                                </label>
                            </div>
                        </div>

                        <!-- Trash Icon Centered Below -->
                        <div class="flex justify-center pt-1">
                            <button
                                type="button"
                                @click="removeAccount(index)"
                                class="text-slate-500 hover:text-red-500 transition-colors p-1"
                                title="Remove bank account"
                            >
                                <span class="material-icons-outlined text-base">delete</span>
                            </button>
                        </div>
                    </div>
                </template>

                <div class="border-b border-slate-100 my-2"></div>

                <!-- Add a bank account button -->
                <button
                    type="button"
                    @click="addAccount()"
                    class="inline-flex items-center gap-2 text-slate-900 hover:text-indigo-600 font-semibold text-sm transition-colors pt-2 focus:outline-none"
                >
                    <span class="material-icons text-xl text-slate-800">add_circle</span>
                    <span>Add a bank account</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
