@props([
    'options' => [10, 25, 50, 100, 300, -1], // -1 lebih aman sebagai representasi "Semua"
    'paramName' => 'number',
    'default' => 10
])

<div class="flex flex-wrap items-center space-x-2 bg-gray-50/70 border border-gray-100 rounded-xl px-3 py-2 text-sm text-gray-600 my-4 w-fit shadow-xs">
    <span class="font-semibold text-gray-700 tracking-wide text-xs mr-1">Tampilkan</span>
    
    <div class="relative">
        <select 
            onchange="handlePerPageChange_{{ $paramName }}(this.value)"
            class="appearance-none text-center mr-1 bg-white rounded-lg border border-gray-200 py-1 pl-2.5 pr-7 text-xs font-bold text-gray-700 hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-xs cursor-pointer transition-all"
        >
            @foreach ($options as $option)
                <option value="{{ $option }}" {{ request($paramName, $default) == $option ? 'selected' : '' }}>
                    {{ $option == -1 ? 'Semua' : $option }}
                </option>
            @endforeach
        </select>
        
        <!-- Ikon Panah Kustom (SVG) -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-gray-500">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    <span class="text-gray-700 text-xs font-medium">data</span>
</div>

<script>
    if (typeof window.handlePerPageChange_{{ $paramName }} !== 'function') {
        window.handlePerPageChange_{{ $paramName }} = function(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('{{ $paramName }}', value);
            url.searchParams.set('page', 1); // Reset ke halaman 1
            window.location.href = url.href;
        }
    }
</script>