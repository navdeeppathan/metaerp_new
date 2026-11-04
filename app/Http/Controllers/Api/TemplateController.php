<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends BaseApiController
{
    public function index()
    {
        return response()->json(Template::with('language')->get());
    }

    public function show($id)
    {
        return response()->json(Template::with('language')->findOrFail($id));
    }

    public function findByTypeAndLanguage($type, $lang)
    {
        $t = Template::where('type', strtoupper($type))
            ->where(function($q) use ($lang) {
                $q->where('language_id', $lang)
                  ->orWhereHas('language', function($lq) use ($lang) {
                      $lq->where('code', $lang);
                  });
            })
            ->first();

        if (!$t) {
            $t = Template::where('type', strtoupper($type))->first();
        }

        return $t ? response()->json($t) : response()->json(['error' => 'Template not found'], 404);
    }

    public function store(Request $request)
    {
        $template = Template::create($request->all());
        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $template->update($request->all());
        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();
        return response()->json(['message' => 'Template deleted']);
    }
}
