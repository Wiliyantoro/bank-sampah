@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-cog"></i> Pengaturan Bank Sampah</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Pengaturan Dashboard Warga --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-globe"></i> Dashboard Warga / Nasabah</h3>
                </div>

                <form action="{{ url('bank-sampah/pengaturan/update-warga') }}" method="POST">
                    @csrf

                    <div class="box-body">
                        <div class="form-group">
                            <label>Aktifkan Dashboard Warga</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="warga_aktif" value="1" {{ $settings->warga_aktif ? 'checked' : '' }}>
                                    Aktif - Nasabah bisa mengakses dashboard
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="warga_aktif" value="0" {{ ! $settings->warga_aktif ? 'checked' : '' }}>
                                    Nonaktif - Dashboard warga tidak bisa diakses
                                </label>
                            </div>
                        </div>

                        <hr>

                        <h4><i class="fa fa-eye"></i> Yang Ditampilkan ke Warga</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="show_saldo" value="1" {{ $settings->show_saldo ? 'checked' : '' }}>
                                        Tampilkan Saldo
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="show_poin" value="1" {{ $settings->show_poin ? 'checked' : '' }}>
                                        Tampilkan Poin Reward
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="show_riwayat" value="1" {{ $settings->show_riwayat ? 'checked' : '' }}>
                                        Tampilkan Riwayat Transaksi
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Riwayat Ditampilkan</label>
                                    <input type="number" class="form-control" name="max_riwayat"
                                        value="{{ $settings->max_riwayat }}" min="1" max="50">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4><i class="fa fa-lock"></i> Keamanan</h4>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="require_pin" value="1" {{ $settings->require_pin ? 'checked' : '' }}>
                                Wajibkan PIN untuk akses dashboard
                            </label>
                        </div>
                        <small class="text-muted">
                            Jika diaktifkan, nasabah harus memasukkan PIN 6 digit untuk melihat saldo dan riwayat.
                            PIN diatur oleh nasabah melalui halaman profil.
                        </small>

                        <hr>

                        <h4><i class="fa fa-comment"></i> Pesan</h4>

                        <div class="form-group">
                            <label>Pesan Selamat Datang</label>
                            <textarea class="form-control" name="pesan_selamat_datang" rows="2"
                                placeholder="Pesan yang ditampilkan di halaman warga">{{ $settings->pesan_selamat_datang }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Pesan Footer</label>
                            <textarea class="form-control" name="pesan_footer" rows="2"
                                placeholder="Pesan di bagian bawah halaman warga">{{ $settings->pesan_footer }}</textarea>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info Akses Dashboard Warga --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Akses Dashboard Warga</h3>
                </div>
                <div class="box-body">
                    <p><strong>Cara Akses Dashboard Warga:</strong></p>
                    <ol>
                        <li>Buka halaman: <code>{{ url('bank-sampah/warga') }}</code></li>
                        <li>Masukkan <strong>No Member</strong> dan <strong>No HP</strong> yang terdaftar</li>
                        <li>Sistem akan generate link UUID unik untuk nasabah tersebut</li>
                        <li>Link bisa dibagikan ke nasabah (via WhatsApp, SMS, dll)</li>
                        <li>Nasabah akses link → masukkan PIN (jika diaktifkan) → lihat saldo & riwayat</li>
                    </ol>

                    <p style="margin-top: 15px;"><strong>Keamanan:</strong></p>
                    <ul>
                        <li>✅ Menggunakan <strong>UUID</strong> (bukan ID numerik) → tidak bisa ditebak</li>
                        <li>✅ <strong>PIN</strong> opsional → lapisan keamanan tambahan</li>
                        <li>✅ <strong>Rate limiting</strong> → cegah brute force</li>
                        <li>✅ Link bisa di-<strong>reset</strong> kapan saja → jika bocor</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
