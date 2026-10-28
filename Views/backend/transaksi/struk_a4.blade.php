{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi #{{ $transaksi->no_transaksi }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }

        .header { text-align: center; border-bottom: 2px solid #27ae60; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; color: #27ae60; }
        .header p { margin: 3px 0 0; color: #666; font-size: 11px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #ddd; border-radius: 8px; padding: 12px; }
        .info-box h3 { margin: 0 0 8px; font-size: 13px; color: #27ae60; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; padding: 3px 0; }
        .info-label { color: #666; }
        .info-value { font-weight: bold; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-setor { background: #d4edda; color: #155724; }
        .badge-tarik { background: #f8d7da; color: #721c24; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f8f9fa; padding: 8px 10px; text-align: left; border: 1px solid #dee2e6; font-size: 11px; }
        table.items td { padding: 8px 10px; border: 1px solid #dee2e6; }
        table.items tfoot td { font-weight: bold; background: #f8f9fa; }

        .total-box { text-align: right; margin: 20px 0; padding: 15px; background: #f0f9f4; border: 2px solid #27ae60; border-radius: 8px; }
        .total-box .label { font-size: 14px; color: #666; }
        .total-box .amount { font-size: 24px; font-weight: bold; color: #27ae60; }

        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px dashed #ccc; color: #999; font-size: 10px; }
        .footer p { margin: 2px 0; }

        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; z-index: 100; }
        .print-btn:hover { background: #2980b9; }

        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" id="tombolCetak">
        <i class="fa fa-print"></i> Cetak Struk
    </button>

    <div class="header">
        <h1>{{ setting("bank_sampah_nama", "Bank Sampah Desa") }}</h1>
        <p>{{ setting("bank_sampah_alamat", "") }}</p>
        <p>Telp: {{ setting("bank_sampah_no_hp", "-") }}</p>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>📋 Info Transaksi</h3>
            <div class="info-row">
                <span class="info-label">No Transaksi</span>
                <span class="info-value">{{ $transaksi->no_transaksi }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ $transaksi->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipe</span>
                <span class="info-value">
                    @if($transaksi->tipe === 'setor')
                        <span class="badge badge-setor">SETOR</span>
                    @else
                        <span class="badge badge-tarik">TARIK</span>
                    @endif
                </span>
            </div>
            @if($transaksi->lokasi)
            <div class="info-row">
                <span class="info-label">Lokasi/TPS</span>
                <span class="info-value">{{ $transaksi->lokasi->nama }}</span>
            </div>
            @endif
        </div>

        <div class="info-box">
            <h3>👤 Info Nasabah</h3>
            <div class="info-row">
                <span class="info-label">No Member</span>
                <span class="info-value">{{ $transaksi->member->no_member ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value">{{ $transaksi->member->nama ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-value">{{ $transaksi->member->alamat ?? '-' }}</span>
            </div>
            @if($transaksi->poin > 0)
            <div class="info-row">
                <span class="info-label">Poin Didapat</span>
                <span class="info-value" style="color: #f39c12;">+{{ $transaksi->poin }} poin</span>
            </div>
            @endif
        </div>
    </div>

    @if($transaksi->tipe === 'setor' && $transaksi->detail->count() > 0)
    <h3 style="margin-bottom: 10px;">📦 Detail Setoran</h3>
    <table class="items">
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Jenis Sampah</th>
                <th>Kategori</th>
                <th width="80" class="text-right">Berat (kg)</th>
                <th width="100" class="text-right">Harga/kg</th>
                <th width="120" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detail as $d)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $d->sampah->nama ?? '-' }}</td>
                <td>{{ $d->sampah->kategori->nama ?? '-' }}</td>
                <td class="text-right">{{ number_format($d->berat, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($transaksi->detail->sum('berat'), 2, ',', '.') }} kg</strong></td>
                <td></td>
                <td class="text-right"><strong>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="total-box">
        <div class="label">{{ $transaksi->tipe === 'setor' ? 'Total Setoran' : 'Total Penarikan' }}</div>
        <div class="amount">
            {{ $transaksi->tipe === 'setor' ? '+' : '-' }}Rp {{ number_format($transaksi->total, 0, ',', '.') }}
        </div>
    </div>

    @if($transaksi->keterangan)
    <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
        <strong>Keterangan:</strong> {{ $transaksi->keterangan }}
    </div>
    @endif

    <div class="footer">
        <p>Terima kasih telah menggunakan layanan {{ setting("bank_sampah_nama", "Bank Sampah Desa") }}</p>
        <p>Struk ini dicetak secara otomatis oleh sistem</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script nonce="{{ $cspNonce ?? '' }}">
        document.getElementById('tombolCetak').addEventListener('click', function () {
            window.print();
        });
    </script>
</body>
</html>
