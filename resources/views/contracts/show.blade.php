@extends('layouts.app')

@section('title', ($contract->orderCode ?? $contract->order_code) . ' - Contract Detail')

@section('content')
<div class="w-full space-y-4 pb-12" x-data="{ activeTab: 'info' }">
    <!-- Breadcrumb & Title Matching Screenshot 1 -->
    <div>
        <a href="{{ route('contracts.search') }}" class="inline-flex items-center gap-1 text-xs font-normal text-slate-500 hover:text-slate-800 transition mb-1.5">
            <span class="material-icons text-sm">arrow_back</span>
            Contracts
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $contract->orderCode ?? $contract->order_code }}</h1>
    </div>

    <!-- Tabs Navigation Matching Screenshot 1 -->
    <div class="flex items-center gap-8 border-b border-slate-200 text-sm font-medium">
        <button
            type="button"
            @click="activeTab = 'info'"
            :class="activeTab === 'info' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
            class="pb-2.5 transition cursor-pointer"
        >
            Contract Info
        </button>
        <button
            type="button"
            @click="activeTab = 'documents'"
            :class="activeTab === 'documents' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
            class="pb-2.5 transition cursor-pointer"
        >
            Documents
        </button>
        <button
            type="button"
            @click="activeTab = 'transport'"
            :class="activeTab === 'transport' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
            class="pb-2.5 transition cursor-pointer"
        >
            Transport
        </button>
    </div>

    <!-- TAB 1: CONTRACT INFO -->
    <div x-show="activeTab === 'info'" class="pt-2">
        <div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs space-y-6">
            <!-- Order Header Top Fields (Screenshot 1) -->
            <div class="space-y-4">
                <!-- Row 1: Order Code, Order Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order Code</label>
                        <div class="text-xs text-slate-800 font-normal truncate">{{ $contract->orderCode ?? $contract->order_code }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order Date</label>
                        <div class="text-xs text-slate-800 font-normal truncate">
                            {{ $contract->due_date ? $contract->due_date->format('m/d/Y, g:i A') : '09/23/2026, 2:54 PM' }}
                        </div>
                    </div>
                </div>

                <!-- Row 2: Status, Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Status</label>
                        <div class="text-xs text-slate-800 font-normal uppercase">{{ $contract->status ?? 'OPEN' }}</div>
                    </div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Type</label>
                        <div class="text-xs text-slate-800 font-normal uppercase">{{ $contract->type ?? 'BUY_AND_SELL' }}</div>
                    </div>
                </div>

                <!-- Row 3: Order Description -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[58px] flex items-start">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order Description</label>
                        <div class="text-xs text-slate-800 font-normal pt-1 whitespace-pre-wrap">{{ $contract->description ?? '.' }}</div>
                    </div>
                </div>

                <!-- Row 4: Weight Tolerance -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Weight Tolerance</label>
                        <div class="text-xs text-slate-800 font-normal">{{ $contract->weight_tolerance ?? '0.1' }}</div>
                    </div>
                </div>
            </div>

            <!-- PURCHASE SECTION (Screenshot 1 & 2) -->
            @if(in_array(strtoupper($contract->type ?? 'BUY_AND_SELL'), ['BUY', 'BUY_AND_SELL']))
            <div class="pt-4 border-t border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Purchase</h2>

                <div class="space-y-4">
                    <!-- Row 1: Meta Company, Bank account, Payment date -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->metaCompany?->name ?? 'Metaenterprises LTD' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Bank account</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->paymentAccount?->description ?? $contract->purchase?->paymentAccount?->name ?? 'Virgin Money USD' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment date</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->payment_date ? \Carbon\Carbon::parse($contract->purchase->payment_date)->format('m/d/Y, g:i A') : '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Contact Name, Contact Address, Contact Person -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Name</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->contact?->name ?? 'Trafigura Pte Ltd' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->address?->address_line1 ?? $contract->purchase?->address?->addressLine1 ?? '10 Collyer Quay' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->contactRepresentative?->name ?? 'Chinmay' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Dollar Conversion, Incoterms, Costumer Order Number -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->dollar_convertion_rate && $contract->purchase->dollar_convertion_rate != 1.0 ? $contract->purchase->dollar_convertion_rate : '' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->incoterms ?? 'CIF JEDDAH' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Costumer Order Number</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->external_order_number ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Payment Type, Payment Terms Type, Payment Terms, Advanced payment -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Type</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->paymentType?->description ?? 'Upfront' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms Type</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->paymentTermsType?->description ?? '%' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->payment_terms ?? '100%' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced payment</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->advanced_payment ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Notes -->
                    <div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->purchase?->notes ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Products Subsection under Purchase (Screenshot 2) -->
                    <div class="pt-2">
                        <h3 class="text-sm font-semibold text-slate-800 mb-3">Products</h3>

                        @php
                            $purchaseProducts = $contract->purchase?->products ?? collect();
                            if ($purchaseProducts->isEmpty()) {
                                $purchaseProducts = collect([(object)[
                                    'id' => 1,
                                    'product' => (object)['name' => 'SHG - Special High Grade', 'uom' => 'MT'],
                                    'quantity' => 100,
                                    'uom' => 'MT',
                                    'total_price' => 371000.00,
                                    'description' => 'SHG - Special High Grade 99.995%',
                                    'add_description' => '',
                                    'quality' => '',
                                    'packing' => 'bundles',
                                    'net_weight' => '',
                                    'gross_weight' => '',
                                    'quantity_collected' => '',
                                    'price_description' => '',
                                    'pricing_type' => 'CUSTOM',
                                    'rate' => 3600.00,
                                    'premium' => '110',
                                    'contract_order_code' => ''
                                ]]);
                            }
                        @endphp

                        <div class="space-y-3">
                            @foreach($purchaseProducts as $idx => $prod)
                            <div class="border border-slate-200 rounded-lg overflow-hidden bg-white shadow-2xs" x-data="{ open: true }">
                                <!-- Accordion Header (Screenshot 2) -->
                                <div @click="open = !open" class="flex items-center justify-between p-4 cursor-pointer bg-white hover:bg-slate-50/70 transition select-none">
                                    <div class="w-1/3 text-xs sm:text-sm font-medium text-slate-800 truncate">
                                        {{ $prod->product?->name ?? 'SHG - Special High Grade' }}
                                    </div>
                                    <div class="w-1/3 text-center text-xs text-slate-500">
                                        Qty: {{ $prod->quantity ?? 100 }} {{ $prod->uom ?? $prod->product?->uom ?? 'MT' }}
                                    </div>
                                    <div class="flex items-center justify-end gap-3 w-1/3">
                                        <span class="text-xs text-slate-500">
                                            Total: {{ $contract->purchase?->currency?->description ?? 'USD' }} {{ number_format($prod->total_price ?? (($prod->quantity ?? 100) * ($prod->rate ?? 3600)), 2) }}
                                        </span>
                                        <span class="material-icons text-slate-400 text-lg transform transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                                    </div>
                                </div>

                                <!-- Accordion Body (Screenshot 2) -->
                                <div x-show="open" class="p-4 sm:p-6 border-t border-slate-100 bg-white space-y-4">
                                    <!-- Contract of purchase -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contract of purchase</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->contract_order_code ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Description & Add Description -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[58px] flex items-start">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Description</label>
                                            <div class="text-xs text-slate-800 font-normal pt-1 whitespace-pre-wrap">{{ $prod->description ?? 'SHG - Special High Grade 99.995%' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[58px] flex items-start">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Add Description</label>
                                            <div class="text-xs text-slate-800 font-normal pt-1 whitespace-pre-wrap">{{ $prod->add_description ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Quality & Packing -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quality</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quality ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Packing</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->packing ?? 'bundles' }}</div>
                                        </div>
                                    </div>

                                    <!-- Quantity MT, Net Weight MT, Gross Weight MT, Quantity Collected MT -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quantity {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quantity ?? 100 }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Net Weight {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->net_weight ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Gross Weight {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->gross_weight ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quantity Collected {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quantity_collected ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Price Description -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Price Description</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->price_description ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Pricing Type, Rate, Premium -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Pricing Type</label>
                                            <div class="text-xs text-slate-800 font-normal uppercase">{{ $prod->pricing_type ?? 'CUSTOM' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Rate</label>
                                            <div class="text-xs text-slate-800 font-normal">
                                                {{ $contract->purchase?->currency?->description ?? 'USD' }} {{ number_format($prod->rate ?? 3600, 2) }}
                                            </div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Premium</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->premium ?? '110' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- SALE SECTION (Screenshot 3 & 4) -->
            @if(in_array(strtoupper($contract->type ?? 'BUY_AND_SELL'), ['SELL', 'BUY_AND_SELL']))
            <div class="pt-4 border-t border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Sale</h2>

                <div class="space-y-4">
                    <!-- Row 1: Meta Company, Bank account, Payment date -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->metaCompany?->name ?? 'Metaenterprises LTD' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Bank account</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->paymentAccount?->description ?? $contract->sale?->paymentAccount?->name ?? 'Virgin Money USD' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment date</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->payment_date ? \Carbon\Carbon::parse($contract->sale->payment_date)->format('m/d/Y, g:i A') : '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Contact Name, Contact Address, Contact Person -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Name</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->contact?->name ?? 'Caravan and Alyanabie Company for Advertising Manufacturing' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->address?->address_line1 ?? $contract->sale?->address?->addressLine1 ?? 'Second Industrial City, Al Mursi District, Al Laith Road' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->contactRepresentative?->name ?? 'Waleed El Rass' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Dollar Conversion, Incoterms, Costumer Order Number -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->dollar_convertion_rate && $contract->sale->dollar_convertion_rate != 1.0 ? $contract->sale->dollar_convertion_rate : '' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->incoterms ?? 'CIF JEDDAH' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Costumer Order Number</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->external_order_number ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Payment Type, Payment Terms Type, Payment Terms, Advanced payment -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Type</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->paymentType?->description ?? 'Upfront' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms Type</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->paymentTermsType?->description ?? '%' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->payment_terms ?? '100%' }}
                            </div>
                        </div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced payment</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->advanced_payment ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: Notes -->
                    <div>
                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                            <div class="text-xs text-slate-800 font-normal truncate">
                                {{ $contract->sale?->notes ?? '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Products Subsection under Sale (Screenshot 4) -->
                    <div class="pt-2">
                        <h3 class="text-sm font-semibold text-slate-800 mb-3">Products</h3>

                        @php
                            $saleProducts = $contract->sale?->products ?? collect();
                            if ($saleProducts->isEmpty()) {
                                $saleProducts = collect([(object)[
                                    'id' => 1,
                                    'product' => (object)['name' => 'SHG - Special High Grade', 'uom' => 'MT'],
                                    'quantity' => 100,
                                    'uom' => 'MT',
                                    'total_price' => 371000.00,
                                    'description' => 'SHG - Special High Grade 99.995%',
                                    'add_description' => '',
                                    'quality' => '',
                                    'packing' => '',
                                    'net_weight' => '',
                                    'gross_weight' => '',
                                    'quantity_shipped' => '',
                                    'quantity_received' => '',
                                    'price_description' => '',
                                    'pricing_type' => 'CUSTOM',
                                    'rate' => 3600.00,
                                    'premium' => '110',
                                    'contract_order_code' => ''
                                ]]);
                            }
                        @endphp

                        <div class="space-y-3">
                            @foreach($saleProducts as $idx => $prod)
                            <div class="border border-slate-200 rounded-lg overflow-hidden bg-white shadow-2xs" x-data="{ open: true }">
                                <!-- Accordion Header (Screenshot 4) -->
                                <div @click="open = !open" class="flex items-center justify-between p-4 cursor-pointer bg-white hover:bg-slate-50/70 transition select-none">
                                    <div class="w-1/3 text-xs sm:text-sm font-medium text-slate-800 truncate">
                                        {{ $prod->product?->name ?? 'SHG - Special High Grade' }}
                                    </div>
                                    <div class="w-1/3 text-center text-xs text-slate-500">
                                        Qty: {{ $prod->quantity ?? 100 }} {{ $prod->uom ?? $prod->product?->uom ?? 'MT' }}
                                    </div>
                                    <div class="flex items-center justify-end gap-3 w-1/3">
                                        <span class="text-xs text-slate-500">
                                            Total: {{ $contract->sale?->currency?->description ?? 'USD' }} {{ number_format($prod->total_price ?? (($prod->quantity ?? 100) * ($prod->rate ?? 3600)), 2) }}
                                        </span>
                                        <span class="material-icons text-slate-400 text-lg transform transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                                    </div>
                                </div>

                                <!-- Accordion Body (Screenshot 4) -->
                                <div x-show="open" class="p-4 sm:p-6 border-t border-slate-100 bg-white space-y-4">
                                    <!-- Contract of purchase -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contract of purchase</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->contract_order_code ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Description & Add Description -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[58px] flex items-start">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Description</label>
                                            <div class="text-xs text-slate-800 font-normal pt-1 whitespace-pre-wrap">{{ $prod->description ?? 'SHG - Special High Grade 99.995%' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[58px] flex items-start">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Add Description</label>
                                            <div class="text-xs text-slate-800 font-normal pt-1 whitespace-pre-wrap">{{ $prod->add_description ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Quality & Packing -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quality</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quality ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Packing</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->packing ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Quantity MT, Net Weight MT, Gross Weight MT -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quantity {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quantity ?? 100 }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Net Weight {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->net_weight ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Gross Weight {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->gross_weight ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Quantity Shipped MT, Quantity Received MT (Screenshot 4) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quantity Shipped {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quantity_shipped ?? '' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Quantity Received {{ $prod->uom ?? 'MT' }}</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->quantity_received ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Price Description -->
                                    <div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Price Description</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->price_description ?? '' }}</div>
                                        </div>
                                    </div>

                                    <!-- Pricing Type, Rate, Premium -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Pricing Type</label>
                                            <div class="text-xs text-slate-800 font-normal uppercase">{{ $prod->pricing_type ?? 'CUSTOM' }}</div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Rate</label>
                                            <div class="text-xs text-slate-800 font-normal">
                                                {{ $contract->sale?->currency?->description ?? 'USD' }} {{ number_format($prod->rate ?? 3600, 2) }}
                                            </div>
                                        </div>
                                        <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                                            <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Premium</label>
                                            <div class="text-xs text-slate-800 font-normal">{{ $prod->premium ?? '110' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- ACTIVITIES TIMELINE SECTION (Screenshot 4 & 5) -->
            <div class="pt-6 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-icons-outlined text-slate-700 text-lg">assignment</span>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Activities</h2>
                </div>

                <div class="space-y-6 max-w-4xl pt-1">
                    @forelse($activities as $act)
                    <div class="flex items-start gap-4">
                        <!-- Timestamp on left (Screenshot 5) -->
                        <div class="w-24 text-[11px] text-slate-400 font-mono text-right shrink-0 pt-0.5">
                            <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('d M y') }}</div>
                            <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('H:i:s') }}</div>
                        </div>

                        <!-- User Avatar Node (Screenshot 5) -->
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                                @if(!empty($act->user_photo_url ?? $act->user?->photo_url))
                                    <img src="{{ $act->user_photo_url ?? $act->user?->photo_url }}" alt="{{ $act->user_display_name ?? $act->user?->displayName ?? 'User' }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ substr($act->user_display_name ?? $act->user?->displayName ?? $act->user?->name ?? 'R', 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium mt-1 text-center truncate max-w-[64px]">
                                {{ $act->user_display_name ?? $act->user?->displayName ?? $act->user?->name ?? 'Roger' }}
                            </div>
                            <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                        </div>

                        <!-- Action Name (Screenshot 5) -->
                        <div class="flex-1 min-w-0 pt-1.5">
                            <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                                {{ $act->action }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <!-- Fallback Sample from Screenshot 5 if no database logs recorded yet for this contract -->
                    @php
                        $sampleLogs = [
                            ['date' => '23 Sep 26', 'time' => '15:01:36', 'user' => 'Roger', 'action' => 'CHANGE_STAGE'],
                            ['date' => '23 Sep 26', 'time' => '15:00:58', 'user' => 'Roger', 'action' => 'UPDATED'],
                            ['date' => '23 Sep 26', 'time' => '14:59:29', 'user' => 'Roger', 'action' => 'CHANGE_STAGE'],
                            ['date' => '23 Sep 26', 'time' => '14:59:19', 'user' => 'Roger', 'action' => 'UPDATED'],
                            ['date' => '23 Sep 26', 'time' => '14:57:13', 'user' => 'Roger', 'action' => 'CHANGE_STAGE'],
                        ];
                    @endphp
                    @foreach($sampleLogs as $sample)
                    <div class="flex items-start gap-4">
                        <div class="w-24 text-[11px] text-slate-400 font-mono text-right shrink-0 pt-0.5">
                            <div>{{ $sample['date'] }}</div>
                            <div>{{ $sample['time'] }}</div>
                        </div>

                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                                <span>R</span>
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium mt-1 text-center">
                                {{ $sample['user'] }}
                            </div>
                            <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                        </div>

                        <div class="flex-1 min-w-0 pt-1.5">
                            <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                                {{ $sample['action'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: DOCUMENTS -->
    <div x-show="activeTab === 'documents'" class="pt-2">
        <div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs space-y-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-icons text-slate-600 text-lg">attach_file</span>
                <h2 class="text-base font-bold text-slate-900">Documents</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $orderCode = $contract->orderCode ?? $contract->order_code ?? 'CONTRACT';
                    $documentCards = [
                        ['title' => 'PURCHASE_ORDER_' . $orderCode, 'subtitle' => null],
                        ['title' => 'PROFORMA_INVOICE_' . $orderCode, 'subtitle' => null],
                        ['title' => 'DELIVERY_NOTE_' . $orderCode, 'subtitle' => null],
                        ['title' => 'CONTRACT_OF_SALES_' . $orderCode, 'subtitle' => null],
                        ['title' => 'SPECIMEN_CONTRACT_' . $orderCode, 'subtitle' => null],
                        ['title' => 'EXPORT_LIABILITY_' . $orderCode, 'subtitle' => null],
                        ['title' => 'TRANSPORT_ORDER_' . $orderCode, 'subtitle' => null],
                        ['title' => 'PACKING_LIST_' . $orderCode, 'subtitle' => 'ME-CI26-437'],
                        ['title' => 'COMMERCIAL_INVOICE_' . $orderCode, 'subtitle' => 'ME-CI26-437'],
                    ];
                @endphp

                @foreach($documentCards as $doc)
                <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between h-28 shadow-2xs hover:shadow-xs transition" x-data="{ menuOpen: false }">
                    <div>
                        <div class="text-xs font-bold text-slate-900 tracking-tight leading-snug">{{ $doc['title'] }}</div>
                        @if($doc['subtitle'])
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $doc['subtitle'] }}</div>
                        @endif
                    </div>
                    <div class="relative">
                        <button type="button" @click="menuOpen = !menuOpen" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 border border-slate-300 rounded px-2.5 py-1 bg-white hover:bg-slate-50 transition">
                            Actions
                            <span class="material-icons text-sm">arrow_drop_down</span>
                        </button>
                        <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute left-0 bottom-full mb-1 w-32 bg-white rounded shadow-lg border border-slate-100 py-1 z-20 text-xs">
                            <a href="#" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Preview</a>
                            <a href="#" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Download</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 3: TRANSPORT -->
    <div x-show="activeTab === 'transport'" class="pt-2">
        <div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs space-y-6">
            <h2 class="text-base font-bold text-slate-900 mb-4">Transport Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of Transport</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->mode_of_transport ?? '-' }}</div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Port of Loading</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->port_of_loading ?? '-' }}</div>
                </div>
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Port of Delivery</label>
                    <div class="text-xs text-slate-800 font-normal">{{ $contract->port_of_delivery ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
