@props(['title', 'subtitle', 'badge', 'theme' => 'yellow'])

@php
    // Konfigurasi Tema Warna Dinamis
    $themes = [
        'yellow' => 'bg-[#fffcf0] border-[#fdeeb9] text-amber-700',
        'blue'   => 'bg-[#f4f7ff] border-[#dce5ff] text-blue-700',
        'green'  => 'bg-[#f2fbf5] border-[#d1f4e0] text-emerald-700',
    ];
    $activeTheme = $themes[$theme] ?? $themes['yellow'];
@endphp

<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-4 rounded-xl border {{ $activeTheme }} transition-all hover:shadow-sm hover:-translate-y-0.5 gap-4">
    <!-- Bagian Kiri (Teks) -->
    <div>
        <h4 class="font-bold text-gray-800 text-[15px]">{{ $title }}</h4>
        <p class="text-[11px] text-gray-500 mt-1 uppercase tracking-wide font-medium">{{ $subtitle }}</p>
    </div>
    
    <!-- Bagian Kanan (Badge Tanggal / Nominal) -->
    <div class="shrink-0 px-3.5 py-1.5 rounded-lg border bg-white text-sm font-bold shadow-sm {{ $activeTheme }}">
        {{ $badge }}
    </div>
</div>