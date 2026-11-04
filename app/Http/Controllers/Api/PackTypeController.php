<?php

namespace App\Http\Controllers\Api;

use App\Models\PackType;
use Illuminate\Http\Request;

class PackTypeController extends BaseApiController
{
    public function index() { return response()->json(PackType::all()); }
    public function show($id) { return response()->json(PackType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PackType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PackType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PackType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
