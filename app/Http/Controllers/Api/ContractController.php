<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Services\OrderCodeService;
use App\Services\ContractActionService;
use App\Services\ContractAssociationService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

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
            'purchase.products.product',
            'sale.metaCompany',
            'sale.contact',
            'sale.products.product'
        ])
        ->where('active', true)
        ->orderBy('id', 'desc')
        ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.contact.types',
            'purchase.contactRepresentative',
            'purchase.metaRepresentative',
            'purchase.address.country',
            'purchase.destinationAddress.country',
            'purchase.currency',
            'purchase.paymentType',
            'purchase.paymentTermsType',
            'purchase.paymentAccount',
            'purchase.products.product',
            'purchase.products.packType',
            'purchase.products.currency',
            'sale.metaCompany',
            'sale.contact.types',
            'sale.contactRepresentative',
            'sale.metaRepresentative',
            'sale.address.country',
            'sale.destinationAddress.country',
            'sale.currency',
            'sale.paymentType',
            'sale.paymentTermsType',
            'sale.paymentAccount',
            'sale.products.product',
            'sale.products.packType',
            'sale.products.currency',
            'packingLists.packs',
            'generatedFiles',
            'uploadedFiles',
            'checklists.checkitems',
            'comments.user'
        ])->findOrFail($id);

        return response()->json($contract);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'BUY');
        $stageId = $request->input('stage_id', 1);

        // Build Deal
        $dealData = $request->input('deal') ?: ($type === 'BUY' ? $request->input('purchase') : $request->input('sale')) ?: [];
        $deal = Deal::create([
            'meta_company_id' => $dealData['metaCompany']['id'] ?? $dealData['meta_company_id'] ?? 1,
            'contact_id' => $dealData['contact']['id'] ?? $dealData['contact_id'] ?? null,
            'contact_representative_id' => $dealData['contactRepresentative']['id'] ?? $dealData['contact_representative_id'] ?? null,
            'meta_representative_id' => $dealData['metaRepresentative']['id'] ?? $dealData['meta_representative_id'] ?? null,
            'address_id' => $dealData['address']['id'] ?? $dealData['address_id'] ?? null,
            'destination_address_id' => $dealData['destinationAddress']['id'] ?? $dealData['destination_address_id'] ?? null,
            'currency_id' => $dealData['currency']['id'] ?? $dealData['currency_id'] ?? 1,
            'payment_type_id' => $dealData['paymentType']['id'] ?? $dealData['payment_type_id'] ?? 1,
            'payment_terms_type_id' => $dealData['paymentTermsType']['id'] ?? $dealData['payment_terms_type_id'] ?? 1,
            'bank_account_id' => $dealData['paymentAccount']['id'] ?? $dealData['bank_account_id'] ?? null,
            'dollarConvertionRate' => $dealData['dollarConvertionRate'] ?? 1.0,
            'total' => $dealData['total'] ?? 0.0,
            'netTotal' => $dealData['netTotal'] ?? ($dealData['total'] ?? 0.0),
            'dealPaymentStatus' => 'PENDING',
        ]);

        if (!empty($dealData['products'])) {
            foreach ($dealData['products'] as $prod) {
                DealProduct::create([
                    'deal_id' => $deal->id,
                    'product_id' => $prod['product']['id'] ?? $prod['product_id'] ?? null,
                    'pack_type_id' => $prod['packType']['id'] ?? $prod['pack_type_id'] ?? null,
                    'currency_id' => $prod['currency']['id'] ?? $deal->currency_id,
                    'quantity' => $prod['quantity'] ?? 0.0,
                    'unitPrice' => $prod['unitPrice'] ?? 0.0,
                    'pricingType' => $prod['pricingType'] ?? 'FIXED',
                    'vat' => $prod['vat'] ?? 0.0,
                    'total' => ($prod['quantity'] ?? 0) * ($prod['unitPrice'] ?? 0),
                    'status' => 'PENDING',
                ]);
            }
        }

        $contract = Contract::create([
            'date' => now(),
            'type' => $type,
            'stage_id' => $stageId,
            'purchase_id' => ($type === 'BUY') ? $deal->id : null,
            'sale_id' => ($type === 'SELL') ? $deal->id : null,
            'contractStatus' => 'ACTIVE',
            'active' => true,
            'loadingDate' => $request->input('loadingDate') ? date('Y-m-d H:i:s', strtotime($request->input('loadingDate'))) : now()->addDays(14),
            'deliveryTerms' => $request->input('deliveryTerms', 'CIF'),
            'suppliesNote' => $request->input('suppliesNote'),
            'customerPurchaseOrder' => $request->input('customerPurchaseOrder'),
            'modeOfPayment' => $request->input('modeOfPayment'),
            'qualityCheck' => $request->input('qualityCheck'),
            'packing' => $request->input('packing'),
            'remarks' => $request->input('remarks'),
        ]);

        // Generate Order Code
        try {
            $contract->orderCode = $this->orderCodeService->orderCodeGenerator($contract);
            $contract->save();
        } catch (\Throwable $e) {
            $contract->orderCode = sprintf('ME-COM%s-%d', date('y'), $contract->id);
            $contract->save();
        }

        $this->auditService->register('CONTRACT', $contract->id, 'CREATE', json_encode($contract));

        return response()->json($contract, 201);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->only([
            'deliveryTerms', 'suppliesNote', 'customerPurchaseOrder',
            'modeOfPayment', 'qualityCheck', 'packing', 'remarks', 'loadingDate'
        ]));

        $this->auditService->register('CONTRACT', $contract->id, 'UPDATE', json_encode($contract));

        return response()->json($contract);
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
        return response()->json($dp);
    }

    public function listContractsForAssociation($productId)
    {
        $contracts = Contract::with(['purchase.products'])
            ->where('type', 'BUY')
            ->where('contractStatus', 'ACTIVE')
            ->whereHas('purchase.products', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->get();

        return response()->json($contracts);
    }

    public function cancel($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'CANCELLED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CANCEL', null);
        return response()->json($contract);
    }

    public function close($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->type === 'BUY') {
            try {
                $this->associationService->verifyPurchaseContractSaleAssociations($contract->id);
            } catch (\Throwable $e) {
                return response()->json($e->getMessage(), 409);
            }
        }

        $contract->contractStatus = 'CLOSED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CLOSE', null);
        return response()->json($contract);
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->active = false;
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'DELETE', null);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
