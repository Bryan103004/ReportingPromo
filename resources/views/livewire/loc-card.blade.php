<div wire:loading.class="opacity-50" class="transition-opacity h-full relative">

    <div class="absolute top-4 right-5 z-10">
        <select wire:change="$set('selectedYear', $event.target.value)" class="text-xs border-gray-200 text-gray-600 rounded-md py-1 pl-2 pr-6 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white cursor-pointer">
            @for ($i = 2025; $i <= 2050; $i++)
                <!-- Tambahkan logika ini agar tulisan tahun di dropdown tetap sesuai dengan yang dipilih -->
                <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>
    
    <x-stat-card wire:key="stat-loc-{{ $selectedYear }}" title="Total Loc" :items="$data" theme="green" route="loc.show_month"/>
    
</div>