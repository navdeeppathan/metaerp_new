<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function register(string $module, int|string $dataId, string $action, ?string $history = null, ?int $userId = null): Log
    {
        $resolvedUserId = $userId ?: (Auth::id() ?: 1);

        return Log::create([
            'module' => strtoupper($module),
            'dataId' => $dataId,
            'dateTime' => now(),
            'userId' => $resolvedUserId,
            'action' => strtoupper($action),
            'history' => $history,
        ]);
    }
}
