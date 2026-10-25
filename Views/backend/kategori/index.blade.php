@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-tags"></i> Kategori Sampah</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Kategori</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/kategori-sampah/form') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah Kategori
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Keterangan</th>
                                    <th width="100">Jumlah Sampah</th>
                                    <th width="100">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategori as $item)
                                <tr>
                                    <td>{{ ($kategori->currentPage() - 1) * $kategori->perPage() + $loop->iteration }}</td>
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
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td class="text-center">{{ $item->sampah()->count() }}</td>
                                    <td class="text-center">{!! bank_sampah_status_aktif_label($item->aktif) !!}</td>
                                    <td>
                                        <a href="{{ url('bank-sampah/kategori-sampah/form', $item->id) }}" class="btn btn-warning btn-xs" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(!$item->is_default)
                                            {{-- Hanya data custom desa yang bisa dihapus --}}
                                            {!! form_open(url('bank-sampah/kategori-sampah/delete', $item->id), 'style="display:inline" onsubmit="return confirm('Yakin hapus kategori ini?')"') !!}
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
                                    <td colspan="6" class="text-center">Belum ada data kategori</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        {{ $kategori->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
