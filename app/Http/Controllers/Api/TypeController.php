<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Type::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Type::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Type::create($request->all()), 201); }
    public function update(Request $request, $id) { $t = Type::findOrFail($id); $t->update($request->all()); return response()->json($t); }
    public function destroy($id) { Type::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
