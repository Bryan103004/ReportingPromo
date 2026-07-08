@props([
    'options' => [10, 25, 50, 100, 300, 999999999],
    'paramName' => 'number',
    'default' => 10
])

<div class="flex items-center space-x-2 text-sm text-gray-700 my-4">
    <span style="margin-right: 0.5em;">Tampilkan</span>
    <select 
        onchange="handlePerPageChange(this.value)"
        class="rounded border-gray-300 py-1 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
    >
        @foreach ($options as $option)
            <option value="{{ $option }}" {{ request($paramName, $default) == $option ? 'selected' : '' }}>
                {{ $option == 999999999 ? 'Semua' : $option }}
            </option>
        @endforeach
    </select>
    <span style="margin-left: 0.5em;">data</span>
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