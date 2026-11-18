@extends('layouts.app')

@section('title', 'Detached Notes')

@section('content')
<div class="space-y-6" x-data="detachedNotesManager()">
    <!-- Header with Title, Contract Code Combobox & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#0f172a] tracking-tight">Detached Notes</h1>
            @if($selectedContract)
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-slate-500">Filtered by contract:</span>
                <span class="font-bold text-xs px-2.5 py-0.5 rounded-full {{ $selectedContract->type == 'SELL' ? 'bg-[#F57C00] text-slate-950' : ($selectedContract->type == 'BUY' ? 'bg-[#81C784] text-slate-950' : 'bg-slate-200 text-slate-800') }}">
                    {{ $selectedContract->orderCode }}
                </span>
                <button type="button" @click="clearContract()" class="text-slate-400 hover:text-slate-700 text-xs font-semibold flex items-center gap-0.5 ml-1">
                    <span class="material-icons text-sm leading-none">close</span>
                    <span>Clear filter</span>
                </button>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <!-- Contract Code Search / Dropdown Pill -->
            <div class="relative w-64 sm:w-72" @click.outside="dropdownOpen = false">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input type="text" 
                       x-model="contractQuery" 
                       @focus="dropdownOpen = true"
                       @input="dropdownOpen = true"
                       placeholder="type a contract code" 
                       class="w-full bg-white border border-slate-200 rounded-full pl-9 pr-14 py-2 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-2xs transition">
                
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                    <template x-if="contractQuery">
                        <button type="button" @click="clearContract()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                            <span class="material-icons text-xs leading-none">close</span>
                        </button>
                    </template>
                    <button type="button" @click="dropdownOpen = !dropdownOpen" class="text-slate-400 hover:text-slate-600 focus:outline-none flex items-center">
                        <span class="material-icons text-sm leading-none">arrow_drop_down</span>
                    </button>
                </div>

                <!-- Dropdown List of Contracts -->
                <div x-show="dropdownOpen" 
                     x-cloak
                     x-transition
                     class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto z-40 py-1 text-xs">
                    <template x-for="c in filteredContracts" :key="c.id">
                        <div @click="selectContract(c)" 
                             class="px-3.5 py-2 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-slate-700 hover:text-indigo-600 transition-colors">
                            <span class="font-medium" x-text="c.orderCode"></span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                                  :class="c.type === 'BUY' ? 'bg-[#81C784] text-slate-950' : (c.type === 'SELL' ? 'bg-[#F57C00] text-slate-950' : 'bg-slate-100 text-slate-700')"
                                  x-text="c.type"></span>
                        </div>
                    </template>
                    <div x-show="filteredContracts.length === 0" class="px-3.5 py-2 text-slate-400 text-center text-xs">
                        No contract codes found
                    </div>
                </div>
            </div>

            <!-- + Add Button -->
            <button type="button"
                    @click="openAddModal()"
                    class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-2 rounded-full font-semibold text-xs shadow-2xs transition cursor-pointer select-none whitespace-nowrap flex items-center gap-1.5">
                <span class="text-sm font-bold leading-none">+</span>
                <span>Add</span>
            </button>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 min-h-[460px] flex flex-col justify-start overflow-hidden">
        @if($detachedNotes->isEmpty())
            <!-- Empty State matching screenshot: 'There are no detachedNotes!' -->
            <div class="flex-1 flex flex-col items-center justify-center p-16 select-none min-h-[420px]">
                <span class="text-slate-400 font-normal text-base sm:text-lg">
                    There are no detachedNotes!
                </span>
                @if($selectedContract)
                <button type="button" @click="clearContract()" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Show all detached notes
                </button>
                @endif
            </div>
        @else
            <!-- Detached Notes Table if records exist -->
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-800 font-bold text-xs uppercase tracking-wider">
                            <th class="py-3 px-4">Contract</th>
                            <th class="py-3 px-4">Kind</th>
                            <th class="py-3 px-4">Complement / Notes</th>
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Items</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4 text-center">Document</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($detachedNotes as $note)
                        @php
                            $cCompany = $note->contract?->type == 'BUY' 
                                ? ($note->contract?->purchase?->metaCompany?->name ?? 'MetaZn LTD') 
                                : ($note->contract?->sale?->metaCompany?->name ?? 'Metaenterprices LTD');
                            $cContact = $note->contract?->type == 'BUY' 
                                ? ($note->contract?->purchase?->contact?->name ?? '-') 
                                : ($note->contract?->sale?->contact?->name ?? '-');
                            $cCurrency = $note->contract?->sale?->currency?->currencySymbol 
                                ?? $note->contract?->purchase?->currency?->currencySymbol 
                                ?? '$';
                            $itemsCount = $note->details->count();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition cursor-pointer" @click="viewNoteDetails({{ $note->id }})">
                            <!-- Contract Order Code -->
                            <td class="py-3.5 px-4">
                                @if($note->contract)
                                <a href="{{ route('contracts.board') }}" class="font-bold text-slate-900 hover:text-indigo-600" @click.stop>
                                    {{ $note->contract->orderCode }}
                                </a>
                                @else
                                <span class="text-slate-400 font-mono">Contract #{{ $note->contract_id }}</span>
                                @endif
                            </td>

                            <!-- Kind Badge -->
                            <td class="py-3.5 px-4">
                                @if($note->kind == 'CREDIT')
                                <span class="bg-[#81C784] text-slate-950 font-semibold text-[11px] px-2.5 py-0.5 rounded-full select-none inline-block">
                                    CREDIT
                                </span>
                                @else
                                <span class="bg-[#F57C00] text-slate-950 font-semibold text-[11px] px-2.5 py-0.5 rounded-full select-none inline-block">
                                    DEBIT
                                </span>
                                @endif
                            </td>

                            <!-- Complement / Reason -->
                            <td class="py-3.5 px-4 max-w-xs truncate" title="{{ $note->complement }}">
                                {{ $note->complement ?: '-' }}
                            </td>

                            <!-- Company -->
                            <td class="py-3.5 px-4 font-medium text-slate-900">{{ $cCompany }}</td>

                            <!-- Contact -->
                            <td class="py-3.5 px-4">{{ $cContact }}</td>

                            <!-- Items -->
                            <td class="py-3.5 px-4">
                                <span class="bg-slate-100 text-slate-700 text-[11px] font-medium px-2 py-0.5 rounded-md border border-slate-200">
                                    {{ $itemsCount }} {{ $itemsCount === 1 ? 'item' : 'items' }}
                                </span>
                            </td>

                            <!-- Total -->
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $cCurrency }} {{ number_format($note->total, 2) }}
                            </td>

                            <!-- Document -->
                            <td class="py-3.5 px-4 text-center" @click.stop>
                                @if($note->generated_file_id)
                                <a href="{{ route('detached-notes.document', $note->id) }}" target="_blank"
                                   class="inline-flex items-center text-indigo-600 hover:text-indigo-800 p-1 rounded hover:bg-indigo-50 transition"
                                   title="View / Download Document">
                                    <span class="material-icons text-base">picture_as_pdf</span>
                                </a>
                                @else
                                <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap" @click.stop>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click="viewNoteDetails({{ $note->id }})" 
                                            class="text-slate-500 hover:text-indigo-600 p-1.5 rounded-md hover:bg-slate-100 transition" title="View Details">
                                        <span class="material-icons text-base">visibility</span>
                                    </button>

                                    <form action="{{ route('detached-notes.destroy', $note->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-md hover:bg-rose-50 transition" title="Delete">
                                            <span class="material-icons text-base">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Add Detached Note Modal -->
    <div x-show="showAddModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showAddModal = false"
             class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden border border-slate-100 animate-in fade-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">New Detached Note</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Create a credit or debit note attached to a contract</p>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <span class="material-icons text-xl">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('detached-notes.store') }}" method="POST" class="p-6 space-y-5">
                @csrf

                <!-- Contract & Kind Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Contract Selector -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Contract *</label>
                        <select name="contract_id" x-model="modalContractId" @change="onModalContractChange()" required class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer">
                            <option value="">Select a contract...</option>
                            <template x-for="c in contracts" :key="c.id">
                                <option :value="c.id" x-text="c.orderCode + ' (' + c.type + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Kind Selector -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Note Kind *</label>
                        <select name="kind" required class="w-full bg-transparent text-xs text-slate-800 font-semibold focus:outline-none cursor-pointer">
                            <option value="CREDIT">CREDIT NOTE</option>
                            <option value="DEBIT">DEBIT NOTE</option>
                        </select>
                    </div>
                </div>

                <!-- General Complement / Notes -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">General Description / Remarks</label>
                    <textarea name="complement" rows="2" placeholder="e.g. Final LME as agreed on 06/03/2026..." 
                              class="w-full bg-transparent text-xs text-slate-800 focus:outline-none resize-none"></textarea>
                </div>

                <!-- Line Items Section -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Detail Line Items</span>
                        <button type="button" @click="addLineItem()" 
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 cursor-pointer">
                            <span class="material-icons text-sm leading-none">add</span>
                            <span>Add Item</span>
                        </button>
                    </div>

                    <!-- Items Repeater List -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(item, index) in lineItems" :key="index">
                            <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200 space-y-2.5 relative group">
                                <button type="button" @click="removeLineItem(index)" 
                                        x-show="lineItems.length > 1"
                                        class="absolute top-2 right-2 text-slate-400 hover:text-rose-600 p-1" title="Remove Item">
                                    <span class="material-icons text-base">delete</span>
                                </button>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pr-6">
                                    <!-- Credit / Debit Type Reason -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] text-slate-500 font-medium">Reason / Type *</label>
                                        <select :name="'details[' + index + '][credit_debit_type_id]'" x-model="item.credit_debit_type_id" required class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                                            <option value="">Select Reason...</option>
                                            <template x-for="t in creditDebitTypes" :key="t.id">
                                                <option :value="t.id" x-text="t.description"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Product -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] text-slate-500 font-medium">Product</label>
                                        <select :name="'details[' + index + '][deal_product_id]'" x-model="item.deal_product_id" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                                            <option value="">Select Contract Product...</option>
                                            <template x-for="p in contractProducts" :key="p.id">
                                                <option :value="p.id" x-text="p.code + ' - ' + p.name + ' (' + p.deal_type + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <!-- Container -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] text-slate-500 font-medium">Container</label>
                                        <input type="text" :name="'details[' + index + '][container]'" x-model="item.container" placeholder="e.g. MSMU1860791" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                                    </div>

                                    <!-- Amount -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] text-slate-500 font-medium">Amount *</label>
                                        <input type="number" step="0.01" :name="'details[' + index + '][amount]'" x-model.number="item.amount" placeholder="0.00" required class="w-full bg-transparent text-xs text-slate-800 font-bold focus:outline-none">
                                    </div>

                                    <!-- Line Complement -->
                                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                                        <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] text-slate-500 font-medium">Line Note / Calculation</label>
                                        <input type="text" :name="'details[' + index + '][complement]'" x-model="item.complement" placeholder="e.g. 8% diff * 24 MT" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Running Total -->
                    <div class="flex items-center justify-between bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 text-xs">
                        <span class="font-bold text-slate-700">Total Note Amount:</span>
                        <span class="font-extrabold text-sm text-indigo-700" x-text="calculateTotal()"></span>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs text-slate-600 hover:text-slate-800 font-medium cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-6 py-2 rounded-full font-semibold text-xs shadow-2xs transition cursor-pointer">
                        Create Detached Note
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Detached Note Details Modal -->
    <div x-show="showViewModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showViewModal = false"
             class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 animate-in fade-in duration-200"
             x-show="viewNote">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold"
                          :class="viewNote?.kind === 'CREDIT' ? 'bg-[#81C784] text-slate-950' : 'bg-[#F57C00] text-slate-950'"
                          x-text="viewNote?.kind + ' NOTE'"></span>
                    <span class="font-mono font-bold text-sm text-slate-900" x-text="'#' + viewNote?.id"></span>
                </div>
                <button type="button" @click="showViewModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <span class="material-icons text-xl">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-xl text-xs border border-slate-200/80">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Contract</div>
                        <div class="font-bold text-indigo-700 mt-0.5" x-text="viewNote?.contract?.orderCode || ('Contract #' + viewNote?.contract_id)"></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Company</div>
                        <div class="font-medium text-slate-900 mt-0.5" x-text="viewNote?.contract?.sale?.metaCompany?.name || viewNote?.contract?.purchase?.metaCompany?.name || '-'"></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Contact</div>
                        <div class="font-medium text-slate-900 mt-0.5" x-text="viewNote?.contract?.sale?.contact?.name || viewNote?.contract?.purchase?.contact?.name || '-'"></div>
                    </div>
                </div>

                <!-- Complement / Remarks -->
                <template x-if="viewNote?.complement">
                    <div class="bg-amber-50/60 border border-amber-200/70 p-3 rounded-xl text-xs text-amber-900">
                        <div class="text-[10px] uppercase font-bold text-amber-700">General Notes / Complement</div>
                        <div class="mt-0.5" x-text="viewNote?.complement"></div>
                    </div>
                </template>

                <!-- Line Items Table -->
                <div class="space-y-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-800">Detail Line Items</div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                    <th class="py-2.5 px-3">Reason</th>
                                    <th class="py-2.5 px-3">Product</th>
                                    <th class="py-2.5 px-3">Container</th>
                                    <th class="py-2.5 px-3">Line Note</th>
                                    <th class="py-2.5 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <template x-for="d in viewNote?.details" :key="d.id">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-2.5 px-3 font-medium" x-text="d.credit_debit_type?.description || ('Type #' + d.credit_debit_type_id)"></td>
                                        <td class="py-2.5 px-3" x-text="d.deal_product?.product?.name || (d.deal_product ? 'Product #' + d.deal_product.id : '-')"></td>
                                        <td class="py-2.5 px-3 font-mono text-[11px]" x-text="d.container || '-'"></td>
                                        <td class="py-2.5 px-3 text-slate-500" x-text="d.complement || '-'"></td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900" x-text="formatCurrency(d.amount)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total & Document Download -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
                    <div>
                        <template x-if="viewNote?.generated_file_id">
                            <a :href="'/detached-notes/' + viewNote.id + '/document'" target="_blank"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-200">
                                <span class="material-icons text-sm">picture_as_pdf</span>
                                <span>Download PDF Document</span>
                            </a>
                        </template>
                    </div>

                    <div class="text-right">
                        <span class="text-xs text-slate-500 mr-2">Total:</span>
                        <span class="text-base font-extrabold text-slate-900" x-text="formatCurrency(viewNoteTotal)"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-end">
                <button type="button" @click="showViewModal = false" class="px-5 py-1.5 rounded-full text-xs font-semibold bg-slate-200 hover:bg-slate-300 text-slate-800">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function detachedNotesManager() {
    return {
        contracts: @json($contracts),
        contractQuery: '{{ $selectedCode ?? '' }}',
        selectedContract: @json($selectedContract),
        creditDebitTypes: @json($creditDebitTypes),
        dropdownOpen: false,
        showAddModal: false,
        showViewModal: false,
        modalContractId: '{{ $selectedContract?->id ?? '' }}',
        contractProducts: [],
        viewNote: null,

        lineItems: [
            { credit_debit_type_id: '', deal_product_id: '', container: '', amount: '', complement: '' }
        ],

        init() {
            if (this.modalContractId) {
                this.loadContractProducts(this.modalContractId);
            }
        },

        get filteredContracts() {
            if (!this.contractQuery) {
                return this.contracts.slice(0, 15);
            }
            const q = this.contractQuery.toLowerCase();
            return this.contracts.filter(c => c.orderCode && c.orderCode.toLowerCase().includes(q)).slice(0, 15);
        },

        selectContract(c) {
            this.selectedContract = c;
            this.contractQuery = c.orderCode;
            this.dropdownOpen = false;
            window.location.href = "{{ route('detached-notes.index') }}?code=" + encodeURIComponent(c.orderCode);
        },

        clearContract() {
            this.selectedContract = null;
            this.contractQuery = '';
            window.location.href = "{{ route('detached-notes.index') }}";
        },

        openAddModal() {
            if (this.selectedContract) {
                this.modalContractId = this.selectedContract.id;
                this.loadContractProducts(this.modalContractId);
            } else if (this.contracts.length > 0 && !this.modalContractId) {
                this.modalContractId = this.contracts[0].id;
                this.loadContractProducts(this.modalContractId);
            }
            this.showAddModal = true;
        },

        onModalContractChange() {
            if (this.modalContractId) {
                this.loadContractProducts(this.modalContractId);
            } else {
                this.contractProducts = [];
            }
        },

        loadContractProducts(contractId) {
            fetch(`/detached-notes/contract/${contractId}/products`)
                .then(res => res.json())
                .then(data => {
                    this.contractProducts = data || [];
                })
                .catch(() => {
                    this.contractProducts = [];
                });
        },

        addLineItem() {
            this.lineItems.push({
                credit_debit_type_id: '',
                deal_product_id: '',
                container: '',
                amount: '',
                complement: ''
            });
        },

        removeLineItem(index) {
            if (this.lineItems.length > 1) {
                this.lineItems.splice(index, 1);
            }
        },

        calculateTotal() {
            let sum = 0;
            for (const item of this.lineItems) {
                const val = parseFloat(item.amount);
                if (!isNaN(val)) {
                    sum += val;
                }
            }
            return sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        viewNoteDetails(noteId) {
            fetch(`/detached-notes/${noteId}`)
                .then(res => res.json())
                .then(data => {
                    this.viewNote = data;
                    this.showViewModal = true;
                })
                .catch(err => {
                    console.error('Failed to load note details', err);
                });
        },

        get viewNoteTotal() {
            if (!this.viewNote || !this.viewNote.details) return 0;
            return this.viewNote.details.reduce((acc, curr) => acc + (parseFloat(curr.amount) || 0), 0);
        },

        formatCurrency(num) {
            const val = parseFloat(num) || 0;
            return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endsection
