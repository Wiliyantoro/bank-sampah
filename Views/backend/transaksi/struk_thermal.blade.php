{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi #{{ $transaksi->no_transaksi }}</title>
    <style>
        @page { size: 80mm auto; margin: 5mm; }
        body { font-family: 'Courier New', monospace; font-size: 11px; line-height: 1.4; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .flex { display: flex; justify-content: space-between; }
        .header-logo { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="center">
        <div class="header-logo">{{ $config['nama'] ?? 'BANK SAMPAH' }}</div>
        <div>{{ $config['alamat'] ?? '' }}</div>
        <div>Telp: {{ $config['no_hp'] ?? '' }}</div>
    </div>
    <div class="line"></div>
    <div class="center bold">STRUK TRANSAKSI</div>
    <div class="line"></div>

    <table>
        <tr><td>No</td><td>: {{ $transaksi->no_transaksi }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $transaksi->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Tipe</td><td>: {{ strtoupper($transaksi->tipe) }}</td></tr>
        <tr><td>Nasabah</td><td>: {{ $transaksi->member->nama ?? '-' }}</td></tr>
        <tr><td>No Member</td><td>: {{ $transaksi->member->no_member ?? '-' }}</td></tr>
    </table>

    <div class="line"></div>

    @if($transaksi->tipe === 'setor' && $transaksi->detail->count() > 0)
    <table>
        <thead>
            <tr>
                <td class="bold">Jenis</td>
                <td class="bold">Berat</td>
                <td class="bold text-right">Harga</td>
                <td class="bold text-right">Subtotal</td>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detail as $d)
            <tr>
                <td>{{ substr($d->sampah->nama ?? '-', 0, 15) }}</td>
                <td>{{ number_format($d->berat, 2) }}kg</td>
                <td class="text-right">{{ number_format($d->harga, 0) }}</td>
                <td class="text-right">{{ number_format($d->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="line"></div>
    <div class="flex bold">
        <td>TOTAL</td>
        <td class="text-right">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
    </div>

    @if($transaksi->tipe === 'setor' && $transaksi->poin > 0)
    <div class="flex">
        <td>Poin Didapat</td>
        <td class="text-right">+{{ $transaksi->poin }} poin</td>
    </div>
    @endif

    <div class="flex">
        <td>Saldo</td>
        <td class="text-right">Rp {{ number_format($transaksi->member->saldo ?? 0, 0, ',', '.') }}</td>
    </div>

    <div class="line"></div>
    <div class="center">Terima kasih! 🌱</div>
    <div class="center" style="font-size: 9px;">Salam hijau! 💚</div>
    <div class="line"></div>

    <script nonce="{{ $cspNonce ?? '' }}">
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>
