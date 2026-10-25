@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-user"></i> Detail Nasabah</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Data Nasabah</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/member/ubah', $member->id) }}" class="btn btn-warning btn-xs">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="{{ url('bank-sampah/member/kartu', $member->id) }}" class="btn btn-success btn-xs" target="_blank">
                            <i class="fa fa-id-card"></i> Cetak Kartu
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">No Member</th>
                            <td><span class="label label-info" style="font-size: 14px;">{{ $member->no_member }}</span></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $member->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td>{{ $member->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $member->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi / TPS</th>
                            <td>{{ $member->lokasi->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{!! bank_sampah_status_aktif_label($member->aktif) !!}</td>
                        </tr>
                        <tr>
                            <th>Terdaftar</th>
                            <td>{{ $member->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Saldo & Poin</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">SALDO</span>
                                    <span class="info-box-number" style="font-size: 20px;">{{ bank_sampah_format_rupiah($member->saldo) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">POIN</span>
                                    <span class="info-box-number" style="font-size: 20px;">{{ $member->poin }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <a href="{{ url('bank-sampah/transaksi/setor?' . http_build_query(['member_id' => $member->id])) }}" class="btn btn-success">
                            <i class="fa fa-plus-circle"></i> Setor Sampah
                        </a>
                        <a href="{{ url('bank-sampah/transaksi/tarik?' . http_build_query(['member_id' => $member->id])) }}" class="btn btn-danger">
                            <i class="fa fa-minus-circle"></i> Tarik Saldo
                        </a>
                        <a href="{{ url('bank-sampah/reward/riwayat', $member->id) }}" class="btn btn-warning">
                            <i class="fa fa-gift"></i> Riwayat Poin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Transaksi --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Transaksi</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Detail</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatTransaksi as $trx)
                            <tr>
                                <td><strong>{{ $trx->no_transaksi }}</strong></td>
                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                <td>{!! bank_sampah_tipe_transaksi_label($trx->tipe) !!}</td>
                                <td>
                                    @if($trx->tipe === 'setor' && $trx->detail->count() > 0)
                                        @foreach($trx->detail as $d)
                                            <span class="label label-default">{{ $d->sampah->nama ?? '-' }}: {{ number_format($d->berat, 2) }}kg</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right text-bold">
                                    {{ $trx->tipe === 'setor' ? '+' : '-' }}{{ bank_sampah_format_rupiah($trx->total) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('bank-sampah/transaksi/detail', $trx->id) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-center">
                    {{ $riwayatTransaksi->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
