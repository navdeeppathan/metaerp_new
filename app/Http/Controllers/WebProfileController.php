<?php

namespace App\Http\Controllers;

use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebProfileController extends Controller
{
    public function index(TotpService $totp)
    {
        $user = Auth::user();
        $qrUrl = $totp->generateQRUrl($user);

        return view('profile.index', compact('user', 'qrUrl'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'displayName' => 'nullable|string|max:255',
            'occupation' => 'nullable|string',
        ]);

        $user->update($validated);
        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password changed successfully');
    }

    public function toggle2fa(Request $request, TotpService $totp)
    {
        $user = Auth::user();
        $code = $request->input('code');

        if (!$user->enable2fa) {
            // Verify code to enable
            if (!$totp->validate($user, (string) $code)) {
                return back()->withErrors(['code' => 'Invalid verification code. Please check your authenticator app.']);
            }
            $user->enable2fa = true;
            $user->save();
            return back()->with('success', 'Two-Factor Authentication is now ENABLED!');
        } else {
            $user->enable2fa = false;
            $user->save();
            return back()->with('info', 'Two-Factor Authentication has been disabled.');
        }
    }
}
