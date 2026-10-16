@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-file-pdf-o"></i> Laporan Bank Sampah</h1>
</section>

<section class="content">
    {{-- Filter Bulan/Tahun --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filter Periode</h3>
                </div>
                <div class="box-body">
                    <form action="{{ url('bank-sampah/laporan') }}" method="GET" class="form-inline">
                        <div class="form-group">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                                        {{ $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group" style="margin-left: 10px;">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="{{ url('bank-sampah/laporan/export-pdf?' . http_build_query(['bulan' => $bulan, 'tahun' => $tahun])) }}" class="btn btn-danger" style="margin-left: 10px;" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </a>
                        <a href="{{ url('bank-sampah/laporan/export-csv?' . http_build_query(['bulan' => $bulan, 'tahun' => $tahun])) }}" class="btn btn-success" style="margin-left: 5px;">
                            <i class="fa fa-download"></i> Export CSV
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="row">
        <div class="col-md-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL SETOR</span>
                    <span class="info-box-number">{{ $totalSetor->jumlah }} transaksi</span>
                    <span>{{ format_rupiah($totalSetor->nominal) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL TARIK</span>
                    <span class="info-box-number">{{ $totalTarik->jumlah }} transaksi</span>
                    <span>{{ format_rupiah($totalTarik->nominal) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekap Harian --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Rekap Transaksi Harian</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-striped">
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
                                <td class="text-right text-success">{{ format_rupiah($h['setor_nominal']) }}</td>
                                <td class="text-center">{{ $h['tarik_jumlah'] }}</td>
                                <td class="text-right text-danger">{{ format_rupiah($h['tarik_nominal']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekap Jenis Sampah --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Rekap Jenis Sampah</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Jenis Sampah</th>
                                <th>Kategori</th>
                                <th class="text-right">Total Berat</th>
                                <th class="text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapSampah as $rs)
                            <tr>
                                <td>{{ $rs->sampah->nama ?? '-' }}</td>
                                <td>{{ $rs->sampah->kategori->nama ?? '-' }}</td>
                                <td class="text-right">{{ format_berat($rs->total_berat) }}</td>
                                <td class="text-right">{{ format_rupiah($rs->total_nilai) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
