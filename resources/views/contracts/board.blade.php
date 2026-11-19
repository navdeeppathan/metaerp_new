@extends('layouts.app')

@section('title', 'Contracts Pipeline')

@section('content')
<div class="space-y-4" x-data="kanbanBoard()">
    <!-- Header with Search & Add a Contract -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="sr-only">Contract Pipeline</span>
        </div>

        <!-- Search Bar and Add a Contract Button (Matching Screenshot Exactly) -->
        <div class="flex items-center gap-3">
            <div class="relative w-72 sm:w-80">
                <span class="material-icons-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
                <input type="text" 
                       x-model="searchQuery"
                       placeholder="Search by code or contact" 
                       class="w-full bg-white border border-slate-200/90 rounded-full pl-11 pr-5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs transition">
            </div>

            @fullAccess('CONTRACT')
            <button @click="showNewModal = true" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-7 py-2.5 rounded-full font-semibold text-sm shadow-xs transition-colors whitespace-nowrap cursor-pointer flex items-center justify-center">
                Add a contract
            </button>
            @endfullAccess
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
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-w-[220px] flex items-center justify-between cursor-pointer"
                                         @click="$refs.newOrderDateInput && ($refs.newOrderDateInput._flatpickr ? $refs.newOrderDateInput._flatpickr.open() : $refs.newOrderDateInput.focus())">
                                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order date</label>
                                        <input type="text" 
                                               id="new_contract_order_date" 
                                               name="order_date" 
                                               x-ref="newOrderDateInput"
                                               x-model="orderDateValue" 
                                               class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                        <span class="material-icons text-slate-400 text-lg ml-2 cursor-pointer">calendar_today</span>
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

                            <!-- Dynamic Selected Labels Display (from Toolbar Labels icon) -->
                            <div x-show="selectedLabels.length > 0" class="flex flex-wrap items-center gap-2 pt-1" x-cloak>
                                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    Labels:
                                </span>
                                <template x-for="lbl in selectedLabels" :key="lbl.id">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium shadow-2xs" :class="lbl.class">
                                        <span x-text="lbl.title"></span>
                                        <button type="button" @click="toggleLabel(lbl)" class="hover:opacity-75 cursor-pointer ml-1 text-xs">✕</button>
                                        <input type="hidden" name="labels[]" :value="lbl.title">
                                    </span>
                                </template>
                            </div>

                            <!-- Dynamic Selected Members Display (from Toolbar Members icon) -->
                            <div x-show="selectedMembers.length > 0" class="flex flex-wrap items-center gap-2 pt-1" x-cloak>
                                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Members:
                                </span>
                                <template x-for="mem in selectedMembers" :key="mem.id">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                        <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold" x-text="(mem.name || mem.username || 'U').charAt(0).toUpperCase()"></span>
                                        <span x-text="mem.name || mem.username"></span>
                                        <button type="button" @click="toggleMember(mem)" class="hover:text-red-500 text-slate-400 cursor-pointer ml-0.5">✕</button>
                                        <input type="hidden" name="member_ids[]" :value="mem.id">
                                    </span>
                                </template>
                            </div>

                            <!-- Dynamic Attached Files Display (from Toolbar Attachments icon) -->
                            <div x-show="attachedFiles.length > 0" class="space-y-2 pt-2 border-t border-slate-100" x-cloak>
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                                    <svg class="w-4 h-4 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <span>Attachments</span>
                                    <span class="text-slate-400 font-normal" x-text="'(' + attachedFiles.length + ')'"></span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    <template x-for="(file, idx) in attachedFiles" :key="idx">
                                        <div class="border border-slate-200 rounded-lg p-2.5 flex items-center justify-between bg-slate-50/50 hover:bg-slate-50 transition">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="material-icons text-slate-400 text-base shrink-0">insert_drive_file</span>
                                                <div class="truncate">
                                                    <p class="text-xs font-medium text-slate-800 truncate" x-text="file.name"></p>
                                                    <p class="text-[10px] text-slate-400" x-text="file.size"></p>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeAttachedFile(idx)" class="text-slate-400 hover:text-red-500 p-1 cursor-pointer" title="Remove attachment">✕</button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Dynamic Checklists Display (from Toolbar Checklist icon) -->
                            <div x-show="checklists.length > 0" class="space-y-4 pt-2 border-t border-slate-100" x-cloak>
                                <template x-for="(chk, cIndex) in checklists" :key="chk.id">
                                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/30 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-600 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="font-semibold text-xs text-slate-800" x-text="chk.name"></span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-semibold text-slate-500" x-text="getChecklistProgress(chk) + '%'"></span>
                                                <button type="button" @click="removeChecklist(chk.id)" class="text-slate-400 hover:text-red-500 text-xs transition cursor-pointer" title="Delete checklist">
                                                    <span class="material-icons text-base">delete_outline</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + getChecklistProgress(chk) + '%'"></div>
                                        </div>

                                        <!-- Check Items -->
                                        <div class="space-y-1.5">
                                            <template x-for="(item, iIndex) in chk.checkItems" :key="item.id">
                                                <div class="flex items-center justify-between group py-1 px-2 rounded-md hover:bg-white transition">
                                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer flex-1">
                                                        <input type="checkbox" x-model="item.checked" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span :class="item.checked ? 'line-through text-slate-400' : 'text-slate-800'" x-text="item.name"></span>
                                                    </label>
                                                    <button type="button" @click="removeChecklistItem(chk, item.id)" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 text-xs transition cursor-pointer">✕</button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Add an item input -->
                                        <div class="flex items-center gap-2 pt-1">
                                            <input type="text" x-model="chk.newItemName" placeholder="Add an item" @keydown.enter.prevent="addChecklistItem(chk)" class="flex-1 text-xs border border-slate-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-indigo-500 bg-white">
                                            <button type="button" @click="addChecklistItem(chk)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer">Add</button>
                                        </div>
                                    </div>
                                </template>
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
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between cursor-pointer"
                                             @click="$refs.newBuyingPaymentDateInput && ($refs.newBuyingPaymentDateInput._flatpickr ? $refs.newBuyingPaymentDateInput._flatpickr.open() : $refs.newBuyingPaymentDateInput.focus())">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                                            <input type="text" 
                                                   id="new_contract_buying_payment_date" 
                                                   name="buying_payment_date" 
                                                   x-ref="newBuyingPaymentDateInput"
                                                   placeholder="DD/MM/YYYY" 
                                                   class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 placeholder-slate-400 cursor-pointer">
                                            <span class="material-icons text-slate-400 text-base cursor-pointer">calendar_today</span>
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
                                                                    <option value="CUSTOM">Custom</option>
                                                                    <option value="LME">LME</option>
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
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between cursor-pointer"
                                             @click="$refs.newSellingPaymentDateInput && ($refs.newSellingPaymentDateInput._flatpickr ? $refs.newSellingPaymentDateInput._flatpickr.open() : $refs.newSellingPaymentDateInput.focus())">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                                            <input type="text" 
                                                   id="new_contract_selling_payment_date" 
                                                   name="selling_payment_date" 
                                                   x-ref="newSellingPaymentDateInput"
                                                   placeholder="DD/MM/YYYY" 
                                                   class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 placeholder-slate-400 cursor-pointer">
                                            <span class="material-icons text-slate-400 text-base cursor-pointer">calendar_today</span>
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
                                                                    <option value="CUSTOM">Custom</option>
                                                                    <option value="LME">LME</option>
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

                                    <!-- Weight Tolerance (Screenshot 4: right below Add Product in Selling tab) -->
                                    <div class="pt-4 border-t border-slate-100">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Weight Tolerance</label>
                                            <input type="text" name="weight_tolerance" placeholder="Weight Tolerance" class="text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 w-full">
                                        </div>
                                    </div>

                                    <!-- Transport Section (Screenshot 4: before Comment in Selling tab) -->
                                    <div class="pt-4 border-t border-slate-100">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="material-icons-outlined text-slate-700 text-base">local_shipping</span>
                                            <h4 class="font-bold text-xs text-slate-900">Transport</h4>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of transport *</label>
                                            <select name="mode_of_transport" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                                                <option value="SEA" selected>SEA</option>
                                                <option value="ROAD">ROAD</option>
                                                <option value="SEA/ROAD">SEA/ROAD</option>
                                            </select>
                                            <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Comment Section (Screenshot 2 & 4) -->
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

                        <!-- Right Sidebar Toolbar (Matching Screenshots 1 & 2 exact capsule & icons) -->
                        <div class="shrink-0 p-3 sm:p-4 flex items-start select-none">
                            <!-- Hidden File Upload for Attachments -->
                            <input type="file" multiple x-ref="newContractFileInput" @change="handleFileUpload($event)" class="hidden">

                            <div class="w-11 bg-[#f0f4f8] rounded-2xl py-3.5 px-1.5 flex flex-col items-center gap-5 text-slate-500 shadow-2xs relative">
                                <!-- 1. Close Button (X) -->
                                <button type="button" @click="showNewModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-800 hover:text-slate-950 hover:bg-slate-200/60 transition cursor-pointer" title="Close">
                                    <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>

                                <!-- 2. Due Date (Calendar) -->
                                <div class="relative">
                                    <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'due' ? null : 'due')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'due' ? 'bg-slate-200/80 text-slate-800' : ''" title="Due Date">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                    <!-- Due Date Popover -->
                                    <div x-show="openToolbarMenu === 'due'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-4 text-slate-800 text-left">
                                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                                            <span class="text-xs font-bold text-slate-800">Due Date</span>
                                            <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] font-medium text-slate-500 mb-1">Set due date</label>
                                                <input type="datetime-local" x-model="dueDateInput" @change="orderDateValue = formatDueDate(dueDateInput); openToolbarMenu = null; showToast('Due date updated');" class="w-full text-xs border border-slate-300 rounded-md px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                                            </div>
                                            <template x-if="orderDateValue">
                                                <button type="button" @click="orderDateValue = ''; dueDateInput = ''; openToolbarMenu = null; showToast('Due date removed');" class="w-full py-1.5 text-center text-xs text-red-600 hover:bg-red-50 rounded-md font-medium transition cursor-pointer">
                                                    Remove Due Date
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Labels (Tag) -->
                                <div class="relative">
                                    <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'labels' ? null : 'labels')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'labels' ? 'bg-slate-200/80 text-slate-800' : ''" title="Labels">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </button>
                                    <!-- Labels Popover -->
                                    <div x-show="openToolbarMenu === 'labels'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-3 text-slate-800 text-left">
                                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                            <span class="text-xs font-bold text-slate-800">Labels</span>
                                            <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                                        </div>
                                        <div class="space-y-1 max-h-56 overflow-y-auto">
                                            <template x-for="lbl in availableLabels" :key="lbl.id">
                                                <button type="button" @click="toggleLabel(lbl)" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition cursor-pointer text-left">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-3 h-3 rounded-full" :style="'background-color: ' + lbl.color"></span>
                                                        <span class="text-xs font-medium text-slate-700" x-text="lbl.title"></span>
                                                    </div>
                                                    <span class="text-xs" :class="isLabelSelected(lbl.id) ? 'text-indigo-600 font-bold' : 'text-transparent'">✓</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Members (User Circle) -->
                                <div class="relative">
                                    <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'members' ? null : 'members')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'members' ? 'bg-slate-200/80 text-slate-800' : ''" title="Members">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <!-- Members Popover -->
                                    <div x-show="openToolbarMenu === 'members'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-3 text-slate-800 text-left">
                                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                            <span class="text-xs font-bold text-slate-800">Members</span>
                                            <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                                        </div>
                                        <div class="space-y-1 max-h-56 overflow-y-auto">
                                            <template x-for="mem in availableMembers" :key="mem.id">
                                                <button type="button" @click="toggleMember(mem)" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition cursor-pointer text-left">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0" x-text="(mem.name || mem.username || 'U').charAt(0).toUpperCase()"></div>
                                                        <span class="text-xs font-medium text-slate-700 truncate" x-text="mem.name || mem.username"></span>
                                                    </div>
                                                    <span class="text-xs" :class="isMemberSelected(mem.id) ? 'text-indigo-600 font-bold' : 'text-transparent'">✓</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Attachments (Paperclip) -->
                                <div>
                                    <button type="button" @click="$refs.newContractFileInput.click()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" title="Attachments">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- 6. Checklist (Check Circle) -->
                                <div class="relative">
                                    <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'checklist' ? null : 'checklist')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'checklist' ? 'bg-slate-200/80 text-slate-800' : ''" title="Checklist">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <!-- Checklist Popover -->
                                    <div x-show="openToolbarMenu === 'checklist'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-4 text-slate-800 text-left">
                                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                                            <span class="text-xs font-bold text-slate-800">Add Checklist</span>
                                            <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] font-medium text-slate-500 mb-1">Checklist title</label>
                                                <input type="text" x-model="newChecklistTitle" placeholder="Checklist title" @keydown.enter.prevent="addChecklist()" class="w-full text-xs border border-slate-300 rounded-md px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="button" @click="openToolbarMenu = null" class="px-3 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-md font-medium transition cursor-pointer">Cancel</button>
                                                <button type="button" @click="addChecklist()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold shadow-xs transition cursor-pointer">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. Options (Dots Horizontal) -->
                                <div class="relative">
                                    <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'options' ? null : 'options')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'options' ? 'bg-slate-200/80 text-slate-800' : ''" title="Options">
                                        <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                        </svg>
                                    </button>
                                    <!-- Options Popover -->
                                    <div x-show="openToolbarMenu === 'options'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 bottom-0 z-50 w-52 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 text-slate-800 text-left">
                                        <button type="button" @click="finishContract()" class="w-full px-4 py-2 text-left text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition flex items-center gap-2 cursor-pointer">
                                            <span class="material-icons text-sm text-slate-400">task_alt</span>
                                            <span>Finish contract</span>
                                        </button>
                                        <button type="button" @click="duplicateContract()" class="w-full px-4 py-2 text-left text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition flex items-center gap-2 cursor-pointer">
                                            <span class="material-icons text-sm text-slate-400">content_copy</span>
                                            <span>Duplicate Contract</span>
                                        </button>
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        <button type="button" @click="cancelContract()" class="w-full px-4 py-2 text-left text-xs text-red-600 hover:bg-red-50 transition flex items-center gap-2 cursor-pointer">
                                            <span class="material-icons text-sm text-red-500">cancel</span>
                                            <span>Cancel contract</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                        this.initCardModalPickers(content);
                    }
                    this.modalLoading = false;
                })
                .catch(err => {
                    console.error('Failed to load contract modal:', err);
                    this.modalLoading = false;
                });
        },

        init() {
            this.$watch('showNewModal', val => {
                if (val) {
                    this.$nextTick(() => {
                        this.initNewContractPickers();
                    });
                }
            });
        },

        initNewContractPickers() {
            if (typeof flatpickr === 'undefined') return;
            const orderEl = document.getElementById('new_contract_order_date');
            if (orderEl && !orderEl._flatpickr) {
                flatpickr(orderEl, {
                    enableTime: true,
                    dateFormat: 'd/m/Y h:i K',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: this.orderDateValue || new Date(),
                    onChange: (dates, dateStr) => {
                        this.orderDateValue = dateStr;
                    }
                });
            }
            const buyPayEl = document.getElementById('new_contract_buying_payment_date');
            if (buyPayEl && !buyPayEl._flatpickr) {
                flatpickr(buyPayEl, {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
            const sellPayEl = document.getElementById('new_contract_selling_payment_date');
            if (sellPayEl && !sellPayEl._flatpickr) {
                flatpickr(sellPayEl, {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        },

        initCardModalPickers(container) {
            if (typeof flatpickr === 'undefined' || !container) return;
            setTimeout(() => {
                const orderEl = container.querySelector('input[name="due_date"]');
                if (orderEl && !orderEl._flatpickr) {
                    flatpickr(orderEl, {
                        enableTime: true,
                        dateFormat: 'd/m/Y h:i K',
                        allowInput: true,
                        disableMobile: true
                    });
                }
                const payEl = container.querySelector('input[name="payment_date"]');
                if (payEl && !payEl._flatpickr) {
                    flatpickr(payEl, {
                        dateFormat: 'd/m/Y',
                        allowInput: true,
                        disableMobile: true
                    });
                }
            }, 60);
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

        // Floating Toolbar State & Actions
        openToolbarMenu: null,
        orderDateValue: '{{ now()->format("d/m/Y h:i A") }}',
        dueDateInput: '',
        selectedLabels: [],
        availableLabels: [
            { id: 1, title: 'High Priority', class: 'bg-red-500 text-white', color: '#ef4444' },
            { id: 2, title: 'Design', class: 'bg-blue-500 text-white', color: '#3b82f6' },
            { id: 3, title: 'App', class: 'bg-emerald-500 text-white', color: '#10b981' },
            { id: 4, title: 'Feature', class: 'bg-purple-500 text-white', color: '#8b5cf6' },
            { id: 5, title: 'Urgent', class: 'bg-amber-500 text-white', color: '#f59e0b' }
        ],
        selectedMembers: [],
        availableMembers: @js($users ?? []),
        attachedFiles: [],
        checklists: [],
        newChecklistTitle: '',

        formatDueDate(val) {
            if (!val) return '';
            const d = new Date(val);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            let hours = d.getHours();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const strHours = String(hours).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${strHours}:${minutes} ${ampm}`;
        },
        toggleLabel(lbl) {
            const idx = this.selectedLabels.findIndex(l => l.id === lbl.id);
            if (idx >= 0) {
                this.selectedLabels.splice(idx, 1);
            } else {
                this.selectedLabels.push(lbl);
            }
        },
        isLabelSelected(id) {
            return this.selectedLabels.some(l => l.id === id);
        },
        toggleMember(mem) {
            const idx = this.selectedMembers.findIndex(m => m.id === mem.id);
            if (idx >= 0) {
                this.selectedMembers.splice(idx, 1);
            } else {
                this.selectedMembers.push(mem);
            }
        },
        isMemberSelected(id) {
            return this.selectedMembers.some(m => m.id === id);
        },
        handleFileUpload(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            for (let i = 0; i < files.length; i++) {
                const f = files[i];
                const sizeFormatted = (f.size / 1024).toFixed(1) + ' KB';
                this.attachedFiles.push({
                    name: f.name,
                    size: sizeFormatted
                });
            }
            this.showToast(files.length + ' file(s) attached');
            event.target.value = '';
        },
        removeAttachedFile(index) {
            this.attachedFiles.splice(index, 1);
        },
        addChecklist() {
            if (!this.newChecklistTitle || !this.newChecklistTitle.trim()) {
                this.newChecklistTitle = 'Checklist';
            }
            this.checklists.push({
                id: Date.now(),
                name: this.newChecklistTitle.trim(),
                newItemName: '',
                checkItems: []
            });
            this.newChecklistTitle = '';
            this.openToolbarMenu = null;
            this.showToast('Checklist added');
        },
        removeChecklist(id) {
            this.checklists = this.checklists.filter(c => c.id !== id);
        },
        addChecklistItem(checklist) {
            if (!checklist.newItemName || !checklist.newItemName.trim()) return;
            if (!checklist.checkItems) checklist.checkItems = [];
            checklist.checkItems.push({
                id: Date.now(),
                name: checklist.newItemName.trim(),
                checked: false
            });
            checklist.newItemName = '';
        },
        removeChecklistItem(checklist, itemId) {
            checklist.checkItems = checklist.checkItems.filter(item => item.id !== itemId);
        },
        getChecklistProgress(checklist) {
            if (!checklist.checkItems || checklist.checkItems.length === 0) return 0;
            const checked = checklist.checkItems.filter(i => i.checked).length;
            return Math.round((checked / checklist.checkItems.length) * 100);
        },
        duplicateContract() {
            this.openToolbarMenu = null;
            this.showToast('Contract form duplicated');
        },
        finishContract() {
            this.openToolbarMenu = null;
            this.showToast('Contract finished');
        },
        cancelContract() {
            this.openToolbarMenu = null;
            this.showNewModal = false;
            this.showToast('Contract creation cancelled');
        },
        newBuyingProducts: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 0, uom: 'MT', unitPrice: 0, pricing_type: 'CUSTOM' }
        ],
        newSellingProducts: [
            { product_id: '{{ $products->first()?->id ?? 1 }}', quantity: 0, uom: 'MT', unitPrice: 0, pricing_type: 'CUSTOM' }
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
                pricing_type: 'CUSTOM'
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
                pricing_type: 'CUSTOM'
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
