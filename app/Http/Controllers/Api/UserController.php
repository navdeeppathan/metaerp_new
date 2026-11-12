<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 1000);
        $paginator = User::with(['permissions.accessGroup', 'permissions.company'])
            ->orderBy('name')
            ->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($u) {
            return $this->formatUserDto($u);
        });
        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $user = User::with(['permissions.accessGroup.accessList', 'permissions.company'])->findOrFail($id);
        return response()->json($this->formatUserDto($user));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->password = Hash::make($validated['password']);
        $user->display_name = $request->input('displayName', $validated['name']);
        $user->occupation = $request->input('occupation');
        $user->role = $request->input('role', 'ROLE_USER');
        $user->active = $request->input('active', true);
        $user->enable2fa = $request->input('enable2fa', false);
        $user->photo_url = $request->input('photoURL') ?: $request->input('photoUrl');
        $user->secret = app(\App\Services\TotpService::class)->generateSecret();
        $user->save();

        return response()->json($this->formatUserDto($user), 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->filled('name')) $user->name = $request->input('name');
        if ($request->filled('displayName')) $user->display_name = $request->input('displayName');
        if ($request->filled('display_name')) $user->display_name = $request->input('display_name');
        if ($request->filled('occupation')) $user->occupation = $request->input('occupation');
        if ($request->filled('role')) $user->role = $request->input('role');
        if ($request->has('active')) $user->active = (bool) $request->input('active');
        if ($request->has('enable2fa')) $user->enable2fa = (bool) $request->input('enable2fa');
        if ($request->filled('photoURL') || $request->filled('photo_url')) {
            $user->photo_url = $request->input('photoURL') ?: $request->input('photo_url');
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();
        return response()->json($this->formatUserDto($user));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->active = false;
        $user->save();
        return response()->json(['message' => 'User deactivated']);
    }

    public function uploadSignature(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No signature file provided'], 400);
        }

        $file = $request->file('file');
        $b64 = base64_encode(file_get_contents($file->getRealPath()));

        $archive = Archive::create([
            'content' => $b64,
        ]);

        $user->signature_file = $b64;
        $user->signature_file_name = $file->getClientOriginalName();
        $user->archive_signature_id = $archive->id;
        $user->save();

        return response()->json(['message' => 'Signature uploaded successfully']);
    }

    public function downloadSignature($userId)
    {
        $user = User::findOrFail($userId);
        if (empty($user->signature_file) && $user->archive_signature_id) {
            $archive = Archive::find($user->archive_signature_id);
            if ($archive) {
                $user->signature_file = $archive->content;
            }
        }

        if (empty($user->signature_file)) {
            return response()->json(['error' => 'No signature on file'], 404);
        }

        $raw = base64_decode($user->signature_file);
        return response($raw, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="signature.png"');
    }

    private function formatUserDto(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'displayName' => $u->display_name ?: $u->name,
            'username' => $u->username,
            'email' => $u->username,
            'occupation' => $u->occupation,
            'role' => $u->role,
            'photoURL' => $u->photo_url ?: '/assets/images/avatars/brian-hughes.jpg',
            'photoUrl' => $u->photo_url,
            'active' => (bool) $u->active,
            'enable2fa' => (bool) $u->enable2fa,
            'permissions' => $u->permissions ?? [],
        ];
    }
}
