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
        .pin-form { text-align: center; }
        .pin-form input { width: 200px; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 24px; text-align: center; letter-spacing: 8px; margin-bottom: 10px; }
        .pin-form input:focus { border-color: #27ae60; outline: none; }
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
            {{-- Form Login --}}
            <div class="card">
                <h3><i class="fa fa-sign-in"></i> Login Nasabah</h3>
                <form action="{{ url('bank-sampah/warga/login') }}" method="POST" class="login-form">
                    @csrf
                    <input type="text" name="no_member" placeholder="Nomor Member" required autocomplete="off">
                    <input type="text" name="no_hp" placeholder="Nomor HP" required autocomplete="off">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-sign-in"></i> Masuk
                    </button>
                </form>
                <p class="security-note">🔒 Sesi akan disimpan di browser. Tidak ada token di URL.</p>
            </div>

            @if(isset($settings) && $settings->pesan_selamat_datang)
            <div class="card">
                <p style="text-align: center; color: #666; padding: 15px;">
                    {{ $settings->pesan_selamat_datang }}
                </p>
            </div>
            @endif

            {{-- Form PIN --}}
            @if(isset($showPin) && $showPin)
            <div class="card">
                <h3><i class="fa fa-lock"></i> Verifikasi PIN</h3>
                <form action="{{ url('bank-sampah/warga') }}" method="POST" class="pin-form">
                    @csrf
                    <input type="password" name="pin" placeholder="····" required maxlength="6" pattern="[0-9]{4,6}" inputmode="numeric" autofocus>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Verifikasi
                    </button>
                </form>
                <p class="security-note">Masukkan PIN 4-6 digit Anda</p>
            </div>
            @endif
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

            @if($settings->show_saldo || $settings->show_poin)
            <div class="card">
                <h3><i class="fa fa-wallet"></i> Saldo & Poin</h3>
                @if($settings->show_saldo)
                <div class="info-row">
                    <span class="info-label">💰 Saldo Tabungan</span>
                    <span class="info-value saldo">Rp {{ number_format($member->saldo, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($settings->show_poin)
                <div class="info-row">
                    <span class="info-label">⭐ Poin Reward</span>
                    <span class="info-value poin">{{ $member->poin }} poin</span>
                </div>
                @endif
            </div>
            @endif

            @if($settings->show_riwayat && $riwayat->count() > 0)
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

            @if(isset($settings) && $settings->pesan_footer)
            <div class="card">
                <p style="text-align: center; color: #666; padding: 15px;">
                    {{ $settings->pesan_footer }}
                </p>
            </div>
            @endif

            <a href="{{ url('bank-sampah/warga/logout') }}" class="btn btn-danger">
                <i class="fa fa-sign-out"></i> Keluar
            </a>
            <p class="security-note">🔒 Sesi aktif — tidak ada token di URL</p>
        @endif
    </div>
</body>
</html>
