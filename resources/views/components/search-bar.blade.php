{{-- Kita mendefinisikan properti apa saja yang bisa diubah-ubah saat komponen dipanggil --}}
@props([
    'placeholder' => 'Cari data...', // Teks default jika tidak diisi
    'tableId' => '' // ID tabel spesifik (opsional)
])

<div class="search-component-container">
    <input 
        type="text" 
        class="form-control my-4 dynamic-search-bar w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"

        placeholder="{{ $placeholder }}" 
        data-target="{{ $tableId }}"
    >
</div>

{{-- Gunakan @once agar script ini hanya dirender 1 kali oleh browser walaupun komponen dipanggil berkali-kali --}}
@once
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchBars = document.querySelectorAll('.dynamic-search-bar');
            
            searchBars.forEach(searchBar => {
                searchBar.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const targetId = this.getAttribute('data-target');
                    
                    // Jika tableId diisi, cari di tabel tersebut. Jika tidak, cari di semua tabel di halaman itu.
                    const rowsSelector = targetId 
                        ? `#${targetId} tbody tr` 
                        : 'table tbody tr';
                        
                    const rows = document.querySelectorAll(rowsSelector);
                    
                    rows.forEach(row => {
                        // Upgrade: Mencari di SELURUH teks dalam baris (Nama, Email, Posisi, dll), bukan cuma kolom 1
                        const rowText = row.textContent.toLowerCase();
                        row.style.display = rowText.includes(query) ? '' : 'none';
                    });
                });
            });
        });
    </script>
@endonce