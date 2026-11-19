<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ModelWorkflow;
use App\Models\Stage;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Currency;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\Comment;
use App\Models\Checkitem;
use App\Models\Template;
use App\Models\Log;
use App\Models\GeneratedFile;
use App\Models\Country;
use App\Models\User;
use App\Services\OrderCodeService;
use App\Services\ContractActionService;
use App\Services\ContractAssociationService;
use App\Services\DocumentTemplateService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebContractController extends Controller
{
    protected OrderCodeService $orderCodeService;
    protected ContractActionService $actionService;
    protected ContractAssociationService $associationService;
    protected DocumentTemplateService $docService;
    protected AuditLogService $auditService;

    public function __construct(
        OrderCodeService $orderCodeService,
        ContractActionService $actionService,
        ContractAssociationService $associationService,
        DocumentTemplateService $docService,
        AuditLogService $auditService
    ) {
        $this->orderCodeService = $orderCodeService;
        $this->actionService = $actionService;
        $this->associationService = $associationService;
        $this->docService = $docService;
        $this->auditService = $auditService;
    }

    public function board(Request $request, $modelId = null)
    {
        $models = ModelWorkflow::where('active', true)->get();
        $currentModel = $modelId ? ModelWorkflow::find($modelId) : $models->first();

        if (!$currentModel && $models->isNotEmpty()) {
            $currentModel = $models->first();
        }

        $stages = $currentModel ? $currentModel->stages()->orderBy('position')->get() : collect();

        $contracts = Contract::with([
            'stage',
            'purchase.metaCompany', 'purchase.contact', 'purchase.products.product',
            'sale.metaCompany', 'sale.contact', 'sale.products.product', 'sale.products.associatedContract',
            'checklists.checkitems', 'comments'
        ])
        ->where('active', true)
        ->where('status', '!=', 'CANCELLED')
        ->orderBy('id', 'desc')
        ->get();

        $companies = Company::with('bankAccounts')->where('active', true)->get();
        $contacts = Contact::with(['addresses.representatives'])->where('active', true)->get();
        $products = Product::where('active', true)->get();
        $currencies = Currency::all();
        $packTypes = PackType::all();
        $paymentTypes = PaymentType::all();
        $paymentTerms = PaymentTermsType::all();
        $countries = Country::all();
        $users = User::all();

        return view('contracts.board', compact(
            'models', 'currentModel', 'stages', 'contracts',
            'companies', 'contacts', 'products', 'currencies',
            'packTypes', 'paymentTypes', 'paymentTerms', 'countries', 'users'
        ));
    }

    public function cardModal($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany.bankAccounts',
            'purchase.contact.addresses.representatives',
            'purchase.address',
            'purchase.contactRepresentative',
            'purchase.shippingAddress',
            'purchase.countryOfOrigin',
            'purchase.currency',
            'purchase.paymentType',
            'purchase.paymentTermsType',
            'purchase.paymentAccount',
            'purchase.products.product',
            'sale.metaCompany.bankAccounts',
            'sale.contact.addresses.representatives',
            'sale.address',
            'sale.contactRepresentative',
            'sale.shippingAddress',
            'sale.countryOfOrigin',
            'sale.currency',
            'sale.paymentType',
            'sale.paymentTermsType',
            'sale.paymentAccount',
            'sale.products.product',
            'packingLists.packs.packType',
            'generatedFiles.template',
            'uploadedFiles',
            'checklists.checkitems',
            'comments.user'
        ])->findOrFail($id);

        $stages = Stage::where('model_id', $contract->stage?->model_id ?? 1)->orderBy('position')->get();
        $templates = Template::all();
        $companies = Company::with('bankAccounts')->where('active', true)->get();
        $contacts = Contact::with(['addresses.representatives'])->where('active', true)->get();
        $products = Product::where('active', true)->get();
        $currencies = Currency::all();
        $packTypes = PackType::all();
        $paymentTypes = PaymentType::all();
        $paymentTerms = PaymentTermsType::all();
        $countries = Country::all();
        $users = User::all();

        $activities = Log::with('user')
            ->where('module', 'CONTRACT')
            ->where('data_id', $contract->id)
            ->orderBy('date_time', 'desc')
            ->get();

        return view('contracts.partials.card-modal', compact(
            'contract', 'stages', 'templates', 'companies', 'contacts', 
            'products', 'currencies', 'packTypes', 'paymentTypes', 'paymentTerms', 
            'countries', 'activities', 'users'
        ));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::with(['purchase', 'sale'])->findOrFail($id);

        if ($request->has('dueDate') || $request->has('due_date')) {
            $rawDate = $request->input('dueDate') ?: $request->input('due_date');
            $contract->due_date = Contract::normalizeDateValue($rawDate) ?: $contract->due_date;
        }
        if ($request->has('type')) {
            $contract->type = $request->input('type');
        }
        if ($request->has('description')) {
            $contract->description = $request->input('description');
        }
        if ($request->has('weight_tolerance') || $request->has('weightTolerance')) {
            $contract->weight_tolerance = $request->input('weight_tolerance') ?: $request->input('weightTolerance');
        }
        if ($request->has('mode_of_transport') || $request->has('modeOfTransport')) {
            $contract->mode_of_transport = $request->input('mode_of_transport') ?: $request->input('modeOfTransport');
        }
        if ($request->has('port_of_loading') || $request->has('portOfLoading')) {
            $contract->port_of_loading = $request->input('port_of_loading') ?: $request->input('portOfLoading');
        }
        if ($request->has('port_of_delivery') || $request->has('portOfDelivery')) {
            $contract->port_of_delivery = $request->input('port_of_delivery') ?: $request->input('portOfDelivery');
        }
        $contract->save();

        // Update active deal (Purchase or Sale)
        $dealTarget = null;
        if (in_array($contract->type, ['BUY', 'BUY_AND_SELL']) && $contract->purchase) {
            $dealTarget = $contract->purchase;
        } elseif ($contract->sale) {
            $dealTarget = $contract->sale;
        }

        if ($dealTarget) {
            $dealFields = [];
            if ($request->filled('meta_company_id')) $dealFields['meta_company_id'] = $request->input('meta_company_id');
            if ($request->filled('payment_account_id')) $dealFields['payment_account_id'] = $request->input('payment_account_id');
            if ($request->filled('payment_date')) $dealFields['payment_date'] = Contract::normalizeDateValue($request->input('payment_date'));
            if ($request->filled('country_of_origin_id')) $dealFields['country_of_origin_id'] = $request->input('country_of_origin_id');
            if ($request->filled('contact_id')) $dealFields['contact_id'] = $request->input('contact_id');
            if ($request->filled('address_id')) $dealFields['address_id'] = $request->input('address_id');
            if ($request->filled('contact_representative_id')) $dealFields['contact_representative_id'] = $request->input('contact_representative_id');
            if ($request->filled('shipping_address_id')) $dealFields['shipping_address_id'] = $request->input('shipping_address_id');
            if ($request->filled('currency_id')) $dealFields['currency_id'] = $request->input('currency_id');
            if ($request->filled('dollar_convertion_rate')) $dealFields['dollar_convertion_rate'] = $request->input('dollar_convertion_rate');
            if ($request->filled('incoterms')) $dealFields['incoterms'] = $request->input('incoterms');
            if ($request->filled('external_order_number')) $dealFields['external_order_number'] = $request->input('external_order_number');
            if ($request->filled('payment_type_id')) $dealFields['payment_type_id'] = $request->input('payment_type_id');
            if ($request->filled('payment_terms_type_id')) $dealFields['payment_terms_type_id'] = $request->input('payment_terms_type_id');
            if ($request->filled('payment_terms')) $dealFields['payment_terms'] = $request->input('payment_terms');
            if ($request->filled('advanced_payment')) $dealFields['advanced_payment'] = $request->input('advanced_payment');
            if ($request->filled('notes')) $dealFields['notes'] = $request->input('notes');

            if (!empty($dealFields)) {
                $dealTarget->update($dealFields);
            }
        }

        $this->auditService->register('CONTRACT', $contract->id, 'UPDATED', json_encode($request->all()));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contract updated successfully',
                'contract' => $contract
            ]);
        }

        return back()->with('success', 'Contract updated successfully');
    }

    public function moveStage(Request $request, $contractId)
    {
        $stageId = (int) $request->input('stage_id');
        try {
            $this->actionService->move((int) $contractId, $stageId, Auth::id());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contract moved successfully',
                    'contract_id' => $contractId,
                    'stage_id' => $stageId
                ]);
            }
            return back()->with('success', 'Contract moved successfully');
        } catch (\Throwable $e) {
            try {
                $contract = Contract::findOrFail($contractId);
                $contract->stage_id = $stageId;
                $contract->save();
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Contract moved successfully',
                        'contract_id' => $contractId,
                        'stage_id' => $stageId
                    ]);
                }
                return back()->with('success', 'Contract moved successfully');
            } catch (\Throwable $fallbackEx) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                return back()->with('error', $e->getMessage());
            }
        }
    }

    public function store(Request $request)
    {
        $rawType = $request->input('type', 'BUY_AND_SELL');
        if ($rawType === 'Purchase & Sales' || $rawType === 'BUY_AND_SELL') {
            $type = 'BUY_AND_SELL';
        } elseif ($rawType === 'Purchase' || $rawType === 'BUY') {
            $type = 'BUY';
        } else {
            $type = 'SELL';
        }

        $firstStage = Stage::where('model_id', $request->input('model_id', 1))->orderBy('position')->first();

        // Parse due_date
        $dueDate = now();
        if ($request->filled('order_date')) {
            $dueDate = Contract::normalizeDateValue($request->input('order_date')) ?: now();
        } elseif ($request->filled('due_date')) {
            $dueDate = Contract::normalizeDateValue($request->input('due_date')) ?: now();
        }

        $purchaseDeal = null;
        $saleDeal = null;

        // 1. Process Buying from deal (Purchase) if BUY or BUY_AND_SELL
        if (in_array($type, ['BUY', 'BUY_AND_SELL'])) {
            $buyingCompanyId = $request->input('buying_meta_company_id') ?: $request->input('meta_company_id') ?: Company::first()?->id;
            $buyingContactId = $request->input('buying_contact_id') ?: $request->input('contact_id') ?: Contact::first()?->id;
            $buyingContact = Contact::with(['addresses', 'representatives'])->find($buyingContactId);

            $buyingAddressId = $request->input('buying_contact_address_id') ?: $request->input('contact_address_id') ?: $buyingContact?->addresses?->first()?->id;
            $buyingRepId = $request->input('buying_contact_person_id') ?: $request->input('contact_person_id') ?: $buyingContact?->representatives?->first()?->id;
            $buyingCollectionAddressId = $request->input('buying_collection_address_id') ?: $request->input('collection_address_id') ?: $buyingAddressId;
            $buyingBankAccountId = $request->input('buying_payment_bank_account_id') ?: $request->input('payment_bank_account_id');
            $buyingCurrencyId = $request->input('buying_currency_id') ?: $request->input('currency_id') ?: Currency::first()?->id;
            $buyingCountryId = $request->input('buying_country_of_origin_id') ?: $request->input('country_id') ?: Country::first()?->id;

            $buyingPaymentDate = null;
            if ($request->filled('buying_payment_date') || $request->filled('payment_date')) {
                $pDate = $request->input('buying_payment_date') ?: $request->input('payment_date');
                $buyingPaymentDate = Contract::normalizeDateValue($pDate);
            }

            $purchaseDeal = Deal::create([
                'meta_company_id' => $buyingCompanyId,
                'contact_id' => $buyingContactId,
                'contact_representative_id' => $buyingRepId,
                'address_id' => $buyingAddressId,
                'shipping_address_id' => $buyingCollectionAddressId,
                'payment_account_id' => $buyingBankAccountId,
                'currency_id' => $buyingCurrencyId,
                'country_of_origin_id' => $buyingCountryId,
                'dollar_convertion_rate' => (float) ($request->input('buying_dollar_conversion') ?: $request->input('dollar_conversion') ?: 1.0),
                'incoterms' => $request->input('buying_incoterms') ?: $request->input('incoterms') ?: 'CIF JEDDAH',
                'external_order_number' => $request->input('buying_customer_order_number') ?: $request->input('customer_order_number'),
                'payment_type_id' => $request->input('buying_payment_type_id') ?: $request->input('payment_type_id'),
                'payment_terms_type_id' => $request->input('buying_payment_terms_type_id') ?: $request->input('payment_terms_type_id'),
                'payment_terms' => $request->input('buying_payment_terms') ?: $request->input('payment_terms') ?: '100%',
                'advanced_payment' => $request->input('buying_advanced_payment') ? (float) $request->input('buying_advanced_payment') : null,
                'notes' => $request->input('buying_notes') ?: $request->input('notes'),
                'payment_date' => $buyingPaymentDate,
                'payment_status' => $buyingPaymentDate ? 'PAID' : 'PENDING',
            ]);

            // Save buying products
            $buyingProducts = $request->input('buying_products');
            if (is_array($buyingProducts) && count($buyingProducts) > 0) {
                foreach ($buyingProducts as $pData) {
                    if (empty($pData['product_id'])) continue;
                    $qty = (float) ($pData['quantity'] ?? 0);
                    $rate = (float) ($pData['unit_price'] ?? 0);
                    $rawPricing = strtoupper(trim((string)($pData['pricing_type'] ?? 'CUSTOM')));
                    $pricingType = ($rawPricing === 'LME' || $rawPricing === 'FORMULA') ? 'LME' : 'CUSTOM';
                    $dealProduct = DealProduct::create([
                        'product_id' => $pData['product_id'],
                        'quantity' => $qty,
                        'rate' => $rate,
                        'uom' => $pData['uom'] ?? 'MT',
                        'total_price' => $qty * $rate,
                        'pricing_type' => $pricingType,
                        'status' => 'STORED',
                        'active' => true,
                    ]);
                    $purchaseDeal->products()->attach($dealProduct->id);
                }
            } else {
                $productId = $request->input('product_id') ?: Product::first()?->id;
                $quantity = (float) ($request->input('quantity') ?: 0);
                $unitPrice = (float) ($request->input('unit_price') ?: 0);
                if ($productId) {
                    $dealProduct = DealProduct::create([
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'rate' => $unitPrice,
                        'uom' => 'MT',
                        'total_price' => $quantity * $unitPrice,
                        'pricing_type' => 'CUSTOM',
                        'status' => 'STORED',
                        'active' => true,
                    ]);
                    $purchaseDeal->products()->attach($dealProduct->id);
                }
            }
        }

        // 2. Process Selling to deal (Sale) if SELL or BUY_AND_SELL
        if (in_array($type, ['SELL', 'BUY_AND_SELL'])) {
            $sellingCompanyId = $request->input('selling_meta_company_id') ?: $request->input('meta_company_id') ?: Company::first()?->id;
            $sellingContactId = $request->input('selling_contact_id') ?: $request->input('contact_id') ?: Contact::first()?->id;
            $sellingContact = Contact::with(['addresses', 'representatives'])->find($sellingContactId);

            $sellingAddressId = $request->input('selling_contact_address_id') ?: $request->input('contact_address_id') ?: $sellingContact?->addresses?->first()?->id;
            $sellingRepId = $request->input('selling_contact_person_id') ?: $request->input('contact_person_id') ?: $sellingContact?->representatives?->first()?->id;
            $sellingShippingAddressId = $request->input('selling_shipping_address_id') ?: $request->input('shipping_address_id') ?: $sellingAddressId;
            $sellingBankAccountId = $request->input('selling_payment_bank_account_id') ?: $request->input('payment_bank_account_id');
            $sellingCurrencyId = $request->input('selling_currency_id') ?: $request->input('currency_id') ?: Currency::first()?->id;
            $sellingCountryId = $request->input('selling_country_of_origin_id') ?: $request->input('country_id') ?: Country::first()?->id;

            $sellingPaymentDate = null;
            if ($request->filled('selling_payment_date')) {
                $sellingPaymentDate = Contract::normalizeDateValue($request->input('selling_payment_date'));
            }

            $saleDeal = Deal::create([
                'meta_company_id' => $sellingCompanyId,
                'contact_id' => $sellingContactId,
                'contact_representative_id' => $sellingRepId,
                'address_id' => $sellingAddressId,
                'shipping_address_id' => $sellingShippingAddressId,
                'payment_account_id' => $sellingBankAccountId,
                'currency_id' => $sellingCurrencyId,
                'country_of_origin_id' => $sellingCountryId,
                'dollar_convertion_rate' => (float) ($request->input('selling_dollar_conversion') ?: 1.0),
                'incoterms' => $request->input('selling_incoterms') ?: 'CIF JEDDAH',
                'external_order_number' => $request->input('selling_customer_order_number'),
                'payment_type_id' => $request->input('selling_payment_type_id') ?: $request->input('payment_type_id'),
                'payment_terms_type_id' => $request->input('selling_payment_terms_type_id') ?: $request->input('payment_terms_type_id'),
                'payment_terms' => $request->input('selling_payment_terms') ?: '100%',
                'advanced_payment' => $request->input('selling_advanced_payment') ? (float) $request->input('selling_advanced_payment') : null,
                'notes' => $request->input('selling_notes'),
                'payment_date' => $sellingPaymentDate,
                'payment_status' => $sellingPaymentDate ? 'PAID' : 'PENDING',
            ]);

            // Save selling products
            $sellingProducts = $request->input('selling_products');
            if (is_array($sellingProducts) && count($sellingProducts) > 0) {
                foreach ($sellingProducts as $pData) {
                    if (empty($pData['product_id'])) continue;
                    $qty = (float) ($pData['quantity'] ?? 0);
                    $rate = (float) ($pData['unit_price'] ?? 0);
                    $rawPricing = strtoupper(trim((string)($pData['pricing_type'] ?? 'CUSTOM')));
                    $pricingType = ($rawPricing === 'LME' || $rawPricing === 'FORMULA') ? 'LME' : 'CUSTOM';
                    $dealProduct = DealProduct::create([
                        'product_id' => $pData['product_id'],
                        'quantity' => $qty,
                        'rate' => $rate,
                        'uom' => $pData['uom'] ?? 'MT',
                        'total_price' => $qty * $rate,
                        'pricing_type' => $pricingType,
                        'status' => 'STORED',
                        'active' => true,
                    ]);
                    $saleDeal->products()->attach($dealProduct->id);
                }
            } elseif ($type === 'SELL') {
                $productId = $request->input('product_id') ?: Product::first()?->id;
                $quantity = (float) ($request->input('quantity') ?: 0);
                $unitPrice = (float) ($request->input('unit_price') ?: 0);
                if ($productId) {
                    $dealProduct = DealProduct::create([
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'rate' => $unitPrice,
                        'uom' => 'MT',
                        'total_price' => $quantity * $unitPrice,
                        'pricing_type' => 'CUSTOM',
                        'status' => 'STORED',
                        'active' => true,
                    ]);
                    $saleDeal->products()->attach($dealProduct->id);
                }
            }
        }

        // 3. Create Contract
        $contract = Contract::create([
            'due_date' => $dueDate,
            'type' => $type,
            'stage_id' => $firstStage?->id ?? 1,
            'purchase_id' => $purchaseDeal?->id,
            'sale_id' => $saleDeal?->id,
            'status' => 'OPEN',
            'description' => $request->input('description'),
            'mode_of_transport' => $request->input('mode_of_transport', 'SEA'),
            'weight_tolerance' => $request->input('weight_tolerance') ? (float) $request->input('weight_tolerance') : null,
            'active' => true,
            'version' => 0,
        ]);

        try {
            $contract->orderCode = $this->orderCodeService->orderCodeGenerator($contract);
            $contract->save();
        } catch (\Throwable $e) {
            $contract->orderCode = sprintf('ME-COM%s-%d', date('y'), $contract->id);
            $contract->save();
        }

        $this->auditService->register('CONTRACT', $contract->id, 'CREATE', json_encode($contract));

        return redirect()->route('contracts.board', ['modelId' => $firstStage?->model_id])
            ->with('success', "Contract {$contract->orderCode} created successfully!");
    }

    public function cancel($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'CANCELLED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CANCEL', null);
        return back()->with('info', "Contract {$contract->orderCode} was cancelled");
    }

    public function close($id)
    {
        $contract = Contract::findOrFail($id);
        if ($contract->type === 'BUY') {
            try {
                $this->associationService->verifyPurchaseContractSaleAssociations($contract->id);
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $contract->contractStatus = 'CLOSED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CLOSE', null);
        return back()->with('success', "Contract {$contract->orderCode} was closed");
    }

    public function addComment(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        Comment::create([
            'contract_id' => $id,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
        ]);

        return back()->with('success', 'Comment added');
    }

    public function toggleCheckitem($itemId)
    {
        $item = Checkitem::findOrFail($itemId);
        $item->checked = !$item->checked;
        $item->save();

        return response()->json(['checked' => $item->checked]);
    }

    public function generateDoc(Request $request, $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $template = Template::findOrFail($request->input('template_id'));

        try {
            $doc = $this->docService->render($template, $contract, Auth::id());
            return back()->with('success', "Document {$doc->fileName} generated successfully!");
        } catch (\Throwable $e) {
            return back()->with('error', "Document generation failed: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.contact',
            'purchase.address',
            'purchase.contactRepresentative',
            'purchase.paymentAccount',
            'purchase.paymentType',
            'purchase.paymentTermsType',
            'purchase.products.product',
            'sale.metaCompany',
            'sale.contact',
            'sale.address',
            'sale.contactRepresentative',
            'sale.paymentAccount',
            'sale.paymentType',
            'sale.paymentTermsType',
            'sale.products.product',
            'generatedFiles',
            'uploadedFiles',
            'packingLists.packs.packType',
            'packingLists.packs.product',
            'comments.user',
        ])->where('id', $id)->orWhere('order_code', $id)->firstOrFail();

        $activities = Log::with('user')
            ->where('module', 'CONTRACT')
            ->where('data_id', $contract->id)
            ->orderBy('date_time', 'desc')
            ->get();

        return view('contracts.show', compact('contract', 'activities'));
    }
}
