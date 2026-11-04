<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentTermsType;
use Illuminate\Http\Request;

class PaymentTermsTypeController extends BaseApiController
{
    public function index() { return response()->json(PaymentTermsType::all()); }
    public function show($id) { return response()->json(PaymentTermsType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PaymentTermsType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PaymentTermsType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PaymentTermsType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
