<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Storage;

class FilesController extends BaseApiController
{
    public function show($filename)
    {
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->response($filename);
        }
        return response()->json(['error' => 'File not found'], 404);
    }
}
