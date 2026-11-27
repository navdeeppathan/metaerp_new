@extends('layouts.app')

@section('title', 'New Detached Note - MetaERP')

@section('content')
<div class="max-w-7xl mx-auto space-y-4" x-data="detachedNoteForm()">
    <!-- Top Header: Back Link, Title, Subtitle, Save Button -->
    <div class="flex items-center justify-between pb-2">
        <div>
            <a href="{{ route('detached-notes.index', $selectedContract ? ['code' => $selectedContract->orderCode] : []) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-slate-900 transition-colors mb-2">
                <span class="material-icons text-base">arrow_back</span>
                <span>Detached Notes</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">
                New Detached Note
            </h1>
            <p class="text-xs text-slate-500 font-normal mt-0.5">
                Detached Note Detail
            </p>
        </div>

        <div>
            <button type="button" 
                    @click="submitForm()"
                    class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-7 py-2 rounded-full font-semibold text-sm shadow-xs transition cursor-pointer">
                Save
            </button>
        </div>
    </div>

    <!-- Tab Bar: Basic Info -->
    <div class="border-b border-slate-200 mb-6">
        <div class="flex gap-8">
            <button type="button" class="pb-3 text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600 -mb-px">
                Basic Info
            </button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
        <form id="detachedNoteForm" action="{{ route('detached-notes.store') }}" method="POST">
            @csrf

            <!-- Hidden Inputs for Form Submission -->
            <input type="hidden" name="contract_id" :value="contractId">
            <input type="hidden" name="kind" :value="kind">
            <input type="hidden" name="user_id" :value="userId">
            <input type="hidden" name="complement" :value="complement">

            <!-- Hidden repeater for details items -->
            <template x-for="(item, idx) in items" :key="idx">
                <div>
                    <input type="hidden" :name="'details[' + idx + '][credit_debit_type_id]'" :value="item.credit_debit_type_id">
                    <input type="hidden" :name="'details[' + idx + '][deal_product_id]'" :value="item.deal_product_id">
                    <input type="hidden" :name="'details[' + idx + '][container]'" :value="item.container">
                    <input type="hidden" :name="'details[' + idx + '][amount]'" :value="item.amount">
                    <input type="hidden" :name="'details[' + idx + '][complement]'" :value="item.complement">
                </div>
            </template>

            <div class="space-y-5 max-w-3xl">
                <!-- Contract Field -->
                <div class="relative" @click.outside="contractDropdownOpen = false">
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Contract</label>
                        <input type="text" 
                               x-model="contractSearch"
                               @focus="contractDropdownOpen = true"
                               @input="contractDropdownOpen = true"
                               placeholder="Select or search a contract..."
                               class="w-full bg-transparent text-sm text-slate-900 font-medium focus:outline-none">
                        
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                            <template x-if="contractSearch">
                                <button type="button" @click="clearContract()" class="text-slate-400 hover:text-slate-600">
                                    <span class="material-icons text-sm leading-none">close</span>
                                </button>
                            </template>
                            <button type="button" @click="contractDropdownOpen = !contractDropdownOpen" class="text-slate-400 hover:text-slate-600">
                                <span class="material-icons text-base leading-none">arrow_drop_down</span>
                            </button>
                        </div>
                    </div>

                    <!-- Contract Dropdown Menu -->
                    <div x-show="contractDropdownOpen" 
                         x-cloak 
                         class="absolute z-30 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-56 overflow-y-auto">
                        <template x-for="c in filteredContracts" :key="c.id">
                            <div @click="selectContract(c)" 
                                 class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-xs border-b border-slate-100 last:border-0">
                                <span class="font-semibold text-slate-800" x-text="c.orderCode"></span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                                      :class="c.type === 'BUY' ? 'bg-[#81C784] text-slate-950' : 'bg-[#FFB74D] text-slate-950'"
                                      x-text="c.type"></span>
                            </div>
                        </template>
                        <div x-show="filteredContracts.length === 0" class="px-4 py-3 text-slate-400 text-xs text-center">
                            No contracts found
                        </div>
                    </div>
                </div>

                <!-- Type * Field (CREDIT / DEBIT) -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Type *</label>
                    <select x-model="kind" required class="w-full bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer appearance-none pr-8">
                        <option value="CREDIT">CREDIT</option>
                        <option value="DEBIT">DEBIT</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center text-slate-400">
                        <span class="material-icons text-base">arrow_drop_down</span>
                    </div>
                </div>

                <!-- Authorised Person (Approved by) * -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Authorised Person (Approved by) *</label>
                    <select x-model="userId" required class="w-full bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer appearance-none pr-8">
                        @if(isset($users))
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->name }} {{ $u->occupation ? ' (' . $u->occupation . ')' : '' }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center text-slate-400">
                        <span class="material-icons text-base">arrow_drop_down</span>
                    </div>
                </div>

                <!-- Additional Notes Field -->
                <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Additional Notes</label>
                    <textarea x-model="complement" rows="2" class="w-full bg-transparent text-sm text-slate-800 focus:outline-none resize-none"></textarea>
                </div>
            </div>

            <!-- Items Table Section (Matching Image 1) -->
            <div class="pt-4 space-y-4">
                <div class="border border-slate-200/90 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider">
                                <th class="py-3 px-4 w-1/4">Container</th>
                                <th class="py-3 px-4 w-1/4">Type</th>
                                <th class="py-3 px-4 w-1/4">Product</th>
                                <th class="py-3 px-4 w-1/6">Amount</th>
                                <th class="py-3 px-4 w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-medium text-slate-800" x-text="item.container || '-'"></td>
                                    <td class="py-3 px-4 text-slate-700" x-text="item.type_name"></td>
                                    <td class="py-3 px-4 text-slate-700" x-text="item.product_name"></td>
                                    <td class="py-3 px-4 font-bold text-slate-900" x-text="formatCurrency(item.amount)"></td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" @click="removeItem(idx)" class="text-slate-400 hover:text-rose-600 transition p-1">
                                            <span class="material-icons text-base">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="py-6 px-4 text-center text-slate-400 text-xs italic">
                                    No items added yet. Click "Add Item" below to add line items.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Add Item / Cancel Pill Button (Matching Image 1 & 2) -->
                <div>
                    <button type="button" 
                            @click="toggleItemForm()"
                            class="bg-[#0284c7] hover:bg-[#0369a1] text-white px-7 py-2 rounded-full font-semibold text-xs tracking-wide transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span x-text="showItemForm ? 'Cancel' : 'Add Item'"></span>
                    </button>
                </div>

                <!-- Inline Item Addition Subform (Matching Image 2 & 3) -->
                <div x-show="showItemForm" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="max-w-2xl pt-2 space-y-4">
                    
                    <!-- Type * (Reason Type dropdown) -->
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Type *</label>
                        <select x-model="newItem.credit_debit_type_id" class="w-full bg-transparent text-sm text-slate-900 focus:outline-none cursor-pointer appearance-none pr-8">
                            <option value="">Select a reason type...</option>
                            <template x-for="t in creditDebitTypes" :key="t.id">
                                <option :value="t.id" x-text="t.description"></option>
                            </template>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center text-slate-400">
                            <span class="material-icons text-base">arrow_drop_down</span>
                        </div>
                    </div>

                    <!-- Reason Details -->
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Reason Details</label>
                        <textarea x-model="newItem.complement" rows="3" class="w-full bg-transparent text-sm text-slate-800 focus:outline-none resize-none"></textarea>
                    </div>

                    <!-- Product * -->
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Product *</label>
                        <select x-model="newItem.deal_product_id" class="w-full bg-transparent text-sm text-slate-900 focus:outline-none cursor-pointer appearance-none pr-8">
                            <option value="">Select a product from the contract...</option>
                            <template x-for="p in availableProducts" :key="p.id">
                                <option :value="p.id" x-text="(p.code ? p.code + ' - ' : '') + p.name + (p.deal_type ? ' (' + p.deal_type + ')' : '')"></option>
                            </template>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center text-slate-400">
                            <span class="material-icons text-base">arrow_drop_down</span>
                        </div>
                    </div>

                    <!-- Container / Truck -->
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Container / Truck</label>
                        <input type="text" x-model="newItem.container" placeholder="e.g. MSMU1860791" class="w-full bg-transparent text-sm text-slate-900 focus:outline-none">
                    </div>

                    <!-- Amount -->
                    <div class="relative border border-slate-300 rounded-md px-3.5 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 transition">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium">Amount</label>
                        <input type="number" step="0.01" x-model.number="newItem.amount" placeholder="0.00" class="w-full bg-transparent text-sm text-slate-900 font-bold focus:outline-none">
                    </div>

                    <!-- Save Item Button (Matching Image 3) -->
                    <div class="pt-2">
                        <button type="button" 
                                @click="saveItem()"
                                :disabled="!isItemValid"
                                :class="isItemValid ? 'bg-[#0284c7] hover:bg-[#0369a1] text-white cursor-pointer shadow-xs' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="px-8 py-2 rounded-full font-semibold text-xs tracking-wide transition">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function detachedNoteForm() {
    return {
        contracts: @json($contracts),
        contractId: '{{ $selectedContract->id ?? "" }}',
        contractSearch: '{{ $selectedContract->orderCode ?? "" }}',
        contractDropdownOpen: false,
        kind: 'CREDIT',
        userId: '{{ auth()->id() ?? (isset($users[0]) ? $users[0]->id : 1) }}',
        complement: '',
        
        creditDebitTypes: @json($creditDebitTypes),
        availableProducts: @json($contractProducts),
        
        items: [],
        showItemForm: false,
        
        newItem: {
            credit_debit_type_id: '',
            deal_product_id: '',
            container: '',
            amount: '',
            complement: '',
        },

        init() {
            if (this.contractId && this.availableProducts.length === 0) {
                this.loadProducts(this.contractId);
            }
        },

        get filteredContracts() {
            if (!this.contractSearch) return this.contracts.slice(0, 50);
            const q = this.contractSearch.toLowerCase();
            return this.contracts.filter(c => (c.orderCode && c.orderCode.toLowerCase().includes(q))).slice(0, 50);
        },

        selectContract(c) {
            this.contractId = c.id;
            this.contractSearch = c.orderCode;
            this.contractDropdownOpen = false;
            this.loadProducts(c.id);
        },

        clearContract() {
            this.contractId = '';
            this.contractSearch = '';
            this.availableProducts = [];
            this.items = [];
        },

        async loadProducts(contractId) {
            if (!contractId) return;
            try {
                const res = await fetch(`/detached-notes/contract/${contractId}/products`);
                if (res.ok) {
                    this.availableProducts = await res.json();
                }
            } catch (e) {
                console.error("Failed to load products for contract:", e);
            }
        },

        toggleItemForm() {
            this.showItemForm = !this.showItemForm;
            if (!this.showItemForm) {
                this.resetNewItem();
            }
        },

        get isItemValid() {
            return this.newItem.credit_debit_type_id && 
                   this.newItem.deal_product_id && 
                   this.newItem.amount !== '' && 
                   !isNaN(this.newItem.amount);
        },

        saveItem() {
            if (!this.isItemValid) return;
            
            const selectedType = this.creditDebitTypes.find(t => t.id == this.newItem.credit_debit_type_id);
            const selectedProd = this.availableProducts.find(p => p.id == this.newItem.deal_product_id);

            this.items.push({
                credit_debit_type_id: this.newItem.credit_debit_type_id,
                deal_product_id: this.newItem.deal_product_id,
                container: this.newItem.container,
                amount: parseFloat(this.newItem.amount),
                complement: this.newItem.complement,
                type_name: selectedType ? selectedType.description : 'Type #' + this.newItem.credit_debit_type_id,
                product_name: selectedProd ? (selectedProd.name || selectedProd.code) : 'Product #' + this.newItem.deal_product_id,
            });

            this.resetNewItem();
            this.showItemForm = false;
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        resetNewItem() {
            this.newItem = {
                credit_debit_type_id: '',
                deal_product_id: '',
                container: '',
                amount: '',
                complement: '',
            };
        },

        formatCurrency(val) {
            return parseFloat(val || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        submitForm() {
            if (!this.contractId) {
                alert('Please select a contract.');
                return;
            }
            if (this.items.length === 0) {
                if (!confirm('No items have been added to this detached note. Do you still want to save?')) {
                    return;
                }
            }
            document.getElementById('detachedNoteForm').submit();
        }
    };
}
</script>
@endsection
