<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends BaseApiController
{
    public function index() { return response()->json(BankAccount::all()); }
    public function show($id) { return response()->json(BankAccount::findOrFail($id)); }
    public function store(Request $request) { return response()->json(BankAccount::create($request->all()), 201); }
    public function update(Request $request, $id) { $b = BankAccount::findOrFail($id); $b->update($request->all()); return response()->json($b); }
    public function destroy($id) { BankAccount::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
