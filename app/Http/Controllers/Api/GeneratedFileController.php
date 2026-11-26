<?php

namespace App\Http\Controllers\Api;

use App\Models\GeneratedFile;
use Illuminate\Http\Request;

class GeneratedFileController extends BaseApiController
{
    public function index(Request $request)
    {
        $contractId = $request->input('contractId');
        $query = GeneratedFile::with(['template']);
        if ($contractId) {
            $query->where('contract_id', $contractId);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function show($id)
    {
        $file = GeneratedFile::with(['template'])->findOrFail($id);
        return response()->json($file);
    }

    public function detail($id)
    {
        $file = GeneratedFile::findOrFail($id);
        return response()->json([
            'id' => $file->id,
            'name' => $file->name,
            'content' => $file->content,
            'contractId' => $file->contract_id,
            'templateId' => $file->template_id,
            'active' => (bool)$file->active,
        ]);
    }

    public function getHtml($id)
    {
        $file = GeneratedFile::findOrFail($id);
        return response($file->html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function download($id)
    {
        $file = GeneratedFile::findOrFail($id);
        $fileName = $file->name . '.html';
        return response($file->html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function update(Request $request, $id)
    {
        $file = GeneratedFile::findOrFail($id);
        
        if ($request->has('content')) {
            $file->content = $request->input('content');
        } elseif ($request->has('html')) {
            $file->html = $request->input('html');
        }

        if ($request->has('name')) {
            $file->name = $request->input('name');
        }

        $file->save();
        return response()->json($file);
    }

    public function destroy($id)
    {
        $file = GeneratedFile::findOrFail($id);
        $file->delete();
        return response()->json(['message' => 'Generated file deleted']);
    }
}
