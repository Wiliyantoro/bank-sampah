@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-exchange"></i> Riwayat Transaksi</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Transaksi</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/transaksi/setor') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-plus-circle"></i> Setor Sampah
                        </a>
                        <a href="{{ url('bank-sampah/transaksi/tarik') }}" class="btn btn-danger btn-sm">
                            <i class="fa fa-minus-circle"></i> Tarik Saldo
                        </a>
                    </div>
                </div>

                <div class="box-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Filter --}}
                    <form action="{{ url('bank-sampah/transaksi') }}" method="GET" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari no transaksi/nama..." value="{{ request('cari') }}">
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <select name="tipe" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="setor" {{ request('tipe') == 'setor' ? 'selected' : '' }}>Setor</option>
                                <option value="tarik" {{ request('tipe') == 'tarik' ? 'selected' : '' }}>Tarik</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}" placeholder="Dari tanggal">
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}" placeholder="Sampai tanggal">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-left: 5px;"><i class="fa fa-search"></i> Cari</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>No Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Nasabah</th>
                                    <th>Lokasi</th>
                                    <th>Tipe</th>
                                    <th class="text-right">Total</th>
                                    <th>Poin</th>
                                    <th>Operator</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transaksi as $trx)
                                <tr>
                                    <td>{{ ($transaksi->currentPage() - 1) * $transaksi->perPage() + $loop->iteration }}</td>
                                    <td><strong>{{ $trx->no_transaksi }}</strong></td>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($trx->member)
                                            <a href="{{ url('bank-sampah/member/detail', $trx->member->id) }}">
                                                {{ $trx->member->nama }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $trx->lokasi->nama ?? '-' }}</td>
                                    <td>{!! tipe_transaksi_label($trx->tipe) !!}</td>
                                    <td class="text-right text-bold">
                                        {{ $trx->tipe === 'setor' ? '+' : '-' }}{{ format_rupiah($trx->total) }}
                                    </td>
                                    <td>
                                        @if($trx->poin > 0)
                                            <span class="label label-warning">+{{ $trx->poin }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $trx->user_id ?? '-' }}</td>
                                    <td>
                                        <a href="{{ url('bank-sampah/transaksi/detail', $trx->id) }}" class="btn btn-info btn-xs" title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ url('bank-sampah/transaksi/struk', $trx->id) }}" class="btn btn-default btn-xs" title="Cetak Struk" target="_blank">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada transaksi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        {{ $transaksi->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
