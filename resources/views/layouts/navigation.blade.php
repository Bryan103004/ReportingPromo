<nav x-data="{ open: false }" @keydown.escape.window="open = false" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="transition transform hover:scale-105">
                        <img src="{{ asset('images/gl.png') }}" class="h-9 w-auto" alt="Logo GL">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex items-center">
          
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <i class="fa-solid fa-gauge-high mr-2 opacity-70"></i> {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Dropdown Data Master — pakai Alpine supaya tidak terpotong -->
                    <div class="h-full flex items-center" x-data="{ masterOpen: false, mgmtOpen: false }" @click.outside="masterOpen = false; mgmtOpen = false">
                        <div class="relative">
                            <button
                                @click="masterOpen = !masterOpen; mgmtOpen = false"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['perusahaan.*', 'toko.*', 'user.*', 'role.*', 'permission.*']) ? 'text-indigo-700 font-bold bg-indigo-50' : '' }}">
                                <span>Data Master</span>
                                <svg class="ms-1.5 h-4 w-4 opacity-50 transition-transform duration-150" :class="{ 'rotate-180': masterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown panel utama --> <!-- buat di komputer -->
                            <div
                                x-show="masterOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 z-50"
                                style="top: 100%;">

                                <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-widest">Operasional</div>

                                @can('view_perusahaan')
                                    <a href="{{ route('perusahaan.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors mx-1 rounded-lg {{ request()->routeIs('perusahaan.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                        <span class="text-base">🏢</span> Perusahaan
                                    </a>
                                @endcan

                                @can('email_tambahan')
                                    <x-dropdown-link :href="route('notification-recipients.index')" class="text-sm {{ request()->routeIs('notification-recipients.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                        <span>📧</span> Email Tambahan
                                    </x-dropdown-link>
                                @endcan

                                @can('view_data_kendaraan')
                                <x-dropdown-link :href="route('master-alokasi.index')" class="text-sm {{ request()->routeIs('master-alokasi.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <span></span>📁 Master Alokasi
                                </x-dropdown-link>
                                @endcan

                                @can('view_data_izin_oss')
                                <x-dropdown-link :href="route('master-izin-oss.index')" class="text-sm {{ request()->routeIs('master-izin-oss.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                    <span></span>📁 Master Izin Oss
                                </x-dropdown-link>
                                @endcan
                                <div class="border-t border-gray-100 my-1.5 mx-1"></div>

                                <!-- Manajemen Pengguna — expand inline ke bawah -->
                                <div>
                                    <button
                                        @click="mgmtOpen = !mgmtOpen"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors mx-1 rounded-lg {{ request()->routeIs(['user.*', 'role.*', 'toko.*', 'region.*', 'permission.*']) ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">👥</span>
                                            <span>Manajemen Pengguna</span>
                                        </div>
                                            <svg class="ms-1.5 h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <!-- Sub-items expand ke bawah -->
                                    <div
                                        x-show="mgmtOpen"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mt-0.5 mb-0.5 ml-2">

                                            @can('view_users')
                                            <x-dropdown-link :href="route('user.index')" class="text-sm {{ request()->routeIs('user.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                <span>👤</span> Users
                                            </x-dropdown-link>
                                            @endcan

                                            @can('view_roles')
                                            <x-dropdown-link :href="route('role.index')" class="text-sm {{ request()->routeIs('role.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                <span>🔑</span> Roles
                                             </x-dropdown-link>
                                             @endcan

                                             @can('view_permissions')
                                            <x-dropdown-link :href="route('permission.index')" class="text-sm {{ request()->routeIs('permission.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                <span>🛡️</span> Permissions 
                                            </x-dropdown-link>
                                            @endcan

                                            @can('view_toko')
                                            <x-dropdown-link :href="route('toko.index')" class="text-sm {{  request()->routeIs('toko.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : ''  }}">
                                                <span class="text-base">🏪</span> Master Toko
                                            </x-dropdown-link>
                                            @endcan

                                            @can('view_region')
                                            <x-dropdown-link :href="route('region.index')" class="text-sm {{ request()->routeIs('region.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : ''  }}" >
                                                <span class="text-base">📍</span> Master Region
                                            </x-dropdown-link>
                                            @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Rafaksi -->
                    @canany(['view_jsm', 'view_rafaksi'])
                    <div class="h-full flex items-center" x-data="{ menuRafaksiOpen: false, subRafaksiOpen: false }" @click.outside="menuRafaksiOpen = false; subRafaksiOpen = false">
                        <div class="relative">
                            <button
                                @click="menuRafaksiOpen = !menuRafaksiOpen; subRafaksiOpen = false"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs(['jsm*','rafaksi*','supplier_rafaksi*']) ? 'text-indigo-700 font-bold bg-indigo-50' : '' }}">
                                <span>Data Reporting</span>
                                <svg class="ms-1.5 h-4 w-4 opacity-50 transition-transform duration-150" :class="{ 'rotate-180': menuRafaksiOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown panel utama --> <!-- buat di komputer -->
                            <div
                                x-show="menuRafaksiOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 z-50"
                                style="top: 100%;">

                                <!-- Manajemen Rafaksi -->
                                <div>
                                    <button
                                        @click="subRafaksiOpen = !subRafaksiOpen"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors mx-1 rounded-lg {{ request()->routeIs(['jsm.*', 'rafaksi.*', 'supplier_rafaksi.*']) ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">👥</span>
                                            <span>Manajemen Rafaksi</span>
                                        </div>
                                            <svg class="ms-1.5 h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <!-- Sub-items expand ke bawah -->
                                    <div
                                        x-show="subRafaksiOpen"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mt-0.5 mb-0.5 ml-2">
                                            @can('view_jsm')
                                            <x-dropdown-link :href="route('jsm.index')" class="text-sm {{ request()->routeIs('jsm.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                {{ __('JSM') }}
                                            </x-dropdown-link>
                                            @endcan
                            
                                            @can('view_rafaksi')
                                            <x-dropdown-link :href="route('rafaksi.index')" class="text-sm {{ request()->routeIs('rafaksi.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                {{ __('Rafaksi') }}
                                            </x-dropdown-link>
                                            @endcan

                                            @can('view_loc')
                                            <x-dropdown-link :href="route('loc.index')" class="text-sm {{ request()->routeIs('loc.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                {{ __('Loc') }}
                                            </x-dropdown-link>
                                            @endcan

                                            @canany(['view_jsm', 'view_rafaksi'])
                                            <x-dropdown-link :href="route('supplier_rafaksi.index')" class="text-sm {{ request()->routeIs('supplier_rafaksi.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                                                {{ __('Supplier Rafaksi') }}
                                            </x-dropdown-link>
                                            @endcanany
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endcanany

                    @can('view_reports_promo_weekend')
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')">
                        {{ __('Reports Promo Weekend') }}
                    </x-nav-link>
                    @endcan

                    @can('can_see_partner_and_oss')
                        <div class="relative" x-data="{ partnerOpen: false }" @click.away="partnerOpen = false">
                            <button
                                @click="partnerOpen = !partnerOpen"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md transition duration-150 ease-in-out 
                                {{ request()->routeIs('partnership.*') ? 'text-indigo-700 font-bold bg-indigo-50' : 'text-gray-500 bg-white hover:text-indigo-600 hover:bg-indigo-50' }}">
                                <span>Partnership</span>
                                <svg class="ms-1.5 h-4 w-4 opacity-50 transition-transform duration-200" 
                                    :class="{ 'rotate-180': partnerOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown panel -->
                            <div
                                x-show="partnerOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                class="absolute left-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 z-50 overflow-hidden">

                                <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">
                                    Partner & Kontrak
                                </div>

                                <a href="{{ route('partnership.index') }}" 
                                class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors mx-1 rounded-lg 
                                {{ request()->routeIs('partnership.index') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Data Kontrak
                                </a>

                                @can('view_data_izin_oss')
                                <a href="{{ route('data_izin_oss.index') }}" 
                                class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors mx-1 rounded-lg 
                                {{ request()->routeIs('data_izin_oss.index') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Data Izin OSS
                                </a>
                                @endcan

                                <div class="border-t border-gray-100 my-1.5 mx-1"></div>
                            </div>
                        </div>
                    @endcan     

                    @can('view_data_izin_oss')
                        <x-nav-link :href="route('data_izin_oss.index')" :active="request()->routeIs('data_izin_oss.index')">
                            {{ __('Data Izin OSS') }}
                        </x-nav-link>
                    @endcan

                    @can('view_data_kontrak')
                        <x-nav-link :href="route('partnership.index')" :active="request()->routeIs('partnership.index')">
                            {{ __('Data Kontrak') }}
                        </x-nav-link>
                    @endcan
                    

                    @if(strtolower(auth()->user()->name) ==  strtolower(config('app.admin_name'))) 
                    <div class="h-full flex items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition {{ request()->routeIs(['activity-log.*']) ? 'text-indigo-700 font-bold' : '' }}">
                                    <span>Sistem</span>
                                    <svg class="ms-1.5 h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('activity-log.index')">
                                    📜 Log Aktivitas
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="flex items-center bg-gray-50 rounded-full px-3 py-1 border border-gray-100">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                <div class="w-7 h-7 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center mr-2 font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="ms-1 h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile Settings') }}
                            </x-dropdown-link>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" class="text-red-600"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-indigo-500 hover:bg-indigo-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gray-50 border-t border-gray-200 shadow-inner">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
        {{--    <!-- Mobile Group: Data Master -->
            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Data Master</div>
            @can('view_perusahaan')
                <x-responsive-nav-link :href="route('perusahaan.index')" :active="request()->routeIs('perusahaan.index')">🏢 Perusahaan</x-responsive-nav-link>
            @endcan
            @can('view_toko')
                <x-responsive-nav-link :href="route('toko.index')" :active="request()->routeIs('toko.index')">🏪 Toko</x-responsive-nav-link>
            @endcan
            @can('view_users')
                <x-responsive-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">👤 Users</x-responsive-nav-link>
            @endcan
            @can('view_roles')
                <x-responsive-nav-link :href="route('role.index')" :active="request()->routeIs('role.index')">🔑 Roles</x-responsive-nav-link>
            @endcan    

            @can('email_tambahan')
                <x-dropdown-link :href="route('notification-recipients.index')" class="text-sm {{ request()->routeIs('notification-recipients.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <span>📧</span> Email Tambahan
                </x-dropdown-link>
            @endcan
            
            @can('view_data_kendaraan')
            <x-dropdown-link :href="route('master-alokasi.index')" class="text-sm {{ request()->routeIs('master-alokasi.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                <span></span>📁 Master Alokasi
            </x-dropdown-link>     
            @endcan

            @can('view_data_izin_oss')
            <x-dropdown-link :href="route('master-izin-oss.index')" class="text-sm {{ request()->routeIs('master-izin-oss.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                <span></span>📁 Master Izin Oss
            </x-dropdown-link>
            @endcan

            <div class="border-t border-gray-200 my-1"></div>
            
            <x-responsive-nav-link :href="route('data_kendaraan.index')" :active="request()->routeIs('data_kendaraan.index')">Data Kendaraan</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('perizinan.index')" :active="request()->routeIs('perizinan.index')">Data Perizinan</x-responsive-nav-link>
        </div>
         --}}

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
                <div class="ms-3">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile Settings') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-red-600"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>