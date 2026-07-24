@props([
    'title', 
    'items' => [],
    'theme' => 'gray',
    'route' => null
])

@php
    $themes = [
        'gray'   => 'bg-gray-50 border-gray-200 hover:bg-gray-100 text-gray-700',
        'blue'   => 'bg-blue-50 border-blue-200 hover:bg-blue-100 text-blue-800',
        'green'  => 'bg-emerald-50 border-emerald-200 hover:bg-emerald-100 text-emerald-800',
        'yellow' => 'bg-orange-50 border-orange-200 hover:bg-orange-100 text-orange-800',
        'red'    => 'bg-red-50 border-red-200 hover:bg-red-100 text-red-800',
    ];
    $activeTheme = $themes[$theme] ?? $themes['gray'];
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col h-full hover:shadow-md transition-shadow duration-200">
    
    <h3 class="text-gray-500 font-bold tracking-wider text-xs uppercase mb-4">
        {{ $title }}
    </h3>
    
    <!-- Mobile 1 kolom, Tablet/Desktop 2 kolom -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse($items as $item)
            @php
                $monthName = \Carbon\Carbon::create()->month($item->month)->translatedFormat('M');
            @endphp
            
            <a href="{{ isset($route) ? route($route, ['year' => $item->year, 'month' => $item->month]) : '#' }}" 
            class="flex flex-col p-4 rounded-xl border transition-all duration-200 {{ $activeTheme }} hover:shadow-sm block group">
                
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider opacity-90 group-hover:opacity-100">
                        {{ $monthName }} {{ $item->year }}
                    </span>
                    
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-bold bg-white/90 border border-white/60 shadow-sm">
                        {{ $item->total_dokumen }} Dokumen
                    </span>
                </div>

                <div class="w-full pt-1 border-t border-black/5">
                    <span class="text-base font-black tracking-tight">
                        Rp {{ number_format($item->nominal, 0, ',', '.') }}
                    </span>
                </div>
                
            </a>
        @empty
            <div class="col-span-full text-center py-6 border border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                <p class="text-xs text-gray-400 font-medium">Belum ada data tersedia.</p>
            </div>
        @endforelse
    </div>
</div>