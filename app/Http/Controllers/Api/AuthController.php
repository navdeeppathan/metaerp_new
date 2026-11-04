<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TotpService;

class AuthController extends BaseApiController
{
    protected TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::with(['permissions.accessGroup.accessList', 'permissions.company'])->where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid username or password'], 401);
        }

        if (!$user->active) {
            return response()->json(['error' => 'User is inactive'], 403);
        }

        // Check if 2FA is required and token passed
        if ($user->enable2fa) {
            $token = $request->input('token');
            if (!$token || !$this->totp->validate($user, $token)) {
                return response()->json([
                    'error' => '2FA verification required',
                    'require2fa' => true,
                    'userId' => $user->id,
                ], 403);
            }
        }

        // Generate Sanctum token
        $token = $user->createToken('META_ERP_TOKEN')->plainTextToken;

        // Build UserLoginDetailDto structure expected by frontend
        $permissions = [];
        foreach ($user->permissions as $p) {
            if ($p->accessGroup) {
                foreach ($p->accessGroup->accessList as $acc) {
                    $permissions[] = $acc->module;
                }
            }
        }
        $permissions = array_values(array_unique($permissions));

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'displayName' => $user->displayName ?: $user->name,
            'email' => $user->username,
            'photoURL' => $user->photoUrl ?: '/assets/images/avatars/brian-hughes.jpg',
            'role' => $user->role,
            'permissions' => $permissions,
            'shortcuts' => ['apps' => ['contracts', 'offers', 'contacts', 'products']],
        ];

        return response()->json([
            'access_token' => $token,
            'token' => $token,
            'user' => $userData,
        ]);
    }

    public function validateToken(Request $request)
    {
        $user = $request->user() ?: User::with(['permissions.accessGroup.accessList', 'permissions.company'])->where('active', true)->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $permissions = [];
        foreach ($user->permissions as $p) {
            if ($p->accessGroup) {
                foreach ($p->accessGroup->accessList as $acc) {
                    $permissions[] = $acc->module;
                }
            }
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'displayName' => $user->displayName ?: $user->name,
            'email' => $user->username,
            'photoURL' => $user->photoUrl ?: '/assets/images/avatars/brian-hughes.jpg',
            'role' => $user->role,
            'permissions' => array_values(array_unique($permissions)),
            'shortcuts' => ['apps' => ['contracts', 'offers', 'contacts', 'products']],
        ];

        return response()->json([
            'access_token' => $request->bearerToken() ?: 'SESSION_TOKEN',
            'user' => $userData,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'displayName' => $request->input('displayName', $validated['name']),
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'occupation' => $request->input('occupation', 'Staff'),
            'role' => 'ROLE_USER',
            'active' => true,
            'secret' => $this->totp->generateSecret(),
        ]);

        $token = $user->createToken('META_ERP_TOKEN')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'displayName' => $user->displayName,
                'email' => $user->username,
                'role' => $user->role,
                'permissions' => [],
                'shortcuts' => ['apps' => []],
            ]
        ], 201);
    }
}
