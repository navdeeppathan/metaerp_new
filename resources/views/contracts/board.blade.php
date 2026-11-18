@extends('layouts.app')

@section('title', 'Contracts Pipeline')

@section('content')
<div class="space-y-4" x-data="kanbanBoard()">
    <!-- Header with Search & Add a Contract -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="sr-only">Contract Pipeline</span>
        </div>

        <!-- Search Bar and Add a Contract Button -->
        <div class="flex items-center gap-3">
            <div class="relative w-64">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" 
                       x-model="searchQuery"
                       placeholder="Search by code or contact" 
                       class="w-full bg-white border border-slate-200 rounded-full pl-9 pr-4 py-1.5 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-2xs transition">
            </div>

            <button @click="showNewModal = true" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-1.5 rounded-full font-semibold text-xs shadow-2xs transition whitespace-nowrap">
                Add a contract
            </button>
        </div>
    </div>

    <!-- Toast Notification for Drag & Drop -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-2 rounded-lg shadow-lg text-xs flex items-center gap-2"
         style="display: none;">
        <span class="material-icons text-sm text-emerald-400">check_circle</span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Kanban Stages Horizontal Scroll Container -->
    <div class="flex gap-4 overflow-x-auto pb-6 pt-1 items-start min-h-[calc(100vh-220px)] select-none">
        @forelse($stages as $stage)
        @php
            $stageContracts = $contracts->where('stage_id', $stage->id);
        @endphp
        <div class="shrink-0 w-72 bg-[#f8fafc] rounded-xl p-3 flex flex-col max-h-[calc(100vh-230px)] transition-all duration-150 border border-transparent"
             :class="dropTargetStageId == {{ $stage->id }} ? 'border-2 border-dashed !border-indigo-500 bg-indigo-50/50' : 'border-slate-200/60'"
             @dragover.prevent="onDragOver($event, {{ $stage->id }})"
             @dragleave="onDragLeave($event, {{ $stage->id }})"
             @drop.prevent="onDrop($event, {{ $stage->id }})">
            
            <!-- Stage Header (Matching Screenshot 3 & 4) -->
            <div class="flex items-center justify-between pb-3 mb-2 px-1">
                <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider truncate" title="{{ $stage->description ?? $stage->name }}">
                    {{ $stage->description ?? $stage->name }}
                </h3>
                <span id="badge-stage-{{ $stage->id }}" class="w-5 h-5 rounded-full bg-slate-200/80 text-slate-600 text-[11px] font-semibold flex items-center justify-center">
                    {{ $stageContracts->count() }}
                </span>
            </div>

            <!-- Stage Cards Scrollable Column -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-0.5 min-h-[140px]" id="column-stage-{{ $stage->id }}">
                @forelse($stageContracts as $contract)
                @php
                    $companyName = $contract->type == 'BUY' 
                        ? ($contract->purchase?->metaCompany?->name ?? 'MetaZn LTD') 
                        : ($contract->sale?->metaCompany?->name ?? 'Metaenterprices LTD');
                    
                    $payColor = 'text-[#D32F2F]';
                    $bPay = $contract->purchase?->paymentStatus ?? $contract->buyerContract?->paymentStatus ?? null;
                    $sPay = $contract->sale?->paymentStatus ?? $contract->sellerContract?->paymentStatus ?? null;
                    if ($bPay == 'PAID' && $sPay == 'PAID') {
                        $payColor = 'text-[#388E3C]';
                    } elseif ($bPay == 'PAID' || $sPay == 'PAID') {
                        $payColor = 'text-[#FBC02D]';
                    }
                @endphp
                <div class="bg-white p-3.5 rounded-xl {{ $contract->type == 'SELL' ? 'border-2 border-[#F57C00]' : ($contract->type == 'BUY' ? 'border-2 border-[#81C784]' : 'border border-slate-200/80') }} shadow-2xs hover:shadow-md transition-all cursor-pointer space-y-2.5 relative group"
                     draggable="true"
                     id="card-contract-{{ $contract->id }}"
                     data-contract-id="{{ $contract->id }}"
                     data-stage-id="{{ $stage->id }}"
                     data-order-code="{{ strtolower($contract->orderCode ?? '') }}"
                     data-company="{{ strtolower($companyName) }}"
                     x-show="matchesSearch('{{ strtolower($contract->orderCode ?? '') }}', '{{ strtolower($companyName) }}')"
                     @click="openCardModal({{ $contract->id }})"
                     @dragstart="onDragStart($event, {{ $contract->id }}, {{ $stage->id }})"
                     @dragend="onDragEnd($event)">
                    
                    <!-- Top Pill Tag & Arrow (Matching React BoardCard.js & Tailwind theme) -->
                    <div class="flex items-center justify-between">
                        @if($contract->type == 'SELL')
                            <span class="bg-[#F57C00] text-slate-950 font-semibold text-xs px-3.5 py-0.5 rounded-full select-none">
                                {{ $contract->orderCode }}
                            </span>
                            <div class="flex items-center text-slate-800" title="Sell">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </div>
                        @elseif($contract->type == 'BUY')
                            <span class="bg-[#81C784] text-slate-950 font-semibold text-xs px-3.5 py-0.5 rounded-full select-none">
                                {{ $contract->orderCode }}
                            </span>
                            <div class="flex items-center text-slate-800" title="Buy">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 4.5-15 15m0 0h11.25m-11.25 0V8.25" />
                                </svg>
                            </div>
                        @else
                            <span class="bg-[#f1f5f9] text-slate-800 font-semibold text-xs px-3.5 py-0.5 rounded-full border border-slate-200 select-none">
                                {{ $contract->orderCode }}
                            </span>
                            <div class="flex items-center text-slate-800 -space-x-1" title="Buy & Sell">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 4.5-15 15m0 0h11.25m-11.25 0V8.25" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Status & Company (Matching Screenshot 3 & 4) -->
                    <div class="space-y-0.5 text-xs text-slate-800 leading-tight">
                        <div>
                            Status:<b>{{ strtoupper($contract->status ?? 'OPEN') }}</b>
                        </div>
                        <div class="truncate">
                            Company: <b>{{ $companyName }}</b>
                        </div>
                    </div>

                    <!-- Contracts of purchase box for SELL contracts (Matching React & Screenshot 3 & 4) -->
                    @if($contract->type == 'SELL')
                    @php
                        $purchaseCodes = $contract->purchaseContractCodes ?? collect();
                        $firstPurchaseCode = $purchaseCodes->first();
                    @endphp
                    <div class="bg-[#C8E6C9] text-slate-900 rounded-lg px-2.5 py-1.5 text-xs">
                        <div class="text-[11px] font-normal leading-tight">Contracts of purchase:</div>
                        @if($firstPurchaseCode)
                        <div class="font-bold text-slate-900 mt-0.5 leading-tight">{{ $firstPurchaseCode }}</div>
                        @endif
                    </div>

                    <!-- Products Status Tag for SELL contracts (Matching React & Screenshot 3 & 4) -->
                    @php
                        $assocStatus = $contract->associationStatus;
                    @endphp
                    <div>
                        @if($assocStatus == 'COMPLETE')
                        <span class="bg-[#4CAF50] text-slate-950 text-[11px] font-semibold px-3.5 py-0.5 rounded-full inline-block">
                            Products Status: COMPLETE
                        </span>
                        @else
                        <span class="bg-[#FFA726] text-slate-950 text-[11px] font-semibold px-3.5 py-0.5 rounded-full inline-block">
                            Products Status: PENDING
                        </span>
                        @endif
                    </div>
                    @endif

                    <!-- Card Footer: Heroicons Currency Dollar Icon (Matching React FuseSvgIcon) -->
                    <div class="pt-0.5 flex items-center justify-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5 {{ $payColor }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @empty
                <div class="h-24 border border-dashed border-slate-200/80 rounded-xl flex flex-col items-center justify-center text-slate-400 text-xs empty-placeholder">
                    No contracts
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="w-full py-12 text-center text-slate-500 bg-white rounded-xl border border-slate-200">
            <span class="material-icons text-4xl text-slate-300 mb-2">dashboard_customize</span>
            <p>No workflow stages configured for this model.</p>
        </div>
        @endforelse
    </div>

    <!-- NEW CONTRACT MODAL (Matching exact screenshots) -->
    <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @keydown.escape.window="showNewModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="showNewModal = false"></div>
            
            <div class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-slate-200">
                
                <form action="{{ route('contracts.store') }}" method="POST">
                    @csrf
                    
                    <div class="flex">
                        <!-- Left Main Modal Content Area (Matching Images 1, 2, 3) -->
                        <div class="flex-1 p-6 sm:p-8 space-y-6 max-h-[88vh] overflow-y-auto custom-scrollbar">
                            
                            <!-- Modal Header: New Contract > and Order date & Save Button (Screenshot 1) -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 flex items-center gap-1">
                                    <span>New Contract</span>
                                    <span class="material-icons text-sm text-slate-400">chevron_right</span>
                                </h2>

                                <div class="flex items-center gap-4">
                                    <!-- Order date notch container -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-w-[220px] flex items-center justify-between">
                                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order date</label>
                                        <input type="text" name="order_date" value="{{ now()->format('d/m/Y h:i A') }}" class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                        <span class="material-icons text-slate-400 text-lg ml-2 pointer-events-none">calendar_today</span>
                                    </div>

                                    <button type="submit" class="bg-[#7c3aed] hover:bg-[#6d28d9] text-white px-6 py-2 rounded-full text-xs font-semibold shadow-xs transition cursor-pointer select-none">
                                        Save
                                    </button>
                                </div>
                            </div>

                            <!-- Field 1: Type (Screenshot 1) -->
                            <div>
                                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center">
                                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Type</label>
                                    <select name="type" x-model="contractType" 
                                            @change="if(contractType === 'SELL') sideTab = 'selling'; else if(contractType === 'BUY') sideTab = 'buying';"
                                            class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none py-1 cursor-pointer">
                                        <option value="BUY_AND_SELL" selected>Purchase & Sales</option>
                                        <option value="BUY">Purchase</option>
                                        <option value="SELL">Sales</option>
                                    </select>
                                    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
                                </div>
                            </div>

                            <!-- Field 2: Description (Screenshot 1) -->
                            <div>
                                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-2 bg-white">
                                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Description</label>
                                    <textarea name="description" rows="3" class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none p-0 pt-1 resize-y leading-relaxed" placeholder="Description"></textarea>
                                </div>
                            </div>

                            <!-- Tabs: Buying from / Selling to (Screenshot 1 & 3) -->
                            <div class="border-t-2 border-slate-200/80 pt-4">
                                <div class="flex items-center gap-8 border-b border-slate-200 text-xs font-semibold">
                                    <button type="button" 
                                            @click="if(contractType !== 'SELL') sideTab = 'buying'"
                                            :class="sideTab === 'buying' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'SELL' ? 'text-slate-300 cursor-not-allowed pb-2.5 pointer-events-none' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                                        Buying from
                                    </button>
                                    <button type="button" 
                                            @click="if(contractType !== 'BUY') sideTab = 'selling'"
                                            :class="sideTab === 'selling' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'BUY' ? 'text-slate-300 cursor-not-allowed pb-2.5 pointer-events-none' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                                        Selling to
                                    </button>
                                </div>

                                <!-- BUYING FROM TAB CONTENT (Screenshots 1 & 2) -->
                                <div x-show="sideTab === 'buying'" class="pt-4 space-y-4">
                                    <!-- Row 1: Meta Company, Payment bank account, Payment Date, Country of origin -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Meta Company * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company *</label>
                                            <select name="buying_meta_company_id" x-model="buyingCompanyId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($companies as $comp)
                                                <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment bank account * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment bank account *</label>
                                            <select name="buying_payment_bank_account_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="ba in getBuyingBankAccounts()" :key="ba.id">
                                                    <option :value="ba.id" x-text="ba.description || ba.name || 'Bank Account'"></option>
                                                </template>
                                                <template x-if="getBuyingBankAccounts().length === 0">
                                                    <option value="">No bank account available</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Date -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                                            <input type="text" name="buying_payment_date" placeholder="DD/MM/YYYY" class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 placeholder-slate-400">
                                            <span class="material-icons text-slate-400 text-base pointer-events-none">calendar_today</span>
                                        </div>

                                        <!-- Country of origin * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Country of origin *</label>
                                            <select name="buying_country_of_origin_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($countries as $cntry)
                                                <option value="{{ $cntry->id }}">{{ $cntry->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Name, Contact Address, Contact Person, Collection address (Screenshot 1) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Name * (Contact) -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Name *</label>
                                            <select name="buying_contact_id" x-model="buyingContactId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                @foreach($contacts as $cont)
                                                <option value="{{ $cont->id }}">{{ $cont->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Contact Address * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address *</label>
                                            <select name="buying_contact_address_id" x-model="buyingAddressId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="addr in getBuyingAddresses()" :key="addr.id">
                                                    <option :value="addr.id" x-text="addr.address_name || addr.address_line1 || 'Address'"></option>
                                                </template>
                                                <template x-if="getBuyingAddresses().length === 0">
                                                    <option value="">No address available</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Contact Person * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person *</label>
                                            <select name="buying_contact_person_id" x-model="buyingRepId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="rep in getBuyingRepresentatives()" :key="rep.id">
                                                    <option :value="rep.id" x-text="rep.name"></option>
                                                </template>
                                                <template x-if="getBuyingRepresentatives().length === 0">
                                                    <option value="">No contact person</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Collection address (Screenshot 1: labeled Collection address) -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Collection address</label>
                                            <select name="buying_collection_address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="addr in getBuyingAddresses()" :key="addr.id">
                                                    <option :value="addr.id" x-text="addr.address_name || addr.address_line1 || 'Collection Address'"></option>
                                                </template>
                                                <template x-if="getBuyingAddresses().length === 0">
                                                    <option value="">Select address</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>
                                    </div>

                                    <!-- Row 3: Currency, Dollar Conversion, Incoterms, Customer Order Number (Screenshot 1) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Currency * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Currency *</label>
                                            <select name="buying_currency_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($currencies as $cur)
                                                <option value="{{ $cur->id }}">{{ $cur->description ?? 'USD' }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Dollar Conversion -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                                            <input type="text" name="buying_dollar_conversion" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Incoterms * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms *</label>
                                            <input type="text" name="buying_incoterms" value="CIF JEDDAH" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Customer Order Number -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Customer Order Number</label>
                                            <input type="text" name="buying_customer_order_number" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Row 4: Payment type, Payment Terms type, Payment Terms, Advanced Payment (Screenshot 1) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Payment type * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment type *</label>
                                            <select name="buying_payment_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($paymentTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->description }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Terms type * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms type *</label>
                                            <select name="buying_payment_terms_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($paymentTerms as $ptt)
                                                <option value="{{ $ptt->id }}">{{ $ptt->description }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Terms * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms *</label>
                                            <input type="text" name="buying_payment_terms" value="100%" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Advanced Payment -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced Payment</label>
                                            <input type="text" name="buying_advanced_payment" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Row 5: Notes (Screenshot 2) -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                                            <input type="text" name="buying_notes" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Products Section (Screenshot 2) -->
                                    <div class="pt-4 border-t border-slate-100">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="material-icons-outlined text-slate-700 text-base">table_chart</span>
                                            <h4 class="font-bold text-xs text-slate-900">Products</h4>
                                        </div>

                                        <div class="border border-slate-200 rounded-lg overflow-x-auto">
                                            <table class="w-full text-xs text-left">
                                                <thead class="text-slate-700 font-semibold border-b border-slate-200 bg-slate-50/50">
                                                    <tr>
                                                        <th class="py-2.5 px-3">Item</th>
                                                        <th class="py-2.5 px-3">Qty</th>
                                                        <th class="py-2.5 px-3">UOM</th>
                                                        <th class="py-2.5 px-3">U. Price</th>
                                                        <th class="py-2.5 px-3">Total</th>
                                                        <th class="py-2.5 px-3">Pricing type</th>
                                                        <th class="py-2.5 px-2"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                                    <template x-for="(prod, idx) in newBuyingProducts" :key="idx">
                                                        <tr>
                                                            <td class="py-2 px-3">
                                                                <input type="hidden" :name="'buying_products[' + idx + '][product_id]'" :value="prod.product_id">
                                                                <select x-model="prod.product_id" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                                    @foreach($products as $p)
                                                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="number" step="0.01" :name="'buying_products[' + idx + '][quantity]'" x-model.number="prod.quantity" class="text-xs border border-slate-200 rounded p-1 w-20 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="text" :name="'buying_products[' + idx + '][uom]'" x-model="prod.uom" class="text-xs border border-slate-200 rounded p-1 w-16 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="number" step="0.01" :name="'buying_products[' + idx + '][unit_price]'" x-model.number="prod.unitPrice" class="text-xs border border-slate-200 rounded p-1 w-24 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3 font-semibold text-slate-800" x-text="(prod.quantity * prod.unitPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                                            <td class="py-2 px-3">
                                                                <input type="hidden" :name="'buying_products[' + idx + '][pricing_type]'" :value="prod.pricing_type">
                                                                <select x-model="prod.pricing_type" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                                    <option value="Fixed">Fixed</option>
                                                                    <option value="Formula">Formula</option>
                                                                </select>
                                                            </td>
                                                            <td class="py-2 px-2 text-right">
                                                                <button type="button" @click="removeBuyingProduct(idx)" x-show="newBuyingProducts.length > 1" class="text-slate-400 hover:text-rose-600">
                                                                    <span class="material-icons text-sm">delete</span>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot class="border-t border-slate-200 bg-slate-50/70 font-semibold text-xs text-slate-800">
                                                    <tr>
                                                        <td class="py-2.5 px-3">Total Qty: <span x-text="calcBuyingTotalQty()"></span></td>
                                                        <td colspan="3"></td>
                                                        <td class="py-2.5 px-3">Total: <span x-text="calcBuyingTotalPrice()"></span></td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <button type="button" @click="addBuyingProduct()" class="mt-3 bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-1.5 rounded-full font-medium text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                            Add Product
                                        </button>
                                    </div>
                                </div>

                                <!-- SELLING TO TAB CONTENT (Screenshot 3) -->
                                <div x-show="sideTab === 'selling'" class="pt-4 space-y-4">
                                    <!-- Row 1: Meta Company, Payment bank account, Payment Date, Country of origin -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Meta Company * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company *</label>
                                            <select name="selling_meta_company_id" x-model="sellingCompanyId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($companies as $comp)
                                                <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment bank account * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment bank account *</label>
                                            <select name="selling_payment_bank_account_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="ba in getSellingBankAccounts()" :key="ba.id">
                                                    <option :value="ba.id" x-text="ba.description || ba.name || 'Bank Account'"></option>
                                                </template>
                                                <template x-if="getSellingBankAccounts().length === 0">
                                                    <option value="">No bank account available</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Date -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                                            <input type="text" name="selling_payment_date" placeholder="DD/MM/YYYY" class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 placeholder-slate-400">
                                            <span class="material-icons text-slate-400 text-base pointer-events-none">calendar_today</span>
                                        </div>

                                        <!-- Country of origin * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Country of origin *</label>
                                            <select name="selling_country_of_origin_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($countries as $cntry)
                                                <option value="{{ $cntry->id }}">{{ $cntry->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Name, Contact Address, Contact Person, Shipping Address (Screenshot 3: labeled Shipping Address) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Name * (Contact) -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Name *</label>
                                            <select name="selling_contact_id" x-model="sellingContactId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                @foreach($contacts as $cont)
                                                <option value="{{ $cont->id }}">{{ $cont->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Contact Address * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address *</label>
                                            <select name="selling_contact_address_id" x-model="sellingAddressId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="addr in getSellingAddresses()" :key="addr.id">
                                                    <option :value="addr.id" x-text="addr.address_name || addr.address_line1 || 'Address'"></option>
                                                </template>
                                                <template x-if="getSellingAddresses().length === 0">
                                                    <option value="">No address available</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Contact Person * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person *</label>
                                            <select name="selling_contact_person_id" x-model="sellingRepId" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="rep in getSellingRepresentatives()" :key="rep.id">
                                                    <option :value="rep.id" x-text="rep.name"></option>
                                                </template>
                                                <template x-if="getSellingRepresentatives().length === 0">
                                                    <option value="">No contact person</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Shipping Address (Screenshot 3: labeled Shipping Address) -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Shipping Address</label>
                                            <select name="selling_shipping_address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                                                <template x-for="addr in getSellingAddresses()" :key="addr.id">
                                                    <option :value="addr.id" x-text="addr.address_name || addr.address_line1 || 'Shipping Address'"></option>
                                                </template>
                                                <template x-if="getSellingAddresses().length === 0">
                                                    <option value="">Select address</option>
                                                </template>
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>
                                    </div>

                                    <!-- Row 3: Currency, Dollar Conversion, Incoterms, Customer Order Number -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Currency * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Currency *</label>
                                            <select name="selling_currency_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($currencies as $cur)
                                                <option value="{{ $cur->id }}">{{ $cur->description ?? 'USD' }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Dollar Conversion -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                                            <input type="text" name="selling_dollar_conversion" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Incoterms * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms *</label>
                                            <input type="text" name="selling_incoterms" value="CIF JEDDAH" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Customer Order Number -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Customer Order Number</label>
                                            <input type="text" name="selling_customer_order_number" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Row 4: Payment type, Payment Terms type, Payment Terms, Advanced Payment -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <!-- Payment type * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment type *</label>
                                            <select name="selling_payment_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($paymentTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->description }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Terms type * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms type *</label>
                                            <select name="selling_payment_terms_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                @foreach($paymentTerms as $ptt)
                                                <option value="{{ $ptt->id }}">{{ $ptt->description }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                        </div>

                                        <!-- Payment Terms * -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms *</label>
                                            <input type="text" name="selling_payment_terms" value="100%" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>

                                        <!-- Advanced Payment -->
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced Payment</label>
                                            <input type="text" name="selling_advanced_payment" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Row 5: Notes -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                                            <input type="text" name="selling_notes" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Products Section (Screenshot 3) -->
                                    <div class="pt-4 border-t border-slate-100">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="material-icons-outlined text-slate-700 text-base">table_chart</span>
                                            <h4 class="font-bold text-xs text-slate-900">Products</h4>
                                        </div>

                                        <div class="border border-slate-200 rounded-lg overflow-x-auto">
                                            <table class="w-full text-xs text-left">
                                                <thead class="text-slate-700 font-semibold border-b border-slate-200 bg-slate-50/50">
                                                    <tr>
                                                        <th class="py-2.5 px-3">Item</th>
                                                        <th class="py-2.5 px-3">Qty</th>
                                                        <th class="py-2.5 px-3">UOM</th>
                                                        <th class="py-2.5 px-3">U. Price</th>
                                                        <th class="py-2.5 px-3">Total</th>
                                                        <th class="py-2.5 px-3">Pricing type</th>
                                                        <th class="py-2.5 px-2"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                                    <template x-for="(prod, idx) in newSellingProducts" :key="idx">
                                                        <tr>
                                                            <td class="py-2 px-3">
                                                                <input type="hidden" :name="'selling_products[' + idx + '][product_id]'" :value="prod.product_id">
                                                                <select x-model="prod.product_id" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                                    @foreach($products as $p)
                                                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="number" step="0.01" :name="'selling_products[' + idx + '][quantity]'" x-model.number="prod.quantity" class="text-xs border border-slate-200 rounded p-1 w-20 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="text" :name="'selling_products[' + idx + '][uom]'" x-model="prod.uom" class="text-xs border border-slate-200 rounded p-1 w-16 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3">
                                                                <input type="number" step="0.01" :name="'selling_products[' + idx + '][unit_price]'" x-model.number="prod.unitPrice" class="text-xs border border-slate-200 rounded p-1 w-24 bg-white">
                                                            </td>
                                                            <td class="py-2 px-3 font-semibold text-slate-800" x-text="(prod.quantity * prod.unitPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                                            <td class="py-2 px-3">
                                                                <input type="hidden" :name="'selling_products[' + idx + '][pricing_type]'" :value="prod.pricing_type">
                                                                <select x-model="prod.pricing_type" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                                    <option value="Fixed">Fixed</option>
                                                                    <option value="Formula">Formula</option>
                                                                </select>
                                                            </td>
                                                            <td class="py-2 px-2 text-right">
                                                                <button type="button" @click="removeSellingProduct(idx)" x-show="newSellingProducts.length > 1" class="text-slate-400 hover:text-rose-600">
                                                                    <span class="material-icons text-sm">delete</span>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot class="border-t border-slate-200 bg-slate-50/70 font-semibold text-xs text-slate-800">
                                                    <tr>
                                                        <td class="py-2.5 px-3">Total Qty: <span x-text="calcSellingTotalQty()"></span></td>
                                                        <td colspan="3"></td>
                                                        <td class="py-2.5 px-3">Total: <span x-text="calcSellingTotalPrice()"></span></td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <button type="button" @click="addSellingProduct()" class="mt-3 bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-1.5 rounded-full font-medium text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                            Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Transport Section (Screenshot 2) -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="material-icons-outlined text-slate-700 text-base">local_shipping</span>
                                    <h4 class="font-bold text-xs text-slate-900">Transport</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of transport</label>
                                        <select name="mode_of_transport" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                            <option value="SEA" selected>SEA</option>
                                            <option value="ROAD">ROAD</option>
                                            <option value="SEA/ROAD">SEA/ROAD</option>
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Weight Tolerance (%)</label>
                                        <input type="number" step="0.01" name="weight_tolerance" placeholder="e.g. 5.0" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                    </div>
                                </div>
                            </div>

                            <!-- Comment Section (Screenshot 2) -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="material-icons-outlined text-slate-700 text-base">chat_bubble_outline</span>
                                    <h4 class="font-bold text-xs text-slate-900">Comment</h4>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                                        <span class="material-icons text-base">person</span>
                                    </div>
                                    <input type="text" x-model="newComment" placeholder="Add comment" class="flex-1 text-xs border border-slate-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:border-indigo-500">
                                </div>
                                <button type="button" @click="if (newComment.trim()) { commentsList.unshift({ text: newComment.trim(), date: 'Just now' }); newComment = ''; }" class="mt-2 ml-11 bg-slate-200 hover:bg-slate-300 text-slate-600 px-4 py-1 rounded-full text-xs font-medium transition cursor-pointer">
                                    Add
                                </button>

                                <template x-for="(c, cIdx) in commentsList" :key="cIdx">
                                    <div class="ml-11 mt-2 text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-slate-700 flex justify-between items-center">
                                        <span x-text="c.text"></span>
                                        <span class="text-slate-400 text-[10px]" x-text="c.date"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Activities Section (Screenshot 2) -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="material-icons-outlined text-slate-700 text-base">content_paste</span>
                                    <h4 class="font-bold text-xs text-slate-900">Activities</h4>
                                </div>
                                <div class="relative pl-36 space-y-4 before:absolute before:left-[178px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                    <div class="relative flex items-center gap-3">
                                        <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">{{ now()->format('d M y H:i:s') }}</span>
                                        <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">R</div>
                                        <span class="text-xs font-medium text-slate-800">{{ auth()->user()->name ?? 'Root' }}</span>
                                        <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">NEW_CONTRACT</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Submit Footer -->
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                                <button type="button" @click="showNewModal = false" class="px-5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-full transition cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-semibold rounded-full shadow-xs transition cursor-pointer">
                                    Save Contract
                                </button>
                            </div>
                        </div>

                        <!-- Right Sidebar Toolbar (Screenshot 1, 2, 3) -->
                        <div class="w-12 bg-slate-50/80 border-l border-slate-200 flex flex-col items-center py-4 gap-6 text-slate-400 shrink-0 select-none">
                            <button type="button" @click="showNewModal = false" class="hover:text-slate-600 transition cursor-pointer" title="Close">
                                <span class="material-icons text-lg">close</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Calendar">
                                <span class="material-icons text-base">calendar_today</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Labels">
                                <span class="material-icons text-base">label</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Members">
                                <span class="material-icons text-base">person</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Attachments">
                                <span class="material-icons text-base">attach_file</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Checklists">
                                <span class="material-icons text-base">check_circle</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition cursor-pointer" title="Options">
                                <span class="material-icons text-base">more_horiz</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CARD DETAIL POPUP MODAL (Matching exact screenshots 2, 3, 4) -->
    <div x-show="showCardModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @keydown.escape.window="showCardModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="showCardModal = false"></div>
            
            <div class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-slate-200">
                <!-- Modal Loading State (Matching FuseLoading in React) -->
                <div x-show="modalLoading" class="p-16 flex flex-col items-center justify-center min-h-[350px]">
                    <div class="text-base sm:text-lg font-medium text-slate-500 select-none -mb-1">Loading</div>
                    <div class="fuse-spinner mt-4">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                </div>
                
                <!-- Dynamic Modal Content Injected via AJAX -->
                <div id="cardModalContent" x-show="!modalLoading"></div>
            </div>
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        showNewModal: false,
        showCardModal: false,
        modalLoading: false,
        activeContractId: null,
        searchQuery: '',
        draggingContractId: null,
        draggingSourceStageId: null,
        dropTargetStageId: null,

        openCardModal(id) {
            this.activeContractId = id;
            this.showCardModal = true;
            this.modalLoading = true;
            const content = document.getElementById('cardModalContent');
            if (content) content.innerHTML = '';
            
            fetch(`/contracts/${id}/modal`)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to load contract modal');
                    return res.text();
                })
                .then(html => {
                    if (content) {
                        content.innerHTML = html;
                        if (window.Alpine) {
                            Alpine.initTree(content);
                        }
                    }
                    this.modalLoading = false;
                })
                .catch(err => {
                    console.error('Failed to load contract modal:', err);
                    this.modalLoading = false;
                });
        },
        contractType: 'BUY_AND_SELL',
        sideTab: 'buying',
        buyingCompanyId: '{{ $companies->first()?->id ?? 1 }}',
        sellingCompanyId: '{{ $companies->first()?->id ?? 1 }}',
        buyingContactId: '{{ $contacts->first()?->id ?? 1 }}',
        sellingContactId: '{{ $contacts->first()?->id ?? 1 }}',
        buyingAddressId: '',
        sellingAddressId: '',
        buyingRepId: '',
        sellingRepId: '',
        newComment: '',
        commentsList: [],
        companiesData: @js($companies),
        contactsData: @js($contacts),
        newBuyingProducts: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 0, uom: 'MT', unitPrice: 0, pricing_type: 'Fixed' }
        ],
        newSellingProducts: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 0, uom: 'MT', unitPrice: 0, pricing_type: 'Fixed' }
        ],

        getBuyingBankAccounts() {
            const comp = this.companiesData.find(c => c.id == this.buyingCompanyId);
            return comp && comp.bank_accounts ? comp.bank_accounts : (comp && comp.bankAccounts ? comp.bankAccounts : []);
        },
        getSellingBankAccounts() {
            const comp = this.companiesData.find(c => c.id == this.sellingCompanyId);
            return comp && comp.bank_accounts ? comp.bank_accounts : (comp && comp.bankAccounts ? comp.bankAccounts : []);
        },
        getBuyingAddresses() {
            const cont = this.contactsData.find(c => c.id == this.buyingContactId);
            return cont && cont.addresses ? cont.addresses : [];
        },
        getSellingAddresses() {
            const cont = this.contactsData.find(c => c.id == this.sellingContactId);
            return cont && cont.addresses ? cont.addresses : [];
        },
        getBuyingRepresentatives() {
            const addrs = this.getBuyingAddresses();
            if (this.buyingAddressId) {
                const addr = addrs.find(a => a.id == this.buyingAddressId);
                if (addr && addr.representatives) return addr.representatives;
            }
            return addrs.flatMap(a => a.representatives || []);
        },
        getSellingRepresentatives() {
            const addrs = this.getSellingAddresses();
            if (this.sellingAddressId) {
                const addr = addrs.find(a => a.id == this.sellingAddressId);
                if (addr && addr.representatives) return addr.representatives;
            }
            return addrs.flatMap(a => a.representatives || []);
        },
        addBuyingProduct() {
            this.newBuyingProducts.push({
                product_id: '{{ $products->first()?->id ?? 1 }}',
                quantity: 0,
                uom: 'MT',
                unitPrice: 0,
                pricing_type: 'Fixed'
            });
        },
        removeBuyingProduct(index) {
            if (this.newBuyingProducts.length > 1) {
                this.newBuyingProducts.splice(index, 1);
            }
        },
        calcBuyingTotalQty() {
            const sum = this.newBuyingProducts.reduce((acc, p) => acc + (parseFloat(p.quantity) || 0), 0);
            return sum.toFixed(3);
        },
        calcBuyingTotalPrice() {
            const sum = this.newBuyingProducts.reduce((acc, p) => acc + ((parseFloat(p.quantity) || 0) * (parseFloat(p.unitPrice) || 0)), 0);
            return sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addSellingProduct() {
            this.newSellingProducts.push({
                product_id: '{{ $products->first()?->id ?? 1 }}',
                quantity: 0,
                uom: 'MT',
                unitPrice: 0,
                pricing_type: 'Fixed'
            });
        },
        removeSellingProduct(index) {
            if (this.newSellingProducts.length > 1) {
                this.newSellingProducts.splice(index, 1);
            }
        },
        calcSellingTotalQty() {
            const sum = this.newSellingProducts.reduce((acc, p) => acc + (parseFloat(p.quantity) || 0), 0);
            return sum.toFixed(3);
        },
        calcSellingTotalPrice() {
            const sum = this.newSellingProducts.reduce((acc, p) => acc + ((parseFloat(p.quantity) || 0) * (parseFloat(p.unitPrice) || 0)), 0);
            return sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        toast: {
            show: false,
            message: '',
            timer: null
        },

        showToast(message) {
            if (window.showToast) {
                window.showToast(message, 'success');
            } else {
                this.toast.message = message;
                this.toast.show = true;
                clearTimeout(this.toast.timer);
                this.toast.timer = setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            }
        },

        matchesSearch(code, company) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            return code.includes(q) || company.includes(q);
        },

        onDragStart(event, contractId, stageId) {
            this.draggingContractId = contractId;
            this.draggingSourceStageId = stageId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', JSON.stringify({ contractId, stageId }));
            
            setTimeout(() => {
                if (event.target) {
                    event.target.classList.add('opacity-40', 'scale-95');
                }
            }, 10);
        },

        onDragEnd(event) {
            if (event.target) {
                event.target.classList.remove('opacity-40', 'scale-95');
            }
            this.dropTargetStageId = null;
        },

        onDragOver(event, stageId) {
            this.dropTargetStageId = stageId;
            event.dataTransfer.dropEffect = 'move';
        },

        onDragLeave(event, stageId) {
            if (this.dropTargetStageId === stageId) {
                this.dropTargetStageId = null;
            }
        },

        onDrop(event, targetStageId) {
            this.dropTargetStageId = null;
            const contractId = this.draggingContractId;
            const sourceStageId = this.draggingSourceStageId;

            if (!contractId || sourceStageId === targetStageId) {
                return;
            }

            const cardEl = document.getElementById(`card-contract-${contractId}`);
            const sourceCol = document.getElementById(`column-stage-${sourceStageId}`);
            const targetCol = document.getElementById(`column-stage-${targetStageId}`);
            const sourceBadge = document.getElementById(`badge-stage-${sourceStageId}`);
            const targetBadge = document.getElementById(`badge-stage-${targetStageId}`);

            if (cardEl && targetCol) {
                const targetEmpty = targetCol.querySelector('.empty-placeholder');
                if (targetEmpty) targetEmpty.remove();

                targetCol.prepend(cardEl);
                cardEl.setAttribute('data-stage-id', targetStageId);
                cardEl.classList.remove('opacity-40', 'scale-95');

                if (sourceBadge) {
                    const count = Math.max(0, parseInt(sourceBadge.innerText || '0') - 1);
                    sourceBadge.innerText = count;
                }
                if (targetBadge) {
                    const count = parseInt(targetBadge.innerText || '0') + 1;
                    targetBadge.innerText = count;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch(`/contracts/${contractId}/move-stage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ stage_id: targetStageId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Contract moved successfully');
                    } else {
                        console.warn('Move returned false:', data);
                    }
                })
                .catch(err => {
                    console.error('Failed to move contract stage:', err);
                });
            }

            this.draggingContractId = null;
            this.draggingSourceStageId = null;
        }
    };
}
</script>
@endsection
