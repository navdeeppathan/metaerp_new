<?php

namespace App\Http\Controllers;

use App\Models\AccessGroup;
use App\Models\Access;
use Illuminate\Http\Request;

class WebAccessGroupController extends Controller
{
    public const MODULES = [
        'Access Control',
        'Company',
        'Contact',
        'Contract',
        'Country',
        'Delete documents',
        'Detached notes',
        'Logs',
        'Move backward',
        'Offers',
        'Products',
        'Reactivate contract',
        'Users',
        'System Settings',
        'Templates',
    ];

    public const ACCESS_TYPES = [
        'Read',
        'Full access',
    ];

    public function index(Request $request)
    {
        $query = AccessGroup::with('accessList')->where('active', true);

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $groups = $query->orderBy('id')->paginate($perPage)->withQueryString();

        return view('accessgroups.index', compact('groups'));
    }

    public function create()
    {
        $modules = self::MODULES;
        $accessTypes = self::ACCESS_TYPES;

        return view('accessgroups.create', compact('modules', 'accessTypes'));
    }

    public function show($id)
    {
        $group = AccessGroup::with('accessList')->findOrFail($id);
        $modules = self::MODULES;
        $accessTypes = self::ACCESS_TYPES;

        return view('accessgroups.create', compact('group', 'modules', 'accessTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = AccessGroup::create([
            'name' => $validated['name'],
            'active' => true,
            'version' => 0,
        ]);

        $rules = $request->input('rules', []);
        if (is_string($rules)) {
            $rules = json_decode($rules, true) ?? [];
        }

        foreach ($rules as $r) {
            if (!empty($r['module']) && !empty($r['accessType'])) {
                Access::create([
                    'accessgroup_id' => $group->id,
                    'module' => $r['module'],
                    'accessType' => $r['accessType'],
                    'active' => true,
                    'version' => 0,
                ]);
            }
        }

        // Matrix fallback
        if ($request->has('matrix')) {
            foreach ($request->input('matrix', []) as $mod => $actions) {
                foreach ($actions as $act => $enabled) {
                    if ($enabled) {
                        Access::create([
                            'accessgroup_id' => $group->id,
                            'module' => $mod,
                            'accessType' => $act,
                            'active' => true,
                            'version' => 0,
                        ]);
                    }
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

        $rules = $request->input('rules', []);
        if (is_string($rules)) {
            $rules = json_decode($rules, true) ?? [];
        }

        foreach ($rules as $r) {
            if (!empty($r['module']) && !empty($r['accessType'])) {
                Access::create([
                    'accessgroup_id' => $group->id,
                    'module' => $r['module'],
                    'accessType' => $r['accessType'],
                    'active' => true,
                    'version' => 0,
                ]);
            }
        }

        if ($request->has('matrix')) {
            foreach ($request->input('matrix', []) as $mod => $actions) {
                foreach ($actions as $act => $enabled) {
                    if ($enabled) {
                        Access::create([
                            'accessgroup_id' => $group->id,
                            'module' => $mod,
                            'accessType' => $act,
                            'active' => true,
                            'version' => 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('accessgroups.index')->with('success', 'Access group updated successfully');
    }

    public function destroy($id)
    {
        $group = AccessGroup::findOrFail($id);
        $group->update(['active' => false]);

        return redirect()->route('accessgroups.index')->with('success', 'Access group deleted successfully');
    }
}
