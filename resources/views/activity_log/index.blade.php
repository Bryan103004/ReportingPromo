@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Form Paginate & Search Bar Atas --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 shadow-sm p-3 bg-white rounded" style="gap: 15px;">
        <form method="GET" action="{{ route('activity-log.index') }}" id="paginateForm" class="form-inline">
            @foreach(request()->except('number') as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <label for="number" class="mr-2 small font-weight-bold text-muted">Tampilkan:</label>
            <select id="number" name="number" class="form-control form-control-sm font-weight-bold text-secondary" style="border-radius: 6px;">
                @foreach($number_paginate as $num)
                    <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                        {{ $num == 999999999 ? 'All' : $num }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- ===== SEARCH BAR ===== --}}
        {{-- Tetap mempertahankan Blade Component bawaan kamu --}}
        <div class="flex-grow-1 mx-md-3" style="max-width: 400px;">
            <x-search-bar 
                placeholder="Masukkan user atau aksi..." 
                tableId="logsTable" 
            />
        </div>
    </div>

    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 pb-2 border-bottom">
        
        <form action="{{ route('activity-log.destroy') }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger text-nowrap"
                    onclick="return confirm('Yakin clear semua log?')">Clear Log</button>
        </form>
        <div>
            <h2 class="font-weight-bold text-dark mb-1" style="letter-spacing: 0.5px;">Activity Log</h2>
            <p class="text-muted small mb-0">Pantau seluruh aktivitas perubahan data di dalam sistem.</p>
        </div>
        <div>
            <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem; border-radius: 20px;">
                Total: {{ $logs->total() }} Log
            </span>
        </div>
    </div>

    {{-- Log Table Card --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover table-striped text-left mb-0" id="logsTable" style="text-transform: uppercase; font-size: 0.85rem;">
                <thead class="bg-light text-secondary font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Aksi</th>
                        <th class="py-3 px-4">Modul / Subjek</th>
                        <th class="py-3 px-4">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="text-dark">
                    @forelse($logs as $log)
                        <tr>
                            {{-- Waktu --}}
                            <td class="align-middle px-4 text-nowrap">
                                <div class="font-weight-bold text-dark">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                                <small class="text-muted font-mono" style="font-size: 11px;">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</small>
                            </td>

                            {{-- User --}}
                            <td class="align-middle px-4">
                                <div class="font-weight-bold text-dark">{{ $log->causer ? $log->causer->name : 'System' }}</div>
                                <small class="text-muted" style="font-size: 10px; letter-spacing: -0.3px;">ID: {{ $log->causer_id ?? '-' }}</small>
                            </td>

                            {{-- Aksi --}}
                            <td class="align-middle px-4">
                                @php
                                    $desc = strtolower($log->description);
                                    $badgeColor = 'badge-secondary'; // Fallback

                                    if ($desc === 'created') {
                                        $badgeColor = 'badge-success';
                                    } elseif ($desc === 'updated') {
                                        $badgeColor = 'badge-warning text-dark';
                                    } elseif ($desc === 'deleted') {
                                        $badgeColor = 'badge-danger';
                                    }
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-1.5 font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.5px; min-width: 85px; text-align: center; border-radius: 4px;">
                                    {{ $log->description }}
                                </span>
                            </td>

                            {{-- Modul / Subjek --}}
                            <td class="align-middle px-4">
                                <div class="text-dark font-weight-bold">
                                    {{ class_basename($log->subject_type) }}
                                </div>
                                <small class="text-primary font-weight-bold" style="font-size: 11px;">
                                    ID: {{ $log->subject_id }}
                                </small>
                            </td>

                            {{-- Properties (JSON) --}}
                            <td class="align-middle px-4">
                                <button type="button" 
                                    onclick="toggleProperties('prop-{{ $log->id }}')"
                                    class="btn btn-link btn-sm text-primary p-0 font-weight-bold d-inline-flex align-items-center" style="gap: 5px; text-decoration: none;">
                                    <i class="fas fa-eye small"></i> Lihat Data
                                </button>
                                <div id="prop-{{ $log->id }}" class="d-none mt-2">
                                    <div class="rounded p-3 custom-scrollbar" style="max-height: 160px; overflow-y: auto; font-size: 11px; font-family: monospace; background-color: #212529;">
                                        <pre class="mb-0 text-light" style="white-space: pre-wrap; word-break: break-all;"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted py-3">
                                    <i class="fas fa-history fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                    <p class="mb-0 font-weight-bold">Tidak ada aktivitas yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination (Bawaan Bootstrap 4) --}}
    <div class="d-flex justify-content-center my-4">
        {{ $logs->links() }}
    </div>

    {{-- Form Paginate Bawah --}}
    <form method="GET" action="{{ route('activity-log.index') }}" id="paginateFormBottom" class="form-inline mb-4">
        @foreach(request()->except('number') as $key => $val)
            @if(is_array($val))
                @foreach($val as $v)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        <label for="number-bottom" class="mr-2 small font-weight-bold text-muted">Tampilkan:</label>
        <select id="number-bottom" name="number" class="form-control form-control-sm text-secondary font-weight-bold" style="border-radius: 6px;" onchange="document.getElementById('paginateFormBottom').submit()">
            @foreach($number_paginate as $num)
                <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                    {{ $num == 999999999 ? 'All' : $num }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<script>
    // JS Logic Toggle Properties (Menggantikan utility class toggle Tailwind)
    function toggleProperties(id) {
        const el = document.getElementById(id);
        if (el.classList.contains('d-none')) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }

    // Sinkronkan select jumlah data per halaman atas & bawah
    const selectTop = document.getElementById('number');
    const selectBottom = document.getElementById('number-bottom');
    const formTop = document.getElementById('paginateForm');
    const formBottom = document.getElementById('paginateFormBottom');

    if (selectTop && selectBottom) {
        selectTop.addEventListener('change', function() {
            selectBottom.value = selectTop.value;
            formTop.submit();
        });
        selectBottom.addEventListener('change', function(e) {
            selectTop.value = selectBottom.value;
            setTimeout(function() {
                formBottom.submit();
            }, 10);
        });
    }

    const perPageSelect = document.querySelector('select[name="number"]');

    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('number', this.value);
            url.searchParams.set('page', '1'); 
            window.location.href = url.toString();
        });
    }
</script>

<style>
    /* Styling scrollbar untuk JSON preview agar lebih estetik */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #212529;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #6c757d;
        border-radius: 4px;
    }
    .text-nowrap {
        white-space: nowrap;
    }
    .py-1\.5 {
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
    }
</style>
@endsection