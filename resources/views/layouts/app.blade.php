<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <link rel="stylesheet" href="{{ asset('build/assets/app-CBXajZs2.css') }}">
        <script src="{{ asset('build/assets/app-DXU0SP3V.js') }}" type="module"></script>

        <!-- <script src="https://cdn.tailwindcss.com"></script> -->
        
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        
        <style>
            .paginateForm{
                background-color: #f7fafc ;
            }

            body { 
                background-image: url('{{ asset('images/background.jpg') }}'); 
                background-size: cover; /* Agar gambar memenuhi layar */
                background-position: center center; /* Posisi gambar di tengah */
                background-repeat: no-repeat; /* Agar gambar tidak berulang */
                background-attachment: fixed; /* Agar gambar tetap di tempat saat halaman di-scroll */
                background-color: #f8f9fa; 
            }
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

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

        <!-- Flatpickr: biar tampilan input tanggal SELALU dd/mm/yyyy di semua device,
             gak ikut locale OS/browser user (beda dari <input type="date"> bawaan yang
             tampilannya ikut-ikutan settingan device). Dipasang ke input manapun yang
             punya class "date-dmy" -- nilai yang beneran dikirim ke server tetap format
             Y-m-d (gak berubah), cuma tampilan di layar yang jadi dd/mm/yyyy.
             Disimpan lokal di public/vendor/flatpickr (bukan CDN) biar tetap jalan
             walau device/jaringan gak ada akses internet ke CDN luar. -->
        <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.flatpickr) {
                    var fpInstances = flatpickr('.date-dmy', {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd/m/Y',
                        allowInput: true,
                    });

                    // flatpickr(selector, ...) balikin 1 instance kalau selector cuma
                    // kena 1 elemen, atau array of instance kalau kena banyak -- disamain
                    // jadi array biar bisa di-loop konsisten.
                    (Array.isArray(fpInstances) ? fpInstances : [fpInstances]).forEach(function (fp) {
                        if (!fp || !fp.altInput) return;

                        // Biar pas ngetik angka (30102004) otomatis ke-sisipin "/" jadi
                        // dd/mm/yyyy (30/10/2004) -- allowInput:true doang gak bikin ini
                        // otomatis, jadi perlu format manual tiap ada input baru.
                        fp.altInput.addEventListener('input', function (e) {
                            var digits = e.target.value.replace(/\D/g, '').slice(0, 8);
                            var formatted = digits;
                            if (digits.length > 4) {
                                formatted = digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4);
                            } else if (digits.length > 2) {
                                formatted = digits.slice(0, 2) + '/' + digits.slice(2);
                            }
                            e.target.value = formatted;
                        });
                    });
                }
            });
        </script>

        <!-- @livewireStyle -->
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col bg-transparent">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                @hasSection('content')
                    {{-- Alert Sukses (Jika ada redirect dengan pesan 'success') --}}
                    @if(session('success'))
                        <div class="py-3 mb-6 rounded-lg bg-green-50 p-4 border border-green-200 flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif
                    @yield('content')
                @elseif (isset($slot))
                    {{ $slot }}
                @endif
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl justify-center items-center px-4 py-3 text-center text-xs text-gray-500 sm:px-6 lg:px-8 flex">
                    © {{ now()->year }} {{ config('app.name', 'Tanda Terima') }}
                    <span class="ml-2 text-white">Made by Bryan</span>
                </div>
            </footer>
        </div>
        @livewireScripts
    </body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Cek apakah elemen dengan id 'choices-supplier' ada di halaman ini
            const supplierElement = document.getElementById('choices-supplier');
            
            if (supplierElement) {
                const supplierSelect = new Choices(supplierElement, {
                    searchEnabled: true,
                    itemSelectText: 'Klik untuk pilih',
                    noResultsText: 'Supplier tidak ditemukan',
                    shouldSort: false,
                });

                // Simpan instance ke window agar bisa diakses dari file lain (seperti create.blade.php)
                window.supplierChoices = supplierSelect;
            }
        });
    </script>
</html>
