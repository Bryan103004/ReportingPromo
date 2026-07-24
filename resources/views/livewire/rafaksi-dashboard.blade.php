<x-list-container title="Rafaksi EXPIRING   | Total Data: {{ $data->total() }}">
    <!-- Looping Data dari Controller / Livewire -->
    @forelse($data as $row)
        <a href="{{ route('rafaksi.renew.index', ['id' => $row->id]) }}" class="block">
            <x-list-item 
                title="{{ $row->supplier_name }}" 
                subtitle="{{ $row->nama_toko }} • {{ $row->nama_region }} • No: {{ $row->no_raf }} • Periode Akhir: {{ Carbon::parse($row->periode_akhir)->format('d/M/Y') }}" 
                badge="Rp {{ number_format($row->nominal, 0, ',', '.') }}" 
                theme="yellow" 
            />
        </a>
    @empty
        <div class="flex flex-col items-center justify-center py-10 text-gray-400">
            <svg class="w-10 h-10 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            <span class="text-sm font-medium">Tidak ada data Rafaksi bulan ini.</span>
        </div>
    @endforelse

    <!-- Mengisi Slot Footer untuk Pagination -->
    <x-slot name="footer">
        <!-- <div>{{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} DARI {{ $data->total() }} DATA</div> -->
        
        <!-- Gunakan link pagination sederhana agar mirip tombol "Prev | Next" di gambar -->
        <div class="flex gap-2 mx-auto">
            {{ $data->links() }}
        </div>
    </x-slot>

</x-list-container>