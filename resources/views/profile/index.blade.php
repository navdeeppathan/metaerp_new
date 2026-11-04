@extends('layouts.app')

@section('title', 'My Profile & Security')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <span class="material-icons text-blue-600">badge</span>
            Account & Security Settings
        </h1>
        <p class="text-sm text-slate-500">Update personal information, manage 2FA security credentials</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Info -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Profile Details</h3>
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ $user->displayName ?? $user->name }}" required class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" required class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">New Password (leave empty to keep current)</label>
                    <input type="password" name="password" class="w-full text-sm border-slate-200 rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- 2FA Authentication -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2 flex items-center justify-between">
                <span>Two-Factor Authentication (TOTP)</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $user->is2fa ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $user->is2fa ? 'ACTIVE' : 'INACTIVE' }}
                </span>
            </h3>

            <p class="text-xs text-slate-600">
                Two-factor authentication adds an extra layer of security to your account using Google Authenticator or Microsoft Authenticator.
            </p>

            @if(!$user->is2fa)
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                <p class="text-xs font-bold text-slate-700">Setup 2FA:</p>
                <div class="text-xs text-slate-500 font-mono break-all bg-white p-2 border rounded">
                    Secret: {{ $secret ?? 'JBSWY3DPEHPK3PXP' }}
                </div>
                <form action="{{ route('profile.toggle-2fa') }}" method="POST">
                    @csrf
                    <input type="hidden" name="secret" value="{{ $secret ?? 'JBSWY3DPEHPK3PXP' }}">
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">
                        Enable Two-Factor Authentication
                    </button>
                </form>
            </div>
            @else
            <form action="{{ route('profile.toggle-2fa') }}" method="POST" onsubmit="return confirm('Disable 2FA?')">
                @csrf
                <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg">
                    Disable Two-Factor Authentication
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
