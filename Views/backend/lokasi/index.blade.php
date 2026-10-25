@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-map-marker"></i> Lokasi / TPS</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Lokasi / TPS</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/lokasi/tambah') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Lokasi
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ url('bank-sampah/lokasi') }}" method="GET" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama/kode lokasi..." value="{{ request('cari') }}">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-left: 5px;"><i class="fa fa-search"></i> Cari</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode</th>
                                    <th>Nama Lokasi</th>
                                    <th>Alamat</th>
                                    <th>Penanggung Jawab</th>
                                    <th>No HP</th>
                                    <th class="text-center">Nasabah</th>
                                    <th class="text-center">Transaksi</th>
                                    <th width="100">Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lokasi as $item)
                                <tr>
                                    <td>{{ ($lokasi->currentPage() - 1) * $lokasi->perPage() + $loop->iteration }}</td>
                                    <td><span class="label label-info">{{ $item->kode }}</span></td>
                                    <td>
                                        <strong>{{ $item->nama }}</strong>
                                        @if($item->is_default)
                                            <span class="label label-default" title="Data bawaan sistem">
                                                <i class="fa fa-lock"></i> Bawaan
                                            </span>
                                        @else
                                            <span class="label label-success" title="Data custom desa">
                                                <i class="fa fa-pencil"></i> Desa
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($item->alamat ?? '-', 30) }}</td>
                                    <td>{{ $item->penanggung_jawab ?? '-' }}</td>
                                    <td>{{ $item->no_hp ?? '-' }}</td>
                                    <td class="text-center">{{ $item->member_count ?? $item->member()->count() }}</td>
                                    <td class="text-center">{{ $item->transaksi_count ?? $item->transaksi()->count() }}</td>
                                    <td class="text-center">{!! bank_sampah_status_aktif_label($item->aktif) !!}</td>
                                    <td>
                                        <a href="{{ url('bank-sampah/lokasi/ubah', $item->id) }}" class="btn btn-warning btn-xs" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(!$item->is_default)
                                            <form action="{{ url('bank-sampah/lokasi/hapus', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus lokasi ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="btn btn-default btn-xs" title="Data bawaan tidak bisa dihapus" style="opacity: 0.5; cursor: not-allowed;">
                                                <i class="fa fa-lock"></i>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada data lokasi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        {{ $lokasi->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
