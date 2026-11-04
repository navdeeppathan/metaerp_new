<?php

namespace App\Http\Controllers\Api;

use App\Models\AccessGroup;
use App\Models\Access;
use Illuminate\Http\Request;

class AccessGroupController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = AccessGroup::with(['accessList'])
            ->where('active', true)
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $ag = AccessGroup::with(['accessList'])->findOrFail($id);
        return response()->json($ag);
    }

    public function store(Request $request)
    {
        $ag = AccessGroup::create(['name' => $request->input('name'), 'active' => true]);
        if ($request->has('accessList')) {
            foreach ($request->input('accessList') as $acc) {
                Access::create([
                    'accessgroup_id' => $ag->id,
                    'module' => $acc['module'],
                    'accessType' => $acc['accessType'],
                    'active' => true,
                ]);
            }
        }
        return response()->json($ag, 201);
    }

    public function update(Request $request, $id)
    {
        $ag = AccessGroup::findOrFail($id);
        $ag->update(['name' => $request->input('name')]);

        if ($request->has('accessList')) {
            $ag->accessList()->delete();
            foreach ($request->input('accessList') as $acc) {
                Access::create([
                    'accessgroup_id' => $ag->id,
                    'module' => $acc['module'],
                    'accessType' => $acc['accessType'],
                    'active' => true,
                ]);
            }
        }

        return response()->json($ag);
    }

    public function destroy($id)
    {
        $ag = AccessGroup::findOrFail($id);
        $ag->active = false;
        $ag->save();
        return response()->json(['message' => 'Access group deactivated']);
    }
}
