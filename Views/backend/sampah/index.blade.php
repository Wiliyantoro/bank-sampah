@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-cubes"></i> Jenis Sampah</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Jenis Sampah</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/jenis-sampah/tambah') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Jenis Sampah
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

                    {{-- Filter --}}
                    <form action="{{ url('bank-sampah/jenis-sampah') }}" method="GET" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama sampah..." value="{{ request('cari') }}">
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <select name="kategori_id" class="form-control">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriList as $k)
                                    <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-left: 5px;"><i class="fa fa-search"></i> Cari</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Sampah</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Harga Beli</th>
                                    <th class="text-right">Harga Jual</th>
                                    <th class="text-right">Stok</th>
                                    <th width="100">Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sampah as $item)
                                <tr>
                                    <td>{{ ($sampah->currentPage() - 1) * $sampah->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $item->nama }}</strong>
                                        @if($item->is_default)
                                            <span class="label label-default" title="Data bawaan sistem">
                                                <i class="fa fa-lock"></i>
                                            </span>
                                        @else
                                            <span class="label label-success" title="Data custom desa">
                                                <i class="fa fa-pencil"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="label label-info">{{ $item->kategori->nama ?? '-' }}</span></td>
                                    <td>{{ $item->satuan }}</td>
                                    <td class="text-right text-success">{{ format_rupiah($item->harga_beli) }}/{{ $item->satuan }}</td>
                                    <td class="text-right">{{ format_rupiah($item->harga_jual) }}/{{ $item->satuan }}</td>
                                    <td class="text-right">{{ number_format($item->stok, 2, ',', '.') }} {{ $item->satuan }}</td>
                                    <td class="text-center">{!! status_aktif_label($item->aktif) !!}</td>
                                    <td>
                                        <a href="{{ url('bank-sampah/jenis-sampah/ubah', $item->id) }}" class="btn btn-warning btn-xs" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(!$item->is_default)
                                            {{-- Hanya data custom desa yang bisa dihapus --}}
                                            <form action="{{ url('bank-sampah/jenis-sampah/hapus', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus jenis sampah ini?')">
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
                                    <td colspan="9" class="text-center">Belum ada data jenis sampah</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        {{ $sampah->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
