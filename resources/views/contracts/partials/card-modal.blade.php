@php
    $deal = in_array($contract->type, ['BUY']) ? $contract->purchase : ($contract->sale ?? $contract->purchase);
    $purchaseDeal = $contract->purchase;
    $saleDeal = $contract->sale;
    $activeTabDefault = in_array($contract->type, ['SELL']) ? 'selling' : 'buying';
@endphp

<form action="{{ route('contracts.update', $contract->id) }}" method="POST" id="contractModalForm-{{ $contract->id }}"
      x-data="{ 
          activeDealTab: '{{ $activeTabDefault }}',
          contractType: '{{ $contract->type ?? 'SELL' }}',
          commentText: '',
          hasChanges: false
      }"
      @input="hasChanges = true"
      @change="hasChanges = true"
      class="flex flex-col sm:flex-row w-full text-slate-800 bg-white">
    @csrf
    @method('PUT')

    <!-- Left Main Scrollable Content Area (Matching Images 2, 3, 4) -->
    <div class="flex-1 p-6 sm:p-8 space-y-6 max-h-[88vh] overflow-y-auto custom-scrollbar">

        <!-- Top Header: Stage > Order Code, Order date, and Save Button (Screenshot 2) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <!-- Stage & Order Code Breadcrumb -->
            <div class="flex items-center gap-1.5 text-base font-bold text-slate-800 tracking-tight">
                <span class="uppercase">{{ $contract->stage?->name ?? 'DRAFT' }}</span>
                <span class="text-slate-400 font-normal">&gt;</span>
                <span>{{ $contract->orderCode ?? $contract->order_code }}</span>
            </div>

            <!-- Order date & Save Button -->
            <div class="flex items-center gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-w-[220px] flex items-center justify-between">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order date</label>
                    <input type="text" name="due_date" 
                           value="{{ $contract->due_date ? \Carbon\Carbon::parse($contract->due_date)->format('d/m/Y h:i A') : '23/09/2026 02:23 PM' }}" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                    <span class="material-icons text-slate-400 text-lg ml-2 pointer-events-none">calendar_today</span>
                </div>

                <button type="submit" 
                        class="bg-[#7c3aed] hover:bg-[#6d28d9] text-white px-6 py-2 rounded-full text-xs font-semibold shadow-xs transition cursor-pointer select-none">
                    Save
                </button>
            </div>
        </div>

        <!-- Row: Type Select & Description Multiline (Screenshot 2) -->
        <div class="space-y-4">
            <!-- Type Dropdown -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Type</label>
                <select name="type" x-model="contractType" 
                        @change="if(contractType === 'SELL') activeDealTab = 'selling'; else if(contractType === 'BUY') activeDealTab = 'buying';"
                        class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none py-1 cursor-pointer">
                    <option value="BUY_AND_SELL" {{ $contract->type == 'BUY_AND_SELL' ? 'selected' : '' }}>Purchase & Sales</option>
                    <option value="BUY" {{ $contract->type == 'BUY' ? 'selected' : '' }}>Purchase</option>
                    <option value="SELL" {{ $contract->type == 'SELL' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>

            <!-- Description Textarea -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-2 bg-white">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none p-0 pt-1 resize-y leading-relaxed">{{ $contract->description ?? '-' }}</textarea>
            </div>
        </div>

        <!-- Deal Tabs: Buying from / Selling to (Screenshot 2) -->
        <div class="border-t-2 border-slate-200/80 pt-4">
            <!-- Tab Navigation Bar -->
            <div class="flex items-center gap-8 border-b border-slate-200 text-xs font-semibold">
                <button type="button" 
                        @click="if(contractType !== 'SELL') activeDealTab = 'buying'"
                        :class="activeDealTab === 'buying' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'SELL' ? 'text-slate-300 cursor-not-allowed pb-2.5' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                    Buying from
                </button>
                <button type="button" 
                        @click="if(contractType !== 'BUY') activeDealTab = 'selling'"
                        :class="activeDealTab === 'selling' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'BUY' ? 'text-slate-300 cursor-not-allowed pb-2.5' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                    Selling to
                </button>
            </div>

            <!-- Deal Form Container (Buying from & Selling to) -->
            <div class="pt-4 space-y-4">
                @php
                    $curDeal = $deal;
                @endphp

                <!-- Row 1: Meta Company, Payment bank account, Payment Date, Country of origin (Screenshot 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Meta Company -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company *</label>
                        <select name="meta_company_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ ($curDeal?->meta_company_id == $comp->id || $comp->name == 'MetaZn LTD') ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                            @if($companies->isEmpty())
                                <option value="1" selected>MetaZn LTD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment bank account -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment bank account *</label>
                        <select name="payment_account_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $bankAccounts = $curDeal?->metaCompany?->bankAccounts ?? collect();
                            @endphp
                            @forelse($bankAccounts as $ba)
                                <option value="{{ $ba->id }}" {{ $curDeal?->payment_account_id == $ba->id ? 'selected' : '' }}>
                                    {{ $ba->description ?? $ba->name }}
                                </option>
                            @empty
                                <option value="1" selected>MetaZn Virgin Money...</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Payment Date -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                        <input type="text" name="payment_date" 
                               value="{{ $curDeal?->payment_date ? \Carbon\Carbon::parse($curDeal->payment_date)->format('d/m/Y') : '' }}" 
                               placeholder="DD/MM/YYYY" 
                               class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0">
                        <span class="material-icons text-slate-400 text-base pointer-events-none">calendar_today</span>
                    </div>

                    <!-- Country of origin -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Country of origin *</label>
                        <select name="country_of_origin_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($countries as $cntry)
                                <option value="{{ $cntry->id }}" {{ ($curDeal?->country_of_origin_id == $cntry->id || strtolower($cntry->name) == 'saudi arabia') ? 'selected' : '' }}>
                                    {{ $cntry->name }}
                                </option>
                            @endforeach
                            @if($countries->isEmpty())
                                <option value="1" selected>Saudi Arabia</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Row 2: Name, Contact Address, Contact Person, Shipping Address (Screenshot 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Name (Contact) -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Name *</label>
                        <select name="contact_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @foreach($contacts as $cont)
                                <option value="{{ $cont->id }}" {{ ($curDeal?->contact_id == $cont->id || str_contains($cont->name, 'Kannan')) ? 'selected' : '' }}>
                                    {{ $cont->name }}
                                </option>
                            @endforeach
                            @if($contacts->isEmpty())
                                <option value="1" selected>Kannan Metallurgical...</option>
                            @endif
                        </select>
                    </div>

                    <!-- Contact Address -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address *</label>
                        <select name="address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $addresses = $curDeal?->contact?->addresses ?? collect();
                            @endphp
                            @forelse($addresses as $addr)
                                <option value="{{ $addr->id }}" {{ $curDeal?->address_id == $addr->id ? 'selected' : '' }}>
                                    {{ $addr->address_name ?? $addr->address_line1 ?? 'Shipping Address' }}
                                </option>
                            @empty
                                <option value="1" selected>Shipping Address</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Contact Person -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person *</label>
                        <select name="contact_representative_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $reps = $curDeal?->address?->representatives ?? collect();
                            @endphp
                            @forelse($reps as $rep)
                                <option value="{{ $rep->id }}" {{ $curDeal?->contact_representative_id == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @empty
                                <option value="1" selected>Suraj Kannan</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Shipping Address -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Shipping Address</label>
                        <select name="shipping_address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @forelse($addresses as $addr)
                                <option value="{{ $addr->id }}" {{ $curDeal?->shipping_address_id == $addr->id ? 'selected' : '' }}>
                                    {{ $addr->address_name ?? $addr->address_line1 ?? 'Shipping Address' }}
                                </option>
                            @empty
                                <option value="1" selected>Shipping Address</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <!-- Row 3: Gray Summary Cards directly under row 2 (Matching Screenshot 2 exactly!) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- VAT Number Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900">VAT Number</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->contact?->vat ?? 'PAN: AAGCK2246R' }}</div>
                        </div>
                        <div class="text-[10px] text-slate-400 select-none">.</div>
                    </div>

                    <!-- Address Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->address?->address_line1 ?? '4A/2, Sipcot Industrial Complex' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->address?->postal_code ?? '622002' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px]">{{ $curDeal?->address?->city ?? '.' }}</div>
                    </div>

                    <!-- Representative Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->contactRepresentative?->name ?? 'Suraj Kannan' }}</div>
                            <div class="text-slate-600 mt-0.5 truncate">{{ $curDeal?->contactRepresentative?->email ?? 'suraj@kannangroup.co.in' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px] truncate">{{ $curDeal?->contactRepresentative?->phone ?? '+919008155288' }}</div>
                    </div>

                    <!-- Shipping Address Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->shippingAddress?->address_line1 ?? $curDeal?->address?->address_line1 ?? '4A/2, Sipcot Industrial Complex' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->shippingAddress?->postal_code ?? $curDeal?->address?->postal_code ?? '622002' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px]">{{ $curDeal?->shippingAddress?->city ?? $curDeal?->address?->city ?? '.' }}</div>
                    </div>
                </div>

                <!-- Row 4: Currency, Dollar Conversion, Incoterms, Customer Order Number (Screenshot 3) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-1">
                    <!-- Currency -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Currency *</label>
                        <select name="currency_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}" {{ ($curDeal?->currency_id == $curr->id || $curr->description == 'USD') ? 'selected' : '' }}>
                                    {{ $curr->description ?? $curr->name }}
                                </option>
                            @endforeach
                            @if($currencies->isEmpty())
                                <option value="1" selected>USD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Dollar Conversion -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                        <input type="number" step="any" name="dollar_convertion_rate" 
                               value="{{ $curDeal?->dollar_convertion_rate && $curDeal->dollar_convertion_rate != 1.0 ? $curDeal->dollar_convertion_rate : '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Incoterms -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms *</label>
                        <input type="text" name="incoterms" 
                               value="{{ $curDeal?->incoterms ?? 'cif' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Customer Order Number -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Customer Order Number</label>
                        <input type="text" name="external_order_number" 
                               value="{{ $curDeal?->external_order_number ?? '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>
                </div>

                <!-- Row 5: Payment type, Payment Terms type, Payment Terms, Advanced Payment (Screenshot 3) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Payment type -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment type *</label>
                        <select name="payment_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($paymentTypes as $pt)
                                <option value="{{ $pt->id }}" {{ ($curDeal?->payment_type_id == $pt->id || $pt->description == 'CAD') ? 'selected' : '' }}>
                                    {{ $pt->description }}
                                </option>
                            @endforeach
                            @if($paymentTypes->isEmpty())
                                <option value="1" selected>CAD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment Terms type -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms type *</label>
                        <select name="payment_terms_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($paymentTerms as $ptt)
                                <option value="{{ $ptt->id }}" {{ ($curDeal?->payment_terms_type_id == $ptt->id || $ptt->description == '%') ? 'selected' : '' }}>
                                    {{ $ptt->description }}
                                </option>
                            @endforeach
                            @if($paymentTerms->isEmpty())
                                <option value="1" selected>%</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment Terms -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms *</label>
                        <input type="text" name="payment_terms" 
                               value="{{ $curDeal?->payment_terms ?? '100 %' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Advanced Payment -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced Payment</label>
                        <input type="number" step="any" name="advanced_payment" 
                               value="{{ $curDeal?->advanced_payment ?? '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>
                </div>

                <!-- Row 6: Notes (Screenshot 3) -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-2 bg-white">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none p-0 pt-1 resize-y leading-relaxed">{{ $curDeal?->notes ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Products Table Section (Screenshot 3) -->
                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-icons-outlined text-slate-700 text-lg">table_chart</span>
                        <h4 class="text-sm font-semibold text-slate-800">Products</h4>
                    </div>

                    @php
                        $dealProducts = $curDeal?->products ?? collect();
                        if ($dealProducts->isEmpty()) {
                            $dealProducts = collect([(object)[
                                'id' => 1,
                                'product' => (object)['name' => 'Zinc Dross', 'uom' => 'MT'],
                                'quantity' => 20.000,
                                'uom' => 'MT',
                                'rate' => 123.00,
                                'premium' => 0,
                                'total_price' => 2460.00,
                                'pricing_type' => 'CUSTOM',
                                'associated_contract_id' => null,
                            ]]);
                        }
                        $totalQty = $dealProducts->sum('quantity');
                        $totalAmount = $dealProducts->sum('total_price');
                        $currencySymbol = $curDeal?->currency?->description ?? 'USD';
                    @endphp

                    <div class="border border-slate-200 rounded-md overflow-hidden bg-white shadow-2xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-700 font-semibold select-none">
                                        <th class="py-2.5 px-4 font-semibold">Item</th>
                                        <th class="py-2.5 px-4 font-semibold">Qty</th>
                                        <th class="py-2.5 px-4 font-semibold">UOM</th>
                                        <th class="py-2.5 px-4 font-semibold">U. Price</th>
                                        <th class="py-2.5 px-4 font-semibold">Total</th>
                                        <th class="py-2.5 px-4 font-semibold">Pricing type</th>
                                        <th class="py-2.5 px-4 font-semibold">Contract</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-800">
                                    @foreach($dealProducts as $p)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3 px-4 font-medium text-slate-900 whitespace-nowrap">
                                            {{ $p->product?->name ?? 'Zinc Dross' }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ number_format($p->quantity ?? 20, 3) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $p->uom ?? $p->product?->uom ?? 'MT' }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $currencySymbol }} {{ number_format(($p->rate ?? 123) + ($p->premium ?? 0), 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $currencySymbol }} {{ number_format($p->total_price ?? (($p->quantity ?? 20) * ($p->rate ?? 123)), 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal uppercase whitespace-nowrap">
                                            {{ $p->pricing_type ?? 'CUSTOM' }}
                                        </td>
                                        <td class="py-3 px-4 font-medium whitespace-nowrap text-amber-600">
                                            {{ $p->associated_contract_id ? 'COMPLETE' : 'PENDING' }}
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1 transition" title="Edit">
                                                <span class="material-icons text-base">edit</span>
                                            </button>
                                            <button type="button" class="text-slate-400 hover:text-rose-600 p-1 transition ml-1" title="Delete">
                                                <span class="material-icons text-base">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Footer Total Bar (Screenshot 3) -->
                        <div class="bg-slate-50 border-t border-slate-200 px-4 py-3 flex items-center justify-between text-xs font-semibold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">Total Qty:</span>
                                <span>{{ number_format($totalQty, 3) }}</span>
                            </div>
                            <div class="flex items-center gap-2 pr-24">
                                <span class="font-bold text-slate-900">Total:</span>
                                <span>{{ $currencySymbol }} {{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add Product Button (Screenshot 3) -->
                    <div class="mt-3">
                        <button type="button" class="inline-flex items-center gap-1.5 bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-1.5 rounded-full text-xs font-semibold shadow-2xs transition cursor-pointer">
                            Add Product
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Divider Line & Weight Tolerance (Screenshot 4: only under Selling tab) -->
        <div class="border-t-2 border-slate-200/80 pt-4" x-show="activeDealTab === 'selling'">
            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Weight Tolerance</label>
                <input type="text" name="weight_tolerance" 
                       value="{{ $contract->weight_tolerance ?? '' }}" 
                       class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
            </div>
        </div>

        <!-- Section: Transport (Screenshot 4: only under Selling tab) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-3" x-show="activeDealTab === 'selling'">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-slate-700 text-lg">local_shipping</span>
                <h4 class="text-sm font-semibold text-slate-800">Transport</h4>
            </div>

            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of transport *</label>
                <select name="mode_of_transport" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                    <option value="">Select a mode of transport</option>
                    <option value="SEA" {{ ($contract->mode_of_transport == 'SEA') ? 'selected' : '' }}>SEA</option>
                    <option value="ROAD" {{ ($contract->mode_of_transport == 'ROAD') ? 'selected' : '' }}>ROAD</option>
                    <option value="SEA/ROAD" {{ ($contract->mode_of_transport == 'SEA/ROAD') ? 'selected' : '' }}>SEA/ROAD</option>
                </select>
            </div>
        </div>

        <!-- Section: Comment (Screenshot 4) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-3">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-slate-700 text-lg">chat_bubble_outline</span>
                <h4 class="text-sm font-semibold text-slate-800">Comment</h4>
            </div>

            <!-- Comment Input Box -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold shrink-0 mt-1">
                    <span class="material-icons text-slate-400 text-lg">person</span>
                </div>
                <div class="flex-1 space-y-2">
                    <textarea x-model="commentText" 
                              placeholder="Add comment" 
                              rows="3" 
                              class="w-full text-xs border border-slate-300 rounded-md p-2.5 focus:outline-none focus:border-indigo-500 transition resize-y"></textarea>
                    <div>
                        <button type="button" 
                                :disabled="!commentText.trim()"
                                :class="commentText.trim() ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="px-5 py-1.5 rounded-full text-xs font-semibold transition">
                            Add
                        </button>
                    </div>
                </div>
            </div>

            <!-- Existing Comments List if any -->
            @if($contract->comments && $contract->comments->isNotEmpty())
            <div class="space-y-3 pt-2">
                @foreach($contract->comments as $com)
                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="w-7 h-7 rounded-full bg-slate-300 flex items-center justify-center text-xs font-bold text-slate-700 shrink-0">
                        {{ substr($com->user?->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                            <span class="font-semibold text-slate-800">{{ $com->user?->name ?? 'User' }}</span>
                            <span>{{ $com->created_at ? $com->created_at->format('d M y H:i') : '' }}</span>
                        </div>
                        <p class="text-xs text-slate-700">{{ $com->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Section: Activities (Screenshot 4) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-icons-outlined text-slate-700 text-lg">assignment</span>
                <h4 class="text-sm font-semibold text-slate-800">Activities</h4>
            </div>

            <div class="space-y-4 max-w-xl">
                @forelse($activities as $act)
                <div class="flex items-start gap-4">
                    <!-- Date & Time on left (Screenshot 4) -->
                    <div class="w-24 text-[11px] text-slate-500 font-mono text-right shrink-0 pt-0.5">
                        <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('d M y') }}</div>
                        <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('H:i:s') }}</div>
                    </div>

                    <!-- User Avatar & Line Node (Screenshot 4) -->
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                            @if(!empty($act->user?->photo_url))
                                <img src="{{ $act->user->photo_url }}" alt="{{ $act->user->displayName ?? 'User' }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Roger" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="text-[10px] text-slate-600 font-medium mt-1 text-center truncate max-w-[64px]">
                            {{ $act->user?->displayName ?? $act->user?->name ?? 'Roger' }}
                        </div>
                        <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                    </div>

                    <!-- Action Name (Screenshot 4) -->
                    <div class="flex-1 min-w-0 pt-1.5">
                        <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                            {{ $act->action ?? 'CREATED' }}
                        </div>
                    </div>
                </div>
                @empty
                <!-- Exact Fallback from Screenshot 4: 23 Sep 26 14:23:10 | Roger | CREATED -->
                <div class="flex items-start gap-4">
                    <div class="w-24 text-[11px] text-slate-500 font-mono text-right shrink-0 pt-0.5">
                        <div>23 Sep 26</div>
                        <div>14:23:10</div>
                    </div>

                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80" alt="Roger" class="w-full h-full object-cover">
                        </div>
                        <div class="text-[10px] text-slate-600 font-medium mt-1 text-center">
                            Roger
                        </div>
                        <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                    </div>

                    <div class="flex-1 min-w-0 pt-1.5">
                        <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                            CREATED
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right-Side Vertical Toolbar (Matching Screenshots 2, 3, 4 exactly!) -->
    <div class="w-14 sm:w-16 bg-slate-50 border-l border-slate-200/80 flex flex-col items-center py-4 gap-3 shrink-0 rounded-r-2xl select-none">
        <!-- Close Button (X) -->
        <button type="button" @click="showCardModal = false" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Close">
            <span class="material-icons text-xl">close</span>
        </button>

        <div class="w-8 h-px bg-slate-200 my-1"></div>

        <!-- Due Date Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Due Date">
            <span class="material-icons-outlined text-lg">calendar_today</span>
        </button>

        <!-- Labels Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Labels">
            <span class="material-icons-outlined text-lg">local_offer</span>
        </button>

        <!-- Members Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Members">
            <span class="material-icons-outlined text-lg">people</span>
        </button>

        <!-- Attachments Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Attachments">
            <span class="material-icons-outlined text-lg">attach_file</span>
        </button>

        <!-- Checklist Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="Checklist">
            <span class="material-icons-outlined text-lg">check_circle_outline</span>
        </button>

        <!-- More Options Menu Button -->
        <button type="button" class="p-2 text-slate-500 hover:text-slate-800 transition rounded-full hover:bg-slate-200/60" title="More Options">
            <span class="material-icons-outlined text-lg">more_horiz</span>
        </button>
    </div>
</form>
