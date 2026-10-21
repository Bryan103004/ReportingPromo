<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Import LOC</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; color: #1f2937; line-height: 1.5; }
        .email-container { max-width: 720px; margin: 30px auto; background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; padding: 25px; background-color: #ffffff; border-bottom: 2px solid #f3f4f6; }
        .content { padding: 30px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .stats-grid { display: table; width: 100%; margin: 20px 0; border-collapse: separate; border-spacing: 8px; }
        .stat-card { display: table-cell; width: 25%; padding: 14px 10px; border-radius: 8px; text-align: center; border: 1px solid #e5e7eb; }
        .stat-card.total { background-color: #f8fafc; }
        .stat-card.success { background-color: #f0fdf4; border-color: #bbf7d0; }
        .stat-card.skipped { background-color: #f8fafc; border-color: #e2e8f0; }
        .stat-card.failed { background-color: #fef2f2; border-color: #fecaca; }
        .stat-number { font-size: 22px; font-weight: 800; margin: 0; }
        .stat-label { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #6b7280; margin-top: 4px; }
        
        .alert-box { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
        .alert-info { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-danger { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        table.data-table { width: 100%; border-collapse: collapse; font-size: 12px; margin: 20px 0; }
        table.data-table th { background-color: #f9fafb; padding: 10px 12px; border-bottom: 2px solid #e5e7eb; border-top: 1px solid #e5e7eb; text-align: left; font-weight: 700; color: #4b5563; font-size: 11px; text-transform: uppercase; }
        table.data-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        table.data-table tr:hover { background-color: #f9fafb; }
        
        .error-reason { color: #dc2626; font-weight: 600; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header Logo --}}
        <div class="header">
            @if(isset($message) && file_exists(public_path('images/gl.png')))
                <img src="{{ $message->embed(public_path('images/gl.png')) }}" alt="GrandLucky Logo" style="height: auto; max-width: 280px;">
            @else
                <h1 style="margin: 0; font-size: 20px; color: #1f2937;">GRANDLUCKY SUPERSTORE</h1>
            @endif
        </div>

        {{-- Content --}}
        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0;">
                    Laporan Penarikan & Import Data LOC
                </h2>
            </div>

            <p style="font-size: 13px; color: #4b5563; margin-top: 4px; margin-bottom: 20px;">
                Berikut adalah laporan penarikan otomatis file CSV LOC dari folder inbound pada <strong>{{ now()->translatedFormat('d F Y, H:i:s') }}</strong>.
            </p>

            {{-- File Info Table --}}
            <table style="width: 100%; font-size: 13px; margin-bottom: 15px; background: #f9fafb; padding: 10px; border-radius: 8px;">
                <tr>
                    <td style="width: 140px; color: #6b7280; font-weight: 600; padding: 4px 8px;">Nama File CSV</td>
                    <td style="font-weight: 700; color: #111827; padding: 4px 8px;">: {{ $filename }}</td>
                </tr>
                <tr>
                    <td style="color: #6b7280; font-weight: 600; padding: 4px 8px;">Status Proses</td>
                    <td style="padding: 4px 8px;">
                        : 
                        @if($failedCount > 0 && ($successCount > 0 || $skippedCount > 0))
                            <span class="status-badge badge-warning">Sebagian Gagal / Dilewati</span>
                        @elseif($failedCount > 0 && $successCount === 0 && $skippedCount === 0)
                            <span class="status-badge badge-danger">Gagal Total</span>
                        @else
                            <span class="status-badge" style="background-color: #dcfce7; color: #15803d;">Berhasil Semua</span>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Fatal Error Box if any --}}
            @if ($error_msg)
                <div class="alert-box alert-danger">
                    <strong>Peringatan Sistem:</strong> {{ $error_msg }}
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number" style="color: #3b82f6;">{{ number_format($totalRows) }}</div>
                    <div class="stat-label">Total Baris CSV</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-number" style="color: #16a34a;">{{ number_format($successCount) }}</div>
                    <div class="stat-label">Data Baru (Masuk)</div>
                </div>
                <div class="stat-card" style="background-color: #f5f3ff; border-color: #ddd6fe;">
                    <div class="stat-number" style="color: #7c3aed;">{{ number_format($updatedCount ?? 0) }}</div>
                    <div class="stat-label">Data Diupdate</div>
                </div>
                <div class="stat-card skipped">
                    <div class="stat-number" style="color: #64748b;">{{ number_format($skippedCount) }}</div>
                    <div class="stat-label">Sudah Ada (Sama)</div>
                </div>
                <div class="stat-card failed">
                    <div class="stat-number" style="color: #dc2626;">{{ number_format($failedCount) }}</div>
                    <div class="stat-label">Gagal / Error</div>
                </div>
            </div>

            <div class="alert-box alert-info">
                💡 <strong>Catatan:</strong> Data yang nomor RAF-nya sudah ada di database namun nilai <strong>Nominal, PPN, atau Amount Incl PPN</strong> berubah/berbeda (<strong>{{ number_format($updatedCount ?? 0) }}</strong> baris) otomatis <strong>ditimpa (diperbarui)</strong> dengan data promo terbaru tanpa mengubah status invoice, status email, approval, maupun dokumen lainnya. Data yang nilainya sama (<strong>{{ number_format($skippedCount) }}</strong> baris) dilewati (skip).
            </div>

            {{-- Tabel Baris yang Gagal --}}
            @if (!empty($failedRows))
                <h3 style="font-size: 14px; font-weight: 800; color: #b91c1c; margin-top: 25px; margin-bottom: 8px;">
                    Rincian Baris yang Gagal Diproses ({{ count($failedRows) }} Baris):
                </h3>

                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 70px;">Baris CSV</th>
                                <th style="width: 140px;">No. RAF</th>
                                <th>Supplier</th>
                                <th style="width: 80px;">Toko (Alias)</th>
                                <th>Penyebab Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($failedRows as $index => $fail)
                                <tr>
                                    <td style="text-align: center; color: #9ca3af;">{{ $index + 1 }}</td>
                                    <td style="font-weight: 700; color: #4b5563;">Baris {{ $fail['line'] ?? '-' }}</td>
                                    <td style="font-family: monospace; font-size: 11px; font-weight: 700;">{{ $fail['no_raf'] ?? '-' }}</td>
                                    <td>{{ $fail['supplier'] ?? '-' }}</td>
                                    <td style="font-family: monospace; font-size: 11px;">{{ $fail['toko_alias'] ?? '-' }}</td>
                                    <td class="error-reason">{{ $fail['reason'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        {{-- Footer --}}
        <div class="footer">
            Email ini dikirim otomatis oleh sistem <strong>{{ config('app.name', 'Reporting Promo GrandLucky') }}</strong>.<br>
            Untuk mengubah daftar penerima email pemberitahuan ini, silakan akses menu <em>Master Data &rarr; Email Tambahan</em> pada website.
        </div>
    </div>
</body>
</html>