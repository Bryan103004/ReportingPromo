@props([
    'title', 
    'items' => [],
    'theme' => 'gray'
])

@php
    $themes = [
        'gray'   => 'bg-gray-50/60 border-gray-100 hover:border-gray-200',
        'blue'   => 'bg-blue-50/40 border-blue-100 hover:border-blue-200',
        'green'  => 'bg-emerald-50/40 border-emerald-100 hover:border-emerald-200',
        'yellow' => 'bg-amber-50/40 border-amber-100 hover:border-amber-200',
        'red'    => 'bg-red-50/40 border-red-100 hover:border-red-200',
    ];
    $activeTheme = $themes[$theme] ?? $themes['gray'];
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col h-full hover:shadow-md transition-shadow duration-200">
    
    <!-- Judul Komponen -->
    <h3 class="text-gray-400 font-bold tracking-wider text-[11px] uppercase mb-4">
        {{ $title }}
    </h3>
    
    <!-- Wrapper Konten Utama: Mobile 1 Kolom, Tablet/Desktop 3 Kolom -->
    <div class="grid grid-cols-1 gap-3">

        <div class="flex flex-col p-4 rounded-xl border {{ $activeTheme }} transition-all duration-200">
            
            @if(isset($items->name))
                <div class="text-xs font-bold text-gray-700 mb-3 truncate">
                    {{ $items->name }}
                </div>
            @endif

            <!-- Baris Badges: Menggunakan flex-wrap agar aman di layar kecil handphone -->
            <div class="flex flex-wrap items-center gap-2">
                
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200/80 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Aktif: {{ $items->aktif ?? 0 }}
                </span>

                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide bg-rose-100 text-rose-800 border border-rose-200/80 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    Expired: {{ $items->expired ?? 0 }}
                </span>

                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold tracking-wide bg-blue-100 text-blue-800 border border-blue-200/80 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Done: {{ $items->done ?? 0 }}
                </span>
                
            </div>
            
        </div>
        
    </div>
</div>