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

        /* FUSE Loading Spinner (Matching FuseLoading in React) */
        .fuse-spinner, #spinner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 56px;
        }

        .fuse-spinner > div, #spinner > div {
            width: 12px;
            height: 12px;
            background-color: #1E96F7;
            border-radius: 100%;
            display: inline-block;
            -webkit-animation: fuse-bouncedelay 1s infinite ease-in-out both;
            animation: fuse-bouncedelay 1s infinite ease-in-out both;
        }

        .fuse-spinner .bounce1, #spinner .bounce1 {
            -webkit-animation-delay: -0.32s;
            animation-delay: -0.32s;
        }

        .fuse-spinner .bounce2, #spinner .bounce2 {
            -webkit-animation-delay: -0.16s;
            animation-delay: -0.16s;
        }

        @-webkit-keyframes fuse-bouncedelay {
            0%, 80%, 100% {
                -webkit-transform: scale(0);
            }
            40% {
                -webkit-transform: scale(1.0);
            }
        }

        @keyframes fuse-bouncedelay {
            0%, 80%, 100% {
                -webkit-transform: scale(0);
                transform: scale(0);
            }
            40% {
                -webkit-transform: scale(1.0);
                transform: scale(1.0);
            }
        }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800 bg-slate-50"
      x-data="{
          sidebarMobileOpen: false,
          sidebarCollapsed: JSON.parse(localStorage.getItem('metaerp_sidebar_collapsed') || 'false'),
          sidebarHovered: false,
          toggleCollapsed() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              this.sidebarHovered = false;
              localStorage.setItem('metaerp_sidebar_collapsed', JSON.stringify(this.sidebarCollapsed));
          },
          get isExpanded() {
              return !this.sidebarCollapsed || this.sidebarHovered;
          }
      }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarMobileOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden" @click="sidebarMobileOpen = false"></div>

    <!-- Main Sidebar (Fuse Style with Modern Collapsible Icon Rail & Hover Expansion) -->
    <aside
        @mouseenter="if(sidebarCollapsed) sidebarHovered = true"
        @mouseleave="if(sidebarCollapsed) sidebarHovered = false"
        :class="[
            sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            isExpanded ? 'w-64' : 'w-[72px]'
        ]"
        class="fixed inset-y-0 left-0 z-50 bg-[#0e1726] text-slate-300 transition-all duration-300 ease-in-out flex flex-col shadow-2xl select-none"
    >
        
        <!-- Brand Header: Logo + ERP Badge + Hamburger Toggle -->
        <div class="h-14 flex items-center border-b border-slate-800/80 bg-[#0a101d]/50 shrink-0 transition-all duration-300"
             :class="isExpanded ? 'justify-between px-4' : 'justify-center px-2'">
            <div class="flex items-center gap-2.5 overflow-hidden" x-show="isExpanded" x-transition.opacity>
                <img src="{{ asset('assets/images/logo/meta-new-32.png') }}" alt="ME" class="w-7 h-7 object-contain shrink-0" onerror="this.onerror=null; this.src='/assets/images/logo/meta-new.png'">
                <div class="px-2 py-0.5 rounded bg-[#0a101d] border border-slate-800 text-[#38bdf8] text-[11px] font-bold tracking-wider shrink-0">
                    ERP
                </div>
            </div>
            <button type="button"
                    @click="sidebarMobileOpen ? sidebarMobileOpen = false : toggleCollapsed()"
                    class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800/60 transition-colors focus:outline-none shrink-0"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse to icons'">
                <span class="material-icons-outlined text-lg">menu</span>
            </button>
        </div>

        <!-- Current User Profile in Sidebar -->
        <div class="py-5 px-3 text-center border-b border-slate-800/60 shrink-0 transition-all">
            <div class="font-normal text-slate-400/90 select-none tracking-tight transition-all duration-200"
                 :class="isExpanded ? 'text-4xl sm:text-5xl mb-2.5' : 'text-2xl mb-0'">
                {{ strtoupper(substr(Auth::user()->displayName ?? Auth::user()->name ?? 'R', 0, 1)) }}
            </div>
            <div x-show="isExpanded" x-transition.opacity class="overflow-hidden">
                <div class="font-bold text-sm text-white tracking-wide truncate">
                    {{ Auth::user()->displayName ?? Auth::user()->name ?? 'Root' }}
                </div>
                <div class="text-[12px] text-slate-400 font-normal mt-0.5 truncate">
                    {{ Auth::user()->email ?? Auth::user()->username ?? 'root@root.com' }}
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1 custom-scrollbar text-sm font-medium">
            <!-- Home -->
            <a href="{{ route('home') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Home">
                <span class="material-icons-outlined text-lg shrink-0">home</span>
                <span class="truncate" x-show="isExpanded" x-transition.opacity>Home</span>
            </a>

            <!-- Dashboard -->
            <a href="{{ route('dashboards.analytics') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('dashboards.*') || request()->routeIs('dashboard') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Dashboard">
                <span class="material-icons-outlined text-lg shrink-0">speed</span>
                <span class="truncate" x-show="isExpanded" x-transition.opacity>Dashboard</span>
            </a>

            <!-- Users -->
            <a href="{{ route('users.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('users.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Users">
                <span class="material-icons-outlined text-lg shrink-0">people_outline</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Users</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Manage users</div>
                </div>
            </a>

            <!-- Products -->
            <a href="{{ route('products.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Products">
                <span class="material-icons-outlined text-lg shrink-0">inventory_2</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Products</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">All products</div>
                </div>
            </a>

            <!-- Meta Group (Companies) -->
            <a href="{{ route('companies.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('companies.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Meta Group">
                <span class="material-icons-outlined text-lg shrink-0">apartment</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Meta Group</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Meta Group of companies</div>
                </div>
            </a>

            <!-- Contacts -->
            <a href="{{ route('contacts.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('contacts.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Contacts">
                <span class="material-icons-outlined text-lg shrink-0">groups</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Contacts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Add, find, edit contacts</div>
                </div>
            </a>

            <!-- Offers -->
            <a href="{{ route('offers.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('offers.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Offers">
                <span class="material-icons-outlined text-lg shrink-0">request_quote</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Offers</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Purchase and sales offers</div>
                </div>
            </a>

            <!-- Find Contracts -->
            <a href="{{ route('contracts.search') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('contracts.search') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Find Contracts">
                <span class="material-icons-outlined text-lg shrink-0">find_in_page</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Find Contracts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Search contracts by date, status</div>
                </div>
            </a>

            <!-- Contracts -->
            <a href="{{ route('contracts.board', ['boardId' => 2]) }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('contracts.board') || request()->routeIs('contracts.index') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Contracts">
                <span class="material-icons-outlined text-lg shrink-0">newspaper</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Contracts</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Purchase and sales contracts</div>
                </div>
            </a>

            <!-- Detached Notes -->
            <a href="{{ route('detached-notes.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('detached-notes.*') || request()->is('detached-notes*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Detached Notes">
                <span class="material-icons-outlined text-lg shrink-0">description</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Detached Notes</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Detached Notes</div>
                </div>
            </a>

            <!-- Access Control -->
            <a href="{{ route('accessgroups.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('accessgroups.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Access Control">
                <span class="material-icons-outlined text-lg shrink-0">fingerprint</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Access Control</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Manage access control of users</div>
                </div>
            </a>

            <!-- Templates -->
            <a href="{{ route('templates.index') }}"
               class="flex items-center rounded-xl transition-all {{ request()->routeIs('templates.*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
               :class="isExpanded ? 'gap-3 px-3 py-2 justify-start' : 'p-2.5 justify-center'"
               title="Templates">
                <span class="material-icons-outlined text-lg shrink-0">badge</span>
                <div class="min-w-0" x-show="isExpanded" x-transition.opacity>
                    <div class="truncate text-sm leading-tight">Templates</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">Manage templates</div>
                </div>
            </a>

            <!-- Settings (Collapsible Submenu Matching Screenshots) -->
            <div x-data="{ open: {{ request()->routeIs('settings.*') || request()->is('settings*') || request()->is('countries*') || request()->is('company-types*') || request()->is('credit-debit-types*') || request()->is('currencies*') || request()->is('departments*') || request()->is('packTypes*') || request()->is('paymentTypes*') || request()->is('paymentTermsTypes*') ? 'true' : 'false' }} }">
                <button @click="if(!isExpanded) { toggleCollapsed(); open = true; } else { open = !open; }"
                        type="button"
                        class="w-full flex items-center rounded-xl transition-all cursor-pointer {{ request()->routeIs('settings.*') || request()->is('countries*') || request()->is('company-types*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}"
                        :class="isExpanded ? 'justify-between px-3 py-2' : 'justify-center p-2.5'"
                        title="Settings">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="material-icons-outlined text-lg shrink-0">settings</span>
                        <div class="min-w-0 text-left" x-show="isExpanded" x-transition.opacity>
                            <div class="truncate text-xs font-bold uppercase tracking-wider text-slate-300">SETTINGS</div>
                            <div class="text-[11px] text-slate-400 leading-tight mt-0.5">System settings</div>
                        </div>
                    </div>
                    <span class="material-icons text-sm text-slate-400 transition-transform duration-200"
                          x-show="isExpanded"
                          :class="open ? 'rotate-180' : ''">keyboard_arrow_down</span>
                </button>

                <!-- Submenu Items -->
                <div x-show="open && isExpanded" x-transition class="mt-1 space-y-0.5 pl-2">
                    <!-- 1. Countries -->
                    <a href="{{ route('settings.countries') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.countries*') || request()->is('countries*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">public</span>
                        <span class="text-xs truncate">Countries</span>
                    </a>

                    <!-- 2. Company type -->
                    <a href="{{ route('settings.companyTypes') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.companyTypes*') || request()->is('company-types*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">apartment</span>
                        <span class="text-xs truncate">Company type</span>
                    </a>

                    <!-- 3. Credit/Debit Type -->
                    <a href="{{ route('settings.creditDebitTypes') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.creditDebitTypes*') || request()->is('credit-debit-types*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">receipt_long</span>
                        <span class="text-xs truncate">Credit/Debit Type</span>
                    </a>

                    <!-- 4. Currencies -->
                    <a href="{{ route('settings.currencies') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.currencies*') || request()->is('currencies*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">monetization_on</span>
                        <span class="text-xs truncate">Currencies</span>
                    </a>

                    <!-- 5. Departments -->
                    <a href="{{ route('settings.departments') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.departments*') || request()->is('departments*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">account_balance</span>
                        <span class="text-xs truncate">Departments</span>
                    </a>

                    <!-- 6. Pack Type -->
                    <a href="{{ route('settings.packTypes') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.packTypes*') || request()->is('packTypes*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">inventory_2</span>
                        <span class="text-xs truncate">Pack Type</span>
                    </a>

                    <!-- 7. Payment Type -->
                    <a href="{{ route('settings.paymentTypes') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.paymentTypes*') || request()->is('paymentTypes*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">payment</span>
                        <span class="text-xs truncate">Payment Type</span>
                    </a>

                    <!-- 8. Payment Terms Type -->
                    <a href="{{ route('settings.paymentTerms') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all {{ request()->routeIs('settings.paymentTerms*') || request()->is('paymentTermsTypes*') ? 'bg-[#1e293b] text-white shadow-xs font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                        <span class="material-icons-outlined text-lg shrink-0">price_check</span>
                        <span class="text-xs truncate">Payment Terms Type</span>
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Desktop Spacer Div (reserves layout space for sidebar) -->
    <div class="hidden lg:block shrink-0 transition-all duration-300 ease-in-out"
         :class="sidebarCollapsed ? 'w-[72px]' : 'w-64'">
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        <!-- Topbar: Amber Star on Left, Fullscreen + Search + Root (u) on Right -->
        <header class="h-14 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0 shadow-xs z-30">
            <!-- Left Side: Hamburger Menu & Favorite Star -->
            <div class="flex items-center gap-2">
                <!-- Mobile Menu Button -->
                <button type="button" @click="sidebarMobileOpen = !sidebarMobileOpen" class="p-1.5 text-slate-600 rounded-lg hover:bg-slate-100 lg:hidden">
                    <span class="material-icons-outlined text-xl leading-none">menu</span>
                </button>
                <!-- Desktop Sidebar Toggle Button (visible when collapsed or in topbar) -->
                <button type="button"
                        x-show="sidebarCollapsed"
                        x-cloak
                        @click="toggleCollapsed()"
                        class="hidden lg:flex items-center justify-center p-1.5 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors"
                        title="Expand Sidebar">
                    <span class="material-icons-outlined text-xl leading-none">menu</span>
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

        <!-- Global Toast / Snackbar Notification (Matching Image 1) -->
        <div x-data="{
            show: false,
            message: '',
            type: 'success',
            timeout: null,
            showToast(msg, toastType = 'success') {
                this.message = msg;
                this.type = toastType;
                this.show = true;
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => { this.show = false; }, 5000);
            }
        }"
        x-init="
            @if (session('success'))
                showToast(@js(session('success')), 'success');
            @elseif (session('error'))
                showToast(@js(session('error')), 'error');
            @elseif (session('info'))
                showToast(@js(session('info')), 'info');
            @elseif (session('warning'))
                showToast(@js(session('warning')), 'warning');
            @endif
        "
        @show-toast.window="showToast($event.detail.message, $event.detail.type || 'success')"
        x-cloak
        x-show="show"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-4 opacity-0"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[110] select-none pointer-events-auto">
            <div :class="{
                    'bg-[#43a047]': type === 'success',
                    'bg-[#d32f2f]': type === 'error',
                    'bg-[#1976d2]': type === 'info',
                    'bg-[#ed6c02]': type === 'warning'
                 }"
                 class="flex items-center min-w-[280px] max-w-lg px-4 py-2.5 rounded-md shadow-lg shadow-black/25 text-white">
                <span class="material-icons text-xl leading-none text-white mr-2.5"
                      x-text="type === 'success' ? 'check_circle' : (type === 'error' ? 'error_outline' : (type === 'warning' ? 'warning' : 'info'))">
                    check_circle
                </span>
                <span class="text-xs sm:text-sm font-medium text-white tracking-wide flex-1 mr-2" x-text="message"></span>
                <button type="button" 
                        @click="show = false" 
                        class="text-white/80 hover:text-white transition-colors p-0.5 rounded leading-none flex items-center justify-center cursor-pointer">
                    <span class="material-icons text-base leading-none">close</span>
                </button>
            </div>
        </div>

        <!-- Global Confirmation Modal (Matching Image 2) -->
        <div x-data="{
            open: false,
            title: 'Confirmation',
            message: 'Do you want to delete?',
            confirmForm: null,
            confirmCallback: null,
            openDialog(detail) {
                this.title = detail.title || 'Confirmation';
                this.message = detail.message || 'Do you want to delete?';
                this.confirmForm = detail.form || null;
                this.confirmCallback = detail.onConfirm || null;
                this.open = true;
            },
            confirm() {
                this.open = false;
                if (this.confirmForm) {
                    this.confirmForm.dataset.confirmed = 'true';
                    this.confirmForm.submit();
                } else if (this.confirmCallback) {
                    this.confirmCallback();
                }
            },
            cancel() {
                this.open = false;
                this.confirmForm = null;
                this.confirmCallback = null;
            }
        }"
        @open-confirmation-modal.window="openDialog($event.detail)"
        x-cloak
        x-show="open"
        class="fixed inset-0 z-[120] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="cancel()"></div>

            <!-- Dialog Box Matching Image 2 -->
            <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-[340px] z-10 transform transition-all select-none"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <h3 class="text-base font-semibold text-slate-800 tracking-tight" x-text="title">Confirmation</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-3 font-normal" x-text="message">Do you want to delete?</p>
                
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" 
                            @click="confirm()" 
                            class="text-xs sm:text-sm font-semibold text-[#4f46e5] hover:text-[#4338ca] hover:bg-indigo-50 px-3.5 py-1.5 rounded transition cursor-pointer">
                        Yes
                    </button>
                    <button type="button" 
                            @click="cancel()" 
                            class="text-xs sm:text-sm font-semibold text-[#4f46e5] hover:text-[#4338ca] hover:bg-indigo-50 px-3.5 py-1.5 rounded transition cursor-pointer">
                        No
                    </button>
                </div>
            </div>
        </div>

        <!-- Global FuseLoading Loader Overlay (Matching FuseLoading in React) -->
        <div id="global-page-loader"
             x-data="{ show: false, text: 'Loading' }"
             @show-loader.window="show = true; text = $event.detail?.text || 'Loading'"
             @hide-loader.window="show = false"
             x-cloak
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[200] bg-white/80 backdrop-blur-[2px] flex flex-col items-center justify-center select-none pointer-events-auto">
            <div class="text-base sm:text-lg font-medium text-slate-500 select-none -mb-1" x-text="text">Loading</div>
            <div class="fuse-spinner mt-4">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>

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

    <script>
        // Global Confirmation Dialog Helpers (Matching Image 2)
        window.openConfirmDialog = function(options) {
            window.dispatchEvent(new CustomEvent('open-confirmation-modal', {
                detail: {
                    title: options.title || 'Confirmation',
                    message: options.message || 'Do you want to delete?',
                    form: options.form || null,
                    onConfirm: options.onConfirm || null
                }
            }));
        };

        window.confirmDelete = function(formOrCallback, message = 'Do you want to delete?', title = 'Confirmation') {
            if (typeof formOrCallback === 'function') {
                window.openConfirmDialog({ title: title, message: message, onConfirm: formOrCallback });
            } else {
                window.openConfirmDialog({ title: title, message: message, form: formOrCallback });
            }
        };

        // Global Toast Trigger
        window.showToast = function(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message: message, type: type }
            }));
        };

        // Global FuseLoading Trigger (Matching FuseLoading in React)
        window.showLoading = function(text = 'Loading') {
            window.dispatchEvent(new CustomEvent('show-loader', {
                detail: { text: text }
            }));
        };

        window.hideLoading = function() {
            window.dispatchEvent(new CustomEvent('hide-loader'));
        };

        // Global interceptor for all DELETE forms or forms requiring confirmation
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.dataset && form.dataset.confirmed === 'true') {
                return true;
            }

            const methodInput = form.querySelector('input[name="_method"]');
            const isDelete = (methodInput && methodInput.value.toUpperCase() === 'DELETE') || form.hasAttribute('data-confirm');

            if (isDelete) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const title = form.getAttribute('data-confirm-title') || 'Confirmation';
                const message = form.getAttribute('data-confirm-message') || 'Do you want to delete?';

                window.openConfirmDialog({
                    title: title,
                    message: message,
                    form: form
                });
                return false;
            }
        }, true);
    </script>
</body>
</html>
