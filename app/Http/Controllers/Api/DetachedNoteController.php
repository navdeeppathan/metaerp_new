<?php

namespace App\Http\Controllers\Api;

use App\Models\DetachedNote;
use Illuminate\Http\Request;

class DetachedNoteController extends BaseApiController
{
    public function index()
    {
        return response()->json(DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])->get());
    }

    public function show($id)
    {
        return response()->json(DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $note = DetachedNote::create($request->all());
        return response()->json($note, 201);
    }

    public function update(Request $request, $id)
    {
        $note = DetachedNote::findOrFail($id);
        $note->update($request->all());
        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = DetachedNote::findOrFail($id);
        $note->delete();
        return response()->json(['message' => 'Detached note deleted']);
    }
}
