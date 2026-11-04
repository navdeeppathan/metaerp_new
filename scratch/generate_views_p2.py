import os

def write_view(rel_path, content):
    full_path = os.path.join(r'c:\Users\navde\Downloads\metaerp\resources\views', rel_path)
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    with open(full_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Created view:", rel_path)

# 2. auth/login.blade.php
write_view('auth/login.blade.php', r"""<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MetaERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30 mb-4">
                ME
            </div>
            <h1 class="text-2xl font-bold text-slate-900">MetaERP System</h1>
            <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">Enterprise Commodity & Trading Platform</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-start gap-2">
                <span class="material-icons-outlined text-lg shrink-0 mt-0.5">error_outline</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Username / Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <span class="material-icons-outlined text-lg">person</span>
                    </span>
                    <input type="text" id="username" name="username" value="{{ old('username', 'admin') }}" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-400"
                        placeholder="Enter username">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <span class="material-icons-outlined text-lg">lock</span>
                    </span>
                    <input type="password" id="password" name="password" value="password" required
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-400"
                        placeholder="Enter password">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <span>Remember me</span>
                </label>
                <span class="text-slate-400">Default: admin / password</span>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition-all flex items-center justify-center gap-2">
                <span>Sign In to Dashboard</span>
                <span class="material-icons-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Need an account? <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Register New Staff</a>
        </div>
    </div>

</body>
</html>
""")

# 3. auth/two-factor-challenge.blade.php
write_view('auth/two-factor-challenge.blade.php', r"""<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - MetaERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-indigo-600 items-center justify-center text-white text-2xl font-bold shadow-lg shadow-amber-500/30 mb-4">
                <span class="material-icons-outlined text-3xl">fingerprint</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Two-Factor Authentication</h1>
            <p class="text-xs text-slate-500 mt-1">Please enter the 6-digit TOTP code generated by your Authenticator app.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-start gap-2">
                <span class="material-icons-outlined text-lg shrink-0 mt-0.5">error_outline</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('auth.2fa.post') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5 text-center">6-Digit Security Code</label>
                <input type="text" id="code" name="code" maxlength="6" autofocus required
                    class="w-full py-3 text-center tracking-[0.5em] text-2xl font-mono font-bold rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-300"
                    placeholder="000000">
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition-all flex items-center justify-center gap-2">
                <span>Verify & Proceed</span>
                <span class="material-icons-outlined text-lg">check_circle</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Back to Login</a>
        </div>
    </div>

</body>
</html>
""")

# 4. auth/register.blade.php
write_view('auth/register.blade.php', r"""<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration - MetaERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
        <div class="text-center mb-6">
            <div class="inline-flex w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 items-center justify-center text-white text-xl font-bold shadow-md mb-3">
                ME
            </div>
            <h1 class="text-xl font-bold text-slate-900">Register Staff Account</h1>
            <p class="text-xs text-slate-500 mt-1">Create user credentials for MetaERP system access</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Legal Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Username / Email</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Department / Occupation</label>
                <input type="text" name="occupation" value="{{ old('occupation', 'Trading Specialist') }}"
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition-all text-sm">
                Create Account
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
            Already have an account? <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Sign In</a>
        </div>
    </div>

</body>
</html>
""")

# 5. home.blade.php
write_view('home.blade.php', r"""@extends('layouts.app')

@section('header_title', 'Operational Overview')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">description</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $activeContractsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Contracts</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">request_quote</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $openOffersCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open Offers</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">category</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $productsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Commodities</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">contacts</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $contactsCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Counterparties</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-icons-outlined text-2xl">corporate_fare</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-800">{{ $companiesCount }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Group Companies</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Welcome back, {{ Auth::user()->displayName ?? Auth::user()->name }}</h2>
            <p class="text-sm text-blue-100 mt-1">Trading pipelines and contracts are synchronized and active.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('contracts.board') }}" class="px-4 py-2.5 rounded-xl bg-white text-blue-800 font-semibold text-sm hover:bg-blue-50 shadow transition-all flex items-center gap-2">
                <span class="material-icons-outlined text-lg">view_kanban</span>
                <span>Open Kanban Board</span>
            </a>
            <a href="{{ route('contracts.search') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-500 shadow transition-all flex items-center gap-2">
                <span class="material-icons-outlined text-lg">search</span>
                <span>Search Contracts</span>
            </a>
        </div>
    </div>

    <!-- Recent Contracts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <span class="material-icons-outlined text-blue-600">history</span>
                <span>Recent Contracts Pipeline Activity</span>
            </h3>
            <a href="{{ route('contracts.board') }}" class="text-xs text-blue-600 font-semibold hover:underline">View All &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-5">Order Code</th>
                        <th class="py-3 px-5">Type</th>
                        <th class="py-3 px-5">Counterparty</th>
                        <th class="py-3 px-5">Stage</th>
                        <th class="py-3 px-5">Date</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentContracts as $c)
                        @php
                            $deal = $c->purchase ?: $c->sale;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-5 font-semibold text-slate-800">
                                {{ $c->orderCode ?: "Contract #{$c->id}" }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $c->type === 'BUY' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $c->type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-medium text-slate-700">
                                {{ $deal?->contact?->name ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $c->stage?->name ?? 'Draft' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-xs text-slate-400">
                                {{ $c->date ? $c->date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('contracts.board', ['boardId' => $c->stage?->model_id ?? 2]) }}" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold hover:underline">
                                    <span>Board</span>
                                    <span class="material-icons-outlined text-sm">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">No recent contracts recorded</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
""")
