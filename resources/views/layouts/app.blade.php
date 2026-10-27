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

            // Dipakai di mana pun kode JS lain butuh ngisi field tanggal (date-dmy)
            // secara terprogram (misal auto-isi dari hasil import Excel) -- gak boleh
            // langsung assign .value kayak input biasa, karena field aslinya udah
            // disembunyikan sama flatpickr (altInput:true) dan gak otomatis nge-sync
            // ke tampilan visible-nya kalau di-assign langsung. Pakai fp.setDate()
            // biar tampilan & nilai aslinya ikut ke-update bareng.
            window.setDateFieldValue = function (id, value) {
                var el = document.getElementById(id);
                if (!el) return;
                if (el._flatpickr) {
                    el._flatpickr.setDate(value, true);
                } else {
                    el.value = value;
                }
            };
        </script>

        <!-- @livewireStyle -->
    </head>
    <body class="font-sans antialiased">
        {{-- Floating Toast Container --}}
        <div id="toast-container" style="position: fixed; top: 80px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 12px; pointer-events: none; max-width: 420px; width: calc(100% - 48px);"></div>
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
                    {{-- Alert Validation Errors ($errors->any()) --}}
                    @if($errors->any())
                        <div class="py-3 mb-6 rounded-lg bg-red-50 p-4 border border-red-200 flex items-start shadow-sm">
                            <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="flex-1 text-sm text-red-800">
                                <span class="font-bold">Gagal menyimpan dokumen. Periksa kesalahan berikut:</span>
                                <ul class="mt-1.5 list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    {{-- Alert Sukses (Jika ada redirect dengan pesan 'success') --}}
                    @if(session('success'))
                        <div class="py-3 mb-6 rounded-lg bg-green-50 p-4 border border-green-200 flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Alert Error (Jika ada redirect dengan pesan 'error') --}}
                    @if(session('error'))
                        <div class="py-3 mb-6 rounded-lg bg-red-50 p-4 border border-red-200 flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
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
        <x-invoice-log-modal />
        <x-goodwill-payment-modal />
        @livewireScripts
    
        <script>
            window.showToast = function(message, type = 'error', duration = 5000) {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.style.cssText = `
                    pointer-events: auto;
                    transform: translateY(-8px);
                    opacity: 0;
                    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    padding: 14px 16px;
                    border-radius: 12px;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                    border-width: 1px;
                    border-style: solid;
                    font-size: 14px;
                    line-height: 1.45;
                    width: 100%;
                    box-sizing: border-box;
                `;

                let bg = '#ffffff';
                let borderColor = '#e5e7eb';
                let textColor = '#1f2937';
                let icon = 'ℹ️';

                if (type === 'error') {
                    bg = '#fef2f2';
                    borderColor = '#fca5a5';
                    textColor = '#991b1b';
                    icon = '❌';
                } else if (type === 'success') {
                    bg = '#ecfdf5';
                    borderColor = '#6ee7b7';
                    textColor = '#065f46';
                    icon = '✅';
                } else if (type === 'warning') {
                    bg = '#fffbeb';
                    borderColor = '#fcd34d';
                    textColor = '#92400e';
                    icon = '⚠️';
                }

                toast.style.backgroundColor = bg;
                toast.style.borderColor = borderColor;
                toast.style.color = textColor;

                toast.innerHTML = `
                    <div style="font-size: 16px; line-height: 1; user-select: none; flex-shrink: 0; margin-top: 1px;">${icon}</div>
                    <div style="flex: 1; font-weight: 600; word-break: break-word;">${message}</div>
                    <button type="button" style="background: none; border: none; padding: 0; margin: 0 0 0 8px; cursor: pointer; color: ${textColor}; opacity: 0.6; font-size: 18px; line-height: 1; font-weight: bold; flex-shrink: 0;" title="Tutup">&times;</button>
                `;

                const closeBtn = toast.querySelector('button');
                closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
                closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.6');
                closeBtn.addEventListener('click', () => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';
                    setTimeout(() => toast.remove(), 300);
                });

                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0)';
                });

                if (duration > 0) {
                    setTimeout(() => {
                        if (toast.parentElement) {
                            toast.style.opacity = '0';
                            toast.style.transform = 'translateY(-8px)';
                            setTimeout(() => toast.remove(), 300);
                        }
                    }, duration);
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    window.showToast(@json(session('success')), 'success');
                @endif
                @if(session('error'))
                    window.showToast(@json(session('error')), 'error', 7000);
                @endif
                @if($errors->any())
                    @foreach($errors->all() as $err)
                        window.showToast(@json($err), 'error', 7000);
                    @endforeach
                @endif
            });
        </script>

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
