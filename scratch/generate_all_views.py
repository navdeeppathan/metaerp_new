import os

views_dir = r"c:\Users\navde\Downloads\metaerp\resources\views"

views = {}

# 1. contracts/board.blade.php
views["contracts/board.blade.php"] = """@extends('layouts.app')

@section('title', 'Contract Pipeline Board')

@section('content')
<div class="space-y-6" x-data="{ showNewModal: false, activeContract: null, showCardModal: false }">
    <!-- Header with Model Switcher & New Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">view_kanban</span>
                Contract Pipeline
            </h1>
            <p class="text-sm text-slate-500">Manage and track trade contracts across lifecycle stages</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(isset($models) && count($models) > 1)
            <div class="flex items-center bg-slate-100 p-1 rounded-lg">
                @foreach($models as $m)
                <a href="{{ route('contracts.board', ['modelId' => $m->id]) }}" 
                   class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all {{ (isset($currentModel) && $currentModel->id == $m->id) ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    {{ $m->name }}
                </a>
                @endforeach
            </div>
            @endif
            <button @click="showNewModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <span class="material-icons text-base">add</span>
                New Contract
            </button>
        </div>
    </div>

    <!-- Kanban Stages Horizontal Scroll Container -->
    <div class="flex gap-4 overflow-x-auto pb-6 pt-2 items-start" style="min-height: calc(100vh - 280px);">
        @forelse($stages as $stage)
        @php
            $stageContracts = $contracts->where('stage_id', $stage->id);
        @endphp
        <div class="flex-shrink-0 w-80 bg-slate-100/80 rounded-xl border border-slate-200/80 p-3 flex flex-col max-h-[calc(100vh-260px)]">
            <!-- Stage Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wide truncate max-w-[180px]" title="{{ $stage->name }}">
                        {{ $stage->name }}
                    </h3>
                </div>
                <span class="px-2 py-0.5 text-xs font-bold bg-white text-slate-600 rounded-full border border-slate-200 shadow-2xs">
                    {{ $stageContracts->count() }}
                </span>
            </div>

            <!-- Stage Cards Scrollable Column -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($stageContracts as $contract)
                <div class="bg-white p-3.5 rounded-lg border border-slate-200/90 shadow-2xs hover:shadow-md transition-all cursor-pointer group hover:border-blue-300"
                     @click="window.location.href='{{ route('contracts.search') }}?q={{ $contract->orderCode }}'">
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-bold text-blue-600 group-hover:underline">
                            {{ $contract->orderCode ?? ('CTR-' . $contract->id) }}
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider {{ $contract->type == 'BUY' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ $contract->type }}
                        </span>
                    </div>

                    <!-- Company & Contact Info -->
                    <div class="text-xs text-slate-700 font-medium truncate mb-1">
                        @if($contract->type == 'BUY')
                            <span class="text-slate-400">Seller:</span> {{ $contract->purchase?->metaCompany?->name ?? 'N/A' }}
                        @else
                            <span class="text-slate-400">Buyer:</span> {{ $contract->sale?->metaCompany?->name ?? 'N/A' }}
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500 truncate mb-2.5">
                        Attn: {{ $contract->type == 'BUY' ? ($contract->purchase?->contact?->name ?? 'N/A') : ($contract->sale?->contact?->name ?? 'N/A') }}
                    </div>

                    <!-- Products summary -->
                    @php
                        $dealProducts = $contract->type == 'BUY' ? $contract->purchase?->products : $contract->sale?->products;
                        $dealTotal = $contract->type == 'BUY' ? $contract->purchase?->total : $contract->sale?->total;
                        $curr = $contract->type == 'BUY' ? $contract->purchase?->currency?->currencySymbol : $contract->sale?->currency?->currencySymbol;
                    @endphp
                    @if($dealProducts && $dealProducts->isNotEmpty())
                    <div class="bg-slate-50 p-2 rounded text-[11px] text-slate-600 mb-2 border border-slate-100 flex items-center justify-between">
                        <span class="truncate max-w-[150px] font-medium">{{ $dealProducts->first()->product?->name ?? 'Product' }}</span>
                        <span class="font-bold text-slate-800">{{ number_format($dealTotal ?? 0, 2) }} {{ $curr ?? 'USD' }}</span>
                    </div>
                    @endif

                    <!-- Card Footer: Checklist and Comments counters -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1" title="Comments">
                                <span class="material-icons text-xs text-slate-400">chat_bubble_outline</span>
                                {{ $contract->comments?->count() ?? 0 }}
                            </span>
                            @php
                                $totalItems = $contract->checklists->flatMap->checkitems->count();
                                $doneItems = $contract->checklists->flatMap->checkitems->where('checked', true)->count();
                            @endphp
                            <span class="flex items-center gap-1" title="Checklist items done">
                                <span class="material-icons text-xs text-slate-400">check_circle_outline</span>
                                {{ $doneItems }}/{{ $totalItems }}
                            </span>
                        </div>
                        
                        <!-- Move Stage Dropdown -->
                        <div @click.stop class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" class="text-slate-400 hover:text-slate-600 p-0.5 rounded">
                                <span class="material-icons text-sm">more_vert</span>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute right-0 bottom-full mb-1 w-44 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-30 text-xs">
                                <div class="px-3 py-1 font-bold text-slate-400 text-[10px] uppercase">Move to Stage</div>
                                @foreach($stages as $targetStage)
                                @if($targetStage->id != $stage->id)
                                <form action="{{ route('contracts.move-stage', ['id' => $contract->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="stage_id" value="{{ $targetStage->id }}">
                                    <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 hover:text-blue-700 text-slate-700 flex items-center justify-between">
                                        <span class="truncate">{{ $targetStage->name }}</span>
                                        <span class="material-icons text-[14px]">arrow_forward</span>
                                    </button>
                                </form>
                                @endif
                                @endforeach
                                <div class="border-t border-slate-100 my-1"></div>
                                <form action="{{ route('contracts.cancel', ['id' => $contract->id]) }}" method="POST" onsubmit="return confirm('Cancel this contract?')">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-rose-50 text-rose-600 flex items-center justify-between">
                                        <span>Cancel Contract</span>
                                        <span class="material-icons text-[14px]">close</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="h-28 border-2 border-dashed border-slate-200 rounded-lg flex flex-col items-center justify-center text-slate-400 text-xs">
                    <span class="material-icons text-slate-300 text-xl mb-1">inbox</span>
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

    <!-- Create Contract Modal -->
    <div x-show="showNewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="showNewModal = false"></div>
            <div class="inline-block w-full max-w-xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-slate-200">
                <form action="{{ route('contracts.store') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-blue-600">post_add</span>
                            <h3 class="text-lg font-bold text-slate-800">Create New Contract</h3>
                        </div>
                        <button type="button" @click="showNewModal = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <!-- Contract Type -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contract Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center justify-center gap-2 p-3 border-2 rounded-xl cursor-pointer hover:border-blue-400 transition"
                                       :class="contractType === 'BUY' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800 font-bold' : 'border-slate-200 text-slate-700'"
                                       x-data="{ contractType: 'BUY' }">
                                    <input type="radio" name="type" value="BUY" checked class="hidden" @change="contractType = 'BUY'">
                                    <span class="material-icons text-emerald-600">arrow_downward</span>
                                    BUY (Purchase Order)
                                </label>
                                <label class="flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 transition">
                                    <input type="radio" name="type" value="SELL" class="hidden">
                                    <span class="material-icons text-blue-600">arrow_upward</span>
                                    SELL (Sales Contract)
                                </label>
                            </div>
                        </div>

                        <!-- Company & Contact -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company *</label>
                                <select name="meta_company_id" required class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contact Person *</label>
                                <select name="contact_id" required class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Contact</option>
                                    @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->name }} - {{ $contact->metaCompany?->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Primary Product *</label>
                            <select name="product_id" required class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity, Unit Price, Currency -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity (MT) *</label>
                                <input type="number" step="0.01" name="quantity" required placeholder="100.00" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unit Price *</label>
                                <input type="number" step="0.01" name="unit_price" required placeholder="2500.00" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Currency *</label>
                                <select name="currency_id" required class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ $c->currencyCode == 'USD' ? 'selected' : '' }}>{{ $c->currencyCode }} ({{ $c->currencySymbol }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Terms & Delivery -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Delivery Terms</label>
                                <input type="text" name="delivery_terms" value="CIF" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Payment Mode</label>
                                <input type="text" name="mode_of_payment" value="TT in Advance" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="showNewModal = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            Create Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 2. contracts/partials/card-modal.blade.php
views["contracts/partials/card-modal.blade.php"] = """<div class="bg-white rounded-xl shadow-xl max-w-4xl w-full overflow-hidden text-slate-800" x-data="{ tab: 'general' }">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
        <div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-0.5 text-xs font-bold rounded uppercase tracking-wider {{ $contract->type == 'BUY' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $contract->type }}
                </span>
                <h2 class="text-xl font-bold font-mono text-slate-800">{{ $contract->orderCode }}</h2>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-200 text-slate-700">
                    {{ $contract->stage?->name ?? 'Stage' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Status: <span class="font-semibold">{{ $contract->contractStatus }}</span> | Date: {{ $contract->date }}</p>
        </div>
        <button @click="showCardModal = false" class="text-slate-400 hover:text-slate-600">
            <span class="material-icons">close</span>
        </button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 bg-white px-6 overflow-x-auto text-xs font-semibold text-slate-500">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Buyer / Seller</button>
        <button @click="tab = 'deal'" :class="tab === 'deal' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Deal Details</button>
        <button @click="tab = 'packing'" :class="tab === 'packing' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Packing List</button>
        <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Generated Docs</button>
        <button @click="tab = 'attachments'" :class="tab === 'attachments' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Attachments</button>
        <button @click="tab = 'comments'" :class="tab === 'comments' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Comments ({{ $contract->comments?->count() ?? 0 }})</button>
        <button @click="tab = 'checklist'" :class="tab === 'checklist' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Checklist</button>
        <button @click="tab = 'actions'" :class="tab === 'actions' ? 'border-b-2 border-blue-600 text-blue-600' : 'hover:text-slate-800'" class="py-3 px-3 transition">Actions</button>
    </div>

    <!-- Tab Contents -->
    <div class="p-6 max-h-[60vh] overflow-y-auto text-sm">
        <!-- 1. General Buyer / Seller -->
        <div x-show="tab === 'general'" class="space-y-4">
            <div class="grid grid-cols-2 gap-6">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <h4 class="font-bold text-xs uppercase text-slate-500 mb-3">Purchase Side (Supplier)</h4>
                    <p class="font-semibold text-slate-800">{{ $contract->purchase?->metaCompany?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500 mt-1">Contact: {{ $contract->purchase?->contact?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">Email: {{ $contract->purchase?->contact?->email ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">Phone: {{ $contract->purchase?->contact?->phone ?? 'N/A' }}</p>
                    <div class="mt-3 pt-3 border-t border-slate-200 text-xs">
                        <span class="text-slate-400">Total:</span> 
                        <span class="font-bold text-slate-800">{{ number_format($contract->purchase?->total ?? 0, 2) }} {{ $contract->purchase?->currency?->currencySymbol }}</span>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <h4 class="font-bold text-xs uppercase text-slate-500 mb-3">Sale Side (Customer)</h4>
                    <p class="font-semibold text-slate-800">{{ $contract->sale?->metaCompany?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500 mt-1">Contact: {{ $contract->sale?->contact?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">Email: {{ $contract->sale?->contact?->email ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500">Phone: {{ $contract->sale?->contact?->phone ?? 'N/A' }}</p>
                    <div class="mt-3 pt-3 border-t border-slate-200 text-xs">
                        <span class="text-slate-400">Total:</span> 
                        <span class="font-bold text-slate-800">{{ number_format($contract->sale?->total ?? 0, 2) }} {{ $contract->sale?->currency?->currencySymbol }}</span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 pt-2">
                <div class="bg-white p-3 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-400 block">Delivery Terms</span>
                    <span class="font-semibold text-slate-700">{{ $contract->deliveryTerms ?? 'CIF' }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-400 block">Payment Mode</span>
                    <span class="font-semibold text-slate-700">{{ $contract->modeOfPayment ?? 'TT in Advance' }}</span>
                </div>
                <div class="bg-white p-3 rounded-lg border border-slate-200">
                    <span class="text-xs text-slate-400 block">Customer PO</span>
                    <span class="font-semibold text-slate-700">{{ $contract->customerPurchaseOrder ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Deal Details -->
        <div x-show="tab === 'deal'" class="space-y-4">
            <h4 class="font-bold text-sm text-slate-700">Contract Items & Pricing</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 uppercase font-bold">
                        <tr>
                            <th class="p-3">Type</th>
                            <th class="p-3">Product</th>
                            <th class="p-3 text-right">Quantity (MT)</th>
                            <th class="p-3 text-right">Unit Price</th>
                            <th class="p-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($contract->purchase)
                        @foreach($contract->purchase->products as $p)
                        <tr>
                            <td class="p-3 font-bold text-emerald-600">PURCHASE</td>
                            <td class="p-3 font-medium">{{ $p->product?->name }} ({{ $p->product?->code }})</td>
                            <td class="p-3 text-right font-mono">{{ number_format($p->quantity, 2) }}</td>
                            <td class="p-3 text-right font-mono">{{ number_format($p->unitPrice, 2) }} {{ $p->currency?->currencySymbol }}</td>
                            <td class="p-3 text-right font-mono font-bold">{{ number_format($p->total, 2) }}</td>
                        </tr>
                        @endforeach
                        @endif
                        @if($contract->sale)
                        @foreach($contract->sale->products as $s)
                        <tr>
                            <td class="p-3 font-bold text-blue-600">SALE</td>
                            <td class="p-3 font-medium">{{ $s->product?->name }} ({{ $s->product?->code }})</td>
                            <td class="p-3 text-right font-mono">{{ number_format($s->quantity, 2) }}</td>
                            <td class="p-3 text-right font-mono">{{ number_format($s->unitPrice, 2) }} {{ $s->currency?->currencySymbol }}</td>
                            <td class="p-3 text-right font-mono font-bold">{{ number_format($s->total, 2) }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Packing List -->
        <div x-show="tab === 'packing'" class="space-y-4">
            <h4 class="font-bold text-sm text-slate-700">Packing List & Weight Specifications</h4>
            @forelse($contract->packingLists as $pl)
            <div class="border border-slate-200 rounded-lg p-3 bg-slate-50 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-xs text-slate-700">{{ $pl->name }}</span>
                    <span class="text-xs text-slate-500">Gross: {{ $pl->grossWeight }} MT | Net: {{ $pl->netWeight }} MT</span>
                </div>
                <div class="space-y-1">
                    @foreach($pl->packs as $pack)
                    <div class="flex items-center justify-between text-xs bg-white p-2 rounded border border-slate-100">
                        <span>Pack #{{ $pack->packNumber }} ({{ $pack->packType?->name }})</span>
                        <span class="font-mono text-slate-600">Net: {{ $pack->netWeight }} kg</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-4 text-center">No packing lists registered for this contract.</p>
            @endforelse
        </div>

        <!-- 4. Generated Documents -->
        <div x-show="tab === 'documents'" class="space-y-4">
            <div class="flex items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <h5 class="font-bold text-xs uppercase text-slate-700">Generate New Document</h5>
                    <p class="text-xs text-slate-500">Select a template to generate contract PDF / HTML document</p>
                </div>
                <form action="{{ route('contracts.generate-doc', ['contractId' => $contract->id]) }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="template_id" required class="text-xs border-slate-200 rounded-lg">
                        <option value="">Select Template</option>
                        @foreach($templates ?? [] as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700">
                        Generate
                    </button>
                </form>
            </div>

            <div class="space-y-2">
                @forelse($contract->generatedFiles as $file)
                <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-rose-500 text-base">picture_as_pdf</span>
                        <span class="font-semibold text-slate-700">{{ $file->fileName }}</span>
                        <span class="text-slate-400">({{ $file->template?->name }})</span>
                    </div>
                    <span class="text-slate-400">{{ $file->created_at }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No documents generated yet.</p>
                @endforelse
            </div>
        </div>

        <!-- 5. Attachments -->
        <div x-show="tab === 'attachments'" class="space-y-4">
            <div class="space-y-2">
                @forelse($contract->uploadedFiles as $file)
                <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-lg text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-blue-500 text-base">attach_file</span>
                        <span class="font-semibold text-slate-700">{{ $file->fileName }}</span>
                    </div>
                    <span class="text-slate-400">{{ $file->created_at }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No uploaded files.</p>
                @endforelse
            </div>
        </div>

        <!-- 6. Comments -->
        <div x-show="tab === 'comments'" class="space-y-4">
            <div class="space-y-3">
                @forelse($contract->comments as $comment)
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                    <div class="flex items-center justify-between text-slate-500 mb-1">
                        <span class="font-bold text-slate-700">{{ $comment->user?->name ?? 'User' }}</span>
                        <span>{{ $comment->created_at }}</span>
                    </div>
                    <p class="text-slate-800">{{ $comment->message }}</p>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No comments yet. Start the conversation!</p>
                @endforelse
            </div>

            <form action="{{ route('contracts.add-comment', ['id' => $contract->id]) }}" method="POST" class="pt-2">
                @csrf
                <textarea name="message" required rows="2" placeholder="Write a comment..." class="w-full text-xs border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                <div class="mt-2 text-right">
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700">
                        Post Comment
                    </button>
                </div>
            </form>
        </div>

        <!-- 7. Checklist -->
        <div x-show="tab === 'checklist'" class="space-y-3">
            @forelse($contract->checklists as $cl)
            <div class="border border-slate-200 rounded-lg p-3">
                <h5 class="font-bold text-xs text-slate-700 mb-2">{{ $cl->name }}</h5>
                <div class="space-y-1.5">
                    @foreach($cl->checkitems as $item)
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" {{ $item->checked ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="{{ $item->checked ? 'line-through text-slate-400' : '' }}">{{ $item->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-4 text-center">No checklists assigned.</p>
            @endforelse
        </div>

        <!-- 8. Actions -->
        <div x-show="tab === 'actions'" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <h5 class="font-bold text-xs uppercase text-slate-700 mb-2">Stage Transition</h5>
                    <form action="{{ route('contracts.move-stage', ['id' => $contract->id]) }}" method="POST" class="space-y-3">
                        @csrf
                        <select name="stage_id" class="w-full text-xs border-slate-200 rounded-lg">
                            @foreach($stages as $st)
                            <option value="{{ $st->id }}" {{ $contract->stage_id == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg">
                            Update Stage
                        </button>
                    </form>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <h5 class="font-bold text-xs uppercase text-slate-700 mb-2">Contract Lifecycle</h5>
                    <form action="{{ route('contracts.close', ['id' => $contract->id]) }}" method="POST" onsubmit="return confirm('Close contract? This requires all purchased goods to be associated.')">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg">
                            Close Contract (Complete)
                        </button>
                    </form>
                    <form action="{{ route('contracts.cancel', ['id' => $contract->id]) }}" method="POST" onsubmit="return confirm('Cancel this contract?')">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg">
                            Cancel Contract
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
"""

# 3. contracts/search.blade.php
views["contracts/search.blade.php"] = """@extends('layouts.app')

@section('title', 'Find Contracts')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-600">search</span>
            Find Contracts
        </h1>

        <!-- Search & Filter Form -->
        <form action="{{ route('contracts.search') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Keywords</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Order Code, PO, Contact..." class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
                <select name="status" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                    <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                    <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Type</label>
                <select name="type" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="BUY" {{ request('type') == 'BUY' ? 'selected' : '' }}>BUY</option>
                    <option value="SELL" {{ request('type') == 'SELL' ? 'selected' : '' }}>SELL</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm">
                    Filter
                </button>
                <a href="{{ route('contracts.search') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm rounded-lg">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Search Results Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-4">Order Code</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Stage</th>
                        <th class="p-4">Company / Counterparty</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contracts as $c)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-mono font-bold text-blue-600">{{ $c->orderCode }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded uppercase {{ $c->type == 'BUY' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $c->type }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $c->stage?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-slate-800">
                            {{ $c->type == 'BUY' ? ($c->purchase?->metaCompany?->name ?? 'N/A') : ($c->sale?->metaCompany?->name ?? 'N/A') }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $c->type == 'BUY' ? ($c->purchase?->contact?->name ?? 'N/A') : ($c->sale?->contact?->name ?? 'N/A') }}
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded {{ $c->contractStatus == 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : ($c->contractStatus == 'CLOSED' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-800') }}">
                                {{ $c->contractStatus }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500">{{ $c->date ?? $c->created_at->format('Y-m-d') }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('contracts.board') }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                View in Board
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            No contracts found matching your search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $contracts->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
"""

# 4. offers/index.blade.php
views["offers/index.blade.php"] = """@extends('layouts.app')

@section('title', 'Price Offers')

@section('content')
<div class="space-y-6" x-data="{ showNewOfferModal: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">request_quote</span>
                Price Offers
            </h1>
            <p class="text-sm text-slate-500">Track and manage inbound and outbound price quotations</p>
        </div>
        <button @click="showNewOfferModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">add</span>
            New Offer
        </button>
    </div>

    <!-- Offers Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-4">Offer Code</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Company</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Product</th>
                        <th class="p-4 text-right">Quantity (MT)</th>
                        <th class="p-4 text-right">Unit Price</th>
                        <th class="p-4 text-right">Total</th>
                        <th class="p-4">Valid Until</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($offers as $offer)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-mono font-bold text-blue-600">{{ $offer->orderCode }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded uppercase {{ $offer->type == 'BUY' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $offer->type }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-slate-800">{{ $offer->metaCompany?->name ?? 'N/A' }}</td>
                        <td class="p-4 text-slate-600">{{ $offer->contact?->name ?? 'N/A' }}</td>
                        <td class="p-4 font-semibold text-slate-700">{{ $offer->product?->name ?? 'N/A' }}</td>
                        <td class="p-4 text-right font-mono">{{ number_format($offer->quantity, 2) }}</td>
                        <td class="p-4 text-right font-mono">{{ number_format($offer->unitPrice, 2) }} {{ $offer->currency?->currencySymbol }}</td>
                        <td class="p-4 text-right font-mono font-bold">{{ number_format($offer->total, 2) }}</td>
                        <td class="p-4 text-xs text-slate-500">{{ $offer->validUntil ?? 'N/A' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded {{ $offer->status == 'OPEN' ? 'bg-amber-100 text-amber-800' : ($offer->status == 'CONVERTED' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $offer->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @if($offer->status == 'OPEN')
                            <form action="{{ route('offers.convert', ['id' => $offer->id]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded shadow-xs">
                                    Convert
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="p-8 text-center text-slate-400">
                            No price offers registered.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $offers->links() }}
        </div>
        @endif
    </div>

    <!-- New Offer Modal -->
    <div x-show="showNewOfferModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="showNewOfferModal = false"></div>
            <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-slate-200">
                <form action="{{ route('offers.store') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-blue-600">post_add</span>
                            <h3 class="text-lg font-bold text-slate-800">Create New Offer</h3>
                        </div>
                        <button type="button" @click="showNewOfferModal = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Offer Type *</label>
                            <select name="type" required class="w-full text-sm border-slate-200 rounded-lg">
                                <option value="BUY">BUY Offer (Supplier Quote)</option>
                                <option value="SELL">SELL Offer (Customer Quote)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company *</label>
                                <select name="meta_company_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contact *</label>
                                <select name="contact_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                    <option value="">Select Contact</option>
                                    @foreach($contacts as $cnt)
                                    <option value="{{ $cnt->id }}">{{ $cnt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product *</label>
                            <select name="product_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                <option value="">Select Product</option>
                                @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity (MT)</label>
                                <input type="number" step="0.01" name="quantity" required placeholder="50.00" class="w-full text-sm border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unit Price</label>
                                <input type="number" step="0.01" name="unitPrice" required placeholder="2300.00" class="w-full text-sm border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Currency</label>
                                <select name="currency_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                    @foreach($currencies as $c)
                                    <option value="{{ $c->id }}">{{ $c->currencyCode }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Valid Until</label>
                            <input type="date" name="validUntil" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" @click="showNewOfferModal = false" class="px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">Save Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 5. companies/index.blade.php & show.blade.php
views["companies/index.blade.php"] = """@extends('layouts.app')

@section('title', 'Companies')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">business</span>
                Companies
            </h1>
            <p class="text-sm text-slate-500">Manage trading partner entities, corporate addresses, and accounts</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">add</span>
            Add Company
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">Company Name</th>
                    <th class="p-4">Code</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($companies as $company)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-bold text-slate-800">{{ $company->name }}</td>
                    <td class="p-4 font-mono text-xs text-slate-500">{{ $company->code }}</td>
                    <td class="p-4 text-slate-600">{{ $company->email ?? 'N/A' }}</td>
                    <td class="p-4 text-slate-600">{{ $company->phone ?? 'N/A' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $company->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $company->active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('companies.show', ['id' => $company->id]) }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                            View Profile
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-slate-400">No companies registered.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($companies->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $companies->links() }}</div>
        @endif
    </div>

    <!-- Create Company Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('companies.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">New Company</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company Code *</label>
                            <input type="text" name="code" required placeholder="e.g. MENON" class="w-full text-sm border-slate-200 rounded-lg uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                            <input type="email" name="email" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" name="phone" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

views["companies/show.blade.php"] = """@extends('layouts.app')

@section('title', $company->name . ' - Company Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                {{ substr($company->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $company->name }}</h1>
                <p class="text-sm text-slate-500 font-mono">Code: {{ $company->code }} | VAT: {{ $company->vatNumber ?? 'N/A' }}</p>
            </div>
        </div>
        <a href="{{ route('companies.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg">
            Back to Companies
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Details Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Company Information</h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400">Email:</span> <span class="font-medium text-slate-700">{{ $company->email ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Phone:</span> <span class="font-medium text-slate-700">{{ $company->phone ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Website:</span> <span class="font-medium text-slate-700">{{ $company->website ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Status:</span> <span class="font-bold text-emerald-600">{{ $company->active ? 'ACTIVE' : 'INACTIVE' }}</span></div>
            </div>
        </div>

        <!-- Contacts Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm md:col-span-2 space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Associated Contacts</h3>
            <div class="space-y-2">
                @forelse($company->contacts as $contact)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg text-xs">
                    <div>
                        <p class="font-bold text-slate-800">{{ $contact->name }}</p>
                        <p class="text-slate-500">{{ $contact->email }} | {{ $contact->phone }}</p>
                    </div>
                    <a href="{{ route('contacts.show', ['id' => $contact->id]) }}" class="text-blue-600 font-semibold">View</a>
                </div>
                @empty
                <p class="text-xs text-slate-400">No contacts linked to this company.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 6. contacts/index.blade.php & show.blade.php
views["contacts/index.blade.php"] = """@extends('layouts.app')

@section('title', 'Contacts Directory')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">contacts</span>
                Contacts Directory
            </h1>
            <p class="text-sm text-slate-500">Manage buyer, seller, and logistics representative contacts</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">person_add</span>
            Add Contact
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Company</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contacts as $contact)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-bold text-slate-800">{{ $contact->name }}</td>
                    <td class="p-4 text-slate-600">{{ $contact->metaCompany?->name ?? 'N/A' }}</td>
                    <td class="p-4 text-slate-600">{{ $contact->email ?? 'N/A' }}</td>
                    <td class="p-4 text-slate-600">{{ $contact->phone ?? 'N/A' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $contact->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $contact->active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('contacts.show', ['id' => $contact->id]) }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">
                            View Contact
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-slate-400">No contacts registered.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($contacts->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $contacts->links() }}</div>
        @endif
    </div>

    <!-- Create Contact Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('contacts.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">New Contact</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company *</label>
                            <select name="meta_company_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                <option value="">Select Company</option>
                                @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                            <input type="email" name="email" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" name="phone" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

views["contacts/show.blade.php"] = """@extends('layouts.app')

@section('title', $contact->name . ' - Contact Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $contact->name }}</h1>
            <p class="text-sm text-slate-500">Company: <a href="{{ route('companies.show', ['id' => $contact->metaCompany?->id ?? 0]) }}" class="text-blue-600 underline">{{ $contact->metaCompany?->name ?? 'N/A' }}</a></p>
        </div>
        <a href="{{ route('contacts.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg">
            Back to Contacts
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Contact Details</h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400">Email:</span> <span class="font-medium text-slate-700">{{ $contact->email ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Phone:</span> <span class="font-medium text-slate-700">{{ $contact->phone ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Mobile:</span> <span class="font-medium text-slate-700">{{ $contact->mobile ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Department:</span> <span class="font-medium text-slate-700">{{ $contact->department?->name ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm md:col-span-2 space-y-3">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Assigned Products & Roles</h3>
            <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                    @forelse($contact->types as $t)
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-bold text-xs rounded-full border border-blue-200">
                        {{ $t->name }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400">No types assigned.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 7. products/index.blade.php
views["products/index.blade.php"] = """@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">inventory_2</span>
                Product Catalog
            </h1>
            <p class="text-sm text-slate-500">Manage commodities, metal specifications, and packaging profiles</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">add_box</span>
            Add Product
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">Code</th>
                    <th class="p-4">Name</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-mono font-bold text-blue-600">{{ $product->code }}</td>
                    <td class="p-4 font-bold text-slate-800">{{ $product->name }}</td>
                    <td class="p-4 text-xs font-semibold text-slate-600">{{ $product->category ?? 'Metal / Scrap' }}</td>
                    <td class="p-4 text-xs text-slate-500">{{ $product->description ?? 'N/A' }}</td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $product->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $product->active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-400">No products configured.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $products->links() }}</div>
        @endif
    </div>

    <!-- Create Product Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">New Product</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Code *</label>
                            <input type="text" name="code" required placeholder="e.g. FERROUS" class="w-full text-sm border-slate-200 rounded-lg uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category</label>
                            <input type="text" name="category" placeholder="e.g. Metals" class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 8. users/index.blade.php
views["users/index.blade.php"] = """@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">manage_accounts</span>
                User Management
            </h1>
            <p class="text-sm text-slate-500">Manage internal operators, roles, 2FA credentials, and system access</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">person_add</span>
            Add User
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Access Group</th>
                    <th class="p-4">2FA Status</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-bold text-slate-800 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                            {{ substr($user->displayName ?? $user->name, 0, 1) }}
                        </div>
                        <div>
                            <div>{{ $user->displayName ?? $user->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $user->username }}</div>
                        </div>
                    </td>
                    <td class="p-4 text-slate-600">{{ $user->email }}</td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $user->accessGroup?->name ?? 'Default' }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $user->is2fa ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $user->is2fa ? 'ENABLED' : 'DISABLED' }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $user->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $user->active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $users->links() }}</div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">Create New User</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username *</label>
                            <input type="text" name="username" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email *</label>
                            <input type="email" name="email" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password *</label>
                            <input type="password" name="password" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Access Group *</label>
                            <select name="access_group_id" required class="w-full text-sm border-slate-200 rounded-lg">
                                @foreach($groups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 9. accessgroups/index.blade.php
views["accessgroups/index.blade.php"] = """@extends('layouts.app')

@section('title', 'Access Groups & Permissions')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">security</span>
                Access Groups
            </h1>
            <p class="text-sm text-slate-500">Configure role-based access control, stage permissions, and features</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">add_moderator</span>
            New Access Group
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($groups as $group)
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg text-slate-800">{{ $group->name }}</h3>
                <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $group->users?->count() ?? 0 }} Members
                </span>
            </div>
            <p class="text-xs text-slate-500">{{ $group->description ?? 'No description provided' }}</p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Active: {{ $group->active ? 'Yes' : 'No' }}</span>
                <span class="font-mono">ID: #{{ $group->id }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-2 p-8 text-center text-slate-400 bg-white rounded-xl border">No access groups defined.</div>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('accessgroups.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">New Access Group</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Group Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full text-sm border-slate-200 rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
"""

# 10. profile/index.blade.php
views["profile/index.blade.php"] = """@extends('layouts.app')

@section('title', 'My Profile & Security')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <span class="material-icons text-blue-600">badge</span>
            Account & Security Settings
        </h1>
        <p class="text-sm text-slate-500">Update personal information, manage 2FA security credentials</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Info -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Profile Details</h3>
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ $user->displayName ?? $user->name }}" required class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" required class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">New Password (leave empty to keep current)</label>
                    <input type="password" name="password" class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- 2FA Authentication -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2 flex items-center justify-between">
                <span>Two-Factor Authentication (TOTP)</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $user->is2fa ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $user->is2fa ? 'ACTIVE' : 'INACTIVE' }}
                </span>
            </h3>

            <p class="text-xs text-slate-600">
                Two-factor authentication adds an extra layer of security to your account using Google Authenticator or Microsoft Authenticator.
            </p>

            @if(!$user->is2fa)
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                <p class="text-xs font-bold text-slate-700">Setup 2FA:</p>
                <div class="text-xs text-slate-500 font-mono break-all bg-white p-2 border rounded">
                    Secret: {{ $secret ?? 'JBSWY3DPEHPK3PXP' }}
                </div>
                <form action="{{ route('profile.toggle-2fa') }}" method="POST">
                    @csrf
                    <input type="hidden" name="secret" value="{{ $secret ?? 'JBSWY3DPEHPK3PXP' }}">
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                        Enable Two-Factor Authentication
                    </button>
                </form>
            </div>
            @else
            <form action="{{ route('profile.toggle-2fa') }}" method="POST" onsubmit="return confirm('Disable 2FA?')">
                @csrf
                <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg">
                    Disable Two-Factor Authentication
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
"""

# 11. dashboards/analytics.blade.php
views["dashboards/analytics.blade.php"] = """@extends('layouts.app')

@section('title', 'Analytics & Reporting')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">query_stats</span>
                Metabase Analytics & BI
            </h1>
            <p class="text-sm text-slate-500">Embedded business intelligence, trading volume insights, and operational metrics</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-semibold text-slate-600">Live BI Sync</span>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Active Contracts</span>
                <span class="material-icons text-blue-600">description</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 font-mono">{{ $activeContracts ?? 12 }}</div>
            <div class="text-xs text-emerald-600 font-semibold mt-1">↑ Active in Pipeline</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Trading Volume</span>
                <span class="material-icons text-emerald-600">trending_up</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 font-mono">$1,450,800</div>
            <div class="text-xs text-slate-400 mt-1">USD equivalent</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Open Offers</span>
                <span class="material-icons text-amber-600">request_quote</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 font-mono">{{ $openOffers ?? 5 }}</div>
            <div class="text-xs text-amber-600 font-semibold mt-1">Pending conversion</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Trading Partners</span>
                <span class="material-icons text-purple-600">business</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 font-mono">{{ $companyCount ?? 28 }}</div>
            <div class="text-xs text-slate-400 mt-1">Active verified accounts</div>
        </div>
    </div>

    <!-- Metabase Embed Frame Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <span class="material-icons text-base text-blue-600">analytics</span>
                Trading Operations Dashboard
            </h3>
            <span class="text-xs text-slate-400">HMAC-SHA256 Signed Session</span>
        </div>
        <div class="p-6">
            @if(isset($embedUrl) && $embedUrl)
            <iframe src="{{ $embedUrl }}" frameborder="0" width="100%" height="650" allowtransparency class="rounded-lg border"></iframe>
            @else
            <div class="h-96 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center text-center p-8 bg-slate-50">
                <span class="material-icons text-4xl text-blue-500 mb-3">dashboard</span>
                <h4 class="font-bold text-base text-slate-800 mb-1">Metabase Embedded BI Dashboard</h4>
                <p class="text-xs text-slate-500 max-w-md mb-4">
                    Metabase JWT embedding is enabled. Configure <code>METABASE_SITE_URL</code> and <code>METABASE_SECRET_KEY</code> in your <code>.env</code> file to embed interactive dashboards.
                </p>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-mono rounded">
                    JWT Provider: App\\Services\\MetabaseEmbedService
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
"""

# 12. Settings views
settings_views = ["countries", "currencies", "departments", "pack-types", "payment-types", "payment-terms"]
for sv in settings_views:
    title = sv.replace("-", " ").title()
    views[f"settings/{sv}.blade.php"] = f"""@extends('layouts.app')

@section('title', '{title} Configuration')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">tune</span>
                {title}
            </h1>
            <p class="text-sm text-slate-500">Configure global lookup reference data for ERP transactions</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Name / Title</th>
                    <th class="p-4">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-mono text-xs text-slate-400">#{{{{ $item->id }}}}</td>
                    <td class="p-4 font-bold text-slate-800">{{{{ $item->name ?? $item->currencyCode ?? $item->countryName ?? 'Item' }}}}</td>
                    <td class="p-4 text-xs text-slate-500">{{{{ $item->created_at ?? 'System Seed' }}}}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-8 text-center text-slate-400">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($items, 'links') && $items->hasPages())
        <div class="p-4 border-t border-slate-200">{{{{ $items->links() }}}}</div>
        @endif
    </div>
</div>
@endsection
"""

# Write all views to disk
for path, content in views.items():
    full_path = os.path.join(views_dir, path)
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    with open(full_path, "w", encoding="utf-8") as f:
        f.write(content)
    print(f"Created: {path}")

print("All Blade views created successfully!")
