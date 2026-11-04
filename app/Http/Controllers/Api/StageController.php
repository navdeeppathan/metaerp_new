<?php

namespace App\Http\Controllers\Api;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 50);
        $paginator = Stage::with(['stageActions.action'])->where('active', true)->orderBy('priority')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $stage = Stage::with(['stageActions.action'])->findOrFail($id);
        return response()->json($stage);
    }

    public function store(Request $request)
    {
        $stage = Stage::create($request->all());
        return response()->json($stage, 201);
    }

    public function update(Request $request, $id)
    {
        $stage = Stage::findOrFail($id);
        $stage->update($request->all());
        return response()->json($stage);
    }

    public function destroy($id)
    {
        $stage = Stage::findOrFail($id);
        $stage->active = false;
        $stage->save();
        return response()->json(['message' => 'Stage deleted']);
    }
}
