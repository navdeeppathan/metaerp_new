<!DOCTYPE html>
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
