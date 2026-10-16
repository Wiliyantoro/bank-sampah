<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Nasabah - {{ $member->no_member }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        .card {
            width: 85.6mm;
            height: 54mm;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 8px;
            padding: 5mm;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .card::before {
            content: '';
            position: absolute;
            top: -20mm;
            right: -20mm;
            width: 40mm;
            height: 40mm;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3mm;
        }

        .card-logo {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
        }

        .card-logo small {
            font-size: 8px;
            font-weight: normal;
            opacity: 0.8;
        }

        .card-qr {
            text-align: right;
        }

        .card-qr canvas {
            width: 18mm !important;
            height: 18mm !important;
            border-radius: 3px;
        }

        .card-info {
            position: absolute;
            bottom: 5mm;
            left: 5mm;
            right: 5mm;
        }

        .card-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .card-no {
            font-size: 8px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .card-saldo {
            position: absolute;
            bottom: 5mm;
            right: 5mm;
            text-align: right;
        }

        .card-saldo small {
            font-size: 7px;
            opacity: 0.8;
        }

        .card-saldo div {
            font-size: 10px;
            font-weight: bold;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover { background: #2980b9; }

        @media print {
            body { background: white; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fa fa-print"></i> Cetak Kartu
    </button>

    <div class="card">
        <div class="card-header">
            <div class="card-logo">
                🏦 {{ $config['nama'] ?? 'BANK SAMPAH' }}
                <br><small>{{ $config['alamat'] ?? '' }}</small>
            </div>
            <div class="card-qr">
                <canvas id="qr"></canvas>
            </div>
        </div>

        <div class="card-info">
            <div class="card-name">{{ $member->nama }}</div>
            <div class="card-no">{{ $member->no_member }}</div>
        </div>

        <div class="card-saldo">
            <small>SALDO</small>
            <div>Rp {{ number_format($member->saldo, 0, ',', '.') }}</div>
        </div>
    </div>

    <script>
        QRCode.toCanvas(document.getElementById('qr'), '{{ $member->no_member }}', {
            width: 70,
            margin: 1,
            color: { dark: '#ffffff', light: '#00000000' }
        });
    </script>
</body>
</html>
