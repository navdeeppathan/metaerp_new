<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;

class ContractReactivationController extends BaseApiController
{
    public function reactivate($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'ACTIVE';
        $contract->save();

        app(\App\Services\AuditLogService::class)->register('CONTRACT', $contract->id, 'REACTIVATE', null);
        return response()->json($contract);
    }
}
