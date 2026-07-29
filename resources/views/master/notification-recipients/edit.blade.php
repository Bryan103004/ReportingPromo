@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tight">Edit Penerima</h1>
            <p class="mt-1 text-sm text-gray-500 italic uppercase">Penerima: <span class="text-indigo-600 font-bold">{{ $notificationRecipient->name }}</span></p>
        </div>
        <a href="{{ route('notification-recipients.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 uppercase italic transition-colors">Batal</a>
    </div>

    <form action="{{ route('notification-recipients.update', $notificationRecipient->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $notificationRecipient->name) }}" required class="w-full rounded-xl border-gray-200 bg-slate-50 text-sm font-bold focus:ring-indigo-500 shadow-inner p-3">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-2">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $notificationRecipient->email) }}" required class="w-full rounded-xl border-gray-200 bg-slate-50 text-sm font-mono focus:ring-indigo-500 shadow-inner p-3">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-4">Langganan Modul <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        @foreach($modules as $key => $label)
                            @php
                                $hasModule = is_array(old('modules', $notificationRecipient->modules)) && in_array($key, old('modules', $notificationRecipient->modules));
                            @endphp
                            <label class="relative flex cursor-pointer rounded-xl border border-gray-200 bg-white p-4 shadow-sm focus:outline-none hover:bg-slate-50 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/30 group">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" onchange="toggleFilterSection('{{ $key }}', this.checked)" class="module-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                    {{ $hasModule ? 'checked' : '' }}>
                                <span class="ml-3">
                                    <span class="block text-sm font-black text-gray-900 group-has-[:checked]:text-indigo-700 uppercase">{{ $label }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 space-y-6">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-600">Pengaturan Filter Khusus <span class="text-[10px] font-normal text-slate-400 lowercase italic">(kosongkan jika ingin menerima semua data)</span></h3>
                    
                    @php
                        $savedFilters = $notificationRecipient->filters ?? [];
                    @endphp

                    <div id="filter-section-rafaksi" class="hidden p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-700 uppercase">Filter Modul Rafaksi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Berdasarkan Toko</label>
                                <select name="filters[rafaksi][toko_id][]" multiple class="tom-select text-sm">
                                    @foreach($tokos as $t) 
                                        <option value="{{ $t->id }}" 
                                            {{ isset($savedFilters['rafaksi']['toko_id']) && in_array($t->id, $savedFilters['rafaksi']['toko_id']) ? 'selected' : '' }}>
                                            {{ $t->nama_toko }}
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="filter-section-loc" class="hidden p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-700 uppercase">Filter Modul Loc</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Berdasarkan Toko</label>
                                <select name="filters[loc][toko_id][]" multiple class="tom-select text-sm">
                                    @foreach($tokos as $t) 
                                        <option value="{{ $t->id }}" 
                                            {{ isset($savedFilters['loc']['toko_id']) && in_array($t->id, $savedFilters['loc']['toko_id']) ? 'selected' : '' }}>
                                            {{ $t->nama_toko }}
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="filter-section-jsm" class="hidden p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-700 uppercase">Filter Modul Jsm</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Berdasarkan Toko</label>
                                <select name="filters[jsm][toko_id][]" multiple class="tom-select text-sm">
                                    @foreach($tokos as $t) 
                                        <option value="{{ $t->id }}" 
                                            {{ isset($savedFilters['jsm']['toko_id']) && in_array($t->id, $savedFilters['jsm']['toko_id']) ? 'selected' : '' }}>
                                            {{ $t->nama_toko }}
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <div class="flex h-6 items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notificationRecipient->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="text-sm leading-6">
                        <label class="font-bold text-gray-900 uppercase text-xs tracking-tight">Status Aktif</label>
                        <p class="text-gray-500 text-[10px] italic leading-tight">Jika dinonaktifkan, email reminder tidak akan terkirim.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border-t border-gray-100 px-8 py-5 flex justify-end">
                <button type="submit" class="rounded-xl bg-indigo-600 px-10 py-3 text-sm font-black text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all uppercase tracking-widest active:scale-95">
                    Update Penerima
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function updateFilterVisibility() {
        const check = (val) => {
            let el = document.querySelector(`.module-checkbox[value="${val}"]`);
            return el ? el.checked : false;
        };

        const toggleEl = (id, show) => {
            let el = document.getElementById('filter-section-' + id);
            if (el) {
                if (show) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                    el.querySelectorAll('select').forEach(s => {
                        if(s.tomselect) s.tomselect.clear();
                    });
                }
            }
        };

        // Panggil terpisah-pisah untuk semua modul
        toggleEl('rafaksi', check('rafaksi'));
        toggleEl('loc', check('loc'));
        toggleEl('jsm', check('jsm'));
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.tom-select').forEach((el) => {
            new TomSelect(el, {
                plugins: ['remove_button'],
                create: false,
                maxItems: null,
                placeholder: '-- Pilih Spesifikasi Data (Bisa Banyak) --'
            });
        });

        document.querySelectorAll('.module-checkbox').forEach((cb) => {
            cb.addEventListener('change', updateFilterVisibility);
        });

        // Panggil saat page diload
        updateFilterVisibility();
    });
</script>
@endsection