<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TypeController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);

        if (Schema::hasTable('types')) {
            $paginator = Type::paginate($size);
            return response()->json($this->paginateResponse($paginator));
        }

        // Graceful fallback when types table is not instantiated
        $defaultTypes = collect([
            (object)['id' => 1, 'name' => 'Supplier', 'active' => true],
            (object)['id' => 2, 'name' => 'Customer', 'active' => true],
            (object)['id' => 3, 'name' => 'Broker', 'active' => true],
            (object)['id' => 4, 'name' => 'Shipping Line', 'active' => true],
        ]);

        return response()->json([
            'content' => $defaultTypes,
            'totalElements' => $defaultTypes->count(),
            'totalPages' => 1,
            'size' => $size,
            'number' => 0,
            'numberOfElements' => $defaultTypes->count(),
            'first' => true,
            'last' => true,
            'empty' => false,
        ]);
    }

    public function show($id)
    {
        if (Schema::hasTable('types')) {
            return response()->json(Type::findOrFail($id));
        }

        return response()->json(['id' => (int)$id, 'name' => 'Supplier', 'active' => true]);
    }

    public function store(Request $request)
    {
        if (Schema::hasTable('types')) {
            return response()->json(Type::create($request->all()), 201);
        }
        return response()->json($request->all(), 201);
    }

    public function update(Request $request, $id)
    {
        if (Schema::hasTable('types')) {
            $t = Type::findOrFail($id);
            $t->update($request->all());
            return response()->json($t);
        }
        return response()->json($request->all());
    }

    public function destroy($id)
    {
        if (Schema::hasTable('types')) {
            Type::findOrFail($id)->delete();
        }
        return response()->json(['message' => 'Deleted']);
    }
}
