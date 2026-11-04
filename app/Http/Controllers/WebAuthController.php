<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    protected TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['username' => 'Invalid username or password'])->withInput();
        }

        if (!$user->active) {
            return back()->withErrors(['username' => 'Your account is deactivated. Please contact an administrator.'])->withInput();
        }

        // If 2FA enabled, redirect to 2FA challenge
        if ($user->enable2fa) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));
            return redirect()->route('auth.2fa');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showTwoFactor(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyTwoFactor(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $code = $request->input('code');

        if (!$this->totp->validate($user, (string) $code)) {
            return back()->withErrors(['code' => 'Invalid 6-digit authentication code']);
        }

        $remember = $request->session()->get('2fa:remember', false);
        $request->session()->forget(['2fa:user:id', '2fa:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Logged out successfully');
    }
}
