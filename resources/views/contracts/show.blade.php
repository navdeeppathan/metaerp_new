@extends('layouts.app')

@section('title', $contract->orderCode . ' - Contract Detail')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'info' }">
    <!-- Breadcrumb & Title -->
    <div>
        <a href="{{ route('contracts.search') }}" class="inline-flex items-center gap-1 text-xs font-normal text-slate-500 hover:text-slate-800 transition mb-1">
            <span class="material-icons text-sm">arrow_back</span>
            Contracts
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $contract->orderCode }}</h1>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-8 border-b border-slate-200 text-sm font-medium">
        <button @click="activeTab = 'info'" 
                :class="activeTab === 'info' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="pb-2.5 transition">
            Contract Info
        </button>
        <button @click="activeTab = 'documents'" 
                :class="activeTab === 'documents' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="pb-2.5 transition">
            Documents
        </button>
        <button @click="activeTab = 'transport'" 
                :class="activeTab === 'transport' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="pb-2.5 transition">
            Transport
        </button>
    </div>

    <!-- TAB 1: CONTRACT INFO -->
    <div x-show="activeTab === 'info'" class="space-y-6 pt-2">
        <!-- Order Header Fields -->
        <div class="space-y-4">
            <!-- Row 1: Order Code, Order Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Order Code</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->orderCode }}</div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Order Date</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->date ? $contract->date->format('m/d/Y, g:i A') : '09/23/2026, 2:54 PM' }}</div>
                </div>
            </div>

            <!-- Row 2: Status, Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Status</label>
                    <div class="text-xs text-slate-800 font-normal">{{ ($contract->contractStatus == 'ACTIVE' || $contract->contractStatus == 'OPEN') ? 'OPEN' : $contract->contractStatus }}</div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Type</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->type }}</div>
                </div>
            </div>

            <!-- Row 3: Order Description -->
            <div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Order Description</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->orderDescription ?? '.' }}</div>
                </div>
            </div>

            <!-- Row 4: Weight Tolerance -->
            <div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Weight Tolerance</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->weightTolerance ?? '0.1' }}</div>
                </div>
            </div>
        </div>

        <!-- PURCHASE SECTION -->
        <div class="pt-2">
            <h2 class="text-base font-bold text-slate-900 mb-4">Purchase</h2>

            <div class="space-y-4">
                <!-- Row: Meta Company, Bank account, Payment date -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Meta Company</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->metaCompany?->name ?? 'Metaenterprices LTD' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Bank account</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->paymentAccount?->description ?? $contract->purchase?->paymentAccount?->name ?? 'Virgin Money USD' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment date</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->paymentDate ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Contact Name, Contact Address, Contact Person -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Name</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->contact?->name ?? 'Trafigura Pte Ltd' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Address</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->address?->addressLine1 ?? '10 Collyer Quay' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Person</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->contactRepresentative?->name ?? 'Chinmay' }}</div>
                    </div>
                </div>

                <!-- Row: Dollar Conversion, Incoterms, Costumer Order Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Dollar Conversion</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->dollarConvertionRate != 1.0 && $contract->purchase?->dollarConvertionRate ? $contract->purchase->dollarConvertionRate : '' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Incoterms</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->incoterms ?? 'CIF JEDDAH' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Costumer Order Number</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->customerOrderNumber ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Payment Type, Payment Terms Type, Payment Terms, Advanced payment -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Type</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->paymentType?->description ?? 'Upfront' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms Type</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->paymentTermsType?->description ?? '%' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->paymentTerms ?? '100%' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Advanced payment</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->advancedPayment ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Notes -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Notes</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->purchase?->notes ?? '' }}</div>
                    </div>
                </div>

                <!-- Products Accordion (Purchase) -->
                <div class="pt-1">
                    <div class="border border-slate-200 rounded-lg overflow-hidden bg-white shadow-2xs" x-data="{ open: false }">
                        <div @click="open = !open" class="flex items-center justify-between p-3.5 cursor-pointer bg-white hover:bg-slate-50 transition select-none">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-800">SHG - Special High Grade</span>
                                <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">Qty: 100 MT</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-900">Total: USD 371,000.00</span>
                                <span class="material-icons text-slate-400 text-base transform transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                            </div>
                        </div>
                        <div x-show="open" class="p-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-700 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Quantity</div>
                                <div class="font-semibold text-slate-800 mt-0.5">100 MT</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Unit Price</div>
                                <div class="font-semibold text-slate-800 mt-0.5">USD 3,710.00</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">VAT</div>
                                <div class="font-semibold text-slate-800 mt-0.5">0.00%</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Total</div>
                                <div class="font-semibold text-slate-800 mt-0.5">USD 371,000.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SALE SECTION -->
        <div class="pt-2">
            <h2 class="text-base font-bold text-slate-900 mb-4">Sale</h2>

            <div class="space-y-4">
                <!-- Row: Meta Company, Bank account, Payment date -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Meta Company</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->metaCompany?->name ?? 'Metaenterprices LTD' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Bank account</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->paymentAccount?->description ?? $contract->sale?->paymentAccount?->name ?? 'Virgin Money USD' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment date</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->paymentDate ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Contact Name, Contact Address, Contact Person -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Name</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->contact?->name ?? 'Caravan and Alyanable Company for Advertising Manufacturing' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Address</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->address?->addressLine1 ?? 'Second Industrial City, Al Mursi District, Al Laith Road' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Contact Person</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->contactRepresentative?->name ?? 'Waleed El Rass' }}</div>
                    </div>
                </div>

                <!-- Row: Dollar Conversion, Incoterms, Costumer Order Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Dollar Conversion</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->dollarConvertionRate != 1.0 && $contract->sale?->dollarConvertionRate ? $contract->sale->dollarConvertionRate : '' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Incoterms</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->incoterms ?? '' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Costumer Order Number</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->customerOrderNumber ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Payment Type, Payment Terms Type, Payment Terms, Advanced payment -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Type</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->paymentType?->description ?? 'Upfront' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms Type</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->paymentTermsType?->description ?? '%' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Payment Terms</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->paymentTerms ?? '100%' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Advanced payment</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->advancedPayment ?? '' }}</div>
                    </div>
                </div>

                <!-- Row: Notes -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Notes</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->sale?->notes ?? '' }}</div>
                    </div>
                </div>

                <!-- Products Accordion (Sale) -->
                <div class="pt-1">
                    <div class="border border-slate-200 rounded-lg overflow-hidden bg-white shadow-2xs" x-data="{ open: false }">
                        <div @click="open = !open" class="flex items-center justify-between p-3.5 cursor-pointer bg-white hover:bg-slate-50 transition select-none">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-800">SHG - Special High Grade</span>
                                <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">Qty: 100 MT</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-900">Total: USD 371,000.00</span>
                                <span class="material-icons text-slate-400 text-base transform transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                            </div>
                        </div>
                        <div x-show="open" class="p-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-700 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Quantity</div>
                                <div class="font-semibold text-slate-800 mt-0.5">100 MT</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Unit Price</div>
                                <div class="font-semibold text-slate-800 mt-0.5">USD 3,710.00</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">VAT</div>
                                <div class="font-semibold text-slate-800 mt-0.5">0.00%</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">Total</div>
                                <div class="font-semibold text-slate-800 mt-0.5">USD 371,000.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTIVITIES TIMELINE SECTION -->
        <div class="pt-4">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-icons-outlined text-slate-600 text-lg">content_paste</span>
                <h2 class="text-base font-bold text-slate-900">Activities</h2>
            </div>

            <div class="relative pl-36 space-y-6 before:absolute before:left-[178px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                @forelse($activities as $act)
                <div class="relative flex items-center gap-3">
                    <!-- Timestamp on left -->
                    <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">
                        {{ $act->dateTime ? $act->dateTime->format('d M y H:i:s') : '' }}
                    </span>
                    <!-- User Avatar Node -->
                    <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">
                        {{ substr($act->user?->displayName ?? $act->user?->name ?? 'Roger', 0, 1) }}
                    </div>
                    <!-- User Name -->
                    <span class="text-xs font-medium text-slate-800">
                        {{ $act->user?->displayName ?? $act->user?->name ?? 'Roger' }}
                    </span>
                    <!-- Action Badge -->
                    <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $act->action }}
                    </span>
                </div>
                @empty
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
                <div class="relative flex items-center gap-3">
                    <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">23 Sep 26 14:59:29</span>
                    <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">R</div>
                    <span class="text-xs font-medium text-slate-800">Roger</span>
                    <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">CHANGE_STAGE</span>
                </div>
                <div class="relative flex items-center gap-3">
                    <span class="absolute -left-36 w-32 text-right text-xs text-slate-500 font-normal">23 Sep 26 14:59:19</span>
                    <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-semibold z-10 shrink-0 border-2 border-white shadow-2xs">R</div>
                    <span class="text-xs font-medium text-slate-800">Roger</span>
                    <span class="px-2.5 py-0.5 text-[11px] font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">UPDATED</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 2: DOCUMENTS -->
    <div x-show="activeTab === 'documents'" class="space-y-8 pt-2">
        <!-- Documents Section Header -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="material-icons text-slate-600 text-lg">attach_file</span>
                <h2 class="text-base font-bold text-slate-900">Documents</h2>
            </div>

            <!-- Documents Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $documentCards = [
                        ['title' => 'PURCHASE_ORDER_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'PROFORMA_INVOICE_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'DELIVERY_NOTE_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'CONTRACT_OF_SALES_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'SPECIMEN_CONTRACT_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'EXPORT_LIABILITY_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'TRANSPORT_ORDER_' . $contract->orderCode, 'subtitle' => null],
                        ['title' => 'PACKING_LIST_' . $contract->orderCode, 'subtitle' => 'ME-CI26-437'],
                        ['title' => 'COMMERCIAL_INVOICE_' . $contract->orderCode, 'subtitle' => 'ME-CI26-437'],
                        ['title' => 'CREDIT_NOTE_DETACHED_' . $contract->orderCode, 'subtitle' => null],
                    ];
                @endphp

                @foreach($documentCards as $doc)
                <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between h-28 shadow-2xs hover:shadow-xs transition" x-data="{ menuOpen: false }">
                    <div>
                        <div class="text-xs font-bold text-slate-900 tracking-tight leading-snug">
                            {{ $doc['title'] }}
                        </div>
                        @if($doc['subtitle'])
                        <div class="text-[11px] text-slate-400 mt-1 font-normal">
                            {{ $doc['subtitle'] }}
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-end relative">
                        <button @click="menuOpen = !menuOpen" type="button" class="bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-normal text-xs px-3 py-1 rounded shadow-2xs flex items-center gap-1 transition">
                            Actions
                            <span class="material-icons text-sm text-slate-400">arrow_drop_down</span>
                        </button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-0 bottom-full mb-1 w-32 bg-white border border-slate-200 rounded-md shadow-lg py-1 z-20 text-xs">
                            <a href="#" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">View</a>
                            <a href="#" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Download</a>
                            <a href="#" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Regenerate</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Attachments Section -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="material-icons text-slate-600 text-lg">attach_file</span>
                <h2 class="text-base font-bold text-slate-900">Attachments</h2>
            </div>
            <div class="border border-dashed border-slate-200 rounded-lg p-8 text-center text-xs text-slate-400 bg-white">
                <span class="material-icons text-3xl text-slate-300 mb-1">cloud_upload</span>
                <div>No attachments uploaded yet</div>
            </div>
        </div>
    </div>

    <!-- TAB 3: TRANSPORT -->
    <div x-show="activeTab === 'transport'" class="space-y-6 pt-2">
        <!-- Top Transport Fields -->
        <div class="space-y-4">
            <!-- Row 1: Loading date, Price agreed, Notes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Loading date</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $contract->loadingDate ? $contract->loadingDate->format('m/d/Y, g:i A') : '09/23/2026, 4:30 AM' }}
                    </div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Price agreed</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $contract->priceAgreed ?? '3000' }}
                    </div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Notes</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $contract->transportNotes ?? '' }}
                    </div>
                </div>
            </div>

            <!-- Row 2: Special notes -->
            <div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[64px] flex items-start">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Special notes</label>
                    <div class="text-xs text-slate-800 font-normal mt-1">
                        {{ $contract->specialNotes ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        @php
            $packingList = $contract->packingLists->first();
        @endphp

        <!-- Packing List Section -->
        <div class="pt-2">
            <h2 class="text-base font-bold text-slate-900 mb-0.5">Packing List</h2>
            <div class="text-xs text-slate-700 font-normal mb-3">
                {{ $packingList?->packingListCode ?? 'ME-CI26-437' }}
            </div>

            <!-- Packing List Info Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Delivery Note</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $packingList?->deliveryNote ?? '' }}
                    </div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">B/L Number</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $packingList?->blNumber ?? 'MEDUDV499790' }}
                    </div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Date</label>
                    <div class="text-xs text-slate-800 font-normal">
                        {{ $packingList?->date ? \Carbon\Carbon::parse($packingList->date)->format('d/m/Y') : '23/09/2026' }}
                    </div>
                </div>
            </div>

            <!-- Packs Section -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3">Packs</h3>

                @php
                    $packs = $packingList?->packs ?? collect();
                @endphp

                @forelse($packs as $index => $pack)
                <div class="mb-6 space-y-4">
                    <div class="text-xs font-semibold text-slate-800"># {{ $index + 1 }}</div>

                    <!-- Pack Row 1: Container Number, Product, Packing Type -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Container Number</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->containerNumber ?? 'TCLU7531178' }}</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Product</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->product?->name ?? 'SHG - Special High Grade' }}</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Packing Type</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->packType?->description ?? 'Bundle' }}</div>
                        </div>
                    </div>

                    <!-- Pack Row 2: Quantity, Gross weight, Net weight -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Quantity</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->quantity ?? '25' }}</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Gross weight</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->grossWeight ?? '101' }}</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Net weight</label>
                            <div class="text-xs text-slate-800 font-normal">{{ $pack->netWeight ?? '99.5' }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="mb-6 space-y-4">
                    <div class="text-xs font-semibold text-slate-800"># 1</div>

                    <!-- Pack Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Container Number</label>
                            <div class="text-xs text-slate-800 font-normal">TCLU7531178</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Product</label>
                            <div class="text-xs text-slate-800 font-normal">SHG - Special High Grade</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Packing Type</label>
                            <div class="text-xs text-slate-800 font-normal">Bundle</div>
                        </div>
                    </div>

                    <!-- Pack Row 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Quantity</label>
                            <div class="text-xs text-slate-800 font-normal">25</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Gross weight</label>
                            <div class="text-xs text-slate-800 font-normal">101</div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">Net weight</label>
                            <div class="text-xs text-slate-800 font-normal">99.5</div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
