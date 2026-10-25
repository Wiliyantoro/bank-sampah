@extends('admin.layouts.index')

@section('title')
    Dashboard Bank Sampah
@endsection

@section('content')
    <!-- Info boxes -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Nasabah</span>
                    <span class="info-box-number">{{ $totalMember ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-arrow-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Setor</span>
                    <span class="info-box-number">{{ $totalSetor ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Tarik</span>
                    <span class="info-box-number">{{ $totalTarik ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-recycle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Sampah (kg)</span>
                    <span class="info-box-number">{{ $totalBerat ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Transaksi Terakhir -->
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Transaksi Terakhir</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/transaksi') }}" class="btn btn-sm btn-default">Lihat Semua</a>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nasabah</th>
                                <th>Tipe</th>
                                <th>Berat (kg)</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($transaksiTerakhir) && count($transaksiTerakhir) > 0)
                                @foreach($transaksiTerakhir as $trx)
                                <tr>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $trx->member->nama ?? '-' }}</td>
                                    <td>{!! bank_sampah_tipe_transaksi_label($trx->tipe) !!}</td>
                                    <td>{{ bank_sampah_format_berat($trx->total_berat) }}</td>
                                    <td>{{ bank_sampah_format_rupiah($trx->total_harga) }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada transaksi</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Status</h3>
                </div>
                <div class="box-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <b>Total Lokasi (TPS)</b>
                            <span class="pull-right badge bg-aqua">{{ $totalLokasi ?? 0 }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Jenis Sampah</b>
                            <span class="pull-right badge bg-green">{{ $totalSampahJenis ?? 0 }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Poin Diberikan</b>
                            <span class="pull-right badge bg-yellow">{{ $totalPoin ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Akses Cepat</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('bank-sampah/transaksi/setor') }}" class="btn btn-success btn-block">
                        <i class="fa fa-plus"></i> Setor Sampah
                    </a>
                    <a href="{{ url('bank-sampah/transaksi/tarik') }}" class="btn btn-danger btn-block">
                        <i class="fa fa-minus"></i> Tarik Saldo
                    </a>
                    <a href="{{ url('bank-sampah/member/tambah') }}" class="btn btn-primary btn-block">
                        <i class="fa fa-user-plus"></i> Tambah Nasabah
                    </a>
                    <a href="{{ url('bank-sampah/laporan') }}" class="btn btn-default btn-block">
                        <i class="fa fa-file-pdf-o"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
