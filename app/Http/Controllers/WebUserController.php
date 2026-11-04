<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccessGroup;
use App\Models\Company;
use App\Models\Permission;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['permissions.accessGroup', 'permissions.company']);

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('displayName', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $accessGroups = AccessGroup::where('active', true)->orderBy('name')->get();
        $companies = Company::where('active', true)->orderBy('name')->get();
        $groups = $accessGroups;

        return view('users.index', compact('users', 'accessGroups', 'companies', 'groups'));
    }

    public function create()
    {
        $companies = Company::where('active', true)->orderBy('name')->get();
        $accessGroups = AccessGroup::where('active', true)->orderBy('name')->get();

        return view('users.create', compact('companies', 'accessGroups'));
    }

    public function show($id)
    {
        $user = User::with(['permissions.accessGroup', 'permissions.company'])->findOrFail($id);
        $companies = Company::where('active', true)->orderBy('name')->get();
        $accessGroups = AccessGroup::where('active', true)->orderBy('name')->get();

        return view('users.create', compact('user', 'companies', 'accessGroups'));
    }

    public function store(Request $request, TotpService $totp)
    {
        $username = $request->input('username');
        if (!$username && $request->filled('email_prefix')) {
            $domain = $request->input('domain', '@metaenterprices.co.uk');
            $username = $request->input('email_prefix') . (str_starts_with($domain, '@') ? $domain : '@' . $domain);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:191',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
            'occupation' => 'nullable|string',
        ]);

        $finalUsername = $username ?: ($validated['username'] ?? ($request->input('name') . rand(100, 999)));

        $user = User::create([
            'name' => $validated['name'],
            'displayName' => $request->input('displayName', $validated['name']),
            'username' => $finalUsername,
            'password' => Hash::make($validated['password']),
            'occupation' => $request->input('occupation', 'Staff'),
            'role' => $request->input('role', 'ROLE_USER'),
            'active' => $request->boolean('active', true),
            'secret' => $totp->generateSecret(),
            'enable2fa' => $request->boolean('enable2fa'),
        ]);

        if ($request->has('permissions_json')) {
            $perms = json_decode($request->input('permissions_json'), true);
            if (is_array($perms)) {
                foreach ($perms as $p) {
                    if (!empty($p['company_id']) && !empty($p['accessgroup_id'])) {
                        Permission::firstOrCreate([
                            'user_id' => $user->id,
                            'company_id' => $p['company_id'],
                            'accessgroup_id' => $p['accessgroup_id'],
                        ]);
                    }
                }
            }
        } elseif ($request->filled('accessgroup_id') && $request->filled('company_id')) {
            Permission::firstOrCreate([
                'user_id' => $user->id,
                'company_id' => $request->input('company_id'),
                'accessgroup_id' => $request->input('accessgroup_id'),
            ]);
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} created successfully");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'displayName', 'occupation', 'role', 'active']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return back()->with('success', 'User updated successfully');
    }

    public function uploadSignature(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $b64 = base64_encode(file_get_contents($file->getRealPath()));
            $user->signatureFile = $b64;
            $user->signature_file_name = $file->getClientOriginalName();
            $user->save();

            return back()->with('success', 'Digital signature uploaded');
        }
        return back()->with('error', 'No file was provided');
    }
}
