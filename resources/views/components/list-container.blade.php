@props(['title'])

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-gray-500 font-bold tracking-wider text-sm">{{ strtoupper($title) }}</h3>
        
        <!-- Filter Tampil (Opsional jika ingin disambung ke Livewire) -->
        <div class="flex items-center gap-2">
            <!-- <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wide">Tampil:</span> -->
            <x-per-page/>
        </div>
    </div>

    <!-- Tempat Data (Slot) -->
    <div class="space-y-4 flex-1">
        {{ $slot }}
    </div>

    <!-- Footer / Pagination -->
    @if(isset($footer))
    <div class="mt-8 flex justify-between items-center text-xs text-gray-500 font-medium">
        {{ $footer }}
    </div>
    @endif
</div>