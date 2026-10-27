<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InvoiceLog;
use App\Models\Loc;
use App\Models\User;
use App\Services\CategorySpvService;
use Illuminate\Http\Request;
use App\Models\Rafaksi;
use App\Models\Jsm;
use App\Models\Pwp;
use App\Models\RafaksiDocument;
use App\Models\JsmDocument;
use App\Models\PwpDocument;
use App\Models\LocDocument;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class UtilityController extends Controller
{
    public function nextNoRaf(Request $request)
    {
        $category = strtoupper($request->query('category', 'RAF'));
        $cat = Category::find($request->query('category_id'));
        $cat_init = $cat ? $cat->initial_category : '';
        $categoryId = $cat ? $cat->id : $request->query('category_id');
        $periode = $request->query('periode');
        $date = $periode ? Carbon::parse($periode) : Carbon::now();
        $month = $date->format('m');
        $year = $date->format('Y');

        if ($category === 'JSM') {
            $maxSeq = Jsm::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAFJSM';
        } elseif ($category === 'PWP') {
            $maxSeq = Pwp::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAFPWP';
        } else {
            $maxSeq = Rafaksi::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAF';
        }

        $nextSeq = $maxSeq ? $maxSeq + 1 : 1;
        $padded = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'no_raf' => "{$prefix}/{$cat_init}/{$padded}/{$month}/{$year}",
            'sequence' => $nextSeq,
        ]);
    }

    public function resolveCategorySpv($document)
    {
        $category = null;
        if ($document->relationLoaded('categories') && $document->categories) {
            $category = $document->categories;
        } elseif (!empty($document->category_id)) {
            $category = Category::find($document->category_id);
        }

        if (! $category) {
            return [null, null];
        }

        return CategorySpvService::resolveSignatories($category, $document);
    }

    public function selectStoresForPrint($type, $id)
    {   
        $type = strtolower($type);
        $relations = ['tokos', 'items.stores', 'documents', 'categories'];

        switch ($type) {
            case 'jsm':
                $document = Jsm::with($relations)->findOrFail($id);
                $users = User::where('is_jsm_spv', true)->get();
                break;
            case 'pwp':
                $document = Pwp::with($relations)->findOrFail($id);
                $users = User::where('is_pwp_spv', true)->get();
                break;
            case 'loc':
                $document = Loc::with(['tokos', 'documents', 'categories'])->findOrFail($id);
                $users = User::all();
                break;
            default:
                $document = Rafaksi::with($relations)->findOrFail($id);
                $users = User::where('is_rafaksi_spv', true)->get();
                break;
        }

        // Kalau dokumen punya baris item, checklist toko cetak cuma nampilin toko
        // yang beneran punya sales (>0) di salah satu item -- toko yang cuma
        // "numpang" ke-link tapi datanya kosong semua (selalu jadi '-' di hasil
        // cetak) gak usah dikasih pilihan, biar gak bikin bingung. Dokumen lama
        // yang belum punya baris item (manual/flat) tetap tampilkan semua toko
        // yang di-link, karena filter per-item ini gak berlaku buat dokumen itu.
        if (isset($document->items) && $document->items->isNotEmpty()) {
            $tokoIdsWithData = $document->items
                ->flatMap(fn ($item) => $item->stores)
                ->where('sales_qty', '>', 0)
                ->pluck('toko_id')
                ->unique();

            $document->setRelation('tokos', $document->tokos->whereIn('id', $tokoIdsWithData));
        }

        [$autoPrepared, $autoAck] = $this->resolveCategorySpv($document);

        $title = 'Pilih Toko Cetak';
        return view('layouts.print-filter', compact('document', 'title', 'type', 'users', 'autoPrepared', 'autoAck'));
    }

    public function printAttachments($type, $id)
    {
        $type = strtolower($type);

        switch ($type) {
            case 'jsm':
                $document = Jsm::with('documents')->findOrFail($id);
                break;
            case 'pwp':
                $document = Pwp::with('documents')->findOrFail($id);
                break;
            case 'loc':
                $document = Loc::with('documents')->findOrFail($id);
                break;
            default:
                $document = Rafaksi::with('documents')->findOrFail($id);
                break;
        }

        if ($document->documents->isEmpty()) {
            return redirect()->back()->with('error', 'Dokumen lampiran tidak tersedia.');
        }

        $existingAttachments = $document->documents->filter(function ($attachment) {
            return Storage::exists($attachment->filepath);
        });

        if ($existingAttachments->isEmpty()) {
            return redirect()->back()->with('error', 'File dokumen lampiran tidak ditemukan di storage.');
        }

        $noRafClean = $document->no_raf ? str_replace(['/', '\\'], '_', $document->no_raf) : 'lampiran';

        // KASUS 1: Jika hanya ada 1 lampiran, langsung stream file asli ke browser
        // Cara ini tidak memerlukan FPDI sehingga mendukung semua versi PDF (1.5, 1.6, 1.7, compressed xref) tanpa error
        if ($existingAttachments->count() === 1) {
            $single = $existingAttachments->first();
            $fullPath = Storage::path($single->filepath);

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $noRafClean . '_lampiran.pdf"',
            ]);
        }

        // KASUS 2: Jika ada lebih dari 1 lampiran, coba gabungkan dengan FPDI
        $pdf = new Fpdi();
        $tempConvertedFiles = [];

        try {
            foreach ($existingAttachments as $attachment) {
                $filePath = Storage::path($attachment->filepath);
                $effectivePath = $this->preparePdfForFpdi($filePath, $tempConvertedFiles);

                $pageCount = $pdf->setSourceFile($effectivePath);
                for ($page = 1; $page <= $pageCount; $page++) {
                    $template = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($template);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            $this->cleanupTempFiles($tempConvertedFiles);

            if ($pdf->PageNo() === 0) {
                return redirect()->back()->with('error', 'File dokumen lampiran tidak dapat dibaca.');
            }

            return response($pdf->Output('S'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $noRafClean . '_lampiran.pdf"',
            ]);
        } catch (\Throwable $e) {
            $this->cleanupTempFiles($tempConvertedFiles);

            Log::warning("Gagal merge PDF lampiran untuk {$type} #{$id}: " . $e->getMessage());

            return response()->view('layouts.print-attachments-fallback', [
                'document' => $document,
                'type' => $type,
                'attachments' => $existingAttachments,
                'errorMessage' => 'Dokumen memiliki beberapa file lampiran yang menggunakan format kompresi modern (PDF 1.5+) sehingga tidak dapat digabungkan otomatis menjadi 1 file. Anda dapat langsung membuka atau mencetak masing-masing lampiran di bawah ini:',
            ]);
        }
    }

    public function viewAttachment($type, $attachmentId)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'jsm':
                $attachment = JsmDocument::findOrFail($attachmentId);
                break;
            case 'pwp':
                $attachment = PwpDocument::findOrFail($attachmentId);
                break;
            case 'loc':
                $attachment = LocDocument::findOrFail($attachmentId);
                break;
            default:
                $attachment = RafaksiDocument::findOrFail($attachmentId);
                break;
        }

        if (! Storage::exists($attachment->filepath)) {
            abort(404, 'File lampiran tidak ditemukan di storage.');
        }

        $filename = $attachment->filename ?? basename($attachment->filepath);

        return response()->file(Storage::path($attachment->filepath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected function preparePdfForFpdi(string $filePath, array &$tempFiles): string
    {
        static $tool = null;
        if ($tool === null) {
            $tool = false;
            $qpdfCheck = @shell_exec('qpdf --version 2>&1');
            if ($qpdfCheck && stripos($qpdfCheck, 'qpdf version') !== false) {
                $tool = 'qpdf';
            } else {
                $gsCheck = @shell_exec('gs --version 2>&1');
                if ($gsCheck && preg_match('/^\d+\.\d+/', trim($gsCheck))) {
                    $tool = 'gs';
                }
            }
        }

        if ($tool === 'qpdf') {
            $tempPath = tempnam(sys_get_temp_dir(), 'pdf_norm_') . '.pdf';
            $cmd = sprintf('qpdf --qdf %s %s 2>&1', escapeshellarg($filePath), escapeshellarg($tempPath));
            @exec($cmd, $output, $returnVar);
            if ($returnVar === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                $tempFiles[] = $tempPath;
                return $tempPath;
            }
        } elseif ($tool === 'gs') {
            $tempPath = tempnam(sys_get_temp_dir(), 'pdf_norm_') . '.pdf';
            $cmd = sprintf('gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1', escapeshellarg($tempPath), escapeshellarg($filePath));
            @exec($cmd, $output, $returnVar);
            if ($returnVar === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                $tempFiles[] = $tempPath;
                return $tempPath;
            }
        }

        return $filePath;
    }

    protected function cleanupTempFiles(array $tempFiles): void
    {
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    public function printDocument(Request $request, $type, $id)
    {
        $type = strtolower($type);

        // Ambil input array toko_ids yang dicentang dari form filter
        $selectedTokoIds = $request->input('toko_ids', []);

        // Load relasi standar
        $relations = ['items.stores.toko', 'tokos', 'categories'];

        switch ($type) {
            case 'jsm':
                $document = Jsm::with($relations)->findOrFail($id);
                $title = 'DOKUMEN JSM';
                break;
            case 'pwp':
                $document = Pwp::with($relations)->findOrFail($id);
                $title = 'DOKUMEN PWP';
                break;
            case 'loc':
                $document = Loc::with(['tokos', 'categories'])->findOrFail($id);
                $title = 'DOKUMEN LOC';
                break;
            default:
                $document = Rafaksi::with($relations)->findOrFail($id);
                $title = 'DOKUMEN RAFAKSI';
                break;
        }

        $prepared_by = null;
        $acknowledged_by = null;

        // Jika status_email sudah nonaktif ('tidak_aktif'), otomatis isi TTD dari SPV Kategori
        if ($document->status_email === 'tidak_aktif') {
            [$autoPrepared, $autoAck] = $this->resolveCategorySpv($document);
            $prepared_by = $autoPrepared;
            $acknowledged_by = $autoAck;
        }

        // Fallback jika tidak otomatis atau belum diset
        if (! $prepared_by) {
            $prepId = $request->input('prepared_by');
            $prepared_by = $prepId ? User::find($prepId) : auth()->user();
        }
        if (! $acknowledged_by) {
            $acknowledged_by = User::find($request->input('acknowledged_by'));
        }

        if ($prepared_by == null || $acknowledged_by == null) {
            return redirect()->back()->with('error', 'Prepared By atau Acknowledged By tidak ditemukan. Pastikan Tipe Dokumen ini memiliki SPV yang sesuai.');
        }

        // Filter daftar toko utama dokumen agar hanya menampilkan yang dipilih user
        if (!empty($selectedTokoIds)) {
            $document->setRelation('tokos', $document->tokos->whereIn('id', $selectedTokoIds));
            
            // Filter juga rincian stores di dalam setiap item agar hanya menghitung toko yang dipilih
            if (isset($document->items)) {
                foreach ($document->items as $item) {
                    $filteredStores = $item->stores->whereIn('toko_id', $selectedTokoIds);
                    $item->setRelation('stores', $filteredStores);

                    // HITUNG ULANG TOTAL SECARA DINAMIS BERDASARKAN TOKO YANG DIPILIH
                    $item->sales_total = $filteredStores->sum('sales_qty');
                    $item->value_total = $filteredStores->sum('value');
                }
            }
        }

        return view('layouts.print-universal', compact('document', 'title', 'type', 'prepared_by', 'acknowledged_by'));
    }

    public function getInvoiceLogs($type, $id)
    {
        $logs = InvoiceLog::where('document_type', strtolower($type))
            ->where('document_id', $id)
            ->orderBy('action_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs->map(function ($log) {
                return [
                    'id'           => $log->id,
                    'status'       => $log->status,
                    'status_label' => $log->status === 'done' ? 'Done' : 'Cancel (Batal)',
                    'user_name'    => $log->user_name,
                    'date'         => $log->action_at ? $log->action_at->format('d/m/Y') : '-',
                    'time'         => $log->action_at ? $log->action_at->format('H:i:s') : '-',
                    'datetime'     => $log->action_at ? $log->action_at->format('d M Y, H:i:s') : '-',
                ];
            })
        ]);
    }
}
