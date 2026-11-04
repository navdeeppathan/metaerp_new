<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class WebTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = Template::with('language');

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
            // Default to ANNEX_VII templates if present to match the exact view
            $hasAnnex = Template::where('name', 'like', 'ANNEX%')->exists();
            if ($hasAnnex) {
                $query->where('name', 'like', 'ANNEX%');
            }
        }

        $templates = $query->orderBy('id', 'asc')->get();

        return view('templates.index', compact('templates'));
    }

    public function show($id)
    {
        $template = Template::with('language')->findOrFail($id);

        return view('templates.show', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $template->update([
            'html' => $request->input('html', $template->html),
            'name' => $request->input('name', $template->name),
            'type' => $request->input('type', $template->type),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully',
                'template' => $template,
            ]);
        }

        return redirect()->route('templates.show', $template->id)->with('success', 'Template updated successfully.');
    }
}
