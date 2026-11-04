<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;

class TOTPController extends BaseApiController
{
    public function getQrCode(Request $request, $id, TotpService $totp)
    {
        $user = User::findOrFail($id);
        $url = $totp->generateQRUrl($user);
        return response()->json(['qrUrl' => $url, 'secret' => $user->secret]);
    }

    public function update(Request $request, $id, TotpService $totp)
    {
        $user = User::findOrFail($id);
        $code = $request->input('code');

        if (!$totp->validate($user, $code)) {
            return response()->json(['error' => 'Invalid verification code'], 400);
        }

        $user->enable2fa = true;
        $user->save();

        return response()->json(['message' => '2FA verified and enabled successfully']);
    }
}
