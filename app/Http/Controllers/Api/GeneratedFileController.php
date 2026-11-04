<?php

namespace App\Http\Controllers\Api;

use App\Models\GeneratedFile;
use Illuminate\Http\Request;

class GeneratedFileController extends BaseApiController
{
    public function index(Request $request)
    {
        $contractId = $request->input('contractId');
        $query = GeneratedFile::with(['template', 'user']);
        if ($contractId) {
            $query->where('contract_id', $contractId);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function show($id)
    {
        $file = GeneratedFile::with(['template', 'user'])->findOrFail($id);
        return response()->json($file);
    }

    public function getHtml($id)
    {
        $file = GeneratedFile::findOrFail($id);
        return response($file->html, 200)->header('Content-Type', 'text/html');
    }

    public function update(Request $request, $id)
    {
        $file = GeneratedFile::findOrFail($id);
        if ($request->has('html')) {
            $file->html = $request->input('html');
            $file->save();
        }
        return response()->json($file);
    }

    public function destroy($id)
    {
        $file = GeneratedFile::findOrFail($id);
        $file->delete();
        return response()->json(['message' => 'Generated file deleted']);
    }
}
