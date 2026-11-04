<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MetaERP - Enterprise' }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Inter Font & Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.25); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.45); }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Main Sidebar (Fuse Style) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0e1726] text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shadow-2xl select-none">
        
        <!-- Brand Header: Logo + ERP Badge + Hamburger -->
        <div class="h-14 flex items-center justify-between px-5 border-b border-slate-800/80 bg-[#0a101d]/50">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('assets/images/logo/meta-new-32.png') }}" alt="ME" class="w-7 h-7 object-contain" onerror="this.onerror=null; this.src='/assets/images/logo/meta-new.png'">
                <div class="px-2 py-0.5 rounded bg-[#0a101d] border border-slate-800 text-[#38bdf8] text-[11px] font-bold tracking-wider">
                    ERP
                </div>
            </div>
            <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-1 rounded transition-colors focus:outline-none">
                <span class="material-icons-outlined text-lg">menu</span>
            </button>
        </div>

        <!-- Current User Profile in Sidebar -->
        <div class="py-6 px-4 text-center border-b border-slate-800/60">
            <div class="text-4xl sm:text-5xl font-normal text-slate-400/90 mb-3 select-none tracking-tight">
                {{ strtoupper(substr(Auth::user()->displayName ?? Auth::user()->name ?? 'R', 0, 1)) }}
            </div>
            <div class="font-bold text-sm text-white tracking-wide">
                {{ Auth::user()->displayName ?? Auth::user()->name ?? 'Root' }}
            </div>
            <div class="text-[12px] text-slate-400 font-normal mt-0.5">
                {{ Auth::user()->email ?? Auth::user()->username ?? 'root@root.com' }}
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1 custom-scrollbar text-sm font-medium">
            <!-- Home -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">home</span>
                <span class="truncate">Home</span>
            </a>

            <!-- Dashboard -->
            <a href="{{ route('dashboards.analytics') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dashboards.*') || request()->routeIs('dashboard') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">speed</span>
                <span class="truncate">Dashboard</span>
            </a>

            <!-- Users -->
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('users.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">people_outline</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Users</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Manage users</div>
                </div>
            </a>

            <!-- Products -->
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">inventory_2</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Products</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">All products</div>
                </div>
            </a>

            <!-- Meta Group (Companies) -->
            <a href="{{ route('companies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('companies.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">apartment</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Meta Group</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Meta Group of companies</div>
                </div>
            </a>

            <!-- Contacts -->
            <a href="{{ route('contacts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contacts.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">groups</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Contacts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Add, find, edit contacts</div>
                </div>
            </a>

            <!-- Offers -->
            <a href="{{ route('offers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('offers.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">request_quote</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Offers</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Purchase and sales offers</div>
                </div>
            </a>

            <!-- Find Contracts -->
            <a href="{{ route('contracts.search') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contracts.search') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">find_in_page</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Find Contracts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Search contracts by date, status</div>
                </div>
            </a>

            <!-- Contracts -->
            <a href="{{ route('contracts.board', ['boardId' => 2]) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contracts.board') || request()->routeIs('contracts.index') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">newspaper</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Contracts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Purchase and sales contracts</div>
                </div>
            </a>

            <!-- Detached Notes -->
            <a href="{{ route('detached-notes.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('detached-notes.*') || request()->is('detached-notes*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">description</span>
                <div class="min-w-0">
                    <div class="truncate text-sm leading-tight">Detached Notes</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Detached Notes</div>
                </div>
            </a>

            <!-- Access Control -->
            <a href="{{ route('accessgroups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('accessgroups.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <span class="material-icons-outlined text-lg shrink-0">fingerprint</span>
                <span class="truncate">Access Control</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        <!-- Topbar: Amber Star on Left, Fullscreen + Search + Root (u) on Right -->
        <header class="h-14 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0 shadow-xs z-30">
            <!-- Left Side: Favorite Star -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 text-slate-600 rounded-lg hover:bg-slate-100 lg:hidden">
                    <span class="material-icons-outlined">menu</span>
                </button>
                <button type="button" class="text-amber-500 hover:text-amber-600 focus:outline-none transition-transform active:scale-95" title="Favorite">
                    <span class="material-icons text-xl leading-none">star</span>
                </button>
            </div>

            <!-- Right Side: Fullscreen, Search, User Avatar -->
            <div class="flex items-center gap-3">
                <!-- Fullscreen Button -->
                <button type="button" @click="if (!document.fullscreenElement) { document.documentElement.requestFullscreen(); } else { document.exitFullscreen(); }" class="p-1 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition-colors" title="Toggle Fullscreen">
                    <span class="material-icons-outlined text-xl leading-none">fullscreen</span>
                </button>

                <!-- Search Button -->
                <a href="{{ route('contracts.search') }}" class="p-1 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition-colors" title="Search">
                    <span class="material-icons-outlined text-lg leading-none">search</span>
                </a>

                <!-- User Dropdown: Text 'Root' + circle 'u' -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 py-1 px-1.5 rounded-full hover:bg-slate-100 transition-colors">
                        <span class="text-xs font-semibold text-slate-700">
                            {{ Auth::user()->name ?? 'Root' }}
                        </span>
                        <div class="w-7 h-7 rounded-full bg-[#b0b8c4] text-white font-normal flex items-center justify-center text-xs select-none">
                            {{ strtolower(substr(Auth::user()->name ?? 'u', 0, 1)) }}
                        </div>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50 text-sm">
                        <a href="{{ route('profile.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <span class="material-icons-outlined text-base">person</span> My Profile
                        </a>
                        <hr class="my-1 border-slate-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-red-50 text-red-600 text-left">
                                <span class="material-icons-outlined text-base">logout</span> Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-2.5 flex items-center justify-between text-emerald-800 text-sm">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-emerald-600 text-lg">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-b border-red-200 px-6 py-2.5 flex items-center justify-between text-red-800 text-sm">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-red-600 text-lg">error_outline</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-2.5 flex items-center justify-between text-blue-800 text-sm">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-blue-600 text-lg">info</span>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-blue-500 hover:text-blue-700">&times;</button>
            </div>
        @endif

        <!-- Main Body Content -->
        <main class="flex-1 overflow-y-auto custom-scrollbar p-6 bg-slate-50">
            @yield('content')
        </main>

        <!-- Footer: Copyright & Version -->
        <footer class="h-9 bg-[#0b1424] text-slate-400 px-6 flex items-center justify-between text-xs shrink-0 select-none border-t border-slate-800/80">
            <div>© Copyright 2023-2026 Metaenterprices LTD</div>
            <div class="font-mono">v2.8.6</div>
        </footer>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
