<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends BaseApiController
{
    public function index() { return response()->json(Address::with('country')->get()); }
    public function show($id) { return response()->json(Address::with('country')->findOrFail($id)); }
    public function store(Request $request) { return response()->json(Address::create($request->all()), 201); }
    public function update(Request $request, $id) { $a = Address::findOrFail($id); $a->update($request->all()); return response()->json($a); }
    public function destroy($id) { Address::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
