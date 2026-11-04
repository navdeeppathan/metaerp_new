<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 300);
        $paginator = Country::where('active', true)->orderBy('name')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Country::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Country::create($request->all()), 201); }
    public function update(Request $request, $id) { $c = Country::findOrFail($id); $c->update($request->all()); return response()->json($c); }
    public function destroy($id) { Country::findOrFail($id)->update(['active' => false]); return response()->json(['message' => 'Deleted']); }
}
