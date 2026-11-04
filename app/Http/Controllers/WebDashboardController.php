<?php

namespace App\Http\Controllers;

use App\Services\MetabaseEmbedService;
use Illuminate\Http\Request;

class WebDashboardController extends Controller
{
    public function analytics(Request $request, MetabaseEmbedService $metabaseService)
    {
        $dashboardId = (int) $request->input('dashboardId', 1);
        $embedUrl = $metabaseService->generateEmbedUrl($dashboardId);

        return view('dashboards.analytics', compact('embedUrl', 'dashboardId'));
    }
}
