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

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $user = User::with(['permissions.accessGroup.accessList', 'permissions.company'])->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
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
            'occupation' => $request->input('occupation'),
            'role' => $request->input('role', 'ROLE_USER'),
            'active' => $request->input('active', true),
            'secret' => app(\App\Services\TotpService::class)->generateSecret(),
            'enable2fa' => $request->input('enable2fa', false),
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'displayName', 'occupation', 'role', 'active', 'enable2fa']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);
        return response()->json($user);
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
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $b64 = base64_encode($content);

            $archive = Archive::create([
                'fileName' => $file->getClientOriginalName(),
                'file' => $b64,
            ]);

            $user->signatureFile = $b64;
            $user->signature_file_name = $file->getClientOriginalName();
            $user->archiveSignatureId = $archive->id;
            $user->save();

            return response()->json(['message' => 'Signature uploaded successfully']);
        }
        return response()->json(['error' => 'No signature file provided'], 400);
    }

    public function downloadSignature($userId)
    {
        $user = User::findOrFail($userId);
        if (empty($user->signatureFile)) {
            return response()->json(['error' => 'No signature on file'], 404);
        }

        $binary = base64_decode($user->signatureFile);
        return response($binary, 200)->header('Content-Type', 'image/png');
    }
}
