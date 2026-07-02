@extends('layouts.app')

@section('content')
    <div class="bg-gray-100 p-8 font-sans mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Sales Reporting Panel</h1>
                    <p class="text-gray-500 mt-1">Upload file TXT export laporan penjualan.</p>
                </div>
                <button id="buttonExportForm" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </button>
            </div>

            <div id="exportForm" class="hidden bg-white p-6 rounded-xl mb-6 shadow-md border {{ $errors->any() ? 'border-red-400 bg-red-50/30' : 'border-gray-100' }} transition-all duration-300">
                <form action="{{ route('reports.export') }}" method="GET" class="space-y-4">
                    
                    <!-- Judul Form Kecil untuk Memperjelas Fungsi -->
                    <div class="border-b border-gray-100 pb-3 mb-2">
                        <h3 class="text-base font-semibold text-gray-800">Ekspor Laporan</h3>
                        <p class="text-xs text-gray-500">Filter data berdasarkan waktu dan outlet sebelum mengunduh.</p>
                    </div>

                    <!-- Grid Tata Letak Input & Tombol -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                        
                        <!-- Filter Tahun -->
                        <div class="flex flex-col gap-1.5">
                            <label for="year" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Tahun</label>
                            <div class="relative">
                                <select name="year" id="year" class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 pl-3 pr-10 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer appearance-none">
                                    @for ($year = 2026; $year <= 2050; $year++)
                                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                                <!-- Ikon Panah Kustom -->
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="fill-current h-4 w-4" xmlns="http://w3.org" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Bulan -->
                        <div class="flex flex-col gap-1.5">
                            <label for="month" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Bulan</label>
                            <div class="relative">
                                <select name="month" id="month" class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 pl-3 pr-10 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer appearance-none">
                                    @foreach([
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ] as $value => $label)
                                        <svg></svg>
                                        <option value="{{ $value }}" {{ $value == date('m') ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <!-- Ikon Panah Kustom -->
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="fill-current h-4 w-4" xmlns="http://w3.org" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Input Kode Outlet -->
                        <div class="flex flex-col gap-1.5">
                            <label for="kode_outlet" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Kode Outlet</label>
                            <input type="text" name="kode_outlet" id="kode_outlet" placeholder="Contoh: OTL01" class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2.5 px-3.5 text-sm text-gray-700 shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>

                        <!-- Tombol Submit Ekspor -->
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all duration-150 flex items-center justify-center gap-2 group">
                                <!-- Ikon Unduh/Ekspor -->
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span>Unduh Laporan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>


            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                    <p class="font-bold">Error!</p>
                    <ul class="list-disc ml-5 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg px-8 pt-6 pb-8 mb-8 border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Upload Data Baru</h2>
                <form action="{{ route('reports.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                    @csrf
                    <div class="w-1/2">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="report_file">
                            File Laporan (Format: .TXT)
                        </label>
                        <input type="file" name="report_file[]" id="report_file" accept=".txt" required multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded focus:outline-none">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-150">
                            Upload & Proses
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg px-8 pt-6 pb-8 border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Preview Data Tersimpan</h2>
                <div class="overflow-x-auto mt-4">
                <table id="salesTable" class="display w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th>Kode Outlet</th>
                            <th>Nama Outlet</th>
                            <th>Periode</th>
                            <th>Mgg 4 (Lalu)</th>
                            <th>Mgg 3</th>
                            <th>Mgg 2</th>
                            <th>Mgg 1 (Skrg)</th>
                            <th>D-Day (Skrg)</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let table = $('#salesTable').DataTable({
                "language": {
                    "emptyTable": "Belum ada data. Silakan upload file TXT terlebih dahulu."
                },
                "pageLength": 10,
                "ajax": {
                    "url": "{{ route('api.reports.matrix') }}",
                    "type": "GET",
                    "dataSrc": "data" // Mengambil dari response()->json(['data' => $reports])
                },
                "columns": [
                    { data: 'outlet_code' },
                    { data: 'outlet_name' },
                    { data: 'periode' },
                    { 
                        data: 'week_4_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    },
                    { 
                        data: 'week_3_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    },
                    { 
                        data: 'week_2_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    },
                    { 
                        data: 'week_1_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    },
                    { 
                        data: 'd_day_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    },
                    { 
                        data: 'total_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    }
                ]
            });
        });

        var buttonExportForm = document.getElementById('buttonExportForm');
        var exportForm = document.getElementById('exportForm');

        buttonExportForm.addEventListener('click', function(){
            exportForm.classList.toggle('hidden');
        });
    </script>
@endsection