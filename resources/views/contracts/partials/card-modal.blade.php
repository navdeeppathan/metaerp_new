<div class="bg-white rounded-xl shadow-xl max-w-4xl w-full overflow-hidden text-slate-800" x-data="{ tab: 'general' }">
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
            <p class="text-xs text-slate-500 mt-1">Status: <span class="font-semibold">{{ ($contract->contractStatus == 'ACTIVE' || $contract->contractStatus == 'OPEN') ? 'OPEN' : $contract->contractStatus }}</span> | Date: {{ $contract->date }}</p>
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
