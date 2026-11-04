<?php

namespace App\Http\Controllers;

use App\Models\AccessGroup;
use App\Models\Access;
use Illuminate\Http\Request;

class WebAccessGroupController extends Controller
{
    public function index()
    {
        $groups = AccessGroup::with('accessList')->where('active', true)->get();
        $modules = ['DASHBOARD', 'USERS', 'PRODUCT', 'COMPANY', 'CONTACT', 'OFFER', 'CONTRACT', 'ACCESS_CONTROL', 'SYSTEM_SETTINGS'];
        $actions = ['CREATE', 'READ', 'UPDATE', 'DELETE'];

        return view('accessgroups.index', compact('groups', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $group = AccessGroup::create(['name' => $validated['name'], 'active' => true]);

        $matrix = $request->input('matrix', []);
        foreach ($matrix as $mod => $actions) {
            foreach ($actions as $act => $enabled) {
                if ($enabled) {
                    Access::create([
                        'accessgroup_id' => $group->id,
                        'module' => $mod,
                        'accessType' => $act,
                        'active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('accessgroups.index')->with('success', 'Access group created successfully');
    }

    public function update(Request $request, $id)
    {
        $group = AccessGroup::findOrFail($id);
        $group->update(['name' => $request->input('name')]);

        $group->accessList()->delete();
        $matrix = $request->input('matrix', []);
        foreach ($matrix as $mod => $actions) {
            foreach ($actions as $act => $enabled) {
                if ($enabled) {
                    Access::create([
                        'accessgroup_id' => $group->id,
                        'module' => $mod,
                        'accessType' => $act,
                        'active' => true,
                    ]);
                }
            }
        }

        return back()->with('success', 'Permissions updated successfully');
    }
}
