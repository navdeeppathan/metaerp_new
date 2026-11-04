<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Currency::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Currency::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Currency::create($request->all()), 201); }
    public function update(Request $request, $id) { $c = Currency::findOrFail($id); $c->update($request->all()); return response()->json($c); }
    public function destroy($id) { Currency::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
