@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1><i class="fa fa-tags"></i> Kategori Sampah</h1>
@endsection

@section('breadcrumb')
    <li class="active">Kategori Sampah</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        <div class="box-header with-border">
            @if (can('u'))
                <a href="{{ url('bank-sampah/kategori-sampah/form') }}" class="btn btn-social btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Kategori</a>
            @endif
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th>NAMA KATEGORI</th>
                            <th>KETERANGAN</th>
                            <th class="padat">SUMBER</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('bank-sampah/kategori-sampah') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'nama', name: 'nama' },
                    { data: 'keterangan', name: 'keterangan', searchable: false, orderable: false, defaultContent: '-' },
                    { data: 'is_default', name: 'is_default', class: 'padat', searchable: false, orderable: false },
                ],
            });

            // Hapus (POST + token sidcsrf inti)
            $(document).on('click', '.btn-hapus', function(e) {
                e.preventDefault();
                var url = $(this).data('href');
                if (! confirm('Yakin hapus data ini?')) return;
                var data = {};
                data[csrfParam] = getCsrfToken();
                $.post(url, data).done(function() {
                    TableData.ajax.reload();
                }).fail(function() {
                    alert('Gagal menghapus data');
                });
            });
        });
    </script>
@endpush
