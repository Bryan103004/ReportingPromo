<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0; }
        .header { text-align: center; padding: 20px 0; }
        .logo { height: auto; width: 450px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f8f9fa; }
        .footer { font-size: 12px; color: #777; margin-top: 30px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <img src="{{ $message->embed(public_path('images/gl.png')) }}" alt="Logo Grandlucky" class="logo">
        </div>

        <!-- CONTENT -->
        <div class="content">
            <h1 style="color: #dc2626;">URGENT: RAFAKSI TELAH KADALUARSA</h1>
            <p>Halo {{ $user->name }},</p>
            <p>Sistem mendeteksi bahwa RAFAKSI berikut telah <strong style="color: #dc2626;">KADALUARSA (MELEWATI BATAS WAKTU)</strong> namun belum diperbarui di sistem:</p>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No RAF</th>
                        <th>Periode Berakhir</th>
                        <th>Toko</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rafaksis as $index => $rafaksi)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $rafaksi->no_raf ?? '-' }}</td>
                        <td>{{ $rafaksi->periode_akhir ? \Carbon\Carbon::parse($rafaksi->periode_akhir)->format('d M Y') : '-' }}</td>
                        <td>{{ $rafaksi->tokos->pluck('nama_toko')->implode(', ') ?: '-' }}</td>                        <td>
                            <a href="{{ route('rafaksi.renew.index', ['id' => $rafaksi->id]) }}">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Silakan periksa dan lakukan proses renewal secepatnya.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            Terima kasih,<br>
            <strong>{{ env('APP_NAME') }}</strong><br>
            GrandLucky Group
        </div>
    </div>
</body>
</html>