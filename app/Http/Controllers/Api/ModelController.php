<?php

namespace App\Http\Controllers\Api;

use App\Models\ModelWorkflow;
use App\Models\Contract;
use Illuminate\Http\Request;

class ModelController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = ModelWorkflow::with([
            'stages.contracts.purchase.products',
            'stages.contracts.sale.products',
        ])->where('active', true)->paginate($size);
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
            'purchase.metaCompany', 'purchase.contact', 'purchase.products.product',
            'sale.metaCompany', 'sale.contact', 'sale.products.product',
            'checklists.checkitems', 'comments'
        ])
        ->where('active', true)
        ->where('contractStatus', '!=', 'CANCELLED')
        ->get();

        // Build Board format matching Fuse React Scrumboard
        $lists = [];
        $cards = [];

        foreach ($model->stages as $stage) {
            $stageContractIds = [];
            foreach ($contracts as $c) {
                if ($c->stage_id == $stage->id) {
                    $stageContractIds[] = (string) $c->id;
                    $deal = $c->purchase ?: $c->sale;
                    $partnerName = $deal?->contact?->name ?? 'Unknown Partner';
                    $total = $deal?->total ? number_format($deal->total, 2) : '0.00';

                    $cards[] = [
                        'id' => (string) $c->id,
                        'name' => $c->orderCode ?: "Contract #{$c->id}",
                        'description' => "{$c->type} - {$partnerName} ({$total})",
                        'orderCode' => $c->orderCode,
                        'type' => $c->type,
                        'contract' => $c,
                        'stageContractId' => $stage->id,
                        'idList' => (string) $stage->id,
                        'attachments' => [],
                        'checklists' => $c->checklists,
                        'comments' => $c->comments,
                        'due' => $c->loadingDate ? $c->loadingDate->timestamp * 1000 : null,
                    ];
                }
            }

            $lists[] = [
                'id' => (string) $stage->id,
                'name' => $stage->name,
                'title' => $stage->name,
                'idCards' => $stageContractIds,
            ];
        }

        return response()->json([
            'id' => (string) $model->id,
            'name' => $model->name,
            'title' => $model->name,
            'lists' => $lists,
            'cards' => $cards,
        ]);
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
