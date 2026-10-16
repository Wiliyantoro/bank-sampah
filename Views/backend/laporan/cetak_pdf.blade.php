<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Bank Sampah - {{ date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
        h2 { text-align: center; font-size: 14px; color: #666; margin-bottom: 20px; }
        .header-info { text-align: center; margin-bottom: 20px; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #e9ecef; font-weight: bold; }
        .summary { margin-bottom: 20px; }
        .summary-box { display: inline-block; width: 48%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; vertical-align: top; }
        .summary-box.setor { border-left: 4px solid #27ae60; }
        .summary-box.tarik { border-left: 4px solid #e74c3c; }
        .summary-title { font-size: 11px; color: #666; }
        .summary-value { font-size: 20px; font-weight: bold; }
        .summary-detail { font-size: 11px; color: #999; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>{{ $config['nama'] ?? 'BANK SAMPAH' }}</h1>
    <h2>Laporan Bulanan - {{ date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) }}</h2>

    <div class="header-info">
        Alamat: {{ $config['alamat'] ?? '-' }} | Telp: {{ $config['no_hp'] ?? '-' }}
    </div>

    {{-- Ringkasan --}}
    <div class="summary">
        <div class="summary-box setor">
            <div class="summary-title">TOTAL SETOR</div>
            <div class="summary-value" style="color: #27ae60;">{{ format_rupiah($totalSetor->nominal) }}</div>
            <div class="summary-detail">{{ $totalSetor->jumlah }} transaksi</div>
        </div>
        &nbsp;
        <div class="summary-box tarik">
            <div class="summary-title">TOTAL TARIK</div>
            <div class="summary-value" style="color: #e74c3c;">{{ format_rupiah($totalTarik->nominal) }}</div>
            <div class="summary-detail">{{ $totalTarik->jumlah }} transaksi</div>
        </div>
    </div>

    {{-- Rekap Harian --}}
    <h3 style="font-size: 14px; margin-bottom: 10px;">📋 Rekap Transaksi Harian</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th class="text-center">Setor (trx)</th>
                <th class="text-right">Setor (Rp)</th>
                <th class="text-center">Tarik (trx)</th>
                <th class="text-right">Tarik (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapHarian as $h)
            <tr>
                <td>{{ date('d/m/Y', strtotime($h['tanggal'])) }}</td>
                <td>{{ $h['nama_hari'] }}</td>
                <td class="text-center">{{ $h['setor_jumlah'] }}</td>
                <td class="text-right">{{ format_rupiah($h['setor_nominal']) }}</td>
                <td class="text-center">{{ $h['tarik_jumlah'] }}</td>
                <td class="text-right">{{ format_rupiah($h['tarik_nominal']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rekap Jenis Sampah --}}
    <h3 style="font-size: 14px; margin-bottom: 10px;">♻️ Rekap Jenis Sampah</h3>
    <table>
        <thead>
            <tr>
                <th>Jenis Sampah</th>
                <th>Kategori</th>
                <th class="text-right">Total Berat</th>
                <th class="text-right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapSampah as $rs)
            <tr>
                <td>{{ $rs->sampah->nama ?? '-' }}</td>
                <td>{{ $rs->sampah->kategori->nama ?? '-' }}</td>
                <td class="text-right">{{ format_berat($rs->total_berat) }}</td>
                <td class="text-right">{{ format_rupiah($rs->total_nilai) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i') }} | {{ $config['nama'] ?? 'Bank Sampah Desa' }}
    </div>
</body>
</html>
