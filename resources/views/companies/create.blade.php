@extends('layouts.app')

@section('title', $company->exists ? $company->name : 'New Company')

@section('content')
@php
    $initialBankAccounts = [];
    if ($company->exists && $company->bankAccounts && $company->bankAccounts->count() > 0) {
        foreach ($company->bankAccounts as $b) {
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
        bankAccounts: {{ json_encode($initialBankAccounts) }},
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
        <a href="{{ route('companies.index') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors mb-3">
            <span class="material-icons-outlined text-base">arrow_back</span>
            <span>Meta group</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">
                    {{ $company->exists ? $company->name : 'New Company' }}
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">Company Detail</p>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-3">
                @if($company->exists)
                    <!-- Remove Button with Trash Icon -->
                    <form id="delete-form" method="POST" action="{{ route('companies.destroy', $company->id) }}" onsubmit="return confirm('Are you sure you want to remove this company?');" class="inline">
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
                    form="company-form"
                    class="inline-flex items-center px-6 py-2 {{ $company->exists ? 'bg-slate-200 text-slate-700 hover:bg-[#4f46e5] hover:text-white' : 'bg-slate-200 text-slate-400 hover:bg-[#4f46e5] hover:text-white' }} text-sm font-semibold rounded-full shadow-xs transition-colors"
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
                @click="activeTab = 'address'"
                :class="activeTab === 'address' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                class="focus:outline-none"
            >
                Address
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
            id="company-form"
            method="POST"
            action="{{ $company->exists ? route('companies.update', $company->id) : route('companies.store') }}"
            enctype="multipart/form-data"
            class="pt-6 max-w-4xl"
        >
            @csrf
            @if($company->exists)
                @method('PUT')
            @endif

            <!-- TAB 1: BASIC INFO -->
            <div x-show="activeTab === 'basic-info'" class="space-y-6">
                <!-- Field 1: Name * -->
                <div class="relative">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        required
                        value="{{ old('name', $company->name) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="Type company name"
                    />
                    <label
                        for="name"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Name <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 2: Code * -->
                <div class="relative">
                    <input
                        type="text"
                        name="code"
                        id="code"
                        required
                        value="{{ old('code', $company->code) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="AZ9999"
                    />
                    <label
                        for="code"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Code <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 3: Company Number * -->
                <div class="relative">
                    <input
                        type="text"
                        name="companyNumber"
                        id="companyNumber"
                        value="{{ old('companyNumber', $company->companyNumber) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="9999999"
                    />
                    <label
                        for="companyNumber"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Company Number <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 4: VAT * -->
                <div class="relative">
                    <input
                        type="text"
                        name="vatNumber"
                        id="vatNumber"
                        value="{{ old('vatNumber', $company->vatNumber) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="GB123456789"
                    />
                    <label
                        for="vatNumber"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        VAT <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 5: EORI Number -->
                <div class="relative">
                    <input
                        type="text"
                        name="eoriNumber"
                        id="eoriNumber"
                        value="{{ old('eoriNumber', $company->eoriNumber) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="GB123456789000"
                    />
                    <label
                        for="eoriNumber"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        EORI Number
                    </label>
                </div>

                <!-- Field 6: Phone Number * -->
                <div class="relative">
                    <input
                        type="text"
                        name="phoneNumber"
                        id="phoneNumber"
                        value="{{ old('phoneNumber', $company->phoneNumber) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="+44 99 9999 9999"
                    />
                    <label
                        for="phoneNumber"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Phone Number <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 7: Fax Number -->
                <div class="relative">
                    <input
                        type="text"
                        name="faxNumber"
                        id="faxNumber"
                        value="{{ old('faxNumber', $company->faxNumber) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="+44 99 9999 9999"
                    />
                    <label
                        for="faxNumber"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Fax Number
                    </label>
                </div>

                <!-- Field 8: e-mail * -->
                <div class="relative">
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $company->email) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="companyname@domain.com"
                    />
                    <label
                        for="email"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        e-mail <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 9: Domain Name * -->
                <div class="relative">
                    <input
                        type="text"
                        name="domain"
                        id="domain"
                        value="{{ old('domain', $company->domain) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="metaenterprices.uk"
                    />
                    <label
                        for="domain"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Domain Name <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 10: Website * -->
                <div class="relative">
                    <input
                        type="text"
                        name="webSite"
                        id="webSite"
                        value="{{ old('webSite', $company->webSite) }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="Website"
                    />
                    <label
                        for="webSite"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Website <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 11: Country -->
                <div class="relative">
                    <select
                        name="countryId"
                        id="countryId"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                    >
                        <option value="">Country</option>
                        @foreach($countries as $cntry)
                            <option value="{{ $cntry->id }}" {{ old('countryId', $company->countryId) == $cntry->id ? 'selected' : '' }}>
                                {{ $cntry->name }}
                            </option>
                        @endforeach
                    </select>
                    <label
                        for="countryId"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Country
                    </label>
                    <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
                </div>

                <!-- Media Upload Boxes: Logo, Stamp, Footer (Matching Image 3) -->
                <div class="space-y-4 pt-2">
                    <!-- Logo Upload Box -->
                    <label class="block border border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors group relative">
                        <input type="file" name="logo" class="hidden">
                        <div class="font-bold text-slate-800 text-sm">Logo</div>
                        <div class="text-xs text-slate-500 mt-0.5">( click to update )</div>
                        <div class="mt-3 flex justify-center">
                            @if($company->logoUrl)
                                <div class="relative inline-flex items-center justify-center">
                                    <img src="{{ asset($company->logoUrl) }}" alt="Logo" class="max-h-16 object-contain">
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-8 h-8 rounded-full bg-white/95 border border-rose-300 text-rose-500 flex items-center justify-center shadow-xs">
                                            <span class="material-icons text-base">photo_camera</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-full bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <span class="material-icons-outlined text-lg">photo_camera</span>
                                </div>
                            @endif
                        </div>
                    </label>

                    <!-- Stamp Upload Box -->
                    <label class="block border border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors group relative">
                        <input type="file" name="stamp" class="hidden">
                        <div class="font-bold text-slate-800 text-sm">Stamp</div>
                        <div class="text-xs text-slate-500 mt-0.5">( click to update )</div>
                        <div class="mt-3 flex justify-center">
                            @if($company->stampUrl)
                                <div class="relative inline-flex items-center justify-center">
                                    <img src="{{ asset($company->stampUrl) }}" alt="Stamp" class="max-h-24 object-contain">
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-8 h-8 rounded-full bg-white/95 border border-rose-300 text-rose-500 flex items-center justify-center shadow-xs">
                                            <span class="material-icons text-base">photo_camera</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-full bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <span class="material-icons-outlined text-lg">photo_camera</span>
                                </div>
                            @endif
                        </div>
                    </label>

                    <!-- Footer Upload Box -->
                    <label class="block border border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:bg-slate-50 transition-colors group relative">
                        <input type="file" name="footer" class="hidden">
                        <div class="font-bold text-slate-800 text-sm">Footer</div>
                        <div class="text-xs text-slate-500 mt-0.5">( click to update )</div>
                        <div class="mt-3 flex justify-center">
                            @if($company->footer)
                                <div class="relative inline-flex items-center justify-center">
                                    <img src="{{ asset($company->footer) }}" alt="Footer" class="max-h-8 object-contain">
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-8 h-8 rounded-full bg-white/95 border border-rose-300 text-rose-500 flex items-center justify-center shadow-xs">
                                            <span class="material-icons text-base">photo_camera</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-full bg-rose-50 border border-rose-200 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <span class="material-icons-outlined text-lg">photo_camera</span>
                                </div>
                            @endif
                        </div>
                    </label>
                </div>

                <!-- Activities Timeline Section (Matching Image 4) -->
                @if($company->exists)
                    <div class="mt-10 pt-6 border-t border-slate-200">
                        <div class="flex items-center gap-2 mb-6">
                            <span class="material-icons-outlined text-slate-700 text-xl">assignment</span>
                            <h3 class="font-bold text-slate-900 text-base">Activities</h3>
                        </div>

                        <!-- Timeline with vertical connecting line -->
                        <div class="relative pl-2 max-h-96 overflow-y-auto pr-3 custom-scrollbar">
                            <!-- Connecting Line -->
                            <div class="absolute left-[134px] top-4 bottom-8 w-0.5 bg-slate-200"></div>

                            <div class="space-y-6">
                                @php
                                    $activities = [
                                        ['date' => '09 Dec 25', 'time' => '16:26:32', 'name' => 'Roger', 'avatar' => '/assets/images/avatars/roger.jpg', 'action' => 'UPDATED'],
                                        ['date' => '09 Dec 25', 'time' => '16:25:43', 'name' => 'Roger', 'avatar' => '/assets/images/avatars/roger.jpg', 'action' => 'UPDATED'],
                                        ['date' => '09 Dec 25', 'time' => '16:20:13', 'name' => 'Roger', 'avatar' => '/assets/images/avatars/roger.jpg', 'action' => 'UPDATED'],
                                        ['date' => '22 May 25', 'time' => '14:48:01', 'name' => 'Hasseb', 'avatar' => null, 'action' => 'UPDATED'],
                                        ['date' => '26 Nov 24', 'time' => '22:17:35', 'name' => '', 'avatar' => null, 'action' => 'UPDATED'],
                                    ];
                                @endphp

                                @foreach($activities as $act)
                                    <div class="flex items-start gap-6 relative">
                                        <!-- Left: Date & Time -->
                                        <div class="w-24 text-right shrink-0 pt-0.5">
                                            <div class="text-xs font-mono text-slate-500 leading-tight">{{ $act['date'] }}</div>
                                            <div class="text-xs font-mono text-slate-500 leading-tight">{{ $act['time'] }}</div>
                                        </div>

                                        <!-- Middle: Avatar & Name on Timeline -->
                                        <div class="flex flex-col items-center shrink-0 w-10 relative z-10">
                                            @if(!empty($act['avatar']))
                                                <img src="{{ asset($act['avatar']) }}" alt="{{ $act['name'] }}" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-xs">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-slate-300 text-slate-500 flex items-center justify-center border-2 border-white shadow-xs">
                                                    <span class="material-icons text-base">person</span>
                                                </div>
                                            @endif
                                            @if(!empty($act['name']))
                                                <span class="text-[11px] font-semibold text-slate-700 mt-1 leading-none">{{ $act['name'] }}</span>
                                            @endif
                                        </div>

                                        <!-- Right: Action Badge -->
                                        <div class="pt-2">
                                            <span class="text-xs font-bold text-slate-900 tracking-wider">{{ $act['action'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 2: ADDRESS (Matching Image 5) -->
            <div x-show="activeTab === 'address'" class="space-y-6" style="display: none;">
                <!-- Field 1: Address Name with Location Pin -->
                <div class="relative">
                    <div class="relative">
                        <span class="material-icons text-slate-500 text-lg absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">location_on</span>
                        <input
                            type="text"
                            name="addressName"
                            id="addressName"
                            value="{{ old('addressName', $company->address->addressName ?? '') }}"
                            class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="Address Name"
                        />
                    </div>
                    <label
                        for="addressName"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Address Name
                    </label>
                </div>

                <!-- Field 2: Address Line 1 -->
                <div class="relative">
                    <input
                        type="text"
                        name="addressLine1"
                        id="addressLine1"
                        value="{{ old('addressLine1', $company->address->addressLine1 ?? '') }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="Address Line 1"
                    />
                    <label
                        for="addressLine1"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Address Line 1
                    </label>
                </div>

                <!-- Row 3: Address Line 2 (left) + State (right) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-7 relative">
                        <input
                            type="text"
                            name="addressLine2"
                            id="addressLine2"
                            value="{{ old('addressLine2', $company->address->addressLine2 ?? '') }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="Address Line 2"
                        />
                        <label
                            for="addressLine2"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Address Line 2
                        </label>
                    </div>

                    <div class="md:col-span-5 relative">
                        <input
                            type="text"
                            name="state"
                            id="state"
                            value="{{ old('state', $company->address->state ?? '') }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="State"
                        />
                        <label
                            for="state"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            State
                        </label>
                    </div>
                </div>

                <!-- Row 4: City + Postal Code + Postal Box -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input
                            type="text"
                            name="city"
                            id="city"
                            value="{{ old('city', $company->address->city ?? '') }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="City"
                        />
                        <label
                            for="city"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            City
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            type="text"
                            name="postalCode"
                            id="postalCode"
                            value="{{ old('postalCode', $company->address->postalCode ?? '') }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="Postal Code"
                        />
                        <label
                            for="postalCode"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Postal Code
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            type="text"
                            name="postalBox"
                            id="postalBox"
                            value="{{ old('postalBox', $company->address->postalBox ?? '') }}"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                            placeholder="Postal Box"
                        />
                        <label
                            for="postalBox"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Postal Box
                        </label>
                    </div>
                </div>

                <!-- Row 5: For google maps search -->
                <div class="relative">
                    <input
                        type="text"
                        name="forGoogleMapsSearch"
                        id="forGoogleMapsSearch"
                        value="{{ old('forGoogleMapsSearch', $company->address->forGoogleMapsSearch ?? '') }}"
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                        placeholder="For google maps search"
                    />
                    <label
                        for="forGoogleMapsSearch"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        For google maps search
                    </label>
                </div>
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
                                    :id="'ba_desc_' + index"
                                    x-model="account.description"
                                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                    placeholder="Description"
                                />
                                <label
                                    :for="'ba_desc_' + index"
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
                                        :id="'ba_name_' + index"
                                        x-model="account.name"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Bank Name"
                                    />
                                </div>
                                <label
                                    :for="'ba_name_' + index"
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
                                        :id="'ba_sort_' + index"
                                        x-model="account.sortCode"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Sort Code"
                                    />
                                </div>
                                <label
                                    :for="'ba_sort_' + index"
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
                                        :id="'ba_acc_' + index"
                                        x-model="account.accountNumber"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="Account Number"
                                    />
                                </div>
                                <label
                                    :for="'ba_acc_' + index"
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
                                        :id="'ba_iban_' + index"
                                        x-model="account.iban"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="IBAN"
                                    />
                                </div>
                                <label
                                    :for="'ba_iban_' + index"
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
                                        :id="'ba_swift_' + index"
                                        x-model="account.swift"
                                        class="peer w-full pl-9 pr-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                                        placeholder="SWIFT"
                                    />
                                </div>
                                <label
                                    :for="'ba_swift_' + index"
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
