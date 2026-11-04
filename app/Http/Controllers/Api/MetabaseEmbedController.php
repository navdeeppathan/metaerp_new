<?php

namespace App\Http\Controllers\Api;

use App\Services\MetabaseEmbedService;
use Illuminate\Http\Request;

class MetabaseEmbedController extends BaseApiController
{
    public function embed(Request $request, MetabaseEmbedService $service)
    {
        $dashboardId = (int) $request->input('dashboardId', 1);
        $url = $service->generateEmbedUrl($dashboardId);
        return response()->json(['url' => $url]);
    }
}
