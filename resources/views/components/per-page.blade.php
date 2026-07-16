@props([
    'options' => [10, 25, 50, 100, 300, 999999999],
    'paramName' => 'number',
    'default' => 10
])

<div class="flex items-center space-x-3 bg-gray-50/70 border border-gray-100 rounded-xl p-3 text-sm text-gray-600 my-5 w-fit shadow-sm">
    <span class="font-semibold text-gray-700 tracking-wide">Tampilkan</span>
    
    <div class="relative">
        <select 
            onchange="handlePerPageChange(this.value)"
            class="appearance-none bg-white rounded-lg border border-gray-200 py-1.5 pl-3 pr-8 text-sm font-medium text-gray-700 hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-xs cursor-pointer transition-all"
        >
            @foreach ($options as $option)
                <option value="{{ $option }}" {{ request($paramName, $default) == $option ? 'selected' : '' }}>
                    {{ $option == 999999999 ? 'Semua' : $option }}
                </option>
            @endforeach
        </select>
        <!-- Ikon Panah Kustom (SVG) -->
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    <span class="text-gray-700">data</span>
</div>


<script>
    if (typeof handlePerPageChange !== 'function') {
        function handlePerPageChange(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('{{ $paramName }}', value);
            
            // Set kembali ke page 1 tiap kali jumlah baris diubah agar tidak out-of-bounds
            url.searchParams.set('page', 1); 
            
            window.location.href = url.href;
        }
    }
</script>