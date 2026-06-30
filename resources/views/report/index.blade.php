<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reporting Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <style>
        /* Sedikit penyesuaian agar DataTables menyatu dengan Tailwind */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 2rem 0.25rem 0.5rem;
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-100 p-8 font-sans">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Sales Reporting Panel</h1>
                <p class="text-gray-500 mt-1">Upload file TXT export laporan penjualan.</p>
            </div>
            <a href="{{ route('reports.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

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
                        <th>Mgg 4 (Lalu)</th>
                        <th>Mgg 3</th>
                        <th>Mgg 2</th>
                        <th>Mgg 1 (Skrg)</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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
                    { 
                        data: 'week_4_sales', 
                        // Menggunakan titik untuk ribuan, koma untuk desimal
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
                        data: 'total_sales', 
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') 
                    }
                ]
            });
        });
    </script>
</body>
</html>