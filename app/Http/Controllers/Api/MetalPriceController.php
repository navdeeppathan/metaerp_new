<?php

namespace App\Http\Controllers\Api;

use App\Services\MetalPriceService;

class MetalPriceController extends BaseApiController
{
    public function index(MetalPriceService $service)
    {
        return response()->json($service->getPrices());
    }
}
