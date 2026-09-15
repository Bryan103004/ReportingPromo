<?php

namespace App\Services;

use App\Models\SupplierRafaksi;
use App\Models\Toko;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Parser untuk template Excel Rafaksi/Jsm/Pwp (dipakai bareng ketiga modul --
 * layout template-nya sama, cuma judul dokumennya beda).
 *
 * Baris 1-8-an (posisinya dicari berdasarkan label di kolom A, bukan nomor
 * baris tetap) berisi field header: periode_awal, periode_akhir, no_raf,
 * supplier_code, supplier_name, nama_toko, no_shiji. Lalu ada 1 baris header
 * tabel (NO, ARTICLE, ...) diikuti baris-baris item sampai kolom ARTICLE kosong.
 */
class RafaksiExcelImportParser
{
    private const HEADER_LABELS = ['periode_awal', 'periode_akhir', 'no_raf', 'supplier_code', 'supplier_name', 'nama_toko', 'no_shiji', 'no_shiji_promotion'];

    private array $warnings = [];
    private array $errors = [];

    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('FORMAT') ?? $spreadsheet->getActiveSheet();

        $header = $this->parseHeader($sheet);

        $tableHeaderRow = $this->findTableHeaderRow($sheet);
        if ($tableHeaderRow === null) {
            $this->errors[] = 'Tidak ditemukan baris header tabel item (kolom NO / ARTICLE) di file ini.';

            return $this->buildResult($header, [], []);
        }

        $columns = $this->mapTableColumns($sheet, $tableHeaderRow);

        $storeMap = [];
        $unresolved = [];
        foreach (array_keys($columns['sales']) as $alias) {
            $toko = Toko::where('kode_excel', $alias)->first();
            if ($toko) {
                $storeMap[$alias] = $toko;
            } else {
                $unresolved[] = $alias;
            }
        }

        if (! empty($unresolved)) {
            $this->errors[] = 'Kode toko berikut di file tidak dikenali sistem: ' . implode(', ', $unresolved) . '. Import dibatalkan supaya data Sales/Value tidak salah masuk ke toko yang keliru.';

            return $this->buildResult($header, [], []);
        }

        $items = $this->parseItems($sheet, $tableHeaderRow, $columns, $storeMap);

        return $this->buildResult($header, $items, $storeMap);
    }

    private function buildResult(array $header, array $items, array $storeMap): array
    {
        return [
            'header' => $header,
            'items' => $items,
            'stores' => collect($storeMap)->map(fn (Toko $t, $alias) => [
                'alias' => $alias,
                'toko_id' => $t->id,
                'nama_toko' => $t->nama_toko,
                'region_id' => $t->region_id,
            ])->values()->all(),
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];
    }

    // =========================================================================
    // HEADER (periode_awal, periode_akhir, no_raf, supplier_name, nama_toko, no_shiji)
    // =========================================================================

    private function parseHeader(Worksheet $sheet): array
    {
        $result = [
            'periode_awal' => null,
            'periode_akhir' => null,
            'no_raf' => null,
            'no_shiji' => null,
            'no_shiji_promotion' => null,
            'nama_toko_hint' => null,
            'supplier_match' => null,
            'supplier_name_hint' => null,
        ];

        $labelRows = [];
        for ($row = 1; $row <= 15; $row++) {
            $label = strtolower(trim((string) $sheet->getCell("A{$row}")->getValue()));
            if (in_array($label, self::HEADER_LABELS, true)) {
                $labelRows[$label] = $row;
            }
        }

        if (isset($labelRows['periode_awal'])) {
            $result['periode_awal'] = $this->readHeaderDate($sheet, $labelRows['periode_awal'], 'Periode Awal');
        }
        if (isset($labelRows['periode_akhir'])) {
            $result['periode_akhir'] = $this->readHeaderDate($sheet, $labelRows['periode_akhir'], 'Periode Akhir');
        }
        if (isset($labelRows['no_raf'])) {
            $result['no_raf'] = $this->readHeaderText($sheet, $labelRows['no_raf']);
        }
        if (isset($labelRows['no_shiji'])) {
            $result['no_shiji'] = $this->readHeaderText($sheet, $labelRows['no_shiji']);
        }
        if (isset($labelRows['no_shiji_promotion'])) {
            $result['no_shiji_promotion'] = $this->readHeaderText($sheet, $labelRows['no_shiji_promotion']);
        }
        if (isset($labelRows['nama_toko'])) {
            $result['nama_toko_hint'] = $this->readHeaderText($sheet, $labelRows['nama_toko']);
        }
        if (isset($labelRows['supplier_name']) || isset($labelRows['supplier_code'])) {
            $codeRaw = isset($labelRows['supplier_code']) ? $this->readHeaderText($sheet, $labelRows['supplier_code']) : null;
            $nameRaw = isset($labelRows['supplier_name']) ? $this->readHeaderText($sheet, $labelRows['supplier_name']) : null;
            $result['supplier_name_hint'] = $nameRaw;
            $result['supplier_match'] = $this->resolveSupplier($codeRaw, $nameRaw);
        }

        return $result;
    }

    /**
     * Cari supplier berdasarkan kode dulu (paling pasti), lalu nama. Kalau
     * dua-duanya gak ketemu tapi kode & nama ada di file, langsung dibuatkan
     * record baru -- kode_supplier punya unique constraint jadi aman dari
     * dobel kalau file yang sama di-import berkali-kali.
     */
    private function resolveSupplier(?string $code, ?string $name): ?array
    {
        // Kalau file punya kode supplier, kode itu yang jadi acuan utama --
        // TIDAK jatuh ke pencarian by-nama, karena nama yang kebetulan sama
        // bisa punya kode berbeda (ganti kode internal, dsb). Kalau kodenya
        // gak ketemu, langsung dianggap supplier baru (kalau namanya ada juga).
        if ($code) {
            $byCode = SupplierRafaksi::where('kode_supplier', $code)->first();
            if ($byCode) {
                return ['kode_supplier' => $byCode->kode_supplier, 'nama_supplier' => $byCode->nama_supplier];
            }

            if ($name) {
                $created = SupplierRafaksi::create(['kode_supplier' => $code, 'nama_supplier' => $name]);
                $this->warnings[] = "Supplier \"{$name}\" ({$code}) belum ada di database -- otomatis ditambahkan.";

                return ['kode_supplier' => $created->kode_supplier, 'nama_supplier' => $created->nama_supplier];
            }

            $this->warnings[] = "Kode supplier \"{$code}\" tidak ditemukan di database, dan nama supplier tidak ada di file -- pilih atau buat manual.";

            return null;
        }

        // Gak ada kode di file sama sekali -- baru dicoba cari lewat nama.
        if ($name) {
            $byName = SupplierRafaksi::whereRaw('LOWER(TRIM(nama_supplier)) = ?', [strtolower(trim($name))])->get();
            if ($byName->count() === 1) {
                return ['kode_supplier' => $byName->first()->kode_supplier, 'nama_supplier' => $byName->first()->nama_supplier];
            }
            if ($byName->count() > 1) {
                $this->warnings[] = "Vendor \"{$name}\" cocok dengan lebih dari satu supplier -- pilih manual.";

                return null;
            }

            $this->warnings[] = "Vendor \"{$name}\" tidak ditemukan di daftar supplier, dan kode supplier tidak ada di file -- pilih atau buat manual.";
        }

        return null;
    }

    /**
     * Nilai header ada di kolom B, kadang ditulis polos, kadang masih ada sisa
     * ": " placeholder dari template di depannya -- keduanya dibersihkan di sini.
     */
    private function readHeaderText(Worksheet $sheet, int $row): ?string
    {
        $raw = $sheet->getCell("B{$row}")->getValue();
        if ($raw === null || $raw === '') {
            return null;
        }
        $clean = preg_replace('/^:\s*/', '', trim((string) $raw));

        return $clean === '' ? null : $clean;
    }

    private function readHeaderDate(Worksheet $sheet, int $row, string $label): ?string
    {
        $cell = $sheet->getCell("B{$row}");
        $raw = $cell->getValue();
        if ($raw === null || $raw === '') {
            return null;
        }

        if (ExcelDate::isDateTime($cell)) {
            return ExcelDate::excelToDateTimeObject($raw)->format('Y-m-d');
        }

        $text = preg_replace('/^:\s*/', '', trim((string) $raw));
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $text);
                if ($date && $date->year >= 2000 && $date->year <= 2100) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        $this->warnings[] = "{$label} tidak bisa dibaca dari file (\"{$text}\") -- silakan isi manual.";

        return null;
    }

    // =========================================================================
    // TABEL ITEM
    // =========================================================================

    private function findTableHeaderRow(Worksheet $sheet): ?int
    {
        for ($row = 1; $row <= 20; $row++) {
            $a = strtoupper(trim((string) $sheet->getCell("A{$row}")->getValue()));
            $b = strtoupper(trim((string) $sheet->getCell("B{$row}")->getValue()));
            if ($a === 'NO' && $b === 'ARTICLE') {
                return $row;
            }
        }

        return null;
    }

    /**
     * Baca 1 baris header tabel, kembalikan kolom kolom tetap (article, reg, dst)
     * plus daftar kolom Sales {ALIAS} dan Value {ALIAS} yang ditemukan.
     */
    private function mapTableColumns(Worksheet $sheet, int $headerRow): array
    {
        $fixed = [];
        $sales = [];
        $value = [];
        $mode = 'fixed';

        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $text = trim((string) $sheet->getCell("{$colLetter}{$headerRow}")->getValue());
            if ($text === '') {
                continue;
            }
            $upper = strtoupper($text);

            if ($upper === 'SALES TOTAL') {
                $mode = 'after_sales';
                continue;
            }
            if ($upper === 'VALUE TOTAL') {
                $mode = 'done';
                continue;
            }
            if (strpos($upper, 'SALES ') === 0) {
                $sales[trim(substr($text, 6))] = $colLetter;
                continue;
            }
            if (strpos($upper, 'VALUE ') === 0) {
                $value[trim(substr($text, 6))] = $colLetter;
                continue;
            }

            $fixedColumnKeys = [
                'NO' => 'no',
                'ARTICLE' => 'article',
                'SHIJI CODE' => 'shiji_code',
                'DESCRIPTION' => 'description',
                'DISC NOMINAL' => 'disc_nominal',
                'PROMO DISC' => 'promo_disc',
                'REG' => 'reg',
                'PROMO' => 'promo',
                'CLAIM' => 'claim',
            ];
            $key = $fixedColumnKeys[$upper] ?? null;
            if ($key) {
                $fixed[$key] = $colLetter;
            }
        }

        return ['fixed' => $fixed, 'sales' => $sales, 'value' => $value];
    }

    private function parseItems(Worksheet $sheet, int $headerRow, array $columns, array $storeMap): array
    {
        $items = [];
        $fixed = $columns['fixed'];
        $row = $headerRow + 1;

        while (true) {
            $articleCol = $fixed['article'] ?? null;
            if (! $articleCol) {
                break;
            }
            $article = trim((string) $sheet->getCell("{$articleCol}{$row}")->getFormattedValue());
            if ($article === '') {
                break;
            }

            $discNominal = $this->readNumeric($sheet, $fixed['disc_nominal'] ?? null, $row);
            $promoDisc = $this->readPercent($sheet, $fixed['promo_disc'] ?? null, $row);
            $reg = $this->readNumeric($sheet, $fixed['reg'] ?? null, $row) ?? 0;
            $promo = $this->readNumeric($sheet, $fixed['promo'] ?? null, $row);

            $computedClaim = ClaimCalculator::claim($discNominal, $promoDisc, $reg);

            if (isset($fixed['claim'])) {
                $fileClaim = $this->readCalculatedNumeric($sheet, $fixed['claim'], $row);
                if ($fileClaim !== null && abs($fileClaim - $computedClaim) > 1) {
                    $this->warnings[] = "Baris \"{$article}\": CLAIM di file ({$fileClaim}) beda dari hasil hitung sistem ({$computedClaim}) -- yang dipakai adalah hasil hitung sistem.";
                }
            }

            $sales = [];
            foreach ($storeMap as $alias => $toko) {
                $colLetter = $columns['sales'][$alias] ?? null;
                $qty = $colLetter ? $this->readNumeric($sheet, $colLetter, $row) : null;
                if ($qty !== null && $qty != 0) {
                    $sales[(string) $toko->id] = $qty;
                }
            }

            $items[] = [
                'article' => $article,
                'shiji_code' => isset($fixed['shiji_code']) ? trim((string) $sheet->getCell("{$fixed['shiji_code']}{$row}")->getFormattedValue()) ?: null : null,
                'description' => isset($fixed['description']) ? trim((string) $sheet->getCell("{$fixed['description']}{$row}")->getValue()) ?: null : null,
                'disc_nominal' => $discNominal,
                'promo_disc' => $promoDisc,
                'reg' => $reg,
                'promo' => $promo,
                'claim' => $computedClaim,
                'sales' => $sales,
            ];

            $row++;
            if ($row - $headerRow > 2000) {
                $this->warnings[] = 'Berhenti membaca setelah 2000 baris -- periksa apakah file punya baris kosong di tengah tabel.';
                break;
            }
        }

        return $items;
    }

    private function readNumeric(Worksheet $sheet, ?string $colLetter, int $row): ?float
    {
        if (! $colLetter) {
            return null;
        }
        $raw = $sheet->getCell("{$colLetter}{$row}")->getValue();
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return null;
    }

    private function readCalculatedNumeric(Worksheet $sheet, string $colLetter, int $row): ?float
    {
        try {
            $val = $sheet->getCell("{$colLetter}{$row}")->getCalculatedValue();

            return is_numeric($val) ? (float) $val : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * PROMO DISC di Excel kadang disimpan sebagai pecahan (0.1 = format persen
     * "0%"), kadang sebagai angka polos (10). Dua-duanya dinormalkan jadi angka
     * persen (10) sesuai konvensi yang dipakai di seluruh sistem ini.
     */
    private function readPercent(Worksheet $sheet, ?string $colLetter, int $row): ?float
    {
        if (! $colLetter) {
            return null;
        }
        $cell = $sheet->getCell("{$colLetter}{$row}");
        $raw = $cell->getValue();
        if ($raw === null || $raw === '' || ! is_numeric($raw)) {
            return null;
        }

        $format = $sheet->getStyle("{$colLetter}{$row}")->getNumberFormat()->getFormatCode();
        $isPercentFormat = strpos($format, '%') !== false;

        if ($isPercentFormat || (float) $raw <= 1) {
            return round(((float) $raw) * 100, 4);
        }

        return round((float) $raw, 4);
    }
}
