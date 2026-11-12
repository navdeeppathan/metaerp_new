<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Stage;
use App\Models\StageAction;
use App\Models\Template;
use Illuminate\Support\Facades\DB;

class ContractActionService
{
    protected DocumentTemplateService $docService;
    protected AuditLogService $auditService;

    public function __construct(DocumentTemplateService $docService, AuditLogService $auditService)
    {
        $this->docService = $docService;
        $this->auditService = $auditService;
    }

    /**
     * Move contract to target stage and execute workflow triggers.
     */
    public function move(int $contractId, int $stageId, ?int $userId = null): Contract
    {
        return DB::transaction(function () use ($contractId, $stageId, $userId) {
            $contract = Contract::with(['stage.stageActions.action', 'purchase', 'sale', 'dealProducts'])->findOrFail($contractId);
            $targetStage = Stage::with(['stageActions.action'])->findOrFail($stageId);

            $oldStage = $contract->stage;

            // 1. Check OUT actions of previous stage if any
            if ($oldStage) {
                foreach ($oldStage->stageActions as $sa) {
                    $notAllowed = $sa->contractTypesNotAllowed->pluck('contract_types_not_allowed')->all();
                    if (!empty($notAllowed) && in_array($contract->type, $notAllowed)) {
                        throw new \Exception("This contract type is not allowed in this stage", 409);
                    }
                }
            }

            // 2. Integrity checks depending on stage
            if (strtoupper($targetStage->type ?? '') === 'LOGISTICS' || str_contains(strtolower($targetStage->description ?? ''), 'logistics')) {
                // Must have products
                if ($contract->dealProducts->isEmpty()) {
                    throw new \Exception("Contract cannot proceed to Logistics without deal products", 400);
                }
            }

            // 3. Update stage
            $contract->stage_id = $targetStage->id;
            $contract->stage_contract_id = $targetStage->id;
            $contract->save();

            // 4. Execute IN actions of target stage
            foreach ($targetStage->stageActions as $sa) {
                if ($sa->trigger_type === 'IN') {
                    $this->executeAction($sa, $contract, $userId);
                }
            }

            // 5. Audit Log
            $history = json_encode([
                'stage' => [
                    'from' => $oldStage?->description ?? 'None',
                    'to' => $targetStage->description,
                ]
            ]);
            $this->auditService->register('CONTRACT', $contract->id, 'STAGE_TRANSITION', $history, $userId);

            return $contract;
        });
    }

    private function executeAction(StageAction $stageAction, Contract $contract, ?int $userId = null): void
    {
        $actionName = strtoupper($stageAction->action?->name ?? '');

        if (str_contains($actionName, 'PURCHASE ORDER')) {
            $template = Template::where('type', 'PURCHASE_ORDER')->first();
            if ($template) {
                try {
                    $this->docService->render($template, $contract, $userId);
                } catch (\Throwable $e) {
                    // Log but don't fail unless strict
                }
            }
        } elseif (str_contains($actionName, 'SALES CONTRACT')) {
            $template = Template::where('type', 'CONTRACT_OF_SALES')->first();
            if ($template) {
                try {
                    $this->docService->render($template, $contract, $userId);
                } catch (\Throwable $e) {
                    // Log but don't fail unless strict
                }
            }
        }
    }
}
