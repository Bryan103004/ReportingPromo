# Copilot Progress & Implementation Notes

Date: 2026-08-13

## Summary
- Tujuan: implementasi filter toko di dashboard, multi-store/multi-outlet untuk Rafaksi/Jsm/Pwp, menyatukan Rafaksi/Jsm/Pwp ke satu menu dengan auto `no_raf`, filter outlet berdasar `name_pt`, upload dokumen PDF untuk `Loc`, tanda-tangan user, dan pembatasan data/reminder berdasarkan toko yang di-assign ke user.

## Done (so far)
- Membuat rencana tugas terstruktur (todo list) dan mengonfirmasi persyaratan format `no_raf`.
- Menetapkan format `no_raf`: contoh `RAF/0860/04/2026` — sequence yang dipakai: `0860` (empat digit setelah slash pertama).
- Memastikan nama perusahaan untuk filter: `name_pt` == "PT. MITRA BELANJA ANDA".
- Menentukan file rules: dokumen `Loc` hanya PDF; tanda-tangan disimpan dan ditampilkan ketika user melakukan aksi approve (hidden by default).

## Current TODO (short)
- Add store filter on dashboard
- Support multi-store & multi-outlet for Rafaksi/Jsm/Pwp (multi-select)
- Unify Rafaksi/Jsm/Pwp into single menu with category select and auto `no_raf`
- Allow outlet filtering by `name_pt` == "PT. MITRA BELANJA ANDA"
- Add document upload/view for `Loc` (PDF only; approve toggles signature overlay)
- Add user signature upload/manage
- Restrict data and reminders to user-assigned stores
- Write tests and QA

## Implementation notes & recommended changes

- Database changes (migrations):
  - `users` table: add `signature_path` (nullable string) to store uploaded signature file path.
  - `locs` table: add `document_path` (nullable string), `document_original_name` (nullable string), `approved_by` (nullable user_id), `approved_at` (nullable timestamp).
  - `rafaksis` (or combined table): ensure `no_raf` (string) and add `raf_sequence` (unsigned integer) to store the 4-digit sequence for sorting/indexing.
  - `user_toko` pivot table: `user_id`, `toko_id` to assign stores to users.

- Models & relations:
  - `User`: add `signature()` accessor and `assignedTokos()` relation (belongsToMany `Toko`).
  - `Toko`: add helper `isMitraBelanjaAnda()` that checks `name_pt === 'PT. MITRA BELANJA ANDA'`.
  - `Loc`: add `document()` accessor and `approvedBy()` relation.

- Controllers & UI:
  - Create/merge controller to handle Rafaksi/Jsm/Pwp under one menu: show category select (Rafaksi/Jsm/Pwp) then show same form with category-specific fields; generate `no_raf` automatically using `raf_sequence` + format `RAF/{seq}/{MM}/{YYYY}`.
  - Create multi-select for regions/toko/outlet when creating Rafaksi entries.
  - Add UI checkbox/filter to show outlets where `name_pt` equals the requested string.
  - For `Loc` create/edit/view: add PDF upload field and preview (embed PDF). Add `Approve` action which sets `approved_by`/`approved_at` and unhides signature overlay.

- PDF / Signature overlay:
  - To stamp signature into PDF at approve-time use a PDF library (FPDI/FPDF or `setasign/fpdi`) or generate a new PDF with DomPDF/laravel-dompdf merging an image overlay. Recommend adding `setasign/fpdi` via Composer if exact PDF stamping is required.

- Storage & security:
  - Store uploaded PDFs under `storage/app/public/loc_documents` and run `php artisan storage:link`.
  - Validate uploads: `mimes:pdf|max:10240` (example 10MB limit).

- Query scoping / permissions:
  - Scope every query that lists `Toko` data and reminders by the current user's assigned tokos (via the pivot). Implement via a policy or repository layer, or add a global scope on the models used for user-facing lists.

## Example migration snippets

```php
// add signature_path to users
Schema::table('users', function (Blueprint $table) {
    $table->string('signature_path')->nullable();
});

// add document_path to locs
Schema::table('locs', function (Blueprint $table) {
    $table->string('document_path')->nullable();
    $table->string('document_original_name')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
});
```

## Commands & packages

- Run after migrations: `php artisan storage:link`
- Composer packages (suggested): `composer require setasign/fpdi` (for PDF stamping) or `barryvdh/laravel-dompdf` for PDF generation.

## Next steps I can start now
1. Scan repository for relevant models, controllers, and views to prepare concrete patches.
2. Add migrations for `users.signature_path` and `locs.document_path` and create pivot `user_toko`.
3. Implement backend scaffolding (models relations + basic controller changes).

Do you want saya mulai dari langkah (2) membuat migration dan model relations sekarang? Jika iya, saya akan buat file migration dan update model `User`/`Loc`.

-- Copilot
