<?php

namespace App\Http\Controllers\Api;

use App\Models\CreditDebitType;
use Illuminate\Http\Request;

class CreditDebitTypeController extends BaseApiController
{
    public function index() { return response()->json(CreditDebitType::all()); }
    public function show($id) { return response()->json(CreditDebitType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(CreditDebitType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = CreditDebitType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { CreditDebitType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
