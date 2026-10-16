@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-users"></i> Nasabah / Member</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Nasabah</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/member/tambah') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Nasabah
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
                    <form action="{{ url('bank-sampah/member') }}" method="GET" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari nama/no member/HP..." value="{{ request('cari') }}">
                        </div>
                        <div class="form-group" style="margin-left: 5px;">
                            <select name="lokasi_id" class="form-control">
                                <option value="">Semua Lokasi</option>
                                @foreach($lokasiList as $l)
                                    <option value="{{ $l->id }}" {{ request('lokasi_id') == $l->id ? 'selected' : '' }}>{{ $l->nama }}</option>
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
                                    <th>No Member</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No HP</th>
                                    <th>Lokasi</th>
                                    <th class="text-right">Saldo</th>
                                    <th class="text-center">Poin</th>
                                    <th width="80">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($member as $item)
                                <tr>
                                    <td>{{ ($member->currentPage() - 1) * $member->perPage() + $loop->iteration }}</td>
                                    <td><span class="label label-info">{{ $item->no_member }}</span></td>
                                    <td><strong>{{ $item->nama }}</strong></td>
                                    <td>{{ Str::limit($item->alamat ?? '-', 30) }}</td>
                                    <td>{{ $item->no_hp ?? '-' }}</td>
                                    <td>{{ $item->lokasi->nama ?? '-' }}</td>
                                    <td class="text-right text-success text-bold">{{ format_rupiah($item->saldo) }}</td>
                                    <td class="text-center"><span class="label label-warning">{{ $item->poin }} poin</span></td>
                                    <td class="text-center">{!! status_aktif_label($item->aktif) !!}</td>
                                    <td>
                                        <a href="{{ url('bank-sampah/member/detail', $item->id) }}" class="btn btn-info btn-xs" title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ url('bank-sampah/member/ubah', $item->id) }}" class="btn btn-warning btn-xs" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="{{ url('bank-sampah/member/kartu', $item->id) }}" class="btn btn-success btn-xs" title="Cetak Kartu" target="_blank">
                                            <i class="fa fa-id-card"></i>
                                        </a>
                                        <form action="{{ url('bank-sampah/member/hapus', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus nasabah ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada data nasabah</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        {{ $member->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
