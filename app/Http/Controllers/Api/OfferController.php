<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Offer::with(['metaCompany', 'contact', 'product', 'currency'])
            ->orderBy('id', 'desc')
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $offer = Offer::with(['metaCompany', 'contact', 'product', 'currency'])->findOrFail($id);
        return response()->json($offer);
    }

    public function store(Request $request)
    {
        $offer = Offer::create($request->all());
        return response()->json($offer, 201);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->update($request->all());
        return response()->json($offer);
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();
        return response()->json(['message' => 'Offer deleted']);
    }
}
