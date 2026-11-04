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
            
            <!-- Stage Header -->
            <div class="flex items-center justify-between pb-3 mb-2 px-1">
                <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider truncate" title="{{ $stage->name }}">
                    {{ $stage->name }}
                </h3>
                <span id="badge-stage-{{ $stage->id }}" class="w-5 h-5 rounded-full bg-slate-200/80 text-slate-600 text-[11px] font-semibold flex items-center justify-center">
                    {{ $stageContracts->count() }}
                </span>
            </div>

            <!-- Stage Cards Scrollable Column -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-0.5 min-h-[140px]" id="column-stage-{{ $stage->id }}">
                @forelse($stageContracts as $contract)
                @php
                    $meta = json_decode($contract->remarks ?? '{}', true) ?: [];
                    $purchaseContract = $meta['purchaseContract'] ?? null;
                    $productsStatus = $meta['productsStatus'] ?? null;
                    $remainingBalance = $meta['remainingBalance'] ?? false;
                    $companyName = $contract->type == 'BUY' 
                        ? ($contract->purchase?->metaCompany?->name ?? 'MetaZn LTD') 
                        : ($contract->sale?->metaCompany?->name ?? 'Metaenterprices LTD');
                @endphp
                <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2.5 relative group"
                     draggable="true"
                     id="card-contract-{{ $contract->id }}"
                     data-contract-id="{{ $contract->id }}"
                     data-stage-id="{{ $stage->id }}"
                     data-order-code="{{ strtolower($contract->orderCode ?? '') }}"
                     data-company="{{ strtolower($companyName) }}"
                     x-show="matchesSearch('{{ strtolower($contract->orderCode ?? '') }}', '{{ strtolower($companyName) }}')"
                     @dragstart="onDragStart($event, {{ $contract->id }}, {{ $stage->id }})"
                     @dragend="onDragEnd($event)">
                    
                    <!-- Top Pill Tag & Arrow -->
                    <div class="flex items-center justify-between">
                        @if($contract->type == 'SELL')
                            <span class="bg-[#ea580c] text-white font-bold text-xs px-3 py-0.5 rounded-full">
                                <a href="{{ route('contracts.show', $contract->id) }}" class="hover:underline">{{ $contract->orderCode }}</a>
                            </span>
                            <span class="text-slate-400 font-bold text-sm leading-none" title="Sell">↗</span>
                        @elseif($contract->type == 'BUY')
                            <span class="bg-[#16a34a] text-white font-bold text-xs px-3 py-0.5 rounded-full">
                                <a href="{{ route('contracts.show', $contract->id) }}" class="hover:underline">{{ $contract->orderCode }}</a>
                            </span>
                            <span class="text-slate-400 font-bold text-sm leading-none" title="Buy">↙</span>
                        @else
                            <span class="bg-[#f1f5f9] text-slate-800 font-bold text-xs px-3 py-0.5 rounded-full border border-slate-200">
                                <a href="{{ route('contracts.show', $contract->id) }}" class="hover:underline">{{ $contract->orderCode }}</a>
                            </span>
                            <span class="text-slate-400 font-bold text-xs tracking-tighter leading-none" title="Buy & Sell">↙ ↗</span>
                        @endif
                    </div>

                    <!-- Status & Company -->
                    <div class="space-y-0.5 text-xs">
                        <div class="text-slate-700">
                            Status:<span class="font-bold text-slate-900 ml-0.5">{{ ($contract->contractStatus == 'ACTIVE' || $contract->contractStatus == 'OPEN') ? 'OPEN' : $contract->contractStatus }}</span>
                        </div>
                        <div class="text-slate-700 truncate">
                            Company: <span class="font-semibold text-slate-900">{{ $companyName }}</span>
                        </div>
                    </div>

                    <!-- Contracts of purchase box if applicable -->
                    @if($purchaseContract || $contract->type == 'SELL')
                    <div class="bg-emerald-100/70 text-emerald-900 rounded-lg px-2.5 py-1.5 text-xs">
                        <div class="text-[11px] text-emerald-800">Contracts of purchase:</div>
                        @if($purchaseContract)
                        <div class="font-bold text-emerald-950">{{ $purchaseContract }}</div>
                        @endif
                    </div>
                    @endif

                    <!-- Products Status Tag if applicable -->
                    @if($productsStatus)
                    <div>
                        @if($productsStatus == 'COMPLETE')
                        <span class="bg-emerald-600 text-white text-[10px] font-bold px-3 py-0.5 rounded-full inline-block">
                            Products Status: COMPLETE
                        </span>
                        @else
                        <span class="bg-amber-500 text-white text-[10px] font-bold px-3 py-0.5 rounded-full inline-block">
                            Products Status: PENDING
                        </span>
                        @endif
                    </div>
                    @endif

                    <!-- Remaining Balance if applicable -->
                    @if($remainingBalance)
                    <div class="bg-amber-100/80 text-amber-900 rounded-lg px-2.5 py-1 text-xs font-semibold">
                        Remaining Balance:
                    </div>
                    @endif

                    <!-- Card Footer: Red Dollar Icon -->
                    <div class="pt-1 flex items-center justify-between">
                        <span class="w-4 h-4 rounded-full border border-rose-500 text-rose-500 flex items-center justify-center text-[10px] font-bold leading-none select-none">
                            $
                        </span>

                        <a href="{{ route('contracts.show', $contract->id) }}" class="opacity-0 group-hover:opacity-100 transition text-[11px] text-indigo-600 hover:underline">
                            View details
                        </a>
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
    <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="showNewModal = false"></div>
            
            <div class="inline-block w-full max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-slate-200"
                 x-data="{ sideTab: 'buying', newComment: '', commentsList: [] }">
                
                <form action="{{ route('contracts.store') }}" method="POST">
                    @csrf
                    
                    <div class="flex">
                        <!-- Left Main Modal Content Area -->
                        <div class="flex-1 p-6 space-y-6 max-h-[85vh] overflow-y-auto custom-scrollbar">
                            
                            <!-- Modal Header: New Contract > and Order date -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 flex items-center gap-1">
                                    <span>New Contract</span>
                                    <span class="material-icons text-sm text-slate-400">chevron_right</span>
                                </h2>

                                <!-- Order date notch container -->
                                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white flex items-center gap-2">
                                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Order date</label>
                                    <input type="text" name="order_date" value="04/11/2026 09:24 AM" class="text-xs text-slate-800 border-0 p-0 focus:ring-0 w-36 bg-transparent">
                                    <span class="material-icons text-slate-400 text-sm">calendar_today</span>
                                </div>
                            </div>

                            <!-- Field 1: Type -->
                            <div>
                                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Type</label>
                                    <select name="type" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                        <option value="Purchase & Sales" selected>Purchase & Sales</option>
                                        <option value="Purchase">Purchase</option>
                                        <option value="Sales">Sales</option>
                                    </select>
                                    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
                                </div>
                            </div>

                            <!-- Field 2: Description -->
                            <div>
                                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white">
                                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Description</label>
                                    <textarea name="description" rows="3" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 resize-none"></textarea>
                                </div>
                            </div>

                            <!-- Tabs: Buying from / Selling to -->
                            <div class="flex items-center gap-6 border-b border-slate-200 text-xs font-semibold">
                                <button type="button" @click="sideTab = 'buying'" :class="sideTab === 'buying' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2 font-bold' : 'text-slate-500 pb-2 hover:text-slate-700'">
                                    Buying from
                                </button>
                                <button type="button" @click="sideTab = 'selling'" :class="sideTab === 'selling' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2 font-bold' : 'text-slate-500 pb-2 hover:text-slate-700'">
                                    Selling to
                                </button>
                            </div>

                            <!-- Form Grid: 4 columns -->
                            <div class="space-y-4">
                                <!-- Row 1: Meta Company, Payment bank account, Payment Date, Country of origin -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Meta Company * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Meta Company *</label>
                                        <select name="meta_company_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($companies as $comp)
                                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Payment bank account * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment bank account *</label>
                                        <select name="payment_bank_account_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($companies->flatMap->bankAccounts as $ba)
                                            <option value="{{ $ba->id }}">{{ $ba->description ?? $ba->name ?? 'Bank Account' }}</option>
                                            @endforeach
                                            @if($companies->flatMap->bankAccounts->isEmpty())
                                            <option value="">Virgin Money USD</option>
                                            @endif
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Payment Date -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Date</label>
                                        <div class="flex items-center justify-between w-full">
                                            <input type="text" name="payment_date" placeholder="DD/MM/YYYY" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full placeholder-slate-400">
                                            <span class="material-icons text-slate-400 text-sm">calendar_today</span>
                                        </div>
                                    </div>

                                    <!-- Country of origin * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Country of origin *</label>
                                        <select name="country_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>
                                </div>

                                <!-- Row 2: Name, Contact Address, Contact Person, Collection address -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Name * (Contact) -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Name *</label>
                                        <select name="contact_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($contacts as $cont)
                                            <option value="{{ $cont->id }}">{{ $cont->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Contact Address * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Address *</label>
                                        <select name="contact_address_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($contacts->flatMap->addresses as $addr)
                                            <option value="{{ $addr->id }}">{{ $addr->addressLine1 ?? 'Address' }}</option>
                                            @endforeach
                                            @if($contacts->flatMap->addresses->isEmpty())
                                            <option value="">10 Collyer Quay</option>
                                            @endif
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Contact Person * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Person *</label>
                                        <select name="contact_person_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($contacts->flatMap->representatives as $rep)
                                            <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                                            @endforeach
                                            @if($contacts->flatMap->representatives->isEmpty())
                                            <option value="">Chinmay</option>
                                            @endif
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Collection address -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Collection address</label>
                                        <select name="collection_address_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            <option value="">Select address</option>
                                            @foreach($contacts->flatMap->addresses as $addr)
                                            <option value="{{ $addr->id }}">{{ $addr->addressLine1 }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>
                                </div>

                                <!-- Row 3: Currency, Dollar Conversion, Incoterms, Customer Order Number -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Currency * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Currency *</label>
                                        <select name="currency_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($currencies as $cur)
                                            <option value="{{ $cur->id }}">{{ $cur->description ?? 'USD' }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Dollar Conversion -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Dollar Conversion</label>
                                        <input type="text" name="dollar_conversion" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>

                                    <!-- Incoterms * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Incoterms *</label>
                                        <input type="text" name="incoterms" value="CIF JEDDAH" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>

                                    <!-- Customer Order Number -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Customer Order Number</label>
                                        <input type="text" name="customer_order_number" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>
                                </div>

                                <!-- Row 4: Payment type, Payment Terms type, Payment Terms, Advanced Payment -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Payment type * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment type *</label>
                                        <select name="payment_type_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($paymentTypes as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->description }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Payment Terms type * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms type *</label>
                                        <select name="payment_terms_type_id" class="w-full text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                            @foreach($paymentTerms as $ptt)
                                            <option value="{{ $ptt->id }}">{{ $ptt->description }}</option>
                                            @endforeach
                                        </select>
                                        <span class="material-icons absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">arrow_drop_down</span>
                                    </div>

                                    <!-- Payment Terms * -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms *</label>
                                        <input type="text" name="payment_terms" value="100%" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>

                                    <!-- Advanced Payment -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Advanced Payment</label>
                                        <input type="text" name="advanced_payment" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>
                                </div>

                                <!-- Row 5: Notes -->
                                <div>
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Notes</label>
                                        <input type="text" name="notes" class="text-xs text-slate-800 bg-transparent border-0 p-0 focus:ring-0 w-full">
                                    </div>
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="material-icons-outlined text-slate-700 text-base">credit_card</span>
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
                                            <template x-for="(prod, idx) in newProducts" :key="idx">
                                                <tr>
                                                    <td class="py-2 px-3">
                                                        <select x-model="prod.product_id" name="product_id" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                            @foreach($products as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <input type="number" step="0.01" x-model.number="prod.quantity" name="quantity" class="text-xs border border-slate-200 rounded p-1 w-20 bg-white">
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <input type="text" x-model="prod.uom" class="text-xs border border-slate-200 rounded p-1 w-16 bg-white" value="MT">
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <input type="number" step="0.01" x-model.number="prod.unitPrice" name="unit_price" class="text-xs border border-slate-200 rounded p-1 w-24 bg-white">
                                                    </td>
                                                    <td class="py-2 px-3 font-semibold text-slate-800" x-text="(prod.quantity * prod.unitPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                                    <td class="py-2 px-3">
                                                        <select x-model="prod.pricing_type" class="text-xs border border-slate-200 rounded p-1 w-full bg-white">
                                                            <option value="Fixed">Fixed</option>
                                                            <option value="Formula">Formula</option>
                                                        </select>
                                                    </td>
                                                    <td class="py-2 px-2 text-right">
                                                        <button type="button" @click="removeProduct(idx)" x-show="newProducts.length > 1" class="text-slate-400 hover:text-rose-600">
                                                            <span class="material-icons text-sm">delete</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot class="border-t border-slate-200 bg-slate-50/70 font-semibold text-xs text-slate-800">
                                            <tr>
                                                <td class="py-2.5 px-3">Total Qty: <span x-text="calcTotalQty()"></span></td>
                                                <td colspan="3"></td>
                                                <td class="py-2.5 px-3">Total: <span x-text="calcTotalPrice()"></span></td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <button type="button" @click="addProduct()" class="mt-3 bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-1.5 rounded-full font-medium text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                    Add Product
                                </button>
                            </div>

                            <!-- Comment Section -->
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
                                <button type="button" @click="if (newComment) { commentsList.unshift({ text: newComment, date: 'Just now' }); newComment = ''; }" class="mt-2 ml-11 bg-slate-200 hover:bg-slate-300 text-slate-600 px-4 py-1 rounded-full text-xs font-medium transition">
                                    Add
                                </button>

                                <template x-for="(c, cIdx) in commentsList" :key="cIdx">
                                    <div class="ml-11 mt-2 text-xs bg-slate-50 p-2 rounded border border-slate-100 text-slate-700 flex justify-between">
                                        <span x-text="c.text"></span>
                                        <span class="text-slate-400 text-[10px]" x-text="c.date"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Activities Section -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="material-icons-outlined text-slate-700 text-base">content_paste</span>
                                    <h4 class="font-bold text-xs text-slate-900">Activities</h4>
                                </div>
                                <div class="relative pl-36 space-y-4 before:absolute before:left-[178px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                    <div class="relative flex items-center gap-3">
                                        <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">23 Sep 26 15:01:36</span>
                                        <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">R</div>
                                        <span class="text-xs font-medium text-slate-800">Roger</span>
                                        <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">CHANGE_STAGE</span>
                                    </div>
                                    <div class="relative flex items-center gap-3">
                                        <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">23 Sep 26 15:00:58</span>
                                        <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">R</div>
                                        <span class="text-xs font-medium text-slate-800">Roger</span>
                                        <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">UPDATED</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Submit Footer -->
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                                <button type="button" @click="showNewModal = false" class="px-5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-full transition">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-xs font-semibold rounded-full shadow-sm transition">
                                    Save Contract
                                </button>
                            </div>
                        </div>

                        <!-- Right Sidebar Toolbar -->
                        <div class="w-12 bg-slate-50/80 border-l border-slate-200 flex flex-col items-center py-4 gap-6 text-slate-400 shrink-0 select-none">
                            <button type="button" @click="showNewModal = false" class="hover:text-slate-600 transition" title="Close">
                                <span class="material-icons text-lg">close</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="Calendar">
                                <span class="material-icons text-base">calendar_today</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="Tags">
                                <span class="material-icons text-base">label</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="Assignees">
                                <span class="material-icons text-base">person</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="Attachments">
                                <span class="material-icons text-base">attach_file</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="Checklist">
                                <span class="material-icons text-base">check_circle</span>
                            </button>
                            <button type="button" class="hover:text-slate-600 transition" title="More">
                                <span class="material-icons text-base">more_horiz</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        showNewModal: false,
        searchQuery: '',
        draggingContractId: null,
        draggingSourceStageId: null,
        dropTargetStageId: null,
        newProducts: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 0, uom: 'MT', unitPrice: 0, pricing_type: 'Fixed' }
        ],
        toast: {
            show: false,
            message: '',
            timer: null
        },

        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        addProduct() {
            this.newProducts.push({
                product_id: '{{ $products->first()?->id ?? 1 }}',
                quantity: 0,
                uom: 'MT',
                unitPrice: 0,
                pricing_type: 'Fixed'
            });
        },

        removeProduct(index) {
            if (this.newProducts.length > 1) {
                this.newProducts.splice(index, 1);
            }
        },

        calcTotalQty() {
            const sum = this.newProducts.reduce((acc, p) => acc + (parseFloat(p.quantity) || 0), 0);
            return sum.toFixed(3);
        },

        calcTotalPrice() {
            const sum = this.newProducts.reduce((acc, p) => acc + ((parseFloat(p.quantity) || 0) * (parseFloat(p.unitPrice) || 0)), 0);
            return sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
