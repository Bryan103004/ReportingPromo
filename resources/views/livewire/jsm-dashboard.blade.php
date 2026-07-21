<div class="bg-white rounded-lg shadow p-4 mb-6 overflow-x-auto">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Tabel Kontrak Jsm</h3>
    <h5>Total Data: {{ $data->total() }}</h5>
    <x-per-page/>
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left">Periode</th>
                <th class="px-4 py-2 text-left">Region</th>
                <th class="px-4 py-2 text-left">Toko</th>
                <th class="px-4 py-2 text-left">No RAF</th>
                <th class="px-4 py-2 text-left">Supplier</th>
                <th class="px-4 py-2 text-right">Nominal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($data as $row)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $row->Periode_Pengerjaan }}</td>
                <td class="px-4 py-2">{{ $row->nama_region }}</td>
                <td class="px-4 py-2">{{ $row->nama_toko }}</td>
                <td class="px-4 py-2 font-mono text-xs">{{ $row->no_raf }}</td>
                <td class="px-4 py-2">{{ $row->supplier_name }}</td>
                <td class="px-4 py-2 text-right font-medium text-green-600">
                    {{ number_format($row->nominal, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada data JSM bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="my-2">
        {{ $data->links() }}
    </div>
</div>
