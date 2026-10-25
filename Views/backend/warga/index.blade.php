<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting("bank_sampah_nama", "Bank Sampah") }} - Dashboard Nasabah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; padding: 20px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .container { max-width: 500px; margin: 0 auto; padding: 20px; }
        .card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card h3 { color: #2c3e50; margin-bottom: 15px; font-size: 16px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #666; }
        .info-value { font-weight: bold; color: #2c3e50; }
        .info-value.saldo { color: #27ae60; font-size: 20px; }
        .info-value.poin { color: #f39c12; }
        .btn { display: block; width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; text-align: center; text-decoration: none; margin-bottom: 10px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { display: inline-block; width: auto; padding: 8px 16px; font-size: 14px; }
        .transaksi-item { padding: 12px 0; border-bottom: 1px solid #eee; }
        .transaksi-item:last-child { border-bottom: none; }
        .transaksi-tipe { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .tipe-setor { background: #d4edda; color: #155724; }
        .tipe-tarik { background: #f8d7da; color: #721c24; }
        .login-form { text-align: center; }
        .login-form input { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; margin-bottom: 10px; }
        .login-form input:focus { border-color: #27ae60; outline: none; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        .empty { text-align: center; padding: 20px; color: #999; }
        .security-note { text-align: center; font-size: 12px; color: #999; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏦 {{ setting("bank_sampah_nama", "Bank Sampah Desa") }}</h1>
        <p>Dashboard Nasabah</p>
    </div>

    <div class="container">
        @if(session('error') || isset($error))
            <div class="alert alert-error">{{ session('error') ?? $error }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(!$member)
            {{-- D-001: login lewat Layanan Mandiri inti (NIK + PIN tweb_penduduk).
                 Modul tidak lagi punya login/PIN buatan sendiri. --}}
            <div class="card">
                <h3><i class="fa fa-sign-in"></i> Masuk Nasabah</h3>
                <p>Dashboard nasabah diakses lewat akun <strong>Layanan Mandiri</strong> desa.</p>
                <a href="{{ $mandiriLogin }}" class="btn btn-success">
                    <i class="fa fa-sign-in"></i> Masuk lewat Layanan Mandiri
                </a>
                <p class="security-note">Belum punya akun Layanan Mandiri? Hubungi petugas desa.</p>
            </div>
        @else
            {{-- Dashboard Nasabah --}}
            <div class="card">
                <h3><i class="fa fa-user"></i> Info Nasabah</h3>
                <div class="info-row">
                    <span class="info-label">No Member</span>
                    <span class="info-value">{{ $member->no_member }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $member->nama }}</span>
                </div>
                @if($member->lokasi)
                <div class="info-row">
                    <span class="info-label">Lokasi/TPS</span>
                    <span class="info-value">{{ $member->lokasi->nama }}</span>
                </div>
                @endif
            </div>

            @if(setting('bank_sampah_show_saldo') == 1 || setting('bank_sampah_show_poin') == 1)
            <div class="card">
                <h3><i class="fa fa-wallet"></i> Saldo & Poin</h3>
                @if(setting('bank_sampah_show_saldo') == 1)
                <div class="info-row">
                    <span class="info-label">💰 Saldo Tabungan</span>
                    <span class="info-value saldo">Rp {{ number_format($member->saldo, 0, ',', '.') }}</span>
                </div>
                @endif
                @if(setting('bank_sampah_show_poin') == 1)
                <div class="info-row">
                    <span class="info-label">⭐ Poin Reward</span>
                    <span class="info-value poin">{{ $member->poin }} poin</span>
                </div>
                @endif
            </div>
            @endif

            @if(setting('bank_sampah_show_riwayat') == 1 && $riwayat->count() > 0)
            <div class="card">
                <h3><i class="fa fa-history"></i> Riwayat Transaksi Terakhir</h3>
                @foreach($riwayat as $trx)
                    <div class="transaksi-item">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span class="transaksi-tipe {{ $trx->tipe === 'setor' ? 'tipe-setor' : 'tipe-tarik' }}">
                                    {{ strtoupper($trx->tipe) }}
                                </span>
                                <span style="font-size: 12px; color: #999; margin-left: 5px;">
                                    {{ $trx->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div>
                                <strong>{{ $trx->tipe === 'setor' ? '+' : '-' }}Rp {{ number_format($trx->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif


            {!! form_open(url('bank-sampah/warga/logout')) !!}
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-sign-out"></i> Keluar
                </button>
            {!! form_close() !!}
            <p class="security-note">🔒 Sesi aktif — tidak ada token di URL</p>
        @endif
    </div>
</body>
</html>
