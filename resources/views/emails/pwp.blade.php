<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; background-color: #f3f4f6; }
        .email-container { max-width: 650px; margin: 40px auto; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
        .header { text-align: center; padding: 25px; border-bottom: 1px solid #f3f4f6; }
        .content { padding: 30px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 20px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }

        th { background: #f9fafb; padding: 12px; border-bottom: 2px solid #e5e7eb; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .btn { display: inline-block; background: #4f46e5; color: #fff !important; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if(isset($message))
                <img src="{{ $message->embed(public_path('images/gl.png')) }}" alt="Logo" style="height: auto; width: 450px">
            @else
                <img src="{{ asset('images/gl.png') }}" alt="Logo" style="height: auto; width:450px;">
            @endif
        </div>
        <div class="content">
            <h2 style="font-size: 18px; color: #111827;">Peringatan Masa Berlaku PWP</h2>
            <p>Halo <strong>{{ $user->name }}</strong>,</p>
            <p>Berikut adalah daftar PWP yang telah memasuki periode pengurusan renewal berdasarkan setting reminder:</p>

            <table>
                <thead>
                    <tr>
                        <th>Toko</th>
                        <th>Nomor RAF</th>
                        <th>Periode Berakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pwps as $pwp)
                    <tr>
                        <!-- Kolom 1: Toko -->
                        <td>{{ $pwp->tokos->pluck('nama_toko')->implode(', ') ?: '-' }}</td>

                        <!-- Kolom 2: Nomor RAF -->
                        <td style="font-family: monospace;">{{ $pwp->no_raf }}</td>

                        <!-- Kolom 3: Periode Berakhir -->
                        <td style="color: #dc2626; font-weight: bold;">
                            {{ Carbon::parse($pwp->periode_akhir)->format('d/m/Y') }}
                        </td>

                        <!-- Kolom 4: Aksi -->
                        <td>
                            <a href="{{ route('pwp.renew.index', ['id' => $pwp->id]) }}" style="color: #4f46e5; text-decoration: underline; font-weight: bold;">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
