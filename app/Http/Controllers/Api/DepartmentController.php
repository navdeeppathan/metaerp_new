<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Department::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Department::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Department::create($request->all()), 201); }
    public function update(Request $request, $id) { $d = Department::findOrFail($id); $d->update($request->all()); return response()->json($d); }
    public function destroy($id) { Department::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
