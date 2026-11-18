<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class WebTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = Template::with(['language', 'country']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('language', function ($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%");
                  });
            });
        } else {
            $hasAnnex = Template::where('name', 'like', 'ANNEX%')->exists();
            if ($hasAnnex) {
                $query->where('name', 'like', 'ANNEX%');
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $templates = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('templates.index', compact('templates'));
    }

    public function show($id)
    {
        $template = Template::with(['language', 'country'])->findOrFail($id);

        return view('templates.show', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $html = $request->input('html', $request->input('content', ''));
        
        $template->html = $html;

        if ($request->filled('name')) {
            $template->name = $request->input('name');
        }
        if ($request->filled('type')) {
            $template->type = $request->input('type');
        }
        
        $template->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template' => $template,
            ]);
        }

        return redirect()->route('templates.show', $template->id)->with('success', 'Template data have been saved');
    }
}
