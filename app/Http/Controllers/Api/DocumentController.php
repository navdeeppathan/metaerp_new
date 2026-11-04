<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use App\Services\DocumentTemplateService;
use Illuminate\Http\Request;

class DocumentController extends BaseApiController
{
    protected DocumentTemplateService $docService;

    public function __construct(DocumentTemplateService $docService)
    {
        $this->docService = $docService;
    }

    public function regenerate(Request $request)
    {
        $generatedFileId = $request->input('generatedFileId');
        $contractId = $request->input('contractId');

        $file = GeneratedFile::findOrFail($generatedFileId);
        $contract = Contract::findOrFail($contractId);
        $template = $file->template ?: Template::where('type', $file->fileType)->first();

        if (!$template) {
            return response()->json('Template not found', 404);
        }

        try {
            $newFile = $this->docService->render($template, $contract, $request->user()?->id);
            return response()->json('OK', 200);
        } catch (\Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return response()->json($e->getMessage(), $code);
        }
    }

    public function generatePackingListRelatedDocuments(Request $request)
    {
        $contractId = $request->input('contractId');
        $contract = Contract::findOrFail($contractId);

        $templates = Template::whereIn('type', ['PACKING_LIST', 'COMMERCIAL_INVOICE'])->get();
        foreach ($templates as $t) {
            try {
                $this->docService->render($t, $contract, $request->user()?->id);
            } catch (\Throwable $e) {
                // Log and continue
            }
        }

        return response()->json('OK', 200);
    }
}
