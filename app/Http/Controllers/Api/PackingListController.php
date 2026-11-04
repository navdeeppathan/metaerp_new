<?php

namespace App\Http\Controllers\Api;

use App\Models\PackingList;
use App\Models\Pack;
use Illuminate\Http\Request;

class PackingListController extends BaseApiController
{
    public function index(Request $request)
    {
        $contractId = $request->input('contractId');
        $query = PackingList::with(['packs.packType']);
        if ($contractId) {
            $query->where('contract_id', $contractId);
        }
        return response()->json($query->get());
    }

    public function show($id)
    {
        $pl = PackingList::with(['packs.packType'])->findOrFail($id);
        return response()->json($pl);
    }

    public function store(Request $request)
    {
        $pl = PackingList::create($request->all());
        return response()->json($pl, 201);
    }

    public function update(Request $request, $id)
    {
        $pl = PackingList::findOrFail($id);
        $pl->update($request->all());
        return response()->json($pl);
    }

    public function destroy($id)
    {
        $pl = PackingList::findOrFail($id);
        $pl->delete();
        return response()->json(['message' => 'Packing list deleted']);
    }

    public function addPack(Request $request, $id)
    {
        $pl = PackingList::findOrFail($id);
        $pack = Pack::create(array_merge($request->all(), ['packing_list_id' => $pl->id]));
        return response()->json($pack, 201);
    }

    public function removePack($id, $packId)
    {
        $pack = Pack::where('packing_list_id', $id)->where('id', $packId)->firstOrFail();
        $pack->delete();
        return response()->json(['message' => 'Pack deleted']);
    }
}
