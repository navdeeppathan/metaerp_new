<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends BaseApiController
{
    public function index() { return response()->json(Language::all()); }
    public function show($id) { return response()->json(Language::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Language::create($request->all()), 201); }
    public function update(Request $request, $id) { $l = Language::findOrFail($id); $l->update($request->all()); return response()->json($l); }
    public function destroy($id) { Language::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
