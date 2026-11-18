<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Address;
use App\Models\Representative;
use App\Models\Product;
use App\Models\Currency;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\Deal;
use App\Models\DealProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WebOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query();

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('id', 'like', "%{$search}%");

                $columns = ['subject', 'code', 'order_code', 'orderCode', 'contract_code', 'contract_order_code_when_promoted', 'status', 'type'];
                foreach ($columns as $column) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('offer', $column)) {
                            $sub->orWhere($column, 'like', "%{$search}%");
                        }
                    } catch (\Throwable $e) {
                        // fallback if schema inspection fails
                    }
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $offers = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $offer = new Offer();
        $offer->type = 'SELL';
        $offer->status = 'OPEN';

        $companies = Company::orderBy('name')->get();
        $contacts = Contact::with(['addresses', 'addresses.representatives'])->orderBy('name')->get();
        $currencies = Currency::orderBy('description')->get();
        $paymentTypes = PaymentType::orderBy('description')->get();
        $paymentTermsTypes = PaymentTermsType::orderBy('description')->get();
        $products = Product::where('active', 1)->orWhereNull('active')->orderBy('name')->get();
        $logs = collect();

        $contactsPayload = $this->formatContactsPayload($contacts);

        return view('offers.create', compact(
            'offer', 'companies', 'contacts', 'contactsPayload',
            'currencies', 'paymentTypes', 'paymentTermsTypes', 'products', 'logs'
        ));
    }

    public function show($id)
    {
        $offer = Offer::findOrFail($id);

        $companies = Company::orderBy('name')->get();
        $contacts = Contact::with(['addresses', 'addresses.representatives'])->orderBy('name')->get();
        $currencies = Currency::orderBy('description')->get();
        $paymentTypes = PaymentType::orderBy('description')->get();
        $paymentTermsTypes = PaymentTermsType::orderBy('description')->get();
        $products = Product::where('active', 1)->orWhereNull('active')->orderBy('name')->get();

        try {
            $colDataId = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'data_id') ? 'data_id' : 'dataId';
            $colDateTime = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'date_time') ? 'date_time' : 'dateTime';
            $logs = DB::table('logs')
                ->where('module', 'OFFER')
                ->where($colDataId, $offer->id)
                ->orderBy($colDateTime, 'desc')
                ->get();
        } catch (\Throwable $e) {
            $logs = collect();
        }

        $contactsPayload = $this->formatContactsPayload($contacts);

        return view('offers.create', compact(
            'offer', 'companies', 'contacts', 'contactsPayload',
            'currencies', 'paymentTypes', 'paymentTermsTypes', 'products', 'logs'
        ));
    }

    protected function formatContactsPayload($contacts)
    {
        return $contacts->mapWithKeys(function($contact) {
            $addresses = $contact->addresses ?? collect();
            if ($addresses->isEmpty()) {
                $addresses = Address::where('contact_id', $contact->id)->get();
            }

            $representatives = collect();
            foreach ($addresses as $addr) {
                if ($addr->representatives && $addr->representatives->isNotEmpty()) {
                    $representatives = $representatives->merge($addr->representatives);
                }
            }
            if ($representatives->isEmpty()) {
                $representatives = Representative::where('contact_id', $contact->id)->get();
            }

            return [
                $contact->id => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'vat' => $contact->vat ?? '',
                    'addresses' => $addresses->map(function($addr) {
                        return [
                            'id' => $addr->id,
                            'name' => $addr->address_name ?? $addr->addressName ?? 'Address #' . $addr->id,
                            'line1' => $addr->address_line1 ?? $addr->addressLine1 ?? '',
                            'postal_code' => $addr->postal_code ?? $addr->postalCode ?? '',
                            'city' => $addr->city ?? '',
                        ];
                    })->values()->all(),
                    'representatives' => $representatives->unique('id')->map(function($rep) {
                        return [
                            'id' => $rep->id,
                            'name' => $rep->name,
                            'email' => $rep->email ?? '',
                            'phone' => $rep->phone ?? '',
                        ];
                    })->values()->all(),
                ]
            ];
        })->all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'offer_date' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'delivery_plan' => 'nullable|string',
            'quotational_period' => 'nullable|string',
            'weights' => 'nullable|string',
            'payment_description' => 'nullable|string',
            'terms' => 'nullable|string',
            'extra_information' => 'nullable|string',
        ]);

        $maxId = Offer::max('id') ?? 21;
        $nextId = $maxId + 1;
        $code = 'OFFER-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

        $offer = new Offer();
        $offer->id = $nextId;
        $offer->status = 'OPEN';
        $offer->type = $validated['type'];
        $offer->subject = $validated['subject'];
        $offer->code = $code;
        $offer->orderCode = $code;
        $offer->offer_date = $request->filled('offer_date') ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('offer_date')))) : now();
        $offer->description = $validated['description'] ?? null;
        $offer->internal_notes = $validated['internal_notes'] ?? null;
        $offer->delivery_plan = $validated['delivery_plan'] ?? null;
        $offer->quotational_period = $validated['quotational_period'] ?? null;
        $offer->weights = $validated['weights'] ?? null;
        $offer->payment_description = $validated['payment_description'] ?? null;
        $offer->terms = $validated['terms'] ?? null;
        $offer->extra_information = $validated['extra_information'] ?? null;

        // Deal fields
        $offer->meta_company_id = $request->input('meta_company_id');
        $offer->contact_id = $request->input('contact_id');
        $offer->address_id = $request->input('address_id');
        $offer->contact_representative_id = $request->input('contact_representative_id');
        $offer->currency_id = $request->input('currency_id');
        $offer->incoterms = $request->input('incoterms');
        $offer->payment_type_id = $request->input('payment_type_id');
        $offer->payment_terms_type_id = $request->input('payment_terms_type_id');
        $offer->payment_terms = $request->input('payment_terms');
        if ($request->filled('agree_date')) {
            $offer->agree_date = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('agree_date'))));
        }
        if ($request->filled('products_json')) {
            $offer->products_json = $request->input('products_json');
        }

        $offer->save();

        // Create associated Deal
        try {
            $deal = new Deal();
            $deal->meta_company_id = $offer->meta_company_id;
            $deal->contact_id = $offer->contact_id;
            $deal->address_id = $offer->address_id;
            $deal->contact_representative_id = $offer->contact_representative_id;
            $deal->currency_id = $offer->currency_id;
            $deal->incoterms = $offer->incoterms;
            $deal->payment_type_id = $offer->payment_type_id;
            $deal->payment_terms_type_id = $offer->payment_terms_type_id;
            $deal->paymentTerms = $offer->payment_terms;
            if ($offer->agree_date) {
                $deal->paymentDate = $offer->agree_date->format('Y-m-d');
            }
            $deal->offer_id = $offer->id;
            $deal->save();

            if (strtoupper($offer->type) === 'BUY') {
                $offer->purchase_id = $deal->id;
            } else {
                $offer->sale_id = $deal->id;
            }
            $offer->save();

            // Deal products
            if (!empty($offer->products_json)) {
                $productsList = is_string($offer->products_json) ? json_decode($offer->products_json, true) : $offer->products_json;
                if (is_array($productsList)) {
                    $dealTotal = 0;
                    $dealQty = 0;
                    foreach ($productsList as $p) {
                        $qty = (float)($p['quantity'] ?? 0);
                        $price = (float)($p['unit_price'] ?? 0);
                        $total = (float)($p['total'] ?? ($qty * $price));
                        $dealTotal += $total;
                        $dealQty += $qty;

                        $dp = new DealProduct();
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'deal_id')) {
                            $dp->deal_id = $deal->id;
                        }
                        $dp->product_id = $p['product_id'] ?? null;
                        $dp->quantity = $qty;
                        $dp->rate = $price;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'unitPrice')) {
                            $dp->unitPrice = $price;
                        }
                        $dp->total_price = $total;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'total')) {
                            $dp->total = $total;
                        }
                        $dp->pricing_type = $p['pricing_type'] ?? 'CUSTOM';
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'pricingType')) {
                            $dp->pricingType = $p['pricing_type'] ?? 'CUSTOM';
                        }
                        $dp->uom = $p['uom'] ?? 'MT';
                        $dp->version = 0;
                        $dp->save();

                        if (\Illuminate\Support\Facades\Schema::hasTable('deal_products')) {
                            DB::table('deal_products')->insert([
                                'deal_id' => $deal->id,
                                'products_id' => $dp->id
                            ]);
                        }
                    }
                    $deal->total = $dealTotal;
                    $deal->netTotal = $dealTotal;
                    $deal->save();

                    $offer->total = $dealTotal;
                    $offer->quantity = $dealQty;
                    $offer->save();
                }
            }
        } catch (\Throwable $e) {}

        // Log action
        try {
            $colDataId = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'data_id') ? 'data_id' : 'dataId';
            $colDateTime = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'date_time') ? 'date_time' : 'dateTime';
            $colUserId = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'user_id') ? 'user_id' : 'userId';

            DB::table('logs')->insert([
                'module' => 'OFFER',
                $colDataId => $offer->id,
                $colDateTime => now(),
                $colUserId => Auth::id() ?? 1,
                'action' => 'CREATED',
                'history' => json_encode(['action' => 'Offer created']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('offers.show', $offer->id)->with('success', 'Offer created successfully!');
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        $data = $request->only([
            'type', 'subject', 'description', 'internal_notes',
            'delivery_plan', 'quotational_period', 'weights',
            'payment_description', 'terms', 'extra_information',
            'meta_company_id', 'contact_id', 'address_id',
            'contact_representative_id', 'currency_id', 'incoterms',
            'payment_type_id', 'payment_terms_type_id', 'payment_terms',
        ]);

        if ($request->filled('offer_date')) {
            $data['offer_date'] = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('offer_date'))));
        }

        if ($request->filled('agree_date')) {
            $data['agree_date'] = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('agree_date'))));
        }

        if ($request->has('products_json')) {
            $data['products_json'] = $request->input('products_json');
        }

        $offer->update($data);

        // Update or create associated Deal
        try {
            $dealId = strtoupper($offer->type ?? '') === 'BUY' ? $offer->purchase_id : $offer->sale_id;
            $deal = $dealId ? Deal::find($dealId) : null;
            if (!$deal) {
                $deal = new Deal();
            }

            $deal->meta_company_id = $data['meta_company_id'] ?? $deal->meta_company_id;
            $deal->contact_id = $data['contact_id'] ?? $deal->contact_id;
            $deal->address_id = $data['address_id'] ?? $deal->address_id;
            $deal->contact_representative_id = $data['contact_representative_id'] ?? $deal->contact_representative_id;
            $deal->currency_id = $data['currency_id'] ?? $deal->currency_id;
            $deal->incoterms = $data['incoterms'] ?? $deal->incoterms;
            $deal->payment_type_id = $data['payment_type_id'] ?? $deal->payment_type_id;
            $deal->payment_terms_type_id = $data['payment_terms_type_id'] ?? $deal->payment_terms_type_id;
            $deal->paymentTerms = $data['payment_terms'] ?? $deal->paymentTerms;
            if (!empty($data['agree_date'])) {
                $deal->paymentDate = date('Y-m-d', strtotime($data['agree_date']));
            }
            $deal->offer_id = $offer->id;
            $deal->save();

            if (strtoupper($offer->type ?? '') === 'BUY') {
                $offer->purchase_id = $deal->id;
            } else {
                $offer->sale_id = $deal->id;
            }
            $offer->save();

            // Deal products
            if (!empty($data['products_json'])) {
                $productsList = is_string($data['products_json']) ? json_decode($data['products_json'], true) : $data['products_json'];
                if (is_array($productsList)) {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('deal_products')) {
                            $oldDpIds = DB::table('deal_products')->where('deal_id', $deal->id)->pluck('products_id');
                            if ($oldDpIds->isNotEmpty()) {
                                DealProduct::whereIn('id', $oldDpIds)->delete();
                                DB::table('deal_products')->where('deal_id', $deal->id)->delete();
                            }
                        }
                    } catch (\Throwable $e) {}

                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'deal_id')) {
                            DealProduct::where('deal_id', $deal->id)->delete();
                        }
                    } catch (\Throwable $e) {}

                    $dealTotal = 0;
                    $dealQty = 0;
                    foreach ($productsList as $p) {
                        $qty = (float)($p['quantity'] ?? 0);
                        $price = (float)($p['unit_price'] ?? 0);
                        $total = (float)($p['total'] ?? ($qty * $price));
                        $dealTotal += $total;
                        $dealQty += $qty;

                        $dp = new DealProduct();
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'deal_id')) {
                            $dp->deal_id = $deal->id;
                        }
                        $dp->product_id = $p['product_id'] ?? null;
                        $dp->quantity = $qty;
                        $dp->rate = $price;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'unitPrice')) {
                            $dp->unitPrice = $price;
                        }
                        $dp->total_price = $total;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'total')) {
                            $dp->total = $total;
                        }
                        $dp->pricing_type = $p['pricing_type'] ?? 'CUSTOM';
                        if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'pricingType')) {
                            $dp->pricingType = $p['pricing_type'] ?? 'CUSTOM';
                        }
                        $dp->uom = $p['uom'] ?? 'MT';
                        $dp->version = 0;
                        $dp->save();

                        if (\Illuminate\Support\Facades\Schema::hasTable('deal_products')) {
                            DB::table('deal_products')->insert([
                                'deal_id' => $deal->id,
                                'products_id' => $dp->id
                            ]);
                        }
                    }
                    $deal->total = $dealTotal;
                    $deal->netTotal = $dealTotal;
                    $deal->save();

                    $offer->total = $dealTotal;
                    $offer->quantity = $dealQty;
                    $offer->save();
                }
            }
        } catch (\Throwable $e) {}

        // Log action
        try {
            $colDataId = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'data_id') ? 'data_id' : 'dataId';
            $colDateTime = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'date_time') ? 'date_time' : 'dateTime';
            $colUserId = \Illuminate\Support\Facades\Schema::hasColumn('logs', 'user_id') ? 'user_id' : 'userId';

            DB::table('logs')->insert([
                'module' => 'OFFER',
                $colDataId => $offer->id,
                $colDateTime => now(),
                $colUserId => Auth::id() ?? 1,
                'action' => 'UPDATED',
                'history' => json_encode(['action' => 'Offer details updated']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('offers.show', $offer->id)->with('success', 'Offer details updated successfully!');
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();

        return redirect()->route('offers.index')->with('success', 'Offer deleted successfully!');
    }

    public function convertToContract($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->status = 'CONVERTED';
        $offer->save();

        return redirect()->route('contracts.board')->with('success', "Offer {$offer->code} converted to contract!");
    }
}
