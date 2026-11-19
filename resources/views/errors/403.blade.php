<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden - MetaERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800">
    <div class="max-w-md w-full text-center bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-100">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
            <svg class="w-10 h-10 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Access Restricted</h1>
        <p class="text-slate-500 text-sm mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: "You do not have the required permissions to access this page or perform this action." }}
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url()->previous() ?: route('home') }}" class="px-5 py-2.5 rounded-full border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                Go Back
            </a>
            <a href="{{ route('home') }}" class="px-6 py-2.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors">
                Home Dashboard
            </a>
        </div>
    </div>
</body>
</html>
