<?php

namespace App\Http\Controllers\Api;

class ApplicationController extends BaseApiController
{
    public function getVersion()
    {
        return response()->json(['version' => '1.15.0', 'app' => 'MetaERP']);
    }
}
