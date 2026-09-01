# Upgrading & Performance Notes

Date: 2026-09-01

## Konteks

Tabel `rafaksis` sudah punya 4.546 baris dan terus bertambah (import CSV berkala + input manual). Ditinjau apakah query-query yang ada masih akan sanggup begitu datanya jauh lebih besar (puluhan/ratusan ribu baris).

## Sudah diperbaiki

### 1. `whereYear()`/`whereMonth()` pada kolom `periode_bulan` — index tidak kepakai sama sekali

**Masalah**: Semua `showMonth()`, `exportCsv()` (mode detail), `printPdf()` (mode detail), dan keempat `DetailXxxReport` export class (Rafaksi/Jsm/Loc/Pwp) memfilter periode pakai:
```php
->whereYear('periode_bulan', $year)->whereMonth('periode_bulan', $month)
```
Kolom `periode_bulan` sebenarnya sudah punya index (`*_periode_bulan_index`), tapi membungkusnya dengan fungsi `YEAR()`/`MONTH()` di `WHERE` membuat index itu **sama sekali tidak terlihat oleh MySQL** — dibuktikan lewat `EXPLAIN`:
```
WHERE YEAR(periode_bulan)=2026 AND MONTH(periode_bulan)=1
  → type: ALL, possible_keys: (kosong)
```
Di ~4.500 baris masih kerasa cepat, tapi ini bakal jadi full table scan tiap buka halaman rekap begitu data naik ke puluhan/ratusan ribu baris.

**Perbaikan**: diganti jadi filter range tanggal (awal bulan s/d awal bulan berikutnya), yang sargable (bisa pakai index):
```php
$periodeStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
$periodeEnd = (clone $periodeStart)->addMonth();

->where('periode_bulan', '>=', $periodeStart)
->where('periode_bulan', '<', $periodeEnd)
```
Setelah diganti, `EXPLAIN` menunjukkan `possible_keys` sekarang mengenali index-nya (`rafaksis_periode_bulan_index`) — MySQL bisa memilih pakai index itu kapan pun itu lebih murah (tergantung selektivitas data), yang sebelumnya mustahil dilakukan sama sekali.

**Dikerjakan di 16 titik**, sudah diverifikasi hasil query-nya identik dengan versi lama (jumlah baris sama) dan semua halaman/tetap render normal:
- `RafaksiController`, `JsmController`, `LocController`, `PwpController`: `showMonth()`, `exportCsv()` mode detail, `printPdf()` mode detail (3 titik × 4 controller)
- `DetailRafaksiReport`, `DetailJsmReport`, `DetailLocReport`, `DetailPwpReport`: mode detail export (1 titik × 4 class)

**Catatan**: `groupByRaw('YEAR(periode_bulan), MONTH(periode_bulan)')` di method `index()` (buat rekap per-bulan) **belum** disentuh — itu memang butuh ekstraksi tahun/bulan untuk grouping, bukan filter row, jadi bukan kasus yang sama dan risikonya beda kalau diubah.

## Belum dikerjakan (backlog, ditemukan waktu review)

- **`getDaftarTokoFormattedAttribute()` (accessor di model Rafaksi/Jsm/Loc/Pwp) — N+1 query.** Tiap dipanggil (dipakai di `exportCsv`, export detail, dan beberapa tampilan list), dia jalanin `Toko::where('region_id', ...)->count()` sendiri per baris. Export 500 baris = 500 query tambahan.
- **Laporan "rekap matrix"** (`index()`, export XLS mode rekap/semua, `printPdf()` mode rekap) — untuk tiap kategori, jalanin query `CROSS JOIN` + beberapa `LEFT JOIN` terpisah dengan satu `SUM(CASE WHEN...)` per toko (bisa 20+ kolom). Tidak ada caching — dihitung ulang dari nol tiap request. Belum jadi masalah di skala data sekarang, tapi ini query paling berat di aplikasi dan pantas dipantau kalau data terus tumbuh.
- Tidak ada caching layer sama sekali (Redis/query cache) di aplikasi ini — semua dashboard/laporan selalu live-query ke MySQL.
