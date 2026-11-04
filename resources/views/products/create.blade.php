@extends('layouts.app')

@section('title', $product->exists ? $product->name : 'New Product')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto pb-12">
    <!-- Back Link & Header -->
    <div>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors mb-3">
            <span class="material-icons-outlined text-base">arrow_back</span>
            <span>Products</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">
                    {{ $product->exists ? $product->name : 'New Product' }}
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">Product Detail</p>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-3">
                @if($product->exists)
                    <!-- Remove Button with Trash Icon -->
                    <form id="delete-form" method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to remove this product?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-sm font-semibold rounded-full shadow-xs transition-colors"
                        >
                            <span class="material-icons-outlined text-base">delete</span>
                            <span>Remove</span>
                        </button>
                    </form>
                @endif

                <!-- Save Button -->
                <button
                    type="submit"
                    form="product-form"
                    class="inline-flex items-center px-6 py-2 {{ $product->exists ? 'bg-slate-200 text-slate-700 hover:bg-[#4f46e5] hover:text-white' : 'bg-slate-200 text-slate-400 hover:bg-[#4f46e5] hover:text-white' }} text-sm font-semibold rounded-full shadow-xs transition-colors"
                >
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs font-medium">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Container -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
        <!-- Tabs Row -->
        <div class="border-b border-slate-200 flex items-center gap-6 text-xs font-medium pb-px">
            <button
                type="button"
                class="text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px] focus:outline-none"
            >
                Basic Info
            </button>
        </div>

        <!-- Form Elements -->
        <form
            id="product-form"
            method="POST"
            action="{{ $product->exists ? route('products.update', $product->id) : route('products.store') }}"
            class="pt-6 space-y-6 max-w-4xl"
        >
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <!-- Field 1: Name * -->
            <div class="relative">
                <input
                    type="text"
                    name="name"
                    id="name"
                    required
                    value="{{ old('name', $product->name) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-600 transition-colors"
                    placeholder=" "
                />
                <label
                    for="name"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-indigo-600 pointer-events-none transition-all"
                >
                    Name <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 2: Category * -->
            <div class="relative">
                <select
                    name="category"
                    id="category"
                    required
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors appearance-none cursor-pointer"
                >
                    <option value="">Select Category</option>
                    @foreach(['COMMODITIES', 'FERROUS', 'NON_FERROUS', 'SCRAP', 'SERVICES'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $product->productCategory) === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
                <label
                    for="category"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Category <span class="text-slate-400">*</span>
                </label>
                <span class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">arrow_drop_down</span>
            </div>

            <!-- Field 3: Symbol -->
            <div class="relative">
                <input
                    type="text"
                    name="symbol"
                    id="symbol"
                    value="{{ old('symbol', $product->chemicalFormula) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="symbol"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Symbol
                </label>
            </div>

            <!-- Field 4: Code -->
            <div class="relative">
                <input
                    type="text"
                    name="code"
                    id="code"
                    value="{{ old('code', $product->code) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="code"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Code
                </label>
            </div>

            <!-- Field 5: HS Code -->
            <div class="relative">
                <input
                    type="text"
                    name="hsCode"
                    id="hsCode"
                    value="{{ old('hsCode', $product->hsCode) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="hsCode"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    HS Code
                </label>
            </div>

            <!-- Field 6: Basel Code Annex IX -->
            <div class="relative">
                <input
                    type="text"
                    name="basel_code"
                    id="basel_code"
                    value="{{ old('basel_code', $product->basel_code) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="basel_code"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Basel Code Annex IX
                </label>
            </div>

            <!-- Field 7: UOM * -->
            <div class="relative">
                <input
                    type="text"
                    name="uom"
                    id="uom"
                    required
                    value="{{ old('uom', $product->unitOfMeasurement ?? 'MT') }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="uom"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    UOM <span class="text-slate-400">*</span>
                </label>
            </div>

            <!-- Field 8: EC List Of Wastes -->
            <div class="relative">
                <input
                    type="text"
                    name="ec_waste_code"
                    id="ec_waste_code"
                    value="{{ old('ec_waste_code', $product->ec_waste_code) }}"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors"
                    placeholder=" "
                />
                <label
                    for="ec_waste_code"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    EC List Of Wastes
                </label>
            </div>

            <!-- Field 9: Item Specification -->
            <div class="relative">
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="peer w-full px-3.5 py-3 rounded border border-slate-300 focus:border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none transition-colors resize-y"
                    placeholder=" "
                >{{ old('description', $product->description) }}</textarea>
                <label
                    for="description"
                    class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                >
                    Item Specification
                </label>
            </div>
        </form>

        <!-- Activities Section (Only for existing products) -->
        @if($product->exists)
            <div class="mt-10 pt-6 border-t border-slate-200 max-w-4xl">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-icons-outlined text-slate-600 text-lg">assignment</span>
                    <h3 class="font-bold text-slate-800 text-sm tracking-wide">Activities</h3>
                </div>

                <div class="space-y-4">
                    <!-- Activity Item: Created -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                            <span class="material-icons text-base">person</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ $product->created_at ? $product->created_at->format('d M y H:i:s') : '17 Oct 25 20:12:08' }}
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-sm font-semibold text-slate-800">Doga</span>
                                <span class="text-xs font-medium text-slate-500">CREATED</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Item: Updated (if modified) -->
                    @if($product->updated_at && $product->created_at && $product->updated_at > $product->created_at)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <span class="material-icons text-base">edit</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $product->updated_at->format('d M y H:i:s') }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-sm font-semibold text-slate-800">{{ Auth::user()->name ?? 'Root' }}</span>
                                    <span class="text-xs font-medium text-indigo-600">UPDATED</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
