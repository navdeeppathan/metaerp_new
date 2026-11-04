import os

def write_view(rel_path, content):
    full_path = os.path.join(r'c:\Users\navde\Downloads\metaerp\resources\views', rel_path)
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    with open(full_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Created view:", rel_path)

# 1. layouts/app.blade.php
write_view('layouts/app.blade.php', r"""<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MetaERP - Enterprise Modernization' }}</title>

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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800" x-data="{ sidebarOpen: false, userMenuOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Main Sidebar (Fuse Style) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col shadow-xl">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 bg-slate-950/60 border-b border-slate-800/80 gap-3">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-bold shadow-md">
                ME
            </div>
            <div>
                <span class="text-white font-bold tracking-wide text-lg">MetaERP</span>
                <span class="block text-[10px] text-blue-400 font-semibold uppercase tracking-wider">Enterprise v1.15</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 custom-scrollbar text-sm font-medium">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">home</span>
                <span>Home Dashboard</span>
            </a>

            <div class="pt-2 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Trading & Operations</div>

            <a href="{{ route('contracts.board', ['boardId' => 2]) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('contracts.board') || request()->routeIs('contracts.index') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">view_kanban</span>
                <span>Contracts Board</span>
            </a>

            <a href="{{ route('contracts.search') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('contracts.search') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">search</span>
                <span>Find Contracts</span>
            </a>

            <a href="{{ route('offers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('offers.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">request_quote</span>
                <span>Offers</span>
            </a>

            <div class="pt-2 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Master Catalog</div>

            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">category</span>
                <span>Products & Metals</span>
            </a>

            <a href="{{ route('companies.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('companies.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">corporate_fare</span>
                <span>Companies</span>
            </a>

            <a href="{{ route('contacts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('contacts.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">contacts</span>
                <span>Contacts & Partners</span>
            </a>

            <div class="pt-2 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Administration</div>

            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">people</span>
                <span>Users & Signatures</span>
            </a>

            <a href="{{ route('accessgroups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('accessgroups.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">security</span>
                <span>Access Control</span>
            </a>

            <a href="{{ route('dashboards.analytics') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboards.*') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <span class="material-icons-outlined text-lg">analytics</span>
                <span>Analytics BI</span>
            </a>

            <!-- Settings Collapsible -->
            <div x-data="{ settingsOpen: false }" class="pt-1">
                <button type="button" @click="settingsOpen = !settingsOpen" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors hover:bg-slate-800 hover:text-white">
                    <span class="flex items-center gap-3">
                        <span class="material-icons-outlined text-lg">settings</span>
                        <span>Settings</span>
                    </span>
                    <span class="material-icons-outlined text-sm transition-transform" :class="settingsOpen ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="settingsOpen" x-cloak class="pl-9 pr-2 py-1 space-y-1 text-xs text-slate-400">
                    <a href="{{ route('settings.countries') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Countries</a>
                    <a href="{{ route('settings.currencies') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Currencies</a>
                    <a href="{{ route('settings.departments') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Departments</a>
                    <a href="{{ route('settings.packTypes') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Pack Types</a>
                    <a href="{{ route('settings.paymentTypes') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Payment Types</a>
                    <a href="{{ route('settings.paymentTerms') }}" class="block py-1.5 px-2 rounded hover:text-white hover:bg-slate-800">Payment Terms</a>
                </div>
            </div>
        </nav>

        <!-- Current User Profile in Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40 flex items-center justify-between">
            <a href="{{ route('profile.index') }}" class="flex items-center gap-3 min-w-0 hover:opacity-80">
                <div class="w-9 h-9 rounded-full bg-blue-600/30 border border-blue-500/40 text-blue-300 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr(Auth::user()->displayName ?? Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ Auth::user()->displayName ?? Auth::user()->name ?? 'Administrator' }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->role ?? 'ROLE_ADMIN' }}</div>
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Sign Out" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-slate-800">
                    <span class="material-icons-outlined text-lg">logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 shadow-sm z-30">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 text-slate-600 rounded-lg hover:bg-slate-100 lg:hidden">
                    <span class="material-icons-outlined">menu</span>
                </button>
                
                <h1 class="text-lg font-bold text-slate-800 hidden sm:block">
                    @yield('header_title', 'MetaERP Dashboard')
                </h1>
            </div>

            <!-- Global Live Metal Price Ticker -->
            <div class="hidden md:flex items-center gap-4 px-4 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs">
                <span class="font-bold text-blue-700 uppercase tracking-wider flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Spot GBP:
                </span>
                <span class="text-slate-700">Gold: <strong>£2,020/oz</strong></span>
                <span class="text-slate-400">|</span>
                <span class="text-slate-700">Silver: <strong>£24.10/oz</strong></span>
                <span class="text-slate-400">|</span>
                <span class="text-slate-700">Palladium: <strong>£925/oz</strong></span>
            </div>

            <!-- Topbar Actions -->
            <div class="flex items-center gap-3">
                <a href="{{ route('contracts.search') }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg" title="Search">
                    <span class="material-icons-outlined">search</span>
                </a>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-semibold flex items-center justify-center text-sm shadow">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline text-xs font-semibold text-slate-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <span class="material-icons-outlined text-sm text-slate-400">expand_more</span>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50 text-sm">
                        <a href="{{ route('profile.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <span class="material-icons-outlined text-base">person</span> My Profile
                        </a>
                        <a href="{{ route('contracts.board') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <span class="material-icons-outlined text-base">dashboard</span> Board
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
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
""")
