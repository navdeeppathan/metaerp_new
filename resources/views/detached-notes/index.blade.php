@extends('layouts.app')

@section('title', 'Detached Notes')

@section('content')
<div class="space-y-6" x-data="detachedNotesManager()">
    <!-- Header with Title, Contract Code Combobox & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detached Notes</h1>

        <div class="flex items-center gap-3">
            <!-- Contract Code Search / Dropdown Pill -->
            <div class="relative w-64 sm:w-72" @click.outside="dropdownOpen = false">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input type="text" 
                       x-model="contractQuery" 
                       @focus="dropdownOpen = true"
                       @input="dropdownOpen = true"
                       placeholder="type a contract code" 
                       class="w-full bg-white border border-slate-200 rounded-full pl-9 pr-8 py-1.5 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-2xs transition">
                <button type="button" @click="dropdownOpen = !dropdownOpen" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                    <span class="material-icons text-sm leading-none">arrow_drop_down</span>
                </button>

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
                                  :class="c.type === 'BUY' ? 'bg-emerald-100 text-emerald-800' : (c.type === 'SELL' ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-700')"
                                  x-text="c.type"></span>
                        </div>
                    </template>
                    <div x-show="filteredContracts.length === 0" class="px-3.5 py-2 text-slate-400 text-center text-xs">
                        No contract codes found
                    </div>
                </div>
            </div>

            <!-- + Add Button (Disabled when no contract selected, Active when selected) -->
            <button type="button"
                    @click="showAddModal = true"
                    :disabled="!selectedContract"
                    :class="selectedContract ? 'bg-[#4f46e5] hover:bg-[#4338ca] text-white shadow-2xs cursor-pointer' : 'bg-[#e2e8f0] text-slate-400 cursor-not-allowed'"
                    class="px-5 py-1.5 rounded-full font-semibold text-xs transition whitespace-nowrap">
                + Add
            </button>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 min-h-[520px] flex flex-col justify-center">
        @if($detachedNotes->isEmpty())
            <!-- Empty State matching screenshot: 'There are no detachedNotes!' -->
            <div class="flex-1 flex items-center justify-center p-8 select-none">
                <span class="text-slate-500 font-normal text-base sm:text-lg">
                    There are no detachedNotes!
                </span>
            </div>
        @else
            <!-- Detached Notes Table if records exist -->
            <div class="overflow-x-auto p-4 flex-1">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-900 font-bold text-[13px]">
                            <th class="py-3 px-4">Kind</th>
                            <th class="py-3 px-4">Number</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($detachedNotes as $note)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $note->kind == 'CREDIT' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $note->kind }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">{{ $note->number }}</td>
                            <td class="py-3.5 px-4">{{ $note->date ? $note->date->format('d/m/Y') : '-' }}</td>
                            <td class="py-3.5 px-4 font-medium">{{ $note->metaCompany?->name ?? '-' }}</td>
                            <td class="py-3.5 px-4">{{ $note->contact?->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                {{ number_format($note->total, 2) }} {{ $note->currency?->currencySymbol ?? '$' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('detached-notes.destroy', $note->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this detached note?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 transition" title="Delete">
                                        <span class="material-icons text-sm">delete</span>
                                    </button>
                                </form>
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
             class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-slate-100 animate-in fade-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-base font-bold text-slate-900">New Detached Note</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Contract: <span class="font-mono font-bold text-indigo-600" x-text="selectedContract?.orderCode"></span></p>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <span class="material-icons text-lg">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('detached-notes.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="contract_code" :value="selectedContract?.orderCode">

                <!-- Kind selection -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Kind *</label>
                        <select name="kind" required class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer">
                            <option value="CREDIT">CREDIT NOTE</option>
                            <option value="DEBIT">DEBIT NOTE</option>
                        </select>
                    </div>

                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Note Number</label>
                        <input type="text" name="number" placeholder="e.g. DN-26-001" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                    </div>
                </div>

                <!-- Date & Currency -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                    </div>

                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Currency</label>
                        <select name="currency_id" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer">
                            @foreach($currencies as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->code ?? $curr->currencySymbol }} ({{ $curr->name ?? $curr->description }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Company & Contact -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Meta Company</label>
                        <select name="meta_company_id" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer">
                            <option value="">Select Company...</option>
                            @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                        <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Contact</label>
                        <select name="contact_id" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer">
                            <option value="">Select Contact...</option>
                            @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-500">
                    <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium">Total Amount</label>
                    <input type="number" step="0.01" name="total" placeholder="0.00" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none">
                </div>

                <!-- Footer buttons -->
                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-200">
                    <button type="button" @click="showAddModal = false" class="px-4 py-1.5 text-xs text-slate-600 hover:text-slate-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#4f46e5] hover:bg-[#4338ca] text-white px-6 py-1.5 rounded-full font-semibold text-xs shadow-2xs transition">
                        Create Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function detachedNotesManager() {
    return {
        contracts: @json($contracts),
        contractQuery: '{{ $selectedCode ?? '' }}',
        selectedContract: @json($selectedContract),
        dropdownOpen: false,
        showAddModal: false,

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
        }
    };
}
</script>
@endsection
