@props(['title'])

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-gray-500 font-bold tracking-wider text-sm">{{ strtoupper($title) }}</h3>
        
        <!-- Per-page selector, Livewire-native (this component is only ever used inside a Livewire dashboard) -->
        <div class="flex items-center gap-2">
            <div class="flex flex-wrap items-center space-x-2 bg-gray-50/70 border border-gray-100 rounded-xl px-3 py-2 text-sm text-gray-600 w-fit shadow-xs">
                <span class="font-semibold text-gray-700 tracking-wide text-xs mr-1">Tampilkan</span>
                <div class="relative">
                    <select
                        wire:model.live="perPage"
                        class="appearance-none text-center mr-1 bg-white rounded-lg border border-gray-200 py-1 pl-2.5 pr-7 text-xs font-bold text-gray-700 hover:border-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-xs cursor-pointer transition-all"
                    >
                        @foreach ([10, 25, 50, 100, 300, -1] as $option)
                            <option value="{{ $option }}">{{ $option == -1 ? 'Semua' : $option }}</option>
                        @endforeach
                    </select>
                </div>
                <span class="text-gray-700 text-xs font-medium">data</span>
            </div>
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