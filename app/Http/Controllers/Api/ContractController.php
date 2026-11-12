<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\Product;
use App\Models\UploadedFile;
use App\Models\Archive;
use App\Models\Stage;
use App\Services\OrderCodeService;
use App\Services\ContractActionService;
use App\Services\ContractAssociationService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractController extends BaseApiController
{
    protected OrderCodeService $orderCodeService;
    protected ContractActionService $actionService;
    protected ContractAssociationService $associationService;
    protected AuditLogService $auditService;

    public function __construct(
        OrderCodeService $orderCodeService,
        ContractActionService $actionService,
        ContractAssociationService $associationService,
        AuditLogService $auditService
    ) {
        $this->orderCodeService = $orderCodeService;
        $this->actionService = $actionService;
        $this->associationService = $associationService;
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.contact',
            'sale.metaCompany',
            'sale.contact',
            'dealProducts.product',
        ])
        ->where('active', true)
        ->orderBy('id', 'desc')
        ->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($contract) {
            return $this->formatContractListItem($contract);
        });

        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.metaRepresentative',
            'purchase.contact',
            'purchase.contactRepresentative',
            'purchase.address',
            'purchase.shippingAddress',
            'purchase.currency',
            'purchase.paymentType',
            'purchase.paymentTermsType',
            'purchase.paymentAccount',
            'purchase.countryOfOrigin',
            'sale.metaCompany',
            'sale.metaRepresentative',
            'sale.contact',
            'sale.contactRepresentative',
            'sale.address',
            'sale.shippingAddress',
            'sale.currency',
            'sale.paymentType',
            'sale.paymentTermsType',
            'sale.paymentAccount',
            'sale.countryOfOrigin',
            'dealProducts.product',
            'packingLists.packs',
            'generatedFiles',
            'uploadedFiles',
            'checklists.checkitems',
            'comments',
            'members',
            'tags',
        ])->findOrFail($id);

        return response()->json($this->formatContractDetail($contract));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $type = $request->input('type', 'BUY');
            $stageId = $request->input('stageContractId') ?: $request->input('stage_id') ?: 1;

            $contract = new Contract();
            $contract->type = $type;
            $contract->stage_id = $stageId;
            $contract->stage_contract_id = $stageId;
            $contract->status = 'OPEN';
            $contract->active = true;
            $contract->version = 1;

            $this->applyContractFields($contract, $request->all());

            // Build Purchase Deal if BUY or BUY_AND_SELL
            if (in_array($type, ['BUY', 'BUY_AND_SELL'])) {
                $purchaseData = $request->input('purchase') ?: [];
                $purchaseDeal = $this->createOrUpdateDeal(null, $purchaseData);
                $contract->purchase_id = $purchaseDeal->id;
            }

            // Build Sale Deal if SELL or BUY_AND_SELL
            if (in_array($type, ['SELL', 'BUY_AND_SELL'])) {
                $saleData = $request->input('sale') ?: [];
                $saleDeal = $this->createOrUpdateDeal(null, $saleData);
                $contract->sale_id = $saleDeal->id;
            }

            $contract->save();

            // Order Code generation
            if ($request->filled('orderCode')) {
                $contract->order_code = $request->input('orderCode');
            } else {
                try {
                    $contract->order_code = $this->orderCodeService->orderCodeGenerator($contract);
                } catch (\Throwable $e) {
                    $contract->order_code = sprintf('ME-COM%s-%d', date('y'), $contract->id);
                }
            }
            $contract->save();

            // Save Products
            $this->syncDealProducts($contract, $request->all());

            // Stage IN actions
            try {
                $targetStage = Stage::with('stageActions.action')->find($stageId);
                if ($targetStage) {
                    foreach ($targetStage->stageActions as $sa) {
                        if ($sa->trigger_type === 'IN') {
                            $this->actionService->move($contract->id, $stageId, $request->user()?->id);
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Non-fatal for contract creation
            }

            $this->auditService->register('CONTRACT', $contract->id, 'CREATE', json_encode($contract));

            return response()->json($this->formatContractDetail($contract->fresh([
                'stage', 'purchase.metaCompany', 'purchase.contact',
                'sale.metaCompany', 'sale.contact', 'dealProducts.product',
                'checklists.checkitems', 'comments', 'members', 'tags'
            ])), 201);
        });
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $contract = Contract::findOrFail($id);

            $this->applyContractFields($contract, $request->all());

            if ($request->has('stageContractId') || $request->has('stage_id')) {
                $sId = $request->input('stageContractId') ?: $request->input('stage_id');
                $contract->stage_id = $sId;
                $contract->stage_contract_id = $sId;
            }

            // Update Purchase Deal
            if ($request->has('purchase') && $contract->purchase_id) {
                $this->createOrUpdateDeal($contract->purchase_id, $request->input('purchase'));
            } elseif ($request->has('purchase') && !$contract->purchase_id && in_array($contract->type, ['BUY', 'BUY_AND_SELL'])) {
                $pDeal = $this->createOrUpdateDeal(null, $request->input('purchase'));
                $contract->purchase_id = $pDeal->id;
            }

            // Update Sale Deal
            if ($request->has('sale') && $contract->sale_id) {
                $this->createOrUpdateDeal($contract->sale_id, $request->input('sale'));
            } elseif ($request->has('sale') && !$contract->sale_id && in_array($contract->type, ['SELL', 'BUY_AND_SELL'])) {
                $sDeal = $this->createOrUpdateDeal(null, $request->input('sale'));
                $contract->sale_id = $sDeal->id;
            }

            $contract->save();

            // Sync Deal Products
            $this->syncDealProducts($contract, $request->all());

            $this->auditService->register('CONTRACT', $contract->id, 'UPDATE', json_encode($contract));

            return response()->json($this->formatContractDetail($contract->fresh([
                'stage', 'purchase.metaCompany', 'purchase.contact',
                'sale.metaCompany', 'sale.contact', 'dealProducts.product',
                'checklists.checkitems', 'comments', 'members', 'tags'
            ])));
        });
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->active = false;
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'DELETE', null);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function move(Request $request, $contractId, $stageId)
    {
        try {
            $this->actionService->move((int) $contractId, (int) $stageId, $request->user()?->id);
            return response()->json('OK', 200);
        } catch (\Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return response()->json($e->getMessage(), $code);
        }
    }

    public function associateContract($dealProductId, $purchaseContractId)
    {
        $dp = $this->associationService->associate((int) $dealProductId, (int) $purchaseContractId);
        return response()->json('OK');
    }

    public function listContractsForAssociation($productId)
    {
        $contracts = Contract::with(['purchase.metaCompany', 'dealProducts.product'])
            ->where('type', 'BUY')
            ->where('status', 'OPEN')
            ->where('active', true)
            ->whereHas('dealProducts', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->get();

        $result = $contracts->map(function ($c) use ($productId) {
            return [
                'id' => $c->id,
                'orderCode' => $c->order_code,
                'description' => $c->description,
                'purchaseCompany' => $c->purchase?->metaCompany?->name,
                'dealProducts' => $c->dealProducts->where('product_id', $productId)->values(),
            ];
        });

        return response()->json($result);
    }

    public function listPendingContracts($contractId, $productId)
    {
        $contracts = Contract::with(['dealProducts'])
            ->where('type', 'SELL')
            ->where('status', '!=', 'CANCELLED')
            ->where('id', '!=', $contractId)
            ->whereHas('dealProducts', function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->whereNull('associated_contract_id');
            })
            ->pluck('order_code');

        return response()->json($contracts);
    }

    public function listByStatus($status)
    {
        $contracts = Contract::with([
            'stage', 'purchase.metaCompany', 'purchase.contact',
            'sale.metaCompany', 'sale.contact', 'dealProducts.product'
        ])
        ->where('status', strtoupper($status))
        ->where('active', true)
        ->get();

        return response()->json($contracts->map(fn($c) => $this->formatContractDetail($c)));
    }

    public function listByCriteria(Request $request)
    {
        $query = Contract::with([
            'stage', 'purchase.metaCompany', 'purchase.contact',
            'sale.metaCompany', 'sale.contact', 'dealProducts.product'
        ])->where('active', true);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('startDate')) {
            $startDate = $this->parseTimestamp($request->input('startDate'));
            if ($startDate) $query->where('due_date', '>=', $startDate);
        }
        if ($request->filled('endDate')) {
            $endDate = $this->parseTimestamp($request->input('endDate'));
            if ($endDate) $query->where('due_date', '<=', $endDate);
        }

        $contracts = $query->orderBy('id', 'desc')->get();
        return response()->json($contracts->map(fn($c) => $this->formatContractDetail($c)));
    }

    public function listAssociatedContractsIds($saleContractId)
    {
        $contract = Contract::with('dealProducts')->findOrFail($saleContractId);
        $ids = $contract->dealProducts
            ->whereNotNull('associated_contract_id')
            ->pluck('associated_contract_id')
            ->unique()
            ->values();

        return response()->json($ids);
    }

    public function listAssociatedContractsOrderCode($saleContractId)
    {
        $contract = Contract::with('dealProducts')->findOrFail($saleContractId);
        $orderCodes = $contract->dealProducts
            ->whereNotNull('contract_order_code')
            ->pluck('contract_order_code')
            ->unique()
            ->values();

        return response()->json($orderCodes);
    }

    public function listProductsRemainingBalance($purchaseContractId)
    {
        $contract = Contract::with('dealProducts.product')->findOrFail($purchaseContractId);
        $products = [];

        foreach ($contract->dealProducts as $dp) {
            $allocatedQty = (float) DealProduct::where('associated_contract_id', $purchaseContractId)
                ->where('product_id', $dp->product_id)
                ->sum('quantity');

            $remaining = max(0, ((float) $dp->quantity) - $allocatedQty);
            $products[] = [
                'productId' => $dp->product_id,
                'productName' => $dp->product?->name,
                'totalQuantity' => (float) $dp->quantity,
                'allocatedQuantity' => $allocatedQty,
                'remainingQuantity' => $remaining,
            ];
        }

        return response()->json($products);
    }

    public function cancel(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->status = 'CANCELLED';
        $contract->cancellation_reason = $request->input('cancellationReason');
        $contract->cancellation_date = now();
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CANCEL', null);
        return response()->json($this->formatContractDetail($contract));
    }

    public function close(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->type === 'BUY') {
            try {
                $this->associationService->verifyPurchaseContractSaleAssociations($contract->id);
            } catch (\Throwable $e) {
                return response()->json($e->getMessage(), 409);
            }
        }

        $contract->status = 'CLOSED';
        $contract->close_date = now();
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CLOSE', null);
        return response()->json($this->formatContractDetail($contract));
    }

    public function listFilesForContract($contractId)
    {
        $files = UploadedFile::where('contract_id', $contractId)->get();
        $res = $files->map(function ($f) use ($contractId) {
            return [
                'id' => $f->id,
                'fileName' => $f->file_name,
                'url' => url("/api/contracts/download/{$contractId}/files/{$f->id}"),
            ];
        });

        return response()->json($res);
    }

    public function uploadContractFiles(Request $request, $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file provided'], 400);
        }

        $file = $request->file('file');
        $origName = $file->getClientOriginalName();
        $orderCode = $contract->order_code ?: "CONTRACT_{$contract->id}";
        $safeName = pathinfo($origName, PATHINFO_FILENAME) . "_{$orderCode}." . $file->getClientOriginalExtension();

        $path = $file->storeAs("contracts/{$orderCode}", $safeName, 'public');

        $uploaded = UploadedFile::create([
            'contract_id' => $contract->id,
            'file_name' => $safeName,
            'url' => Storage::disk('public')->url($path),
        ]);

        return response()->json([
            'message' => "Uploaded the file successfully: {$safeName}",
            'file' => [
                'id' => $uploaded->id,
                'fileName' => $uploaded->file_name,
                'url' => url("/api/contracts/download/{$contract->id}/files/{$uploaded->id}"),
            ]
        ]);
    }

    public function deleteFileFromContract($contractId, $fileId)
    {
        $file = UploadedFile::where('contract_id', $contractId)->where('id', $fileId)->first();
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $contract = Contract::find($contractId);
        $orderCode = $contract?->order_code ?: "CONTRACT_{$contractId}";
        Storage::disk('public')->delete("contracts/{$orderCode}/{$file->file_name}");

        $file->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }

    public function downloadFileFromDisk($contractId, $fileId)
    {
        $file = UploadedFile::where('contract_id', $contractId)->where('id', $fileId)->firstOrFail();
        $contract = Contract::findOrFail($contractId);
        $orderCode = $contract->order_code ?: "CONTRACT_{$contractId}";
        $path = "contracts/{$orderCode}/{$file->file_name}";

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found on disk'], 404);
        }

        return Storage::disk('public')->download($path, $file->file_name);
    }

    // Helper: apply contract fields mapping camelCase & snake_case
    private function applyContractFields(Contract $contract, array $data): void
    {
        if (isset($data['description'])) $contract->description = $data['description'];
        if (isset($data['currency'])) $contract->currency = $data['currency'];
        if (isset($data['paymentTerms']) || isset($data['payment_terms'])) {
            $contract->payment_terms = $data['paymentTerms'] ?? $data['payment_terms'];
        }
        if (isset($data['deliveryTerms']) || isset($data['delivery_terms'])) {
            $contract->delivery_terms = $data['deliveryTerms'] ?? $data['delivery_terms'];
        }
        if (isset($data['modeOfPayment']) || isset($data['mode_of_payment'])) {
            $contract->mode_of_payment = $data['modeOfPayment'] ?? $data['mode_of_payment'];
        }
        if (isset($data['qualityCheck']) || isset($data['quality_check'])) {
            $contract->quality_check = $data['qualityCheck'] ?? $data['quality_check'];
        }
        if (isset($data['packing'])) $contract->packing = $data['packing'];
        if (isset($data['suppliesNote']) || isset($data['supplies_note'])) {
            $contract->supplies_note = $data['suppliesNote'] ?? $data['supplies_note'];
        }
        if (isset($data['customerPurchaseOrder']) || isset($data['customer_purchase_order'])) {
            $contract->customer_purchase_order = $data['customerPurchaseOrder'] ?? $data['customer_purchase_order'];
        }
        if (isset($data['transportNotes']) || isset($data['transport_notes'])) {
            $contract->transport_notes = $data['transportNotes'] ?? $data['transport_notes'];
        }
        if (isset($data['transportPriceAgreed']) || isset($data['transport_price_agreed'])) {
            $contract->transport_price_agreed = (float) ($data['transportPriceAgreed'] ?? $data['transport_price_agreed']);
        }
        if (isset($data['transportSpecialNotes']) || isset($data['transport_special_notes'])) {
            $contract->transport_special_notes = $data['transportSpecialNotes'] ?? $data['transport_special_notes'];
        }
        if (isset($data['modeOfTransport']) || isset($data['mode_of_transport'])) {
            $contract->mode_of_transport = $data['modeOfTransport'] ?? $data['mode_of_transport'];
        }
        if (isset($data['portOfLoading']) || isset($data['port_of_loading'])) {
            $contract->port_of_loading = $data['portOfLoading'] ?? $data['port_of_loading'];
        }
        if (isset($data['portOfDelivery']) || isset($data['port_of_delivery'])) {
            $contract->port_of_delivery = $data['portOfDelivery'] ?? $data['port_of_delivery'];
        }
        if (isset($data['weightTolerance']) || isset($data['weight_tolerance'])) {
            $contract->weight_tolerance = (float) ($data['weightTolerance'] ?? $data['weight_tolerance']);
        }
        if (isset($data['salesInvoiceNumber']) || isset($data['sales_invoice_number'])) {
            $contract->sales_invoice_number = $data['salesInvoiceNumber'] ?? $data['sales_invoice_number'];
        }
        if (isset($data['salesOrder']) || isset($data['sales_order'])) {
            $contract->sales_order = $data['salesOrder'] ?? $data['sales_order'];
        }

        // Dates
        if (isset($data['dueDate']) || isset($data['due_date'])) {
            $contract->due_date = $this->parseTimestamp($data['dueDate'] ?? $data['due_date']);
        }
        if (isset($data['loadingDate']) || isset($data['loading_date'])) {
            $contract->loading_date = $this->parseTimestamp($data['loadingDate'] ?? $data['loading_date']);
        }
        if (isset($data['salesInvoiceDate']) || isset($data['sales_invoice_date'])) {
            $contract->sales_invoice_date = $this->parseTimestamp($data['salesInvoiceDate'] ?? $data['sales_invoice_date']);
        }
    }

    private function createOrUpdateDeal(?int $dealId, array $data): Deal
    {
        $deal = $dealId ? Deal::find($dealId) : new Deal();
        if (!$deal) $deal = new Deal();

        $metaCompanyId = $data['metaCompany']['id'] ?? $data['meta_company_id'] ?? $data['purchaseCompany'] ?? $deal->meta_company_id;
        if ($metaCompanyId) $deal->meta_company_id = $metaCompanyId;

        if (isset($data['contact']['id']) || isset($data['contact_id'])) {
            $deal->contact_id = $data['contact']['id'] ?? $data['contact_id'];
        }
        if (isset($data['contactRepresentative']['id']) || isset($data['contact_representative_id'])) {
            $deal->contact_representative_id = $data['contactRepresentative']['id'] ?? $data['contact_representative_id'];
        }
        if (isset($data['metaRepresentative']['id']) || isset($data['meta_representative_id'])) {
            $deal->meta_representative_id = $data['metaRepresentative']['id'] ?? $data['meta_representative_id'];
        }
        if (isset($data['address']['id']) || isset($data['address_id'])) {
            $deal->address_id = $data['address']['id'] ?? $data['address_id'];
        }
        if (isset($data['shippingAddress']['id']) || isset($data['shipping_address_id'])) {
            $deal->shipping_address_id = $data['shippingAddress']['id'] ?? $data['shipping_address_id'];
        }
        if (isset($data['currency']['id']) || isset($data['currency_id'])) {
            $deal->currency_id = $data['currency']['id'] ?? $data['currency_id'];
        }
        if (isset($data['paymentType']['id']) || isset($data['payment_type_id'])) {
            $deal->payment_type_id = $data['paymentType']['id'] ?? $data['payment_type_id'];
        }
        if (isset($data['paymentTermsType']['id']) || isset($data['payment_terms_type_id'])) {
            $deal->payment_terms_type_id = $data['paymentTermsType']['id'] ?? $data['payment_terms_type_id'];
        }
        if (isset($data['paymentAccount']['id']) || isset($data['payment_account_id'])) {
            $deal->payment_account_id = $data['paymentAccount']['id'] ?? $data['payment_account_id'];
        }
        if (isset($data['countryOfOrigin']['id']) || isset($data['country_of_origin_id'])) {
            $deal->country_of_origin_id = $data['countryOfOrigin']['id'] ?? $data['country_of_origin_id'];
        }

        if (isset($data['dollarConvertionRate']) || isset($data['dollar_convertion_rate'])) {
            $deal->dollar_convertion_rate = (float) ($data['dollarConvertionRate'] ?? $data['dollar_convertion_rate']);
        }
        if (isset($data['advancedPayment']) || isset($data['advanced_payment'])) {
            $deal->advanced_payment = (float) ($data['advancedPayment'] ?? $data['advanced_payment']);
        }
        if (isset($data['paymentTerms']) || isset($data['payment_terms'])) {
            $deal->payment_terms = $data['paymentTerms'] ?? $data['payment_terms'];
        }
        if (isset($data['incoterms'])) $deal->incoterms = $data['incoterms'];
        if (isset($data['notes'])) $deal->notes = $data['notes'];
        if (isset($data['creditNoteText']) || isset($data['credit_note_text'])) {
            $deal->credit_note_text = $data['creditNoteText'] ?? $data['credit_note_text'];
        }
        if (isset($data['debitNoteText']) || isset($data['debit_note_text'])) {
            $deal->debit_note_text = $data['debitNoteText'] ?? $data['debit_note_text'];
        }
        if (isset($data['externalOrderNumber']) || isset($data['external_order_number'])) {
            $deal->external_order_number = $data['externalOrderNumber'] ?? $data['external_order_number'];
        }
        if (isset($data['paymentStatus']) || isset($data['payment_status'])) {
            $deal->payment_status = $data['paymentStatus'] ?? $data['payment_status'];
        }
        if (isset($data['paymentDate']) || isset($data['payment_date'])) {
            $deal->payment_date = $this->parseTimestamp($data['paymentDate'] ?? $data['payment_date']);
        }

        $deal->save();
        return $deal;
    }

    private function syncDealProducts(Contract $contract, array $data): void
    {
        $products = [];
        if (!empty($data['products'])) {
            $products = $data['products'];
        } elseif (!empty($data['purchase']['products'])) {
            $products = $data['purchase']['products'];
        } elseif (!empty($data['sale']['products'])) {
            $products = $data['sale']['products'];
        }

        if (empty($products)) {
            return;
        }

        foreach ($products as $prodData) {
            $dpId = $prodData['id'] ?? null;
            $dp = $dpId ? DealProduct::find($dpId) : new DealProduct();
            if (!$dp) $dp = new DealProduct();

            $dp->associated_contract_id = $contract->id;
            $dp->contract_order_code = $contract->order_code;
            $dp->active = true;

            $prodId = $prodData['product']['id'] ?? $prodData['product_id'] ?? null;
            if ($prodId) $dp->product_id = $prodId;

            $dp->quantity = (float) ($prodData['quantity'] ?? $dp->quantity ?? 0.0);
            $dp->rate = (float) ($prodData['rate'] ?? $prodData['unitPrice'] ?? $dp->rate ?? 0.0);
            $dp->total_price = (float) ($prodData['totalPrice'] ?? $prodData['total_price'] ?? ($dp->quantity * $dp->rate));

            if (isset($prodData['itemDescription']) || isset($prodData['item_description'])) {
                $dp->item_description = $prodData['itemDescription'] ?? $prodData['item_description'];
            }
            if (isset($prodData['itemSpecification']) || isset($prodData['item_specification'])) {
                $dp->item_specification = $prodData['itemSpecification'] ?? $prodData['item_specification'];
            }
            if (isset($prodData['pricingType']) || isset($prodData['pricing_type'])) {
                $dp->pricing_type = $prodData['pricingType'] ?? $prodData['pricing_type'];
            }
            if (isset($prodData['grossWeight']) || isset($prodData['gross_weight'])) {
                $dp->gross_weight = (float) ($prodData['grossWeight'] ?? $prodData['gross_weight']);
            }
            if (isset($prodData['netWeight']) || isset($prodData['net_weight'])) {
                $dp->net_weight = (float) ($prodData['netWeight'] ?? $prodData['net_weight']);
            }
            if (isset($prodData['uom'])) $dp->uom = $prodData['uom'];
            if (isset($prodData['status'])) $dp->status = $prodData['status'];

            if (isset($prodData['associatedContractId']) || isset($prodData['associated_contract_id'])) {
                $dp->associated_contract_id = $prodData['associatedContractId'] ?? $prodData['associated_contract_id'];
            }

            $dp->save();
        }
    }

    private function parseTimestamp($value): ?Carbon
    {
        if (!$value) return null;
        if (is_numeric($value)) {
            // Check if milliseconds (13 digits) or seconds (10 digits)
            if ($value > 9999999999) {
                return Carbon::createFromTimestampMs($value);
            }
            return Carbon::createFromTimestamp($value);
        }
        return Carbon::parse($value);
    }

    private function formatContractDetail(Contract $c): array
    {
        $dealProducts = $c->dealProducts->map(function ($dp) {
            return [
                'id' => $dp->id,
                'active' => (bool) $dp->active,
                'description' => $dp->description,
                'itemDescription' => $dp->item_description,
                'itemSpecification' => $dp->item_specification,
                'pricingType' => $dp->pricing_type,
                'rate' => (float) ($dp->rate ?? 0.0),
                'quantity' => (float) ($dp->quantity ?? 0.0),
                'totalPrice' => (float) ($dp->total_price ?? 0.0),
                'grossWeight' => (float) ($dp->gross_weight ?? 0.0),
                'netWeight' => (float) ($dp->net_weight ?? 0.0),
                'uom' => $dp->uom,
                'status' => $dp->status,
                'product' => $dp->product ? [
                    'id' => $dp->product->id,
                    'name' => $dp->product->name,
                    'symbol' => $dp->product->symbol,
                    'code' => $dp->product->code,
                    'UOM' => $dp->product->uom,
                    'productCategory' => $dp->product->product_category,
                    'active' => (bool) $dp->product->active,
                ] : null,
                'associatedContractId' => $dp->associated_contract_id,
                'contractOrderCode' => $dp->contract_order_code,
            ];
        })->values();

        $formatDeal = function (?Deal $deal) use ($dealProducts) {
            if (!$deal) return null;
            return [
                'id' => $deal->id,
                'metaCompany' => $deal->metaCompany ? [
                    'id' => $deal->metaCompany->id,
                    'name' => $deal->metaCompany->name,
                    'code' => $deal->metaCompany->code,
                    'logoUrl' => $deal->metaCompany->logo_url,
                    'active' => (bool) $deal->metaCompany->active,
                ] : null,
                'metaRepresentative' => $deal->metaRepresentative,
                'contact' => $deal->contact,
                'contactRepresentative' => $deal->contactRepresentative,
                'address' => $deal->address,
                'shippingAddress' => $deal->shippingAddress,
                'products' => $dealProducts,
                'paymentAccount' => $deal->paymentAccount,
                'currency' => $deal->currency,
                'paymentType' => $deal->paymentType,
                'paymentTermsType' => $deal->paymentTermsType,
                'dollarConvertionRate' => (float) ($deal->dollar_convertion_rate ?? 1.0),
                'paymentTerms' => $deal->payment_terms,
                'incoterms' => $deal->incoterms,
                'paymentDate' => $deal->payment_date ? $deal->payment_date->timestamp : null,
                'externalOrderNumber' => $deal->external_order_number,
                'advancedPayment' => (float) ($deal->advanced_payment ?? 0.0),
                'notes' => $deal->notes,
                'creditNoteText' => $deal->credit_note_text,
                'debitNoteText' => $deal->debit_note_text,
                'countryOfOrigin' => $deal->countryOfOrigin,
                'paymentStatus' => $deal->payment_status ?? ($deal->payment_date ? 'PAID' : 'PENDING'),
            ];
        };

        return [
            'id' => $c->id,
            'stageContractId' => $c->stage_contract_id ?: $c->stage_id,
            'orderCode' => $c->order_code,
            'description' => $c->description,
            'paymentTerms' => $c->payment_terms,
            'salesInvoiceNumber' => $c->sales_invoice_number,
            'salesInvoiceDate' => $c->sales_invoice_date ? $c->sales_invoice_date->timestamp : null,
            'salesOrder' => $c->sales_order,
            'currency' => $c->currency,
            'type' => $c->type,
            'dueDate' => $c->due_date ? $c->due_date->timestamp : null,
            'tags' => $c->tags->pluck('tag')->values(),
            'qualityCheck' => $c->quality_check,
            'packing' => $c->packing,
            'suppliesNote' => $c->supplies_note,
            'customerPurchaseOrder' => $c->customer_purchase_order,
            'deliveryTerms' => $c->delivery_terms,
            'modeOfPayment' => $c->mode_of_payment,
            'generatedFiles' => $c->generatedFiles,
            'memberIds' => $c->members->pluck('id')->values(),
            'comments' => $c->comments,
            'checklists' => $c->checklists,
            'packingLists' => $c->packingLists,
            'active' => (bool) $c->active,
            'version' => (int) ($c->version ?? 1),
            'transportNotes' => $c->transport_notes,
            'transportPriceAgreed' => (float) ($c->transport_price_agreed ?? 0.0),
            'transportSpecialNotes' => $c->transport_special_notes,
            'loadingDate' => $c->loading_date ? $c->loading_date->timestamp : null,
            'purchase' => $formatDeal($c->purchase),
            'sale' => $formatDeal($c->sale),
            'status' => $c->status,
            'cancellationReason' => $c->cancellation_reason,
            'associationStatus' => $this->associationService->getAssociationStatus($c),
            'modeOfTransport' => $c->mode_of_transport,
            'portOfLoading' => $c->port_of_loading,
            'portOfDelivery' => $c->port_of_delivery,
            'weightTolerance' => (float) ($c->weight_tolerance ?? 0.0),
            'cancellationDate' => $c->cancellation_date ? $c->cancellation_date->timestamp : null,
            'closeDate' => $c->close_date ? $c->close_date->timestamp : null,
            'proformaInvoiceNumberFormatted' => $c->proforma_invoice_number_formatted,
            'commercialInvoiceNumberFormatted' => $c->commercial_invoice_number_formatted,
        ];
    }

    private function formatContractListItem(Contract $c): array
    {
        $purchaseCompany = $c->purchase?->metaCompany;
        $saleCompany = $c->sale?->metaCompany;

        return [
            'id' => $c->id,
            'orderCode' => $c->order_code,
            'description' => $c->description,
            'type' => $c->type,
            'dueDate' => $c->due_date ? $c->due_date->timestamp : null,
            'status' => $c->status,
            'purchaseCompany' => $purchaseCompany?->id ?? $c->purchase?->meta_company_id,
            'purchaseCompanyName' => $purchaseCompany?->name,
            'saleCompany' => $saleCompany?->id ?? $c->sale?->meta_company_id,
            'saleCompanyName' => $saleCompany?->name,
            'contactName' => $c->purchase?->contact?->name ?? $c->sale?->contact?->name,
            'active' => (bool) $c->active,
        ];
    }
}
