<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use Illuminate\Support\Facades\Auth;

class ContractReactivationController extends BaseApiController
{
    public function reactivate($id)
    {
        $contract = Contract::findOrFail($id);

        $contract->status = 'OPEN';
        $contract->contractStatus = 'OPEN';
        $contract->close_date = null;
        $contract->closeDate = null;
        $contract->finishDate = null;
        $contract->cancellation_date = null;
        $contract->cancellationDate = null;
        $contract->cancellation_reason = null;
        $contract->cancellationReason = null;
        $contract->active = true;

        if (empty($contract->stage_id) || $contract->stage_id <= 0) {
            $contract->stage_id = 1;
        }

        $contract->save();

        try {
            app(\App\Services\AuditLogService::class)->register('CONTRACT', $contract->id, 'REACTIVATE', "Contract {$contract->orderCode} was reactivated", Auth::id());
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'contract' => $contract
        ]);
    }
}
