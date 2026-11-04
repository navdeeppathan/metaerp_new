<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - MetaERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-white text-slate-800 antialiased m-0 p-0">

    <div class="flex flex-col md:flex-row h-screen w-full min-h-screen">
        <!-- Left Side: Login Form -->
        <div class="w-full md:w-1/2 bg-white flex items-center justify-center p-8 sm:p-12 md:p-16 h-full">
            <div class="w-full max-w-[340px] mx-auto">
                <!-- Logo -->
                <div class="mb-8">
                    <img src="{{ asset('assets/images/logo/meta-new.png') }}" alt="Metaenterprises" class="w-[210px] h-auto object-contain">
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-6">
                    Sign in
                </h1>

                @if (isset($errors) && $errors->any())
                    <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Email Field with Outlined Notch Style -->
                    <div class="relative">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username', 'root@root.com') }}"
                            required
                            autofocus
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-[#edf5ff] text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                            placeholder=" "
                        />
                        <label
                            for="username"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none peer-focus:text-indigo-600 transition-all"
                        >
                            Email <span class="text-slate-400">*</span>
                        </label>
                    </div>

                    <!-- Password Field with Outlined Notch Style -->
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-[#edf5ff] text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                            placeholder=" "
                        />
                        <label
                            for="password"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 pointer-events-none peer-focus:text-indigo-600 transition-all"
                        >
                            Password <span class="text-slate-400">*</span>
                        </label>
                    </div>

                    <!-- Remember me Checkbox (No Forgot Password) -->
                    <div class="flex items-center gap-2 pt-1 pb-2">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            class="w-4 h-4 rounded border-slate-400 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <label for="remember" class="text-sm font-normal text-slate-800 cursor-pointer select-none">
                            Remember me
                        </label>
                    </div>

                    <!-- Sign In Pill Button -->
                    <button
                        type="submit"
                        class="w-full py-2.5 px-6 rounded-full bg-[#4f46e5] hover:bg-[#4338ca] text-white font-medium text-sm shadow-sm transition-colors duration-150 flex items-center justify-center cursor-pointer"
                    >
                        Sign in
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Side: Dark Slate Hero with Concentric Rings & Dot Grid -->
        <div class="relative hidden md:flex md:w-1/2 bg-[#1e293b] items-center justify-center h-full p-12 lg:p-24 overflow-hidden select-none">
            <!-- Concentric SVG Circles -->
            <svg class="absolute inset-0 pointer-events-none w-full h-full" viewBox="0 0 960 540" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
                <g class="text-[#64748b] opacity-20" fill="none" stroke="currentColor" stroke-width="100">
                    <circle r="234" cx="196" cy="23" />
                    <circle r="234" cx="790" cy="491" />
                </g>
            </svg>

            <!-- Dot Grid Pattern at Top Right -->
            <svg class="absolute -top-16 -right-16 opacity-20 text-[#64748b] pointer-events-none" viewBox="0 0 220 192" width="220" height="192" fill="none">
                <defs>
                    <pattern id="erp-dot-grid" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <rect x="0" y="0" width="4" height="4" fill="currentColor" />
                    </pattern>
                </defs>
                <rect width="220" height="192" fill="url(#erp-dot-grid)" />
            </svg>

            <!-- Brand Content -->
            <div class="relative z-10 w-full max-w-lg">
                <h2 class="text-6xl font-bold tracking-tight text-white mb-4 leading-none">
                    ERP
                </h2>
                <p class="text-base text-slate-400 font-normal leading-relaxed">
                    Enterprise resource planning for Metaenterprices LTD
                </p>
            </div>
        </div>
    </div>

</body>
</html>
