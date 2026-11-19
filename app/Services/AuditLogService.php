<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function register(string $module, int|string $dataId, string $action, ?string $history = null, ?int $userId = null): Log
    {
        $resolvedUserId = $userId ?: (Auth::id() ?: 1);

        $actionMap = [
            'CREATE' => 'CREATED',
            'CREATED' => 'CREATED',
            'UPDATE' => 'UPDATED',
            'UPDATED' => 'UPDATED',
            'EDIT' => 'UPDATED',
            'DELETE' => 'DELETED',
            'DELETED' => 'DELETED',
            'CLOSE' => 'CLOSED',
            'CLOSED' => 'CLOSED',
            'CANCEL' => 'CANCELLED',
            'CANCELLED' => 'CANCELLED',
            'CHANGE_STAGE' => 'CHANGE_STAGE',
            'STAGE_TRANSITION' => 'CHANGE_STAGE',
            'TRANSITION' => 'CHANGE_STAGE',
        ];

        $cleanAction = $actionMap[strtoupper(trim($action))] ?? 'CREATED';

        return Log::create([
            'module' => strtoupper($module),
            'data_id' => $dataId,
            'date_time' => now(),
            'user_id' => $resolvedUserId,
            'action' => $cleanAction,
            'history' => $history,
        ]);
    }
}
