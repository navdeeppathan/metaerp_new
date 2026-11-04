<?php

namespace App\Http\Controllers\Api;

use App\Models\Log;

class LogController extends BaseApiController
{
    public function listByModuleAndDataId($module, $dataId)
    {
        $logs = Log::with('user')
            ->where('module', strtoupper($module))
            ->where('dataId', $dataId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($logs);
    }
}
