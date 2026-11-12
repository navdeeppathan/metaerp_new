<?php

namespace App\Http\Controllers\Api;

use App\Models\ModelWorkflow;
use App\Models\Contract;
use App\Models\Stage;
use Illuminate\Http\Request;

class ModelController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = ModelWorkflow::with(['stages.stageActions'])
            ->where('active', true)
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $model = ModelWorkflow::with(['stages.stageActions'])->findOrFail($id);
        return response()->json($model);
    }

    public function withPermissions(Request $request, $id)
    {
        $model = ModelWorkflow::with(['stages'])->findOrFail($id);

        $contracts = Contract::with([
            'purchase.metaCompany',
            'purchase.contact',
            'sale.metaCompany',
            'sale.contact',
            'checklists.checkitems',
            'comments',
            'dealProducts',
            'members',
            'tags'
        ])
        ->where('active', true)
        ->where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', 'OPEN')
              ->orWhere('status', '!=', 'CANCELLED');
        })
        ->get();

        $stagesData = [];
        $orderedStages = $model->stages->sortBy('position')->values();

        foreach ($orderedStages as $stage) {
            $stageContracts = [];

            foreach ($contracts as $c) {
                $matchesStage = ($c->stage_contract_id == $stage->id) || ($c->stage_id == $stage->id);
                if (!$matchesStage) {
                    continue;
                }

                $purchaseMetaCompany = $c->purchase?->metaCompany;
                $saleMetaCompany = $c->sale?->metaCompany;

                $purchaseCompanyId = $purchaseMetaCompany?->id ?? $c->purchase?->meta_company_id;
                $purchaseCompanyName = $purchaseMetaCompany?->name;

                $saleCompanyId = $saleMetaCompany?->id ?? $c->sale?->meta_company_id;
                $saleCompanyName = $saleMetaCompany?->name;

                if (!$purchaseCompanyId && $saleCompanyId) {
                    $purchaseCompanyId = $saleCompanyId;
                    $purchaseCompanyName = $saleCompanyName;
                }

                $buyerContract = null;
                if ($c->purchase) {
                    $buyerContract = [
                        'contactId' => $c->purchase->contact?->id,
                        'contactName' => $c->purchase->contact?->name,
                        'paymentStatus' => $c->purchase->payment_status ?? ($c->purchase->payment_date ? 'PAID' : 'PENDING'),
                    ];
                }

                $sellerContract = null;
                if ($c->sale) {
                    $sellerContract = [
                        'contactId' => $c->sale->contact?->id,
                        'contactName' => $c->sale->contact?->name,
                        'paymentStatus' => $c->sale->payment_status ?? ($c->sale->payment_date ? 'PAID' : 'PENDING'),
                    ];
                }

                $associationStatus = $this->calculateAssociationStatus($c);

                $stageContracts[] = [
                    'id' => $c->id,
                    'type' => $c->type,
                    'orderCode' => $c->order_code,
                    'description' => $c->description,
                    'dueDate' => $c->due_date ? $c->due_date->timestamp : null,
                    'status' => $c->status ?: 'OPEN',
                    'purchaseCompany' => $purchaseCompanyId,
                    'purchaseCompanyName' => $purchaseCompanyName,
                    'saleCompany' => $saleCompanyId,
                    'saleCompanyName' => $saleCompanyName,
                    'buyerContract' => $buyerContract,
                    'sellerContract' => $sellerContract,
                    'associationStatus' => $associationStatus,
                    'comments' => $c->comments->map(function ($cm) {
                        return [
                            'id' => $cm->id,
                            'message' => $cm->message,
                            'createdAt' => $cm->created_at ? $cm->created_at->timestamp : null,
                        ];
                    })->values(),
                    'checklists' => $c->checklists,
                    'memberIds' => $c->members->pluck('id')->values(),
                    'tags' => $c->tags->pluck('tag')->values(),
                    'stageContractId' => $stage->id,
                ];
            }

            $stageActionsData = [];
            if ($stage->relationLoaded('stageActions')) {
                foreach ($stage->stageActions as $sa) {
                    $stageActionsData[] = $sa;
                }
            }

            $stagesData[] = [
                'id' => $stage->id,
                'description' => $stage->description,
                'type' => $stage->type,
                'position' => (int) $stage->position,
                'version' => (int) ($stage->version ?? 1),
                'active' => (bool) $stage->active,
                'contracts' => $stageContracts,
            ];
        }

        return response()->json([
            'id' => $model->id,
            'description' => $model->description,
            'version' => (int) ($model->version ?? 1),
            'active' => (bool) $model->active,
            'stages' => $stagesData,
        ]);
    }

    private function calculateAssociationStatus(Contract $contract): string
    {
        if ($contract->type !== 'SELL') {
            return 'NOT_APPLY';
        }

        $products = $contract->dealProducts;
        if (!$products || $products->isEmpty()) {
            return 'PENDING';
        }

        $total = $products->count();
        $associated = $products->whereNotNull('associated_contract_id')->count();

        if ($associated === $total) {
            return 'COMPLETE';
        }

        return $associated === 0 ? 'PENDING' : 'PARTIAL';
    }

    public function store(Request $request)
    {
        $model = ModelWorkflow::create($request->all());
        return response()->json($model, 201);
    }

    public function update(Request $request, $id)
    {
        $model = ModelWorkflow::findOrFail($id);
        $model->update($request->all());
        return response()->json($model);
    }

    public function destroy($id)
    {
        $model = ModelWorkflow::findOrFail($id);
        $model->active = false;
        $model->save();
        return response()->json(['message' => 'Model deleted']);
    }
}
